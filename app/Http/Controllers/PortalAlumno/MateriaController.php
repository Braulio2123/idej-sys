<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMateria;
use App\Models\HorarioAcademico;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Materias visibles para el alumno autenticado.
 *
 * El origen principal son los calendarios academicos del grupo; como respaldo,
 * tambien se consultan horarios academicos semanales existentes.
 */
class MateriaController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()->load(['grupo.programa']);

        $materiasCalendario = collect();
        $materiasHorario = collect();

        if ($alumno->grupo_id) {
            $materiasCalendario = CalendarioMateria::query()
                ->with(['materia', 'docente', 'calendario'])
                ->whereHas('calendario', fn ($query) => $query->where('grupo_id', $alumno->grupo_id))
                ->where('estatus', '!=', CalendarioMateria::ESTATUS_CANCELADA)
                ->orderBy('orden')
                ->get();

            $materiasHorario = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->get()
                ->unique('materia_id')
                ->values();
        }

        return view('portal_alumno.materias.index', compact('alumno', 'materiasCalendario', 'materiasHorario'));
    }
}
