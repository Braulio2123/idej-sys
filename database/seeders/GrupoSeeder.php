<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grupo;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        Grupo::insert([
            [
                'nombre' => 'Grupo 1-A',
                'ciclo_escolar_id' => 1,
                'programa_id' => 1,
                'docente_id' => 1,
                'semestre_o_cuatrimestre' => 1,
                'turno' => 'Matutino',
                'aula' => 'A1',
                'cupo_maximo' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Grupo 1-B',
                'ciclo_escolar_id' => 1,
                'programa_id' => 1,
                'docente_id' => 2,
                'semestre_o_cuatrimestre' => 1,
                'turno' => 'Vespertino',
                'aula' => 'A2',
                'cupo_maximo' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Grupo 2-A',
                'ciclo_escolar_id' => 1,
                'programa_id' => 2,
                'docente_id' => 3,
                'semestre_o_cuatrimestre' => 2,
                'turno' => 'Matutino',
                'aula' => 'B1',
                'cupo_maximo' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Grupo 3-A',
                'ciclo_escolar_id' => 2,
                'programa_id' => 3,
                'docente_id' => 4,
                'semestre_o_cuatrimestre' => 3,
                'turno' => 'Sabatino',
                'aula' => 'C1',
                'cupo_maximo' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
