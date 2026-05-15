<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMateria;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Calificaciones del Portal Alumno.
 *
 * Esta sección queda preparada para conectar un módulo formal de calificaciones
 * cuando exista en el sistema interno.
 *
 * Por ahora:
 * - Muestra materias reales del alumno.
 * - No inventa calificaciones.
 * - No modifica información académica.
 */
class CalificacionController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load(['grupo.programa', 'grupo.cicloEscolar']);

        $materias = collect();

        if ($alumno->grupo_id) {
            $desdeCalendario = CalendarioMateria::query()
                ->with(['materia', 'docente', 'calendario'])
                ->whereHas('calendario', function ($query) use ($alumno) {
                    $query->where('grupo_id', $alumno->grupo_id);
                })
                ->where('estatus', '!=', CalendarioMateria::ESTATUS_CANCELADA)
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
                ->map(function (CalendarioMateria $calendarioMateria) {
                    return [
                        'clave' => $calendarioMateria->materia_id
                            ? 'materia_' . $calendarioMateria->materia_id
                            : 'calendario_' . $calendarioMateria->id,
                        'nombre' => $calendarioMateria->nombre_materia ?? 'Materia sin nombre',
                        'docente' => $calendarioMateria->nombre_docente ?? 'Docente pendiente',
                        'origen' => 'Calendario académico',
                        'calendario' => $calendarioMateria->calendario->nombre ?? 'Calendario no definido',
                    ];
                });

            $desdeHorario = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->orderByRaw("FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
                ->orderBy('hora_inicio')
                ->get()
                ->unique('materia_id')
                ->map(function (HorarioAcademico $horario) {
                    return [
                        'clave' => $horario->materia_id
                            ? 'materia_' . $horario->materia_id
                            : 'horario_' . $horario->id,
                        'nombre' => $horario->materia->nombre ?? 'Materia sin nombre',
                        'docente' => $horario->docente->nombre_completo ?? 'Docente pendiente',
                        'origen' => 'Horario académico',
                        'calendario' => 'Detectada desde horario',
                    ];
                });

            $materias = $desdeCalendario
                ->concat($desdeHorario)
                ->unique('clave')
                ->values();
        }

        $totalMaterias = $materias->count();

        return view('portal_alumno.calificaciones.index', compact(
            'alumno',
            'materias',
            'totalMaterias'
        ));
    }
}
