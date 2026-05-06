<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programa;

class ProgramaSeeder extends Seeder
{
    public function run(): void
    {
        Programa::insert([
            ['nombre' => 'Licenciatura en Derecho', 'nivel' => 'Licenciatura', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Maestría en Derecho Constitucional', 'nivel' => 'Maestría', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Doctorado en Derecho Penal', 'nivel' => 'Doctorado', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Maestría en Amparo', 'nivel' => 'Maestría', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
