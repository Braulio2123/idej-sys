<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\HorarioAcademico;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Consulta de horario para el Portal Alumno PWA.
 *
 * Usa el alumno autenticado; no recibe alumno_id por URL para evitar exposicion
 * de informacion academica de otros estudiantes.
 */
class HorarioController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()->load(['grupo.programa']);

        $dias = HorarioAcademico::DIAS;
        $horariosPorDia = collect($dias)->mapWithKeys(fn ($dia) => [$dia => collect()]);
        $proximasSesiones = collect();

        if ($alumno->grupo_id) {
            $horarios = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->orderBy('hora_inicio')
                ->get();

            $horariosPorDia = collect($dias)->mapWithKeys(function ($dia) use ($horarios) {
                return [$dia => $horarios->where('dia_semana', $dia)->values()];
            });

            $proximasSesiones = CalendarioSesion::query()
                ->with(['calendarioMateria.materia', 'calendarioMateria.docente', 'calendarioMateria.calendario'])
                ->whereHas('calendarioMateria.calendario', function ($query) use ($alumno) {
                    $query->where('grupo_id', $alumno->grupo_id)
                        ->whereNotIn('estatus', [
                            CalendarioAcademico::ESTATUS_CANCELADO,
                            CalendarioAcademico::ESTATUS_FINALIZADO,
                        ]);
                })
                ->activas()
                ->whereDate('fecha', '>=', now()->toDateString())
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->limit(12)
                ->get();
        }

        return view('portal_alumno.horario.index', compact('alumno', 'dias', 'horariosPorDia', 'proximasSesiones'));
    }
}
