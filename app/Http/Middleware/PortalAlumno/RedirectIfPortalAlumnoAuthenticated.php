<?php

namespace App\Http\Middleware\PortalAlumno;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Evita que un alumno autenticado vuelva al login del portal.
 */
class RedirectIfPortalAlumnoAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('portal_alumno')->check()) {
            return redirect()->route('portal.alumno.dashboard');
        }

        return $next($request);
    }
}
