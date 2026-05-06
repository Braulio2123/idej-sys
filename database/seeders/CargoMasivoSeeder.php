<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CargoMasivo;

class CargoMasivoSeeder extends Seeder
{
    public function run(): void
    {
        CargoMasivo::create([
            'concepto_id' => 1,
            'monto' => 1200,
            'fecha_vencimiento' => '2025-02-15',
            'descripcion' => 'Cargos de inscripción masivos',
            'programa_id' => 1,
            'grupo_id' => 1,
            'ciclo_escolar_id' => 1,
            'total_alumnos' => 2,
            'usuario_id' => 1
        ]);
    }
}
