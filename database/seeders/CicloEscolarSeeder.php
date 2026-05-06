<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CicloEscolar;

class CicloEscolarSeeder extends Seeder
{
    public function run(): void
    {
        CicloEscolar::insert([
            [
                'nombre' => '2025-A',
                'tipo_periodo' => 'Cuatrimestral',
                'fecha_inicio' => '2025-01-15',
                'fecha_fin' => '2025-04-30',
                'fecha_inicio_inscripcion' => '2025-01-01',
                'fecha_fin_inscripcion' => '2025-01-14',
                'fecha_inicio_clases' => '2025-01-15',
                'fecha_fin_clases' => '2025-04-30',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => '2025-B',
                'tipo_periodo' => 'Cuatrimestral',
                'fecha_inicio' => '2025-05-15',
                'fecha_fin' => '2025-08-30',
                'fecha_inicio_inscripcion' => '2025-05-01',
                'fecha_fin_inscripcion' => '2025-05-14',
                'fecha_inicio_clases' => '2025-05-15',
                'fecha_fin_clases' => '2025-08-30',
                'activo' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
