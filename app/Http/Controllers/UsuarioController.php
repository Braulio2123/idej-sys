<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\InternalSessionSecurityService;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    use RegistraBitacora;

    public function __construct(
        private readonly InternalSessionSecurityService $sessionSecurity
    ) {
    }

    public function index(): View
    {
        $usuarios = Usuario::with('rol')
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $this->asegurarActorAdmin();

        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->asegurarActorAdmin();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email'],
            'notificar_email' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        $hashPassword = Hash::make($validated['password']);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono_notificaciones' => null,
            'whatsapp_notificaciones' => null,
            'notificar_email' => $request->boolean('notificar_email', true),
            'notificar_sms' => false,
            'notificar_whatsapp' => false,
            'password' => $hashPassword,
            'rol_id' => $validated['rol_id'],
            'activo' => true,
            'password_changed_at' => now(),
            'auth_version' => 1,
            'must_change_password' => false,
        ]);

        $usuario->registrarPasswordEnHistorial($hashPassword);

        $this->bitacora(
            'Crear usuario interno',
            "Se creó el usuario interno {$usuario->nombre} ({$usuario->email}).",
            'Usuarios',
            $usuario
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        $this->asegurarActorAdmin();

        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $this->asegurarActorAdmin();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario->id),
            ],
            'notificar_email' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        $passwordActualizada = filled($validated['password'] ?? null);

        try {
            $resultado = DB::transaction(function () use ($usuario, $validated, $passwordActualizada, $request): array {
                $usuarioBloqueado = Usuario::whereKey($usuario->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $usuarioBloqueado->load('rol');

                $rolAnterior = $usuarioBloqueado->rol?->clave ?? 'Sin rol';
                $emailAnterior = $usuarioBloqueado->email;
                $nuevoRol = Rol::findOrFail($validated['rol_id']);

                $this->bloquearAdministradoresActivos();

                if ($usuarioBloqueado->rolClave() === Rol::ADMIN
                    && $nuevoRol->clave !== Rol::ADMIN
                    && $this->esUltimoAdminActivo($usuarioBloqueado)) {
                    throw new \RuntimeException('ULTIMO_ADMIN_ACTIVO');
                }

                if ($passwordActualizada && $usuarioBloqueado->passwordUsadaRecientemente($validated['password'], 6)) {
                    throw new \RuntimeException('PASSWORD_REUTILIZADA');
                }

                $rolCambiado = (int) $usuarioBloqueado->rol_id !== (int) $validated['rol_id'];
                $emailCambiado = $usuarioBloqueado->email !== $validated['email'];

                $usuarioBloqueado->nombre = $validated['nombre'];
                $usuarioBloqueado->email = $validated['email'];
                $usuarioBloqueado->telefono_notificaciones = null;
                $usuarioBloqueado->whatsapp_notificaciones = null;
                $usuarioBloqueado->notificar_email = $request->boolean('notificar_email', true);
                $usuarioBloqueado->notificar_sms = false;
                $usuarioBloqueado->notificar_whatsapp = false;
                $usuarioBloqueado->rol_id = $validated['rol_id'];

                if ($passwordActualizada) {
                    $hashPassword = Hash::make($validated['password']);
                    $usuarioBloqueado->password = $hashPassword;
                    $usuarioBloqueado->password_changed_at = now();
                    $usuarioBloqueado->must_change_password = false;
                    $usuarioBloqueado->temporary_password_generated_at = null;
                    $usuarioBloqueado->temporary_password_expires_at = null;
                }

                $cambioSeguridad = $passwordActualizada || $rolCambiado || $emailCambiado;

                if ($cambioSeguridad) {
                    $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);
                    $usuarioBloqueado->remember_token = Str::random(60);
                }

                $usuarioBloqueado->save();

                if ($passwordActualizada) {
                    $usuarioBloqueado->registrarPasswordEnHistorial($hashPassword);
                }

                $usuarioBloqueado->load('rol');

                return [
                    'usuario' => $usuarioBloqueado,
                    'rol_anterior' => $rolAnterior,
                    'email_anterior' => $emailAnterior,
                    'rol_actual' => $usuarioBloqueado->rol?->clave,
                    'cambio_seguridad' => $cambioSeguridad,
                ];
            }, 3);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'ULTIMO_ADMIN_ACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No se puede cambiar el rol del último administrador activo. Primero asigna otro administrador.');
            }

            if ($e->getMessage() === 'PASSWORD_REUTILIZADA') {
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors([
                        'password' => 'Por seguridad, usa una contraseña que no se haya utilizado con este usuario en los últimos 6 meses.',
                    ]);
            }

            throw $e;
        }

        /** @var Usuario $usuarioActualizado */
        $usuarioActualizado = $resultado['usuario'];

        if ($resultado['cambio_seguridad']) {
            $esSesionActual = (int) auth()->id() === (int) $usuarioActualizado->getKey();
            $sessionId = $esSesionActual ? $request->session()->getId() : null;

            $this->sessionSecurity->invalidatePersistedSessions($usuarioActualizado, $sessionId);

            if ($resultado['email_anterior'] !== $usuarioActualizado->email) {
                $this->sessionSecurity->revokePasswordResetTokenForEmail($resultado['email_anterior']);
            }

            $this->sessionSecurity->revokePasswordResetTokens($usuarioActualizado);

            if ($esSesionActual) {
                $request->session()->regenerate();
                $request->session()->forget('auth.password_confirmed_at');
                $this->sessionSecurity->synchronizeCurrentSession($request, $usuarioActualizado);
            }
        }

        $detallePassword = $passwordActualizada ? ' También se actualizó su contraseña.' : '';

        $this->bitacora(
            'Actualizar usuario interno',
            "Se actualizó el usuario {$usuarioActualizado->nombre}. Email anterior: {$resultado['email_anterior']}. Rol anterior: {$resultado['rol_anterior']}. Rol actual: {$resultado['rol_actual']}.{$detallePassword}",
            'Usuarios',
            $usuarioActualizado
        );

        $destino = (int) auth()->id() === (int) $usuarioActualizado->getKey() && ! $usuarioActualizado->esAdmin()
            ? 'dashboard'
            : 'usuarios.index';

        return redirect()->route($destino)
            ->with('success', 'Usuario actualizado correctamente. Las sesiones anteriores afectadas fueron invalidadas.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        $this->asegurarActorAdmin();

        if ((int) auth()->id() === (int) $usuario->getKey()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propio usuario mientras tienes la sesión activa.');
        }

        try {
            $usuarioDesactivado = DB::transaction(function () use ($usuario): Usuario {
                $usuarioBloqueado = Usuario::whereKey($usuario->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $usuarioBloqueado->load('rol');

                if (! $usuarioBloqueado->activo) {
                    throw new \RuntimeException('USUARIO_YA_INACTIVO');
                }

                $this->bloquearAdministradoresActivos();

                if ($this->esUltimoAdminActivo($usuarioBloqueado)) {
                    throw new \RuntimeException('ULTIMO_ADMIN_ACTIVO');
                }

                $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);
                $usuarioBloqueado->forceFill([
                    'activo' => false,
                    'remember_token' => Str::random(60),
                ])->save();

                return $usuarioBloqueado;
            }, 3);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'USUARIO_YA_INACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'El usuario ya estaba desactivado.');
            }

            if ($e->getMessage() === 'ULTIMO_ADMIN_ACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No se puede desactivar el último administrador activo. Primero asigna otro administrador.');
            }

            throw $e;
        }

        $this->sessionSecurity->invalidatePersistedSessions($usuarioDesactivado);
        $this->sessionSecurity->revokePasswordResetTokens($usuarioDesactivado);

        $this->bitacora(
            'Desactivar usuario interno',
            "Se desactivó el usuario {$usuarioDesactivado->nombre} ({$usuarioDesactivado->email}). Sus sesiones y enlaces de recuperación fueron invalidados.",
            'Usuarios',
            $usuarioDesactivado
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente. Sus sesiones abiertas fueron invalidadas y el historial se conservó.');
    }

    public function reactivar(Usuario $usuario): RedirectResponse
    {
        $this->asegurarActorAdmin();

        try {
            $usuarioReactivado = DB::transaction(function () use ($usuario): Usuario {
                $usuarioBloqueado = Usuario::whereKey($usuario->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($usuarioBloqueado->activo) {
                    throw new \RuntimeException('USUARIO_YA_ACTIVO');
                }

                $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);
                $usuarioBloqueado->forceFill([
                    'activo' => true,
                    'remember_token' => Str::random(60),
                ])->save();

                return $usuarioBloqueado;
            }, 3);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'USUARIO_YA_ACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'El usuario ya está activo.');
            }

            throw $e;
        }

        $this->sessionSecurity->invalidatePersistedSessions($usuarioReactivado);
        $this->sessionSecurity->revokePasswordResetTokens($usuarioReactivado);

        $this->bitacora(
            'Reactivar usuario interno',
            "Se reactivó el usuario {$usuarioReactivado->nombre} ({$usuarioReactivado->email}). No se restauraron sesiones anteriores.",
            'Usuarios',
            $usuarioReactivado
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario reactivado correctamente. Las sesiones anteriores permanecen invalidadas.');
    }

    public function generarPasswordTemporal(Usuario $usuario): RedirectResponse
    {
        $actor = auth()->user();

        if (! $actor instanceof Usuario || ! $actor->puedeGestionarCredencialesDe($usuario)) {
            abort(403, 'No tienes permiso para generar credenciales de esta cuenta.');
        }

        if ((int) $actor->getKey() === (int) $usuario->getKey()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No generes una contraseña temporal para tu propia sesión. Utiliza la opción Cambiar contraseña de tu perfil.');
        }

        $temporal = 'IDEJ-'.Str::upper(Str::random(4)).'-'.Str::random(4).'!'.random_int(10, 99);
        $horasVigencia = max(1, (int) config('auth.temporary_password_expire_hours', 24));

        try {
            $usuarioActualizado = DB::transaction(function () use ($usuario, $temporal, $horasVigencia): Usuario {
                $usuarioBloqueado = Usuario::whereKey($usuario->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $usuarioBloqueado->activo) {
                    throw new \RuntimeException('USUARIO_INACTIVO');
                }

                $hashPassword = Hash::make($temporal);
                $this->sessionSecurity->incrementAuthenticationVersion($usuarioBloqueado);

                $usuarioBloqueado->forceFill([
                    'password' => $hashPassword,
                    'password_changed_at' => now(),
                    'must_change_password' => true,
                    'temporary_password_generated_at' => now(),
                    'temporary_password_expires_at' => now()->addHours($horasVigencia),
                    'remember_token' => Str::random(60),
                ])->save();

                $usuarioBloqueado->registrarPasswordEnHistorial($hashPassword);

                return $usuarioBloqueado;
            }, 3);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'USUARIO_INACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No se puede generar una contraseña temporal para un usuario desactivado. Primero reactiva la cuenta.');
            }

            throw $e;
        }

        $this->sessionSecurity->invalidatePersistedSessions($usuarioActualizado);
        $this->sessionSecurity->revokePasswordResetTokens($usuarioActualizado);

        $this->bitacora(
            'Generar contraseña temporal',
            "Se generó una contraseña temporal para el usuario {$usuarioActualizado->nombre} ({$usuarioActualizado->email}), con vigencia de {$horasVigencia} horas. Sus sesiones anteriores fueron invalidadas.",
            'Usuarios',
            $usuarioActualizado
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Contraseña temporal generada para '.$usuarioActualizado->nombre.': '.$temporal.' Cópiala ahora; vencerá en '.$horasVigencia.' horas y no se volverá a mostrar.');
    }

    private function asegurarActorAdmin(): void
    {
        if (! auth()->user()?->esAdmin()) {
            abort(403, 'Solo un administrador puede crear, modificar, activar o desactivar usuarios internos.');
        }
    }

    private function esUltimoAdminActivo(Usuario $usuario): bool
    {
        if (! $usuario->activo || $usuario->rolClave() !== Rol::ADMIN) {
            return false;
        }

        return ! Usuario::where('activo', true)
            ->where('id', '!=', $usuario->id)
            ->whereHas('rol', fn ($query) => $query->where('clave', Rol::ADMIN))
            ->exists();
    }

    private function bloquearAdministradoresActivos(): void
    {
        Usuario::where('activo', true)
            ->whereHas('rol', fn ($query) => $query->where('clave', Rol::ADMIN))
            ->lockForUpdate()
            ->get(['id']);
    }
}
