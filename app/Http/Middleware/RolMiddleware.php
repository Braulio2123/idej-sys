<?php

namespace App\Http\Middleware;

use App\Models\Rol;
use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    /**
     * Permite acceso solo a roles especificados por clave.
     * Ejemplo: rol:Admin,CAdmin,Recepcion,Academica,Finanzas
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user || ! $user->rol) {
            abort(403, 'Acceso denegado. Usuario sin rol.');
        }

        $clave = $user->rolClave();

        if (! $clave) {
            abort(403, 'Rol no reconocido.');
        }

        // Admin mantiene acceso total.
        if ($clave === Rol::ADMIN) {
            return $next($request);
        }

        if (! in_array($clave, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
