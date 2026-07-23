<?php

namespace App\Console\Commands;

use App\Models\PlanCargoRecurrente;
use App\Services\CargosRecurrentesService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarCargosRecurrentes extends Command
{
    protected $signature = 'app:generar-cargos-recurrentes
        {--fecha= : Fecha de referencia en formato YYYY-MM-DD. Por defecto usa hoy.}
        {--plan= : ID de un plan específico}
        {--dry-run : Simula sin crear cargos}';

    protected $description = 'Genera cargos recurrentes de colegiatura u otros conceptos programados.';

    public function handle(CargosRecurrentesService $service): int
    {
        if (! config('idej_recordatorios.cargos_recurrentes.activo', true)) {
            $this->warn('La generación automática de cargos recurrentes está desactivada en .env.');
            return self::SUCCESS;
        }

        $fecha = $this->option('fecha')
            ? Carbon::parse((string) $this->option('fecha'))
            : now();

        $plan = null;
        if ($this->option('plan')) {
            $plan = PlanCargoRecurrente::find((int) $this->option('plan'));
            if (! $plan) {
                $this->error('No se encontró el plan de cargos recurrentes indicado.');
                return self::FAILURE;
            }
        }

        $resultado = $service->generar($plan, $fecha, (bool) $this->option('dry-run'));

        $this->info("Periodo: {$resultado['periodo']} | Planes: {$resultado['planes']} | Generados: {$resultado['generados']} | Simulados: {$resultado['simulados']} | Omitidos: {$resultado['omitidos']} | Errores: {$resultado['errores']}");

        foreach ($resultado['detalles'] as $detalle) {
            $this->line("- {$detalle['plan']} | alumnos: {$detalle['alumnos']} | vencimiento: {$detalle['fecha_vencimiento']} | generados: {$detalle['generados']} | simulados: {$detalle['simulados']} | omitidos: {$detalle['omitidos']}");
        }

        return $resultado['errores'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
