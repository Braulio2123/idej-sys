<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $usuarioExistente = Usuario::where('email', $request->email)->first();
        if ($usuarioExistente && $usuarioExistente->passwordUsadaRecientemente($request->password, 6)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['password' => 'Por seguridad, usa una contraseña que no hayas utilizado en los últimos 6 meses.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Usuario $user) use ($request) {
                $hashPassword = Hash::make($request->password);

                $user->forceFill([
                    'password' => $hashPassword,
                    'password_changed_at' => now(),
                    'must_change_password' => false,
                    'temporary_password_generated_at' => null,
                    'temporary_password_expires_at' => null,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->registrarPasswordEnHistorial($hashPassword);

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'La contraseña se actualizó correctamente. Ya puedes iniciar sesión.')
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => 'El enlace de recuperación no es válido, ya expiró o el correo no corresponde a un usuario interno.']);
    }
}
