<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Grupo;
use App\Models\HorarioAcademico;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class HorarioAcademicoSeeder extends Seeder
{
    public function run(): void
    {
        $grupo1 = Grupo::find(1);
        $grupo2 = Grupo::find(2);
        $grupo3 = Grupo::find(3);

        $materia1 = Materia::where('clave', 'DER-101')->first();
        $materia2 = Materia::where('clave', 'DER-102')->first();
        $materia3 = Materia::where('clave', 'MDC-101')->first();

        $docente1 = Docente::find(1);
        $docente2 = Docente::find(2);
        $docente3 = Docente::find(3);

        $horarios = array_filter([
            $grupo1 && $materia1 && $docente1 ? [
                'grupo_id' => $grupo1->id,
                'materia_id' => $materia1->id,
                'docente_id' => $docente1->id,
                'dia_semana' => 'Lunes',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
                'aula' => 'A1',
                'modalidad' => 'Presencial',
                'estatus' => 'Activo',
            ] : null,
            $grupo1 && $materia2 && $docente2 ? [
                'grupo_id' => $grupo1->id,
                'materia_id' => $materia2->id,
                'docente_id' => $docente2->id,
                'dia_semana' => 'Miércoles',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
                'aula' => 'A1',
                'modalidad' => 'Presencial',
                'estatus' => 'Activo',
            ] : null,
            $grupo2 && $materia1 && $docente1 ? [
                'grupo_id' => $grupo2->id,
                'materia_id' => $materia1->id,
                'docente_id' => $docente1->id,
                'dia_semana' => 'Martes',
                'hora_inicio' => '16:00',
                'hora_fin' => '18:00',
                'aula' => 'A2',
                'modalidad' => 'Presencial',
                'estatus' => 'Activo',
            ] : null,
            $grupo3 && $materia3 && $docente3 ? [
                'grupo_id' => $grupo3->id,
                'materia_id' => $materia3->id,
                'docente_id' => $docente3->id,
                'dia_semana' => 'Sábado',
                'hora_inicio' => '08:00',
                'hora_fin' => '11:00',
                'aula' => 'B1',
                'modalidad' => 'Mixta',
                'estatus' => 'Activo',
            ] : null,
        ]);

        foreach ($horarios as $horario) {
            HorarioAcademico::updateOrCreate(
                [
                    'grupo_id' => $horario['grupo_id'],
                    'materia_id' => $horario['materia_id'],
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                ],
                [
                    ...$horario,
                    'fecha_inicio' => now()->toDateString(),
                    'observaciones' => 'Horario de ejemplo generado para pruebas locales.',
                ]
            );
        }
    }
}
