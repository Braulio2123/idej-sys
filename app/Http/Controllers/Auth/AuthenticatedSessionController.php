<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RegistraBitacora;

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $request->user()?->forceFill([
            'ultimo_acceso_at' => now(),
            'ultimo_login_ip' => $request->ip(),
            'ultimo_user_agent' => $request->userAgent(),
        ])->save();

        $this->bitacora(
            'Inicio de sesión interno',
            'El usuario inició sesión correctamente en el panel administrativo.',
            'Seguridad',
            $request->user()
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario) {
            $this->bitacora(
                'Cierre de sesión interno',
                'El usuario cerró sesión en el panel administrativo.',
                'Seguridad',
                $usuario
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
