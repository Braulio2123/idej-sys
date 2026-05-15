<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Consulta de horario para el Portal Alumno PWA.
 *
 * Usa únicamente el alumno autenticado.
 * No recibe alumno_id por URL y no modifica información académica.
 */
class HorarioController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load(['grupo.programa', 'grupo.cicloEscolar']);

        $dias = HorarioAcademico::DIAS;
        $diaActual = HorarioAcademico::diaActual();

        $horariosPorDia = collect($dias)->mapWithKeys(fn ($dia) => [$dia => collect()]);
        $horariosHoy = collect();
        $proximasSesiones = collect();
        $proximaSesion = null;

        if ($alumno->grupo_id) {
            $horarios = HorarioAcademico::query()
                ->with(['materia', 'docente'])
                ->activos()
                ->where('grupo_id', $alumno->grupo_id)
                ->orderByRaw("FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
                ->orderBy('hora_inicio')
                ->get();

            $horariosPorDia = collect($dias)->mapWithKeys(function ($dia) use ($horarios) {
                return [$dia => $horarios->where('dia_semana', $dia)->values()];
            });

            $horariosHoy = $horariosPorDia->get($diaActual, collect());

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
                ->limit(8)
                ->get();

            $proximaSesion = $proximasSesiones->first();
        }

        $totalClasesSemana = $horariosPorDia->sum(fn ($items) => $items->count());
        $totalDiasConClase = $horariosPorDia->filter(fn ($items) => $items->isNotEmpty())->count();

        return view('portal_alumno.horario.index', compact(
            'alumno',
            'dias',
            'diaActual',
            'horariosPorDia',
            'horariosHoy',
            'proximasSesiones',
            'proximaSesion',
            'totalClasesSemana',
            'totalDiasConClase'
        ));
    }
}
