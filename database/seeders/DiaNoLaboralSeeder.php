<?php

namespace Database\Seeders;

use App\Models\DiaNoLaboral;
use Illuminate\Database\Seeder;

class DiaNoLaboralSeeder extends Seeder
{
    public function run(): void
    {
        $dias = [
            ['fecha' => '2026-01-01', 'nombre' => 'Año Nuevo', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-02-02', 'nombre' => 'Constitución Mexicana', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-03-16', 'nombre' => 'Natalicio de Benito Juárez', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-05-01', 'nombre' => 'Día del Trabajo', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-09-16', 'nombre' => 'Independencia de México', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-11-16', 'nombre' => 'Revolución Mexicana', 'tipo' => DiaNoLaboral::TIPO_LEY],
            ['fecha' => '2026-12-25', 'nombre' => 'Navidad', 'tipo' => DiaNoLaboral::TIPO_LEY],
        ];

        foreach ($dias as $dia) {
            DiaNoLaboral::updateOrCreate(
                ['fecha' => $dia['fecha']],
                [
                    'nombre' => $dia['nombre'],
                    'tipo' => $dia['tipo'],
                    'activo' => true,
                    'observaciones' => 'Carga inicial referencial. Ajustar según calendario institucional del IDEJ.',
                ]
            );
        }
    }
}
