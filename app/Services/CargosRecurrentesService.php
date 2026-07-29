<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CargoRecurrenteEjecucion;
use App\Models\PlanCargoRecurrente;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CargosRecurrentesService
{
    public function generar(?PlanCargoRecurrente $plan = null, ?Carbon $fechaReferencia = null, bool $dryRun = false): array
    {
        $fechaReferencia ??= now();
        $periodo = $fechaReferencia->format('Y-m');

        $planes = PlanCargoRecurrente::query()
            ->with(['concepto', 'programa', 'grupo'])
            ->when($plan, fn (Builder $query) => $query->whereKey($plan->id))
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', $fechaReferencia->copy()->endOfMonth()->toDateString())
            ->where(function (Builder $query) use ($fechaReferencia) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $fechaReferencia->copy()->startOfMonth()->toDateString());
            })
            ->orderBy('id')
            ->get();

        $resumen = [
            'periodo' => $periodo,
            'planes' => $planes->count(),
            'generados' => 0,
            'omitidos' => 0,
            'simulados' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        foreach ($planes as $planActivo) {
            $resultadoPlan = $this->generarPlan($planActivo, $fechaReferencia, $dryRun);

            foreach (['generados', 'omitidos', 'simulados', 'errores'] as $campo) {
                $resumen[$campo] += $resultadoPlan[$campo] ?? 0;
            }

            $resumen['detalles'][] = $resultadoPlan;
        }

        return $resumen;
    }

    public function generarPlan(PlanCargoRecurrente $plan, Carbon $fechaReferencia, bool $dryRun = false): array
    {
        $periodo = $fechaReferencia->format('Y-m');
        if (! $this->correspondePeriodo($plan, $fechaReferencia)) {
            return [
                'plan_id' => $plan->id,
                'plan' => $plan->nombre,
                'periodo' => $periodo,
                'fecha_vencimiento' => $this->fechaVencimiento($fechaReferencia, $plan->dia_vencimiento)->toDateString(),
                'alumnos' => 0,
                'generados' => 0,
                'omitidos' => 1,
                'simulados' => 0,
                'errores' => 0,
            ];
        }

        $fechaVencimiento = $this->fechaVencimiento($fechaReferencia, $plan->dia_vencimiento);
        $concepto = $plan->concepto;
        $montoBase = round((float) ($plan->monto ?? $concepto->monto_base), 2);
        $alumnos = $this->alumnosDelPlan($plan)->get();

        $resultado = [
            'plan_id' => $plan->id,
            'plan' => $plan->nombre,
            'periodo' => $periodo,
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'alumnos' => $alumnos->count(),
            'generados' => 0,
            'omitidos' => 0,
            'simulados' => 0,
            'errores' => 0,
        ];

        foreach ($alumnos as $alumno) {
            $yaExiste = CargoRecurrenteEjecucion::query()
                ->where('plan_cargo_recurrente_id', $plan->id)
                ->where('alumno_id', $alumno->id)
                ->where('periodo', $periodo)
                ->exists();

            if ($yaExiste) {
                $resultado['omitidos']++;
                continue;
            }

            $becaActiva = $alumno->becaVigente();
            $becaPorcentaje = 0;
            $becaMonto = 0.00;
            $becaId = null;
            $montoAdeudo = $montoBase;

            if ($becaActiva && $concepto->es_becable) {
                $becaPorcentaje = (int) $becaActiva->porcentaje;
                $becaMonto = round($montoBase * ($becaPorcentaje / 100), 2);
                $becaId = $becaActiva->id;
                $montoAdeudo = max(round($montoBase - $becaMonto, 2), 0);
            }

            if ($dryRun) {
                $resultado['simulados']++;
                continue;
            }

            try {
                DB::transaction(function () use ($plan, $alumno, $concepto, $periodo, $fechaVencimiento, $montoBase, $montoAdeudo, $becaId, $becaPorcentaje, $becaMonto): void {
                    $cargo = Cargo::create([
                        'alumno_id' => $alumno->id,
                        'concepto_id' => $concepto->id,
                        'cargo_recurrente_plan_id' => $plan->id,
                        'periodo_recurrente' => $periodo,
                        'generado_automaticamente' => true,
                        'beca_id' => $becaId,
                        'descripcion_cargo' => $this->descripcionCargo($plan, $concepto->nombre, $periodo),
                        'monto_original' => $montoBase,
                        'beca_porcentaje_aplicado' => $becaPorcentaje,
                        'beca_monto_aplicado' => $becaMonto,
                        'monto_adeudo' => $montoAdeudo,
                        'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                        'estatus' => $montoAdeudo <= 0 ? 'Pagado' : 'Pendiente',
                        'moratorio_aplicado' => false,
                    ]);

                    CargoRecurrenteEjecucion::create([
                        'plan_cargo_recurrente_id' => $plan->id,
                        'alumno_id' => $alumno->id,
                        'cargo_id' => $cargo->id,
                        'periodo' => $periodo,
                        'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                        'monto_original' => $montoBase,
                        'monto_adeudo' => $montoAdeudo,
                        'estatus' => 'generado',
                        'ejecutado_at' => now(),
                    ]);

                    if ($montoAdeudo > 0) {
                        $alumno->forceFill(['estatus_financiero' => 'Con Adeudo'])->save();
                    }
                });

                $resultado['generados']++;
            } catch (\Throwable $e) {
                report($e);
                $resultado['errores']++;
            }
        }

        return $resultado;
    }

    public function alumnosDelPlan(PlanCargoRecurrente $plan): Builder
    {
        return Alumno::query()
            ->with(['grupo.programa', 'becas'])
            ->where('estatus_academico', 'Activo')
            ->when($plan->alcance === PlanCargoRecurrente::ALCANCE_GRUPO, function (Builder $query) use ($plan) {
                $query->where('grupo_id', $plan->grupo_id);
            })
            ->when($plan->alcance === PlanCargoRecurrente::ALCANCE_PROGRAMA, function (Builder $query) use ($plan) {
                $query->whereHas('grupo', fn (Builder $grupo) => $grupo->where('programa_id', $plan->programa_id));
            })
            ->orderBy('nombre_completo');
    }

    private function correspondePeriodo(PlanCargoRecurrente $plan, Carbon $fechaReferencia): bool
    {
        $inicio = $plan->fecha_inicio->copy()->startOfMonth();
        $referencia = $fechaReferencia->copy()->startOfMonth();

        if ($referencia->lt($inicio)) {
            return false;
        }

        $diferenciaMeses = (int) $inicio->diffInMonths($referencia);
        $frecuencia = max(1, (int) $plan->frecuencia_meses);

        return $diferenciaMeses % $frecuencia === 0;
    }

    private function fechaVencimiento(Carbon $fechaReferencia, int $diaVencimiento): Carbon
    {
        $dia = max(1, min($diaVencimiento, 28));

        return $fechaReferencia->copy()->startOfMonth()->day($dia);
    }

    private function descripcionCargo(PlanCargoRecurrente $plan, string $concepto, string $periodo): string
    {
        $base = $plan->descripcion ?: $concepto;

        return trim($base.' · Periodo '.$periodo);
    }
}
