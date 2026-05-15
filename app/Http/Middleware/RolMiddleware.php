<?php

namespace App\Http\Middleware;

use App\Models\Rol;
use App\Traits\RegistraBitacora;
use Closure;
use Illuminate\Http\Request;

class RolMiddleware
{
    use RegistraBitacora;

    /**
     * Permite acceso solo a roles especificados por clave.
     * Ejemplo: rol:Admin,CAdmin,Recepcion,Academica,Finanzas
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user || ! $user->rol) {
            $request->attributes->set('idej_access_denied_logged', true);

            $this->bitacora(
                'Acceso denegado por usuario sin rol',
                "Ruta: {$request->method()} {$request->path()}.",
                'Seguridad'
            );

            abort(403, 'Acceso denegado. Usuario sin rol.');
        }

        $clave = $user->rolClave();

        if (! $clave) {
            $request->attributes->set('idej_access_denied_logged', true);

            $this->bitacora(
                'Acceso denegado por rol no reconocido',
                "Usuario ID {$user->id}. Ruta: {$request->method()} {$request->path()}.",
                'Seguridad'
            );

            abort(403, 'Rol no reconocido.');
        }

        // Admin mantiene acceso total.
        if ($clave === Rol::ADMIN) {
            return $next($request);
        }

        if (! in_array($clave, $roles, true)) {
            $request->attributes->set('idej_access_denied_logged', true);

            $this->bitacora(
                'Acceso denegado por rol',
                'Rol actual: '.$clave.'. Roles permitidos: '.implode(', ', $roles).". Ruta: {$request->method()} {$request->path()}.",
                'Seguridad'
            );

            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
