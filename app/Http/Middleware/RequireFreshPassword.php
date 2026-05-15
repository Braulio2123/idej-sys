<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireFreshPassword
{
    /**
     * Exige que el usuario haya confirmado su contraseña recientemente antes de ejecutar acciones sensibles.
     *
     * En peticiones GET redirige a la pantalla de confirmación y regresa a la URL original.
     * En peticiones POST/PUT/PATCH/DELETE redirige a confirmación y después vuelve a la pantalla anterior,
     * evitando que Laravel intente repetir una acción sensible con método GET.
     */
    public function handle(Request $request, Closure $next, int $seconds = 900): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);
        $isFresh = $confirmedAt > 0 && (time() - $confirmedAt) <= $seconds;

        if ($isFresh) {
            return $next($request);
        }

        $intendedUrl = $request->isMethodSafe()
            ? $request->fullUrl()
            : ($request->headers->get('referer') ?: route('dashboard'));

        $request->session()->put('url.intended', $intendedUrl);

        return redirect()
            ->route('password.confirm')
            ->with('status', 'Por seguridad, confirma tu contraseña antes de continuar con esta operación sensible.');
    }
}
