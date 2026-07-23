<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsuarioInternoActivo
{
    use RegistraBitacora;

    /**
     * Cierra la sesión administrativa cuando el usuario fue desactivado o cuando
     * su contraseña cambió después de haber iniciado sesión.
     *
     * El Portal Alumno usa otro guard; por eso se revisa únicamente el guard web.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if (! $guard->check()) {
            return $next($request);
        }

        $usuario = $guard->user();

        if (! $usuario instanceof Usuario) {
            return $next($request);
        }

        if (! $usuario->estaActivo()) {
            $this->cerrarSesionAdministrativa(
                $request,
                'Sesión cerrada por usuario desactivado',
                'Se cerró automáticamente una sesión administrativa porque el usuario interno fue desactivado.',
                'Tu usuario interno fue desactivado. Solicita apoyo a Sistemas o Administración.'
            );
        }

        $passwordChangedAt = optional($usuario->password_changed_at)->timestamp;
        $sessionPasswordChangedAt = $request->session()->get('auth.password_changed_at');

        if ($passwordChangedAt && $sessionPasswordChangedAt && (int) $sessionPasswordChangedAt !== (int) $passwordChangedAt) {
            $this->cerrarSesionAdministrativa(
                $request,
                'Sesión cerrada por cambio de contraseña',
                'Se cerró automáticamente una sesión administrativa porque la contraseña del usuario cambió después del inicio de sesión.',
                'Tu contraseña cambió recientemente. Inicia sesión nuevamente.'
            );
        }

        if ($passwordChangedAt && ! $sessionPasswordChangedAt) {
            $this->cerrarSesionAdministrativa(
                $request,
                'Sesión cerrada por verificación de contraseña',
                'Se cerró automáticamente una sesión administrativa porque no se pudo confirmar la vigencia de la contraseña en la sesión actual.',
                'Por seguridad, inicia sesión nuevamente.'
            );
        }


        if ($usuario->requiereCambioPassword()) {
            if ($usuario->temporary_password_expires_at?->isPast()) {
                $this->cerrarSesionAdministrativa(
                    $request,
                    'Sesión cerrada por contraseña temporal vencida',
                    'Se cerró automáticamente una sesión administrativa porque la contraseña temporal venció.',
                    'La contraseña temporal ya venció. Solicita una nueva al área de Sistemas o Administración.'
                );
            }

            if (! $this->rutaPermitidaParaCambioForzado($request)) {
                throw new HttpResponseException(
                    redirect()
                        ->route('profile.edit')
                        ->with('warning', 'Debes cambiar la contraseña temporal antes de continuar usando el sistema.')
                );
            }
        }

        return $next($request);
    }


    private function rutaPermitidaParaCambioForzado(Request $request): bool
    {
        return $request->routeIs('profile.edit')
            || $request->routeIs('password.update')
            || $request->routeIs('logout')
            || $request->routeIs('password.confirm')
            || $request->routeIs('password.confirmation');
    }

    private function cerrarSesionAdministrativa(Request $request, string $accion, string $descripcion, string $mensaje): never
    {
        $usuario = Auth::guard('web')->user();

        if ($usuario instanceof Usuario) {
            $this->bitacora($accion, $descripcion, 'Seguridad', $usuario);
        }

        Auth::guard('web')->logout();

        $request->session()->forget([
            'auth.password_confirmed_at',
            'auth.password_changed_at',
        ]);

        $request->session()->regenerateToken();

        throw new HttpResponseException(
            redirect()
                ->route('login')
                ->with('status', $mensaje)
        );
    }
}
