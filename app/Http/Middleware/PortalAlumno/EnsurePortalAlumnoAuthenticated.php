<?php

namespace App\Http\Middleware\PortalAlumno;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware exclusivo del Portal Alumno.
 *
 * No usa el guard administrativo `web` para evitar mezclar sesiones del personal
 * interno con las sesiones de estudiantes.
 */
class EnsurePortalAlumnoAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('portal_alumno')->check()) {
            return redirect()->route('portal.alumno.login');
        }

        return $next($request);
    }
}
