<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $eraCambioForzado = method_exists($user, 'requiereCambioPassword') && $user->requiereCambioPassword();

        if (method_exists($user, 'passwordUsadaRecientemente') && $user->passwordUsadaRecientemente($validated['password'], 6)) {
            return back()->withErrors([
                'password' => 'Por seguridad, usa una contraseña que no hayas utilizado en los últimos 6 meses.',
            ], 'updatePassword');
        }

        $hashPassword = Hash::make($validated['password']);

        $user->forceFill([
            'password' => $hashPassword,
            'password_changed_at' => now(),
            'must_change_password' => false,
            'temporary_password_generated_at' => null,
            'temporary_password_expires_at' => null,
        ])->save();

        if (method_exists($user, 'registrarPasswordEnHistorial')) {
            $user->registrarPasswordEnHistorial($hashPassword);
        }

        $request->session()->put('auth.password_changed_at', $user->password_changed_at->timestamp);
        $request->session()->forget('auth.password_confirmed_at');

        if ($eraCambioForzado) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Tu contraseña temporal se actualizó correctamente. Ya puedes usar el sistema.');
        }

        return back()->with('status', 'password-updated');
    }
}
