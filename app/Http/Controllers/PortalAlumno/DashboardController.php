<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AvisoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Panel principal del Portal Alumno PWA.
 *
 * Este controlador solo consulta informacion academica del alumno autenticado.
 * No expone pantallas administrativas ni permite consultar alumnos por ID desde URL.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()
            ->load(['grupo.programa', 'grupo.cicloEscolar', 'cicloEscolar']);

        $diaActual = HorarioAcademico::diaActual();

        $horariosHoy = collect();
        $proximasSesiones = collect();
        $avisos = collect();

        if ($alumno->grupo_id) {
            $horariosHoy = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->where('dia_semana', $diaActual)
                ->orderBy('hora_inicio')
                ->get();

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
                ->limit(5)
                ->get();

            $avisos = AvisoPortal::query()
                ->visiblesParaAlumno($alumno)
                ->recientes()
                ->limit(3)
                ->get();
        }

        return view('portal_alumno.dashboard.index', compact(
            'alumno',
            'diaActual',
            'horariosHoy',
            'proximasSesiones',
            'avisos'
        ));
    }
}
