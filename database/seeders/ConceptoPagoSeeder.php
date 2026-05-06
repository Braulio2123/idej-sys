<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConceptoPago;

class ConceptoPagoSeeder extends Seeder
{
    public function run(): void
    {
        ConceptoPago::insert([
            [
                'nombre' => 'Inscripción',
                'monto_base' => 1800,
                'es_becable' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Colegiatura Mensual',
                'monto_base' => 1500,
                'es_becable' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Constancia de Estudios',
                'monto_base' => 120,
                'es_becable' => false,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'nombre' => 'Reposición de Credencial',
                'monto_base' => 80,
                'es_becable' => false,
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }
}
