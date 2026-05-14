<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMateria;
use App\Models\HorarioAcademico;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Seccion de calificaciones del Portal Alumno PWA.
 *
 * El proyecto base aun no contiene una tabla formal de calificaciones. Por eso,
 * esta primera version muestra las materias reales del alumno y deja preparado
 * el espacio visual para conectar calificaciones cuando exista el modulo academico.
 */
class CalificacionController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()->load(['grupo.programa']);

        $materias = collect();

        if ($alumno->grupo_id) {
            $desdeCalendario = CalendarioMateria::query()
                ->with(['materia', 'docente', 'calendario'])
                ->whereHas('calendario', fn ($query) => $query->where('grupo_id', $alumno->grupo_id))
                ->where('estatus', '!=', CalendarioMateria::ESTATUS_CANCELADA)
                ->orderBy('orden')
                ->get()
                ->map(function (CalendarioMateria $calendarioMateria) {
                    return [
                        'nombre' => $calendarioMateria->nombre_materia,
                        'docente' => $calendarioMateria->nombre_docente,
                        'origen' => 'calendario',
                    ];
                });

            $desdeHorario = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->get()
                ->unique('materia_id')
                ->map(function (HorarioAcademico $horario) {
                    return [
                        'nombre' => $horario->materia->nombre ?? 'Materia sin nombre',
                        'docente' => $horario->docente->nombre_completo ?? 'Docente pendiente',
                        'origen' => 'horario',
                    ];
                });

            $materias = $desdeCalendario
                ->merge($desdeHorario)
                ->unique('nombre')
                ->values();
        }

        return view('portal_alumno.calificaciones.index', compact('alumno', 'materias'));
    }
}
