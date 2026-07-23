<?php

namespace App\Http\Controllers;

use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

class MantenimientoController extends Controller
{
    use RegistraBitacora;

    public function index(): View
    {
        $checks = $this->obtenerChecks();
        $diagnostico = [
            'app' => [
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Ambiente' => config('app.env'),
                'Errores detallados' => config('app.debug') ? 'Activo' : 'Desactivado',
                'URL' => config('app.url'),
                'Zona horaria' => config('app.timezone'),
            ],
            'base_datos' => $this->estadoBaseDatos(),
            'archivos' => [
                'Acceso a archivos públicos' => $this->storageLinkActivo() ? 'Disponible' : 'No detectado',
                'Archivos públicos' => $this->formatearBytes($this->tamanoDirectorio(storage_path('app/public'))),
                'Documentos privados' => $this->formatearBytes($this->tamanoDirectorio(storage_path('app/private'))),
                'Registros técnicos' => $this->formatearBytes($this->tamanoDirectorio(storage_path('logs'))),
                'Carpeta de configuración' => is_writable(base_path('bootstrap/cache')) ? 'Disponible' : 'Sin permisos',
                'Carpeta de archivos' => is_writable(storage_path()) ? 'Disponible' : 'Sin permisos',
            ],
            'logs' => $this->estadoLogs(),
            'migraciones' => $this->estadoMigraciones(),
        ];

        return view('sistema.mantenimiento', compact('checks', 'diagnostico'));
    }

