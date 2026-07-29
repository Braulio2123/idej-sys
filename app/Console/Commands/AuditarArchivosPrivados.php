<?php

namespace App\Console\Commands;

use App\Models\Docente;
use App\Models\DocumentoAlumno;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\SolicitudPagoDocente;
use App\Services\PrivateFileService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AuditarArchivosPrivados extends Command
{
    protected $signature = 'idej:auditar-archivos-privados
        {--migrar-publicos : Copia al disco privado, verifica y retira las copias públicas heredadas}
        {--mostrar-huerfanos : Muestra cada archivo privado que no tiene referencia en la base de datos}';

    protected $description = 'Audita documentos sensibles, detecta faltantes, copias públicas heredadas y archivos privados sin referencia.';

    private array $referencias = [];
    private int $privados = 0;
    private int $publicos = 0;
    private int $migrados = 0;
    private int $faltantes = 0;
    private int $errores = 0;

    public function __construct(private readonly PrivateFileService $privateFiles)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Auditoría de almacenamiento privado IDEJ-SYS');
        $this->line('No se eliminarán archivos huérfanos ni referencias faltantes.');

        $this->auditarModelos();
        $huerfanos = $this->detectarHuerfanos();

        $this->newLine();
        $this->table(['Resultado', 'Cantidad'], [
            ['Referencias privadas válidas', $this->privados],
            ['Referencias encontradas en disco público', $this->publicos],
            ['Archivos migrados y verificados', $this->migrados],
            ['Referencias sin archivo', $this->faltantes],
            ['Errores durante la auditoría', $this->errores],
            ['Archivos privados sin referencia', count($huerfanos)],
        ]);

        if ($huerfanos !== []) {
            $this->warn('Se encontraron archivos privados sin referencia. Revísalos antes de decidir cualquier eliminación.');

            if ($this->option('mostrar-huerfanos')) {
                foreach ($huerfanos as $path) {
                    $this->line('  - '.$path);
                }
            }
        }

        if ($this->faltantes > 0 || $this->errores > 0) {
            $this->error('La auditoría terminó con incidencias que requieren revisión.');

            return self::FAILURE;
        }

        $this->info('La auditoría terminó sin referencias faltantes.');

        return self::SUCCESS;
    }

    private function auditarModelos(): void
    {
        if (Schema::hasTable('documentos_alumnos')) {
            DocumentoAlumno::withTrashed()->whereNotNull('archivo_path')->orderBy('id')->chunkById(200, function ($documentos) {
                foreach ($documentos as $documento) {
                    $this->auditarReferencia($documento, 'archivo_path', 'archivo_sha256', 'Documento de alumno');
                }
            });
        }

        if (Schema::hasTable('pagos')) {
            Pago::whereNotNull('archivo_comprobante')->orderBy('id')->chunkById(200, function ($pagos) {
                foreach ($pagos as $pago) {
                    $this->auditarReferencia($pago, 'archivo_comprobante', 'archivo_comprobante_sha256', 'Comprobante de pago');
                }
            });
        }

        if (Schema::hasTable('solicitudes_pago_docentes')) {
            SolicitudPagoDocente::whereNotNull('comprobante_pago_path')->orderBy('id')->chunkById(200, function ($solicitudes) {
                foreach ($solicitudes as $solicitud) {
                    $this->auditarReferencia($solicitud, 'comprobante_pago_path', 'comprobante_pago_sha256', 'Comprobante de pago docente');
                }
            });
        }

        if (Schema::hasTable('movimientos_caja')) {
            MovimientoCaja::whereNotNull('comprobante_path')->orderBy('id')->chunkById(200, function ($movimientos) {
                foreach ($movimientos as $movimiento) {
                    $this->auditarReferencia($movimiento, 'comprobante_path', 'comprobante_sha256', 'Comprobante de movimiento de caja');
                }
            });
        }

        if (Schema::hasTable('docentes')) {
            Docente::orderBy('id')->chunkById(200, function ($docentes) {
                foreach ($docentes as $docente) {
                    foreach ([
                        ['curriculum_path', 'curriculum_sha256', 'Curriculum docente'],
                        ['titulo_cedula_path', 'titulo_cedula_sha256', 'Título y cédula docente'],
                        ['constancia_fiscal_path', 'constancia_fiscal_sha256', 'Constancia fiscal docente'],
                    ] as [$pathField, $hashField, $etiqueta]) {
                        if ($docente->{$pathField}) {
                            $this->auditarReferencia($docente, $pathField, $hashField, $etiqueta);
                        }
                    }
                }
            });
        }
    }

    private function auditarReferencia(Model $modelo, string $pathField, string $hashField, string $etiqueta): void
    {
        $path = trim((string) $modelo->{$pathField});

        if ($path === '') {
            return;
        }

        $this->referencias[$path] = true;
        $estabaEnPrivado = Storage::disk('local')->exists($path);
        $estabaEnPublico = Storage::disk('public')->exists($path);

        if ($estabaEnPrivado) {
            $this->privados++;
        } elseif ($estabaEnPublico) {
            $this->publicos++;
        }

        try {
            if ($this->option('migrar-publicos')) {
                $privatePath = $this->privateFiles->ensurePrivate($path);

                if ($privatePath && ! $estabaEnPrivado && $estabaEnPublico) {
                    $this->migrados++;
                }
            } else {
                $privatePath = $estabaEnPrivado ? $path : null;
            }

            if (! $privatePath) {
                if (! $estabaEnPublico || $this->option('migrar-publicos')) {
                    $this->faltantes++;
                    $this->warn("{$etiqueta} #{$modelo->getKey()}: archivo no encontrado ({$path}).");
                }

                return;
            }

            $hash = $this->privateFiles->sha256($privatePath);
            $hashRegistrado = (string) ($modelo->{$hashField} ?? '');

            if ($hashRegistrado !== '' && (! $hash || ! hash_equals($hashRegistrado, $hash))) {
                $this->errores++;
                $this->error("{$etiqueta} #{$modelo->getKey()}: la huella SHA-256 no coincide.");

                return;
            }

            if ($hashRegistrado === '' && $hash && Schema::hasColumn($modelo->getTable(), $hashField)) {
                $modelo->forceFill([$hashField => $hash])->saveQuietly();
            }
        } catch (\Throwable $e) {
            $this->errores++;
            $this->error("{$etiqueta} #{$modelo->getKey()}: {$e->getMessage()}");
            report($e);
        }
    }

    private function detectarHuerfanos(): array
    {
        $archivos = [];

        foreach (['documentos', 'comprobantes', 'docentes'] as $directorio) {
            foreach (Storage::disk('local')->allFiles($directorio) as $path) {
                $archivos[$path] = true;
            }
        }

        return array_values(array_diff(array_keys($archivos), array_keys($this->referencias)));
    }
}
