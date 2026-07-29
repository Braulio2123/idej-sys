<?php

namespace App\Console\Commands;

use App\Models\DiaNoLaboral;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CargarDiasNoLaboralesOficiales extends Command
{
    protected $signature = 'idej:cargar-dias-no-laborales {anio? : Año a cargar. Si se omite usa el año actual}';
    protected $description = 'Carga anual de días no laborales oficiales base para México, sin duplicar fechas existentes.';

    public function handle(): int
    {
        $anio = (int) ($this->argument('anio') ?: now()->year);

        foreach ($this->diasOficiales($anio) as $dia) {
            DiaNoLaboral::updateOrCreate(
                ['fecha' => $dia['fecha']],
                [
                    'nombre' => $dia['nombre'],
                    'tipo' => DiaNoLaboral::TIPO_LEY,
                    'activo' => true,
                    'observaciones' => 'Carga anual oficial base. Revisar cada año contra el calendario institucional aplicable.',
                ]
            );
        }

        $this->info("Días no laborales oficiales cargados para {$anio}.");
        return self::SUCCESS;
    }

    public static function diasOficiales(int $anio): array
    {
        return [
            ['fecha' => Carbon::create($anio, 1, 1)->toDateString(), 'nombre' => 'Año Nuevo'],
            ['fecha' => self::primerLunes($anio, 2)->toDateString(), 'nombre' => 'Conmemoración de la Constitución Mexicana'],
            ['fecha' => self::tercerLunes($anio, 3)->toDateString(), 'nombre' => 'Natalicio de Benito Juárez'],
            ['fecha' => Carbon::create($anio, 5, 1)->toDateString(), 'nombre' => 'Día del Trabajo'],
            ['fecha' => Carbon::create($anio, 9, 16)->toDateString(), 'nombre' => 'Independencia de México'],
            ['fecha' => self::tercerLunes($anio, 11)->toDateString(), 'nombre' => 'Conmemoración de la Revolución Mexicana'],
            ['fecha' => Carbon::create($anio, 12, 25)->toDateString(), 'nombre' => 'Navidad'],
        ];
    }

    private static function primerLunes(int $anio, int $mes): Carbon
    {
        return Carbon::create($anio, $mes, 1)->nextOrSame(Carbon::MONDAY);
    }

    private static function tercerLunes(int $anio, int $mes): Carbon
    {
        return self::primerLunes($anio, $mes)->addWeeks(2);
    }
}
