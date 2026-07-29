<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivateFileService
{
    public const MIME_PDF = 'application/pdf';
    public const MIME_JPEG = 'image/jpeg';
    public const MIME_PNG = 'image/png';

    public const DOCUMENT_MIMES = [
        self::MIME_PDF,
        self::MIME_JPEG,
        self::MIME_PNG,
    ];

    /**
     * Guarda un archivo con nombre generado por el servidor dentro del disco privado.
     * Devuelve metadatos obtenidos del contenido real, no del nombre enviado por el navegador.
     */
    public function store(
        UploadedFile $file,
        string $directory,
        array $allowedMimeTypes = self::DOCUMENT_MIMES,
        string $input = 'archivo'
    ): array {
        $mimeType = (string) $file->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                $input => 'El contenido del archivo no corresponde a un PDF, JPG o PNG válido.',
            ]);
        }

        $extension = $this->extensionForMime($mimeType);
        $path = $file->storeAs(
            trim($directory, '/'),
            Str::uuid()->toString().'.'.$extension,
            'local'
        );

        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages([
                $input => 'No fue posible guardar el archivo. Verifica el espacio disponible y los permisos de almacenamiento.',
            ]);
        }

        $absolutePath = Storage::disk('local')->path($path);
        $sha256 = is_file($absolutePath) ? hash_file('sha256', $absolutePath) : false;

        if (! is_string($sha256) || $sha256 === '') {
            $this->delete($path);

            throw ValidationException::withMessages([
                $input => 'No fue posible comprobar la integridad del archivo guardado.',
            ]);
        }

        return [
            'path' => $path,
            'original_name' => $this->safeDownloadName(
                $file->getClientOriginalName(),
                'documento.'.$extension
            ),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => (int) $file->getSize(),
            'sha256' => $sha256,
        ];
    }

    /**
     * Garantiza que una referencia sensible exista en el disco privado.
     * Los archivos heredados del disco público se copian, verifican y después se eliminan.
     */
    public function ensurePrivate(?string $path): ?string
    {
        $path = $this->normalizeRelativePath($path);

        if (! $path) {
            return null;
        }

        if (Storage::disk('local')->exists($path)) {
            if (Storage::disk('public')->exists($path)) {
                $privateHash = hash_file('sha256', Storage::disk('local')->path($path));
                $publicHash = hash_file('sha256', Storage::disk('public')->path($path));

                if (
                    is_string($privateHash)
                    && is_string($publicHash)
                    && hash_equals($privateHash, $publicHash)
                ) {
                    Storage::disk('public')->delete($path);
                } else {
                    report(new \RuntimeException("Existe una copia pública diferente del archivo privado: {$path}"));
                }
            }

            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $source = Storage::disk('public')->readStream($path);

        if (! is_resource($source)) {
            return null;
        }

        try {
            $stored = Storage::disk('local')->writeStream($path, $source);
        } finally {
            fclose($source);
        }

        if (! $stored || ! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);

            return null;
        }

        $privateSize = Storage::disk('local')->size($path);
        $publicSize = Storage::disk('public')->size($path);

        if ($privateSize !== $publicSize) {
            Storage::disk('local')->delete($path);

            return null;
        }

        $privateHash = hash_file('sha256', Storage::disk('local')->path($path));
        $publicHash = hash_file('sha256', Storage::disk('public')->path($path));

        if (! is_string($privateHash) || ! hash_equals($privateHash, (string) $publicHash)) {
            Storage::disk('local')->delete($path);

            return null;
        }

        Storage::disk('public')->delete($path);

        return $path;
    }

    public function delete(?string $path): void
    {
        $path = $this->normalizeRelativePath($path);

        if (! $path) {
            return;
        }

        Storage::disk('local')->delete($path);
    }

    public function exists(?string $path): bool
    {
        $path = $this->normalizeRelativePath($path);

        return $path !== null && Storage::disk('local')->exists($path);
    }

    public function sha256(?string $path): ?string
    {
        $path = $this->ensurePrivate($path);

        if (! $path) {
            return null;
        }

        $hash = hash_file('sha256', Storage::disk('local')->path($path));

        return is_string($hash) && $hash !== '' ? $hash : null;
    }

    public function download(string $path, string $downloadName)
    {
        $privatePath = $this->ensurePrivate($path);

        abort_unless($privatePath, 404, 'El archivo solicitado no está disponible.');

        return Storage::disk('local')->download(
            $privatePath,
            $this->safeDownloadName($downloadName, 'documento')
        );
    }

    public function safeDownloadName(?string $name, string $fallback): string
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?: '';
        $name = preg_replace('/[^\pL\pN._()\- ]+/u', '_', $name) ?: '';
        $name = trim($name, ". \t\n\r\0\x0B");

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            $name = $fallback;
        }

        return Str::limit($name, 180, '');
    }

    private function extensionForMime(string $mimeType): string
    {
        return match ($mimeType) {
            self::MIME_PDF => 'pdf',
            self::MIME_JPEG => 'jpg',
            self::MIME_PNG => 'png',
            default => throw ValidationException::withMessages([
                'archivo' => 'El formato del archivo no está permitido.',
            ]),
        };
    }

    private function normalizeRelativePath(?string $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));

        if ($path === '' || str_starts_with($path, '/') || preg_match('/(^|\/)\.\.(\/|$)/', $path)) {
            return null;
        }

        return ltrim($path, '/');
    }
}
