<?php

namespace App\Http\Middleware;

use App\Traits\RegistraBitacora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermisoMiddleware
{
    use RegistraBitacora;

    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tienePermiso($permiso)) {
            $request->attributes->set('idej_access_denied_logged', true);

            $this->bitacora(
                'Acceso denegado por permiso',
                "Permiso requerido: {$permiso}. Ruta: {$request->method()} {$request->path()}.",
                'Seguridad'
            );

            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
