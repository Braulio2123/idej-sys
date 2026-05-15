<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\PortalAlumno\AlumnoPortal;
use App\Models\PortalAlumno\AvisoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Avisos institucionales del Portal Alumno.
 *
 * Este controlador pertenece exclusivamente al Portal Alumno.
 *
 * IMPORTANTE:
 * - Solo muestra avisos activos y vigentes.
 * - Solo muestra avisos dirigidos a todos o al grupo del alumno.
 * - No modifica información administrativa.
 */
class AvisoController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load(['grupo.programa', 'grupo.cicloEscolar']);

        $avisos = AvisoPortal::query()
            ->visiblesParaAlumno($alumno)
            ->recientes()
            ->paginate(8)
            ->withQueryString();

        return view('portal_alumno.avisos.index', compact(
            'alumno',
            'avisos'
        ));
    }
}
