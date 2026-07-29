<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\InternalSessionSecurityService;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    use RegistraBitacora;

    public function __construct(
        private readonly InternalSessionSecurityService $sessionSecurity
    ) {
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $usuarioActual = $request->user();
        $eraCambioForzado = $usuarioActual->requiereCambioPassword();

        $usuario = DB::transaction(function () use ($usuarioActual, $validated): Usuario {
            $usuarioBloqueado = Usuario::whereKey($usuarioActual->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $usuarioBloqueado->estaActivo()) {
                throw ValidationException::withMessages([
                    'current_password' => 'La cuenta interna ya no está activa.',
                ])->errorBag('updatePassword');
            }

            if (! Hash::check($validated['current_password'], $usuarioBloqueado->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'La contraseña actual no es correcta.',
                ])->errorBag('updatePassword');
            }

            if ($usuarioBloqueado->passwordUsadaRecientemente($validated['password'], 6)) {
                throw ValidationException::withMessages([
                    'password' => 'Por seguridad, usa una contraseña que no hayas utilizado en los últimos 6 meses.',
                ])->errorBag('updatePassword');
            }

            $hashPassword = Hash::make($validated['password']);
            $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);

            $usuarioBloqueado->forceFill([
                'password' => $hashPassword,
                'password_changed_at' => now(),
                'must_change_password' => false,
                'temporary_password_generated_at' => null,
                'temporary_password_expires_at' => null,
                'remember_token' => Str::random(60),
            ])->save();

            $usuarioBloqueado->registrarPasswordEnHistorial($hashPassword);

            return $usuarioBloqueado;
        }, 3);

        $currentSessionId = $request->session()->getId();
        $this->sessionSecurity->invalidatePersistedSessions($usuario, $currentSessionId);
        $this->sessionSecurity->revokePasswordResetTokens($usuario);

        $request->session()->regenerate();
        $request->session()->forget('auth.password_confirmed_at');
        $this->sessionSecurity->synchronizeCurrentSession($request, $usuario);

        $this->bitacora(
            'Actualizar contraseña propia',
            'El usuario actualizó su contraseña. Las demás sesiones y enlaces de recuperación anteriores fueron invalidados.',
            'Seguridad',
            $usuario
        );

        if ($eraCambioForzado) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Tu contraseña temporal se actualizó correctamente. Ya puedes usar el sistema.');
        }

        return back()->with('status', 'password-updated');
    }
}
