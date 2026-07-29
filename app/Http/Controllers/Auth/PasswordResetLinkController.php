<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use RegistraBitacora;

    private const GENERIC_RESPONSE = 'Si la información coincide con un usuario interno activo, recibirás instrucciones para restablecer tu contraseña.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $validated['email'];

        try {
            $status = Password::broker('usuarios')->sendResetLink(
                ['email' => $email],
                function (Usuario $usuario, string $token): string {
                    if (! $usuario->estaActivo()) {
                        Password::broker('usuarios')->deleteToken($usuario);

                        $this->bitacora(
                            'Recuperación bloqueada para usuario inactivo',
                            'Se recibió una solicitud de recuperación para una cuenta interna inactiva. No se emitió un enlace utilizable.',
                            'Seguridad',
                            $usuario
                        );

                        return Password::RESET_LINK_SENT;
                    }

                    try {
                        DB::table('password_reset_tokens')
                            ->where('email', $usuario->getEmailForPasswordReset())
                            ->update(['auth_version' => $usuario->versionAutenticacion()]);

                        $usuario->sendPasswordResetNotification($token);

                        $this->bitacora(
                            'Solicitar recuperación de contraseña',
                            'Se emitió un enlace de recuperación para un usuario interno activo.',
                            'Seguridad',
                            $usuario
                        );
                    } catch (\Throwable $e) {
                        Password::broker('usuarios')->deleteToken($usuario);
                        report($e);

                        $this->bitacora(
                            'Fallo al enviar recuperación de contraseña',
                            'No fue posible entregar el enlace de recuperación. El token emitido fue invalidado.',
                            'Seguridad',
                            $usuario
                        );
                    }

                    return Password::RESET_LINK_SENT;
                }
            );

            if ($status === Password::INVALID_USER) {
                $this->bitacora(
                    'Solicitud de recuperación sin coincidencia',
                    'Se recibió una solicitud sin coincidencia de usuario. Identificador SHA-256: '.hash('sha256', $email).'.',
                    'Seguridad'
                );
            }
        } catch (\Throwable $e) {
            report($e);

            $this->bitacora(
                'Fallo técnico en recuperación de contraseña',
                'El servicio de recuperación presentó un error interno. Identificador SHA-256: '.hash('sha256', $email).'.',
                'Seguridad'
            );
        }

        return back()->with('status', self::GENERIC_RESPONSE);
    }
}
