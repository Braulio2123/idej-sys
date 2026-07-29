<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrarDocumentosAlumnosPrivados extends Command
{
    protected $signature = 'idej:migrar-documentos-alumnos-privados {--eliminar-publicos : Opción conservada por compatibilidad}';

    protected $description = 'Comando de compatibilidad. Ejecuta la auditoría y migración verificada de todos los archivos sensibles.';

    public function handle(): int
    {
        $this->warn('Este comando fue sustituido por idej:auditar-archivos-privados. Se auditarán todos los archivos sensibles, no solo documentos de alumnos.');

        return $this->call('idej:auditar-archivos-privados', [
            '--migrar-publicos' => true,
            '--mostrar-huerfanos' => true,
        ]);
    }
}
