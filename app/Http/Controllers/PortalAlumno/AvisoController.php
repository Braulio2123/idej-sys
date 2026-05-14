<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\PortalAlumno\AvisoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Avisos institucionales del Portal Alumno.
 *
 * Solo muestra avisos activos, vigentes y dirigidos al alumno o a su grupo.
 */
class AvisoController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()->load('grupo');

        $avisos = AvisoPortal::query()
            ->visiblesParaAlumno($alumno)
            ->recientes()
            ->paginate(10);

        return view('portal_alumno.avisos.index', compact('alumno', 'avisos'));
    }
}
