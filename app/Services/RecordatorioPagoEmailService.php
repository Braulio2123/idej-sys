<?php

namespace App\Services;

use App\Mail\RecordatorioPago;
use App\Models\Alumno;
use App\Models\ConfiguracionInstitucional;
use App\Models\RecordatorioEnviado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class RecordatorioPagoEmailService
{
    public function estaActivo(): bool
    {
        return ConfiguracionInstitucional::actual()->recordatorios_pago_activos
            && (bool) config('idej_recordatorios.alumnos_adeudo.activo', true)
            && (bool) config('idej_recordatorios.canales.email.activo', true);
    }

    public function candidatos(int $limite = 100, array $filtros = []): Collection
    {
        $hoy = now()->startOfDay();
        $hasta = $hoy->copy()->addDays((int) config('idej_recordatorios.alumnos_adeudo.dias_antes_vencimiento', 3));
        $desde = $hoy->copy()->subDays((int) config('idej_recordatorios.alumnos_adeudo.dias_despues_vencimiento', 30));
        $soloVencidos = (bool) ($filtros['solo_vencidos'] ?? false);

        $cargoFiltro = function ($query) use ($desde, $hasta, $hoy, $soloVencidos) {
            $query->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->where('monto_adeudo', '>', 0)
                ->when($soloVencidos, fn ($q) => $q->whereDate('fecha_vencimiento', '<', $hoy->toDateString()),
                    fn ($q) => $q->whereBetween('fecha_vencimiento', [$desde->toDateString(), $hasta->toDateString()]))
                ->orderBy('fecha_vencimiento');
        };

        return Alumno::query()
            ->with([
                'grupo.programa',
                'cargos.concepto',
                'cargos' => $cargoFiltro,
            ])
            ->whereNotNull('correo')
            ->where('correo', '<>', '')
            ->where('estatus_academico', 'Activo')
            ->whereHas('cargos', $cargoFiltro)
            ->when($filtros['grupo_id'] ?? null, fn ($query, $grupoId) => $query->where('grupo_id', $grupoId))
            ->when($filtros['programa_id'] ?? null, function ($query, $programaId) {
                $query->whereHas('grupo', fn ($grupo) => $grupo->where('programa_id', $programaId));
            })
            ->orderBy('nombre_completo')
            ->limit(max(1, $limite))
            ->get();
    }

    public function procesar(int $limite = 100, bool $dryRun = false, array $filtros = []): array
    {
        if (! $this->estaActivo()) {
            return [
                'activo' => false,
                'enviados' => 0,
                'omitidos' => 0,
                'errores' => 0,
                'simulados' => 0,
                'mensaje' => 'Los recordatorios por correo están desactivados. Actívalos en Configuración institucional y revisa IDEJ_RECORDATORIOS_EMAIL=true en .env.',
            ];
        }

        $hoy = now()->toDateString();
        $alumnos = $this->candidatos($limite, $filtros);
        $resumen = [
            'activo' => true,
            'enviados' => 0,
            'omitidos' => 0,
            'errores' => 0,
            'simulados' => 0,
            'mensaje' => null,
        ];

        foreach ($alumnos as $alumno) {
            $hash = RecordatorioEnviado::hash('adeudo_alumno', 'email', $alumno->id, $hoy, $this->hashCargos($alumno->cargos));

            if (RecordatorioEnviado::where('referencia_hash', $hash)->exists()) {
                $resumen['omitidos']++;
                continue;
            }

            if ($dryRun) {
                $resumen['simulados']++;
                continue;
            }

            try {
                Mail::to($alumno->correo)->send(new RecordatorioPago($alumno, $alumno->cargos));

                RecordatorioEnviado::create([
                    'alumno_id' => $alumno->id,
                    'tipo' => 'adeudo_alumno',
                    'canal' => 'email',
                    'fecha_recordatorio' => $hoy,
                    'destinatario' => $alumno->correo,
                    'referencia_hash' => $hash,
                    'estatus' => 'enviado',
                    'respuesta' => 'Correo enviado por Laravel Mail.',
                    'enviado_at' => now(),
                ]);

                $resumen['enviados']++;
            } catch (\Throwable $e) {
                report($e);
                $resumen['errores']++;
            }
        }

        return $resumen;
    }

    private function hashCargos(Collection $cargos): string
    {
        return sha1($cargos->map(fn ($cargo) => $cargo->id.':'.$cargo->updated_at?->timestamp)->implode('|'));
    }
}
