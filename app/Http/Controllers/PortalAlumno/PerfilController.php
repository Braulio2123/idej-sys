<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Perfil de consulta del alumno.
 *
 * Este controlador pertenece exclusivamente al Portal Alumno.
 *
 * IMPORTANTE:
 * - Solo muestra información del alumno autenticado.
 * - No permite editar datos administrativos.
 * - No modifica registros internos.
 */
class PerfilController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load([
            'grupo.programa',
            'grupo.cicloEscolar',
            'cicloEscolar',
        ]);

        return view('portal_alumno.perfil.index', compact('alumno'));
    }
}
