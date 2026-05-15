<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMateria;
use App\Models\CalendarioSesion;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Materias visibles para el alumno autenticado.
 *
 * Este controlador pertenece exclusivamente al Portal Alumno.
 *
 * IMPORTANTE:
 * - Solo consulta información del alumno autenticado.
 * - No modifica el calendario académico.
 * - No modifica materias, docentes ni grupos.
 */
class MateriaController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load(['grupo.programa', 'grupo.cicloEscolar']);

        $materiasCalendario = collect();
        $materiasHorario = collect();
        $horariosSemanales = collect();
        $horariosPorMateria = collect();

        if ($alumno->grupo_id) {
            $materiasCalendario = CalendarioMateria::query()
                ->with([
                    'materia',
                    'docente',
                    'calendario',
                    'sesiones' => function ($query) {
                        $query->activas()
                            ->whereDate('fecha', '>=', now()->toDateString())
                            ->orderBy('fecha')
                            ->orderBy('hora_inicio');
                    },
                ])
                ->withCount([
                    'sesiones as sesiones_activas_count' => function ($query) {
                        $query->whereNotIn('estatus', [
                            CalendarioSesion::ESTATUS_CANCELADA,
                            CalendarioSesion::ESTATUS_SUSPENDIDA,
                        ]);
                    },
                ])
                ->whereHas('calendario', function ($query) use ($alumno) {
                    $query->where('grupo_id', $alumno->grupo_id);
                })
                ->where('estatus', '!=', CalendarioMateria::ESTATUS_CANCELADA)
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

            $horariosSemanales = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->orderByRaw("FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
                ->orderBy('hora_inicio')
                ->get();

            $materiasHorario = $horariosSemanales
                ->whereNotNull('materia_id')
                ->unique('materia_id')
                ->values();

            $horariosPorMateria = $horariosSemanales
                ->whereNotNull('materia_id')
                ->groupBy('materia_id');
        }

        $totalMaterias = $materiasCalendario->isNotEmpty()
            ? $materiasCalendario->count()
            : $materiasHorario->count();

        $totalDocentes = $materiasCalendario->pluck('docente_id')
            ->merge($materiasHorario->pluck('docente_id'))
            ->filter()
            ->unique()
            ->count();

        $totalModalidades = $horariosSemanales->pluck('modalidad')
            ->filter()
            ->unique()
            ->count();

        $totalProximasSesiones = $materiasCalendario->sum(function ($materia) {
            return $materia->sesiones->count();
        });

        return view('portal_alumno.materias.index', compact(
            'alumno',
            'materiasCalendario',
            'materiasHorario',
            'horariosSemanales',
            'horariosPorMateria',
            'totalMaterias',
            'totalDocentes',
            'totalModalidades',
            'totalProximasSesiones'
        ));
    }
}
