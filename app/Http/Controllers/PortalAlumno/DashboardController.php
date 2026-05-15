<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\Cargo;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AvisoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\PortalAlumno\AlumnoPortal;


/**
 * Panel principal del Portal Alumno PWA.
 *
 * Este controlador solo consulta información académica y financiera
 * del alumno autenticado.
 *
 * IMPORTANTE:
 * - No permite consultar alumnos por ID desde URL.
 * - No modifica registros administrativos.
 * - No pertenece al panel administrativo interno.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
/** @var AlumnoPortal|null $alumno */
$alumno = Auth::guard('portal_alumno')->user();

abort_unless($alumno instanceof AlumnoPortal, 401);

$alumno->load(['grupo.programa', 'grupo.cicloEscolar', 'cicloEscolar']);

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

        $cargosPendientes = Cargo::query()
            ->where('alumno_id', $alumno->id)
            ->whereNotIn('estatus', ['Pagado', 'Cancelado'])
            ->where('monto_adeudo', '>', 0);

        $totalAdeudo = (clone $cargosPendientes)->sum('monto_adeudo');

        $cargosVencidos = (clone $cargosPendientes)
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->count();

        $proximoVencimiento = (clone $cargosPendientes)
            ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
            ->orderBy('fecha_vencimiento')
            ->first();

        return view('portal_alumno.dashboard.index', compact(
            'alumno',
            'diaActual',
            'horariosHoy',
            'proximasSesiones',
            'avisos',
            'totalAdeudo',
            'cargosVencidos',
            'proximoVencimiento'
        ));
    }
}
