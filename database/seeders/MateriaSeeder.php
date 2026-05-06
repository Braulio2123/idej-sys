<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\Programa;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $derecho = Programa::where('nombre', 'Licenciatura en Derecho')->first();
        $constitucional = Programa::where('nombre', 'Maestría en Derecho Constitucional')->first();
        $penal = Programa::where('nombre', 'Doctorado en Derecho Penal')->first();
        $amparo = Programa::where('nombre', 'Maestría en Amparo')->first();

        $materias = [
            ['programa_id' => $derecho?->id, 'clave' => 'DER-101', 'nombre' => 'Introducción al Estudio del Derecho', 'nivel' => 'Licenciatura', 'semestre_o_cuatrimestre' => 1, 'creditos' => 6, 'horas_teoricas' => 3, 'horas_practicas' => 1],
            ['programa_id' => $derecho?->id, 'clave' => 'DER-102', 'nombre' => 'Derecho Civil I', 'nivel' => 'Licenciatura', 'semestre_o_cuatrimestre' => 1, 'creditos' => 6, 'horas_teoricas' => 3, 'horas_practicas' => 1],
            ['programa_id' => $derecho?->id, 'clave' => 'DER-201', 'nombre' => 'Derecho Penal I', 'nivel' => 'Licenciatura', 'semestre_o_cuatrimestre' => 2, 'creditos' => 6, 'horas_teoricas' => 3, 'horas_practicas' => 1],
            ['programa_id' => $constitucional?->id, 'clave' => 'MDC-101', 'nombre' => 'Teoría Constitucional Avanzada', 'nivel' => 'Maestría', 'semestre_o_cuatrimestre' => 1, 'creditos' => 8, 'horas_teoricas' => 4, 'horas_practicas' => 1],
            ['programa_id' => $amparo?->id, 'clave' => 'AMP-101', 'nombre' => 'Juicio de Amparo Indirecto', 'nivel' => 'Maestría', 'semestre_o_cuatrimestre' => 1, 'creditos' => 8, 'horas_teoricas' => 4, 'horas_practicas' => 1],
            ['programa_id' => $penal?->id, 'clave' => 'DDP-301', 'nombre' => 'Seminario de Investigación Penal', 'nivel' => 'Doctorado', 'semestre_o_cuatrimestre' => 3, 'creditos' => 10, 'horas_teoricas' => 3, 'horas_practicas' => 2],
        ];

        foreach ($materias as $materia) {
            Materia::updateOrCreate(
                ['clave' => $materia['clave']],
                [
                    ...$materia,
                    'estatus' => 'Activa',
                    'descripcion' => 'Materia base del catálogo académico institucional.',
                ]
            );
        }
    }
}
