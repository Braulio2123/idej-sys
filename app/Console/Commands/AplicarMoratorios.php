<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\ConceptoPago;
use App\Models\ConfiguracionInstitucional;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AplicarMoratorios extends Command
{
    /**
     * Ejecuta con: php artisan app:aplicar-moratorios
     */
    protected $signature = 'app:aplicar-moratorios';

    /**
     * Descripción del comando.
     */
    protected $description = 'Aplica cargos moratorios y actualiza estatus de alumnos con pagos vencidos';

    public function handle(): int
    {
        $this->info('🏦 Iniciando aplicación de moratorios...');

        $configuracion = ConfiguracionInstitucional::actual();
        $porcentajeMoratorio = ((float) $configuracion->moratorio_porcentaje) / 100;
        $diasGracia = (int) $configuracion->moratorio_dias_gracia;
        $fechaLimite = now()->subDays($diasGracia)->toDateString();

        $conceptoMoratorio = ConceptoPago::where('nombre', 'Moratorio')->first();

        if (!$conceptoMoratorio) {
            $this->error('❌ No existe el concepto "Moratorio". Agrega el seeder primero.');
            return self::FAILURE;
        }

        // Alumnos aún marcados como "Al Corriente"
        $alumnos = Alumno::where('estatus_financiero', 'Al Corriente')->get();

        if ($alumnos->isEmpty()) {
            $this->warn('No hay alumnos "Al Corriente" para evaluar moratorios.');
            return self::SUCCESS;
        }

        $totalAlumnosAfectados = 0;
        $totalMoratoriosCreados = 0;

        foreach ($alumnos as $alumno) {
            // Cargos vencidos pendientes (y opcional: no penalizados)
            $cargosVencidos = $alumno->cargos()
                ->where('estatus', 'Pendiente')
                ->whereDate('fecha_vencimiento', '<', $fechaLimite)
                // 👉 Si agregas la columna moratorio_aplicado (recomendado), descomenta la siguiente línea:
                ->where('moratorio_aplicado', false)
                ->get();

            if ($cargosVencidos->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($alumno, $cargosVencidos, $conceptoMoratorio, &$totalMoratoriosCreados, &$totalAlumnosAfectados) {

                // Paso A: cambiar estatus del alumno
                $alumno->estatus_financiero = 'Con Adeudo';
                $alumno->save();

                $creadosEsteAlumno = 0;

                // Paso B: aplicar moratorio por cada cargo vencido
                foreach ($cargosVencidos as $cargo) {
                    $montoMoratorio = round($cargo->monto_original * $porcentajeMoratorio, 2);

                    Cargo::create([
                        'alumno_id'        => $alumno->id,
                        'concepto_id'      => $conceptoMoratorio->id,
                        'descripcion_cargo'=> 'Moratorio por: ' . $cargo->descripcion_cargo . ' (' . $configuracion->moratorio_porcentaje . '%)',
                        'monto_original'   => $montoMoratorio,
                        'monto_adeudo'     => $montoMoratorio,
                        'fecha_vencimiento'=> now()->toDateString(),
                        'estatus'          => 'Pendiente',
                        'moratorio_aplicado' => true, // si agregas la columna en cargos
                    ]);

                    // Marca el cargo original para no re-penalizarlo (si agregas la columna)
                    $cargo->moratorio_aplicado = true;
                    $cargo->save();

                    $creadosEsteAlumno++;
                    $totalMoratoriosCreados++;
                }

                if ($creadosEsteAlumno > 0) {
                    $totalAlumnosAfectados++;
                }
            });
        }

        $this->info("✅ Moratorios aplicados a {$totalAlumnosAfectados} alumno(s). Cargos creados: {$totalMoratoriosCreados}.");
        return self::SUCCESS;
    }
}
