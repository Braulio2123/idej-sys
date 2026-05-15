<?php

namespace App\Console\Commands;

use App\Models\DocumentoAlumno;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrarDocumentosAlumnosPrivados extends Command
{
    /**
     * Migra documentos de alumnos cargados antes de la Fase 24 desde storage público a storage privado.
     */
    protected $signature = 'idej:migrar-documentos-alumnos-privados {--eliminar-publicos : Elimina el archivo público después de copiarlo al disco privado}';

    protected $description = 'Copia los documentos de alumnos existentes de storage/app/public a storage/app/private para protegerlos por controlador.';

    public function handle(): int
    {
        $this->info('Iniciando migración de documentos de alumnos a almacenamiento privado...');

        $documentos = DocumentoAlumno::withTrashed()
            ->whereNotNull('archivo_path')
            ->get();

        if ($documentos->isEmpty()) {
            $this->warn('No hay documentos con archivo_path para migrar.');
            return self::SUCCESS;
        }

        $copiados = 0;
        $yaPrivados = 0;
        $noEncontrados = 0;
        $eliminadosPublicos = 0;

        foreach ($documentos as $documento) {
            $path = $documento->archivo_path;

            if (Storage::disk('local')->exists($path)) {
                $yaPrivados++;

                if ($this->option('eliminar-publicos') && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    $eliminadosPublicos++;
                }

                continue;
            }

            if (! Storage::disk('public')->exists($path)) {
                $noEncontrados++;
                $this->warn("Archivo no encontrado: {$path}");
                continue;
            }

            Storage::disk('local')->put($path, Storage::disk('public')->get($path));
            $copiados++;

            if ($this->option('eliminar-publicos')) {
                Storage::disk('public')->delete($path);
                $eliminadosPublicos++;
            }
        }

        $this->info('Migración finalizada.');
        $this->line("Copiados a privado: {$copiados}");
        $this->line("Ya estaban en privado: {$yaPrivados}");
        $this->line("No encontrados: {$noEncontrados}");
        $this->line("Archivos públicos eliminados: {$eliminadosPublicos}");

        return self::SUCCESS;
    }
}
