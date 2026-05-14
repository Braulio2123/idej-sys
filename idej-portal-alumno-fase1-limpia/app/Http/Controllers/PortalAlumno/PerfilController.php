<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Perfil de consulta del alumno.
 *
 * Primera version: solo lectura. La edicion se deja para una fase posterior
 * para evitar choques con el flujo administrativo de control escolar.
 */
class PerfilController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::guard('portal_alumno')->user()
            ->load(['grupo.programa', 'grupo.cicloEscolar', 'cicloEscolar']);

        return view('portal_alumno.perfil.index', compact('alumno'));
    }
}
