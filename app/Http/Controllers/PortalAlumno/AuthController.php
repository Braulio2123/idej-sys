<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controlador de acceso exclusivo del Portal Alumno PWA.
 *
 * No reemplaza el login administrativo ubicado en /login.
 * Ruta del portal: /portal-alumno/login
 */
class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('portal_alumno.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:120'],
        ], [
            'login.required' => 'Ingresa tu matricula o correo institucional.',
            'password.required' => 'Ingresa tu contrasena.',
        ]);

        $login = trim($credentials['login']);

        $alumno = AlumnoPortal::query()
            ->where('portal_activo', true)
            ->where(function ($query) use ($login) {
                $query->where('matricula', $login)
                    ->orWhere('correo', $login);
            })
            ->first();

        if (! $alumno || ! $alumno->portal_password || ! Hash::check($credentials['password'], $alumno->portal_password)) {
            throw ValidationException::withMessages([
                'login' => 'Las credenciales no coinciden con un alumno activo del portal.',
            ]);
        }

        Auth::guard('portal_alumno')->login($alumno);

        $request->session()->regenerate();

        $alumno->forceFill([
            'portal_ultimo_acceso_at' => now(),
        ])->save();

        return redirect()->intended(route('portal.alumno.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('portal_alumno')->logout();

        // Se regenera el token CSRF sin invalidar toda la sesion para no afectar,
        // si existiera, una sesion administrativa abierta en el mismo navegador.
        $request->session()->regenerateToken();

        return redirect()->route('portal.alumno.login');
    }
}