    public function limpiarCache(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');

            $this->bitacora(
                'Limpiar Caché del Sistema',
                'Se ejecutó optimize:clear desde el módulo de mantenimiento.',
                'Mantenimiento'
            );

            return back()->with('success', 'La configuración del sistema se actualizó correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al limpiar caché desde mantenimiento: '.$e->getMessage());

            return back()->with('error', 'No se pudo actualizar la configuración. Revisa permisos del servidor o solicita apoyo técnico.');
        }
    }

    public function crearStorageLink(): RedirectResponse
    {
        try {
            Artisan::call('storage:link');

            $this->bitacora(
                'Crear Storage Link',
                'Se ejecutó storage:link desde el módulo de mantenimiento.',
                'Mantenimiento'
            );

            return back()->with('success', 'Se verificó el acceso a archivos públicos del sistema.');
        } catch (Throwable $e) {
            Log::error('Error al crear storage link desde mantenimiento: '.$e->getMessage());

            return back()->with('error', 'No se pudo verificar el acceso a archivos públicos. Revisa permisos del servidor o solicita apoyo técnico.');
        }
    }

    public function limpiarLogs(): RedirectResponse
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (File::exists($logPath)) {
                File::put($logPath, '');
            }

            $this->bitacora(
                'Limpiar Logs del Sistema',
                'Se vació el archivo storage/logs/laravel.log desde mantenimiento.',
                'Mantenimiento'
            );

            return back()->with('success', 'El registro técnico principal se vació correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al limpiar logs desde mantenimiento: '.$e->getMessage());

            return back()->with('error', 'No se pudo vaciar el registro técnico principal. Revisa permisos del servidor.');
        }
    }

    public function descargarBackupBaseDatos()
    {
        try {
            $backupDir = storage_path('app/backups');
            File::ensureDirectoryExists($backupDir);

            $database = config('database.connections.'.config('database.default').'.database') ?: 'database';
            $filename = 'backup_bd_'.Str::slug(config('app.name', 'idej-sys')).'_'.now()->format('Ymd_His').'.sql';
            $path = $backupDir.DIRECTORY_SEPARATOR.$filename;

            $this->generarDumpSql($path, $database);

            $this->bitacora(
                'Descargar Backup de Base de Datos',
                'Se generó y descargó un respaldo SQL desde mantenimiento.',
                'Mantenimiento'
            );

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Error al generar backup de base de datos: '.$e->getMessage());

            return back()->with('error', 'No se pudo generar el respaldo de base de datos: '.$e->getMessage());
        }
    }

    public function descargarBackupArchivos()
    {
        try {
            if (! class_exists(ZipArchive::class)) {
                return back()->with('error', 'La extensión PHP ZipArchive no está disponible. Activa ext-zip para generar respaldos de archivos.');
            }

            $backupDir = storage_path('app/backups');
            File::ensureDirectoryExists($backupDir);

            $filename = 'backup_archivos_'.Str::slug(config('app.name', 'idej-sys')).'_'.now()->format('Ymd_His').'.zip';
            $path = $backupDir.DIRECTORY_SEPARATOR.$filename;

            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'No se pudo crear el archivo ZIP de respaldo.');
            }

            $agregados = 0;
            $origenes = [
                'public' => storage_path('app/public'),
                'private' => storage_path('app/private'),
            ];

            foreach ($origenes as $prefijo => $source) {
                if (! File::exists($source)) {
                    File::ensureDirectoryExists($source);
                    continue;
                }

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = str_replace('\\', '/', Str::after($filePath, $source.DIRECTORY_SEPARATOR));
                    $zipPath = trim($prefijo.'/'.$relativePath, '/');

                    if (str_starts_with($zipPath, 'private/backups/') || str_starts_with($zipPath, 'public/backups/')) {
                        continue;
                    }

                    if ($file->isDir()) {
                        $zip->addEmptyDir($zipPath);
                    } else {
                        $zip->addFile($filePath, $zipPath);
                        $agregados++;
                    }
                }
            }

            if ($agregados === 0) {
                $zip->addFromString('LEEME.txt', 'No había archivos cargados al momento de generar este respaldo.');
            }

            $zip->close();

            $this->bitacora(
                'Descargar Backup de Archivos',
                'Se generó y descargó un ZIP con archivos públicos y privados cargados al sistema.',
                'Mantenimiento'
            );

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Error al generar backup de archivos: '.$e->getMessage());

            return back()->with('error', 'No se pudo generar el respaldo de archivos: '.$e->getMessage());
        }
    }

    private function generarDumpSql(string $path, string $database): void
    {
        $pdo = DB::connection()->getPdo();
        $connection = config('database.default');

        if (config("database.connections.$connection.driver") !== 'mysql') {
            throw new \RuntimeException('El respaldo SQL integrado actualmente está preparado para MySQL/MariaDB.');
        }

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0] ?? null)
            ->filter()
            ->values();

        $handle = fopen($path, 'w');
        if (! $handle) {
            throw new \RuntimeException('No se pudo abrir el archivo de respaldo para escritura.');
        }

        fwrite($handle, "-- Respaldo SQL generado por IDEJ-SYS\n");
        fwrite($handle, '-- Fecha: '.now()->format('Y-m-d H:i:s')."\n");
        fwrite($handle, '-- Base de datos: '.$database."\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        foreach ($tables as $table) {
            $tableEscaped = $this->quoteIdentifier($table);
            $createResult = DB::select("SHOW CREATE TABLE $tableEscaped");
            $createRow = array_values((array) $createResult[0]);
            $createSql = $createRow[1] ?? null;

            if (! $createSql) {
                continue;
            }

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Estructura de tabla $tableEscaped\n");
            fwrite($handle, "DROP TABLE IF EXISTS $tableEscaped;\n");
            fwrite($handle, $createSql.";\n\n");

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                fwrite($handle, "-- Tabla sin registros\n\n");
                continue;
            }

            fwrite($handle, "-- Datos de tabla $tableEscaped\n");
            foreach ($rows as $row) {
                $data = (array) $row;
                $columns = collect(array_keys($data))
                    ->map(fn ($column) => $this->quoteIdentifier($column))
                    ->implode(', ');

                $values = collect(array_values($data))
                    ->map(function ($value) use ($pdo) {
                        if ($value === null) {
                            return 'NULL';
                        }

                        if (is_bool($value)) {
                            return $value ? '1' : '0';
                        }

                        return $pdo->quote((string) $value);
                    })
                    ->implode(', ');

                fwrite($handle, "INSERT INTO $tableEscaped ($columns) VALUES ($values);\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function obtenerChecks(): array
    {
        return [
            [
                'titulo' => 'Errores detallados desactivados en producción',
                'estado' => ! config('app.debug'),
                'detalle' => config('app.debug') ? 'Actualmente muestra detalles de errores. Correcto solo en desarrollo local.' : 'Correcto para producción.',
            ],
            [
                'titulo' => 'Llave de seguridad configurada',
                'estado' => filled(config('app.key')),
                'detalle' => filled(config('app.key')) ? 'Llave de seguridad detectada.' : 'Genera la llave de seguridad antes de usar el sistema.',
            ],
            [
                'titulo' => 'Conexión a base de datos',
                'estado' => $this->baseDatosDisponible(),
                'detalle' => $this->baseDatosDisponible() ? 'La conexión responde correctamente.' : 'No se pudo conectar a la base de datos.',
            ],
            [
                'titulo' => 'Acceso a archivos públicos disponible',
                'estado' => $this->storageLinkActivo(),
                'detalle' => $this->storageLinkActivo() ? 'Los archivos públicos necesarios se pueden mostrar.' : 'Usa la acción Reparar acceso a archivos públicos.',
            ],
            [
                'titulo' => 'Carpeta de archivos disponible',
                'estado' => is_writable(storage_path()),
                'detalle' => is_writable(storage_path()) ? 'Laravel puede escribir cachés, sesiones y archivos.' : 'Revisa permisos de storage/.',
            ],
            [
                'titulo' => 'Carpeta de configuración disponible',
                'estado' => is_writable(base_path('bootstrap/cache')),
                'detalle' => is_writable(base_path('bootstrap/cache')) ? 'El sistema puede guardar configuración optimizada.' : 'Revisa permisos de la carpeta de configuración.',
            ],
        ];
    }

    private function estadoBaseDatos(): array
    {
        try {
            $connection = config('database.default');
            $database = config("database.connections.$connection.database") ?? 'No definida';
            $tables = collect(DB::select('SHOW TABLES'))->count();

            return [
                'Conexión' => $connection,
                'Base de datos' => $database,
                'Tablas detectadas' => (string) $tables,
                'Estado' => 'Disponible',
            ];
        } catch (Throwable $e) {
            return [
                'Estado' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    private function estadoLogs(): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return [
                'laravel.log' => 'No existe',
                'Tamaño' => '0 B',
                'Última modificación' => 'Sin registro',
            ];
        }

        return [
            'laravel.log' => 'Disponible',
            'Tamaño' => $this->formatearBytes(File::size($logPath)),
            'Última modificación' => date('d/m/Y H:i', File::lastModified($logPath)),
        ];
    }

    private function estadoMigraciones(): array
    {
        try {
            Artisan::call('migrate:status');
            $output = trim(Artisan::output());
            $pendientes = Str::contains($output, 'Pending');

            return [
                'Estado' => $pendientes ? 'Hay migraciones pendientes' : 'Sin pendientes detectadas',
                'Detalle' => Str::limit($output ?: 'Sin salida del comando migrate:status.', 900),
            ];
        } catch (Throwable $e) {
            return [
                'Estado' => 'No se pudo consultar',
                'Detalle' => $e->getMessage(),
            ];
        }
    }

    private function baseDatosDisponible(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function storageLinkActivo(): bool
    {
        $publicStorage = public_path('storage');

        return File::exists($publicStorage) && (is_link($publicStorage) || File::isDirectory($publicStorage));
    }

    private function tamanoDirectorio(string $path): int
    {
        if (! File::exists($path)) {
            return 0;
        }

        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatearBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
