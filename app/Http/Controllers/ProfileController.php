<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Usuario;
use App\Services\InternalSessionSecurityService;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use RegistraBitacora;

    public function __construct(
        private readonly InternalSessionSecurityService $sessionSecurity
    ) {
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'puedeModificarCorreo' => $this->puedeModificarCorreo($request->user()),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var Usuario $user */
        $user = $request->user();

        if (method_exists($user, 'requiereCambioPassword') && $user->requiereCambioPassword()) {
            return Redirect::route('profile.edit')
                ->with('warning', 'Primero debes cambiar la contraseña temporal. Después podrás actualizar otros datos permitidos.');
        }

        $datos = $request->validated();

        if (! $this->puedeModificarCorreo($user)) {
            unset($datos['email']);
        }

        $emailCambiaria = isset($datos['email']) && $datos['email'] !== $user->email;

        if ($emailCambiaria && ! $this->passwordConfirmadaRecientemente($request)) {
            $request->session()->put('url.intended', route('profile.edit'));

            return Redirect::route('password.confirm')
                ->with('status', 'Por seguridad, confirma tu contraseña antes de cambiar el correo institucional. Después vuelve a guardar el perfil.');
        }

        $resultado = DB::transaction(function () use ($user, $datos): array {
            $usuarioBloqueado = Usuario::whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $usuarioBloqueado->estaActivo()) {
                abort(403, 'La cuenta interna ya no está activa.');
            }

            $emailAnterior = $usuarioBloqueado->email;
            $usuarioBloqueado->fill($datos);
            $emailCambiado = $emailAnterior !== $usuarioBloqueado->email;

            if ($emailCambiado) {
                $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);
                $usuarioBloqueado->remember_token = Str::random(60);
            }

            $usuarioBloqueado->save();

            return [
                'usuario' => $usuarioBloqueado,
                'email_anterior' => $emailAnterior,
                'email_cambiado' => $emailCambiado,
            ];
        }, 3);

        /** @var Usuario $usuarioActualizado */
        $usuarioActualizado = $resultado['usuario'];

        if ($resultado['email_cambiado']) {
            $currentSessionId = $request->session()->getId();
            $this->sessionSecurity->invalidatePersistedSessions($usuarioActualizado, $currentSessionId);
            $this->sessionSecurity->revokePasswordResetTokenForEmail($resultado['email_anterior']);
            $this->sessionSecurity->revokePasswordResetTokens($usuarioActualizado);

            $request->session()->regenerate();
            $request->session()->forget('auth.password_confirmed_at');
            $this->sessionSecurity->synchronizeCurrentSession($request, $usuarioActualizado);
        }

        $this->bitacora(
            'Actualizar Perfil',
            $resultado['email_cambiado']
                ? "El usuario {$usuarioActualizado->nombre} actualizó su información personal y cambió su correo de {$resultado['email_anterior']} a {$usuarioActualizado->email}. Las demás sesiones y enlaces anteriores fueron invalidados."
                : "El usuario {$usuarioActualizado->nombre} actualizó su información personal.",
            'Seguridad',
            $usuarioActualizado
        );

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $usuarioActual = $request->user();
        $currentSessionId = $request->session()->getId();

        $usuario = DB::transaction(function () use ($usuarioActual): Usuario {
            $usuarioBloqueado = Usuario::whereKey($usuarioActual->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);
            $usuarioBloqueado->forceFill([
                'remember_token' => Str::random(60),
            ])->save();

            return $usuarioBloqueado;
        }, 3);

        $cerradas = $this->sessionSecurity->invalidatePersistedSessions($usuario, $currentSessionId);

        $request->session()->regenerate();
        $request->session()->forget('auth.password_confirmed_at');
        $this->sessionSecurity->synchronizeCurrentSession($request, $usuario);

        $this->bitacora(
            'Cerrar otras sesiones internas',
            "El usuario invalidó sus demás sesiones administrativas. Sesiones persistidas eliminadas: {$cerradas}.",
            'Seguridad',
            $usuario
        );

        return Redirect::route('profile.edit')
            ->with('success', 'Las demás sesiones de tu usuario fueron invalidadas. Esta sesión permanece activa.');
    }

    /**
     * En IDEJ-SYS los usuarios no deben eliminar su propia cuenta.
     * La baja de usuarios corresponde al módulo administrativo Usuarios.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort(403, 'La eliminación de cuentas solo puede realizarla un administrador desde el módulo Usuarios.');
    }

    private function puedeModificarCorreo($usuario): bool
    {
        return $usuario
            && method_exists($usuario, 'esAdmin')
            && $usuario->esAdmin();
    }

    private function passwordConfirmadaRecientemente(Request $request, int $seconds = 900): bool
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return $confirmedAt > 0 && (time() - $confirmedAt) <= $seconds;
    }
}
