<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\InternalSessionSecurityService;
use App\Traits\RegistraBitacora;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    use RegistraBitacora;

    public function __construct(
        private readonly InternalSessionSecurityService $sessionSecurity
    ) {
    }

    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $usuarioRestablecido = null;

        try {
            $status = DB::transaction(function () use ($validated, &$usuarioRestablecido): string {
                $tokenRegistrado = DB::table('password_reset_tokens')
                    ->where('email', $validated['email'])
                    ->lockForUpdate()
                    ->first();

                $versionEmitida = is_numeric($tokenRegistrado?->auth_version)
                    ? (int) $tokenRegistrado->auth_version
                    : null;

                return Password::broker('usuarios')->reset(
                    $validated,
                    function (Usuario $usuario, string $password) use (&$usuarioRestablecido, $versionEmitida): void {
                        $usuarioBloqueado = Usuario::whereKey($usuario->getKey())
                            ->lockForUpdate()
                            ->firstOrFail();

                        if ($versionEmitida === null
                            || $versionEmitida !== $usuarioBloqueado->versionAutenticacion()) {
                            throw ValidationException::withMessages([
                                'email' => 'El enlace fue invalidado porque las credenciales o el estado de la cuenta cambiaron. Solicita uno nuevo.',
                            ]);
                        }

                        if (! $usuarioBloqueado->estaActivo()) {
                            throw ValidationException::withMessages([
                                'email' => 'El enlace no es válido o la cuenta interna no está autorizada para recuperar acceso.',
                            ]);
                        }

                        if ($usuarioBloqueado->passwordUsadaRecientemente($password, 6)) {
                            throw ValidationException::withMessages([
                                'password' => 'Por seguridad, usa una contraseña que no hayas utilizado en los últimos 6 meses.',
                            ]);
                        }

                        $hashPassword = Hash::make($password);
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
                        $usuarioRestablecido = $usuarioBloqueado;

                        event(new PasswordReset($usuarioBloqueado));
                    }
                );
            }, 3);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'No fue posible completar la recuperación. Solicita un enlace nuevo o contacta al área de Sistemas.',
                ]);
        }

        if ($status !== Password::PASSWORD_RESET || ! $usuarioRestablecido instanceof Usuario) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'El enlace de recuperación no es válido, ya expiró o ya fue utilizado.',
                ]);
        }

        $this->sessionSecurity->invalidatePersistedSessions($usuarioRestablecido);

        $this->bitacora(
            'Restablecer contraseña por correo',
            'El usuario interno completó una recuperación válida. Sus sesiones anteriores fueron invalidadas.',
            'Seguridad',
            $usuarioRestablecido
        );

        return redirect()
            ->route('login')
            ->with('status', 'La contraseña se actualizó correctamente. Ya puedes iniciar sesión.');
    }
}
