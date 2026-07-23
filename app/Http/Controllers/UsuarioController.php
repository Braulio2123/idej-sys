<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
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
        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
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
        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
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

        $passwordActualizada = ! empty($validated['password']);

        if ($passwordActualizada && $usuario->passwordUsadaRecientemente($validated['password'], 6)) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['password' => 'Por seguridad, usa una contraseña que no se haya utilizado con este usuario en los últimos 6 meses.']);
        }

        try {
            $datosBitacora = DB::transaction(function () use ($usuario, $validated, $passwordActualizada, $request) {
                $usuario->refresh();
                $usuario->load('rol');

                $rolAnterior = $usuario->rol?->clave ?? 'Sin rol';
                $emailAnterior = $usuario->email;
                $nuevoRol = Rol::find($validated['rol_id']);

                $this->bloquearAdministradoresActivos();

                if ($usuario->rolClave() === Rol::ADMIN && $nuevoRol?->clave !== Rol::ADMIN && $this->esUltimoAdminActivo($usuario)) {
                    throw new \RuntimeException('ULTIMO_ADMIN_ACTIVO');
                }

                $usuario->nombre = $validated['nombre'];
                $usuario->email = $validated['email'];
                $usuario->telefono_notificaciones = null;
                $usuario->whatsapp_notificaciones = null;
                $usuario->notificar_email = $request->boolean('notificar_email', true);
                $usuario->notificar_sms = false;
                $usuario->notificar_whatsapp = false;
                $usuario->rol_id = $validated['rol_id'];

                if ($passwordActualizada) {
                    $hashPassword = Hash::make($validated['password']);
                    $usuario->password = $hashPassword;
                    $usuario->password_changed_at = now();
                    $usuario->must_change_password = false;
                    $usuario->temporary_password_generated_at = null;
                    $usuario->temporary_password_expires_at = null;
                    $usuario->remember_token = null;
                }

                $usuario->save();

                if ($passwordActualizada) {
                    $usuario->registrarPasswordEnHistorial($hashPassword);
                }
                $usuario->load('rol');

                return [$rolAnterior, $emailAnterior, $usuario->rol?->clave];
            }, 3);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'ULTIMO_ADMIN_ACTIVO') {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No se puede cambiar el rol del último administrador activo. Primero asigna otro administrador.');
            }

            throw $e;
        }

        [$rolAnterior, $emailAnterior, $rolActual] = $datosBitacora;

        if ($passwordActualizada && auth()->id() === $usuario->id && $usuario->password_changed_at) {
            session()->put('auth.password_changed_at', $usuario->password_changed_at->timestamp);
            session()->forget('auth.password_confirmed_at');
        }

        $detallePassword = $passwordActualizada ? ' También se actualizó su contraseña.' : '';

        $this->bitacora(
            'Actualizar usuario interno',
            "Se actualizó el usuario {$usuario->nombre}. Email anterior: {$emailAnterior}. Rol anterior: {$rolAnterior}. Rol actual: {$rolActual}.{$detallePassword}",
            'Usuarios',
            $usuario
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        if (auth()->id() === $usuario->id) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propio usuario mientras tienes la sesión activa.');
        }

        try {
            DB::transaction(function () use ($usuario) {
                $usuario->refresh();
                $usuario->load('rol');

                if (! $usuario->activo) {
                    throw new \RuntimeException('USUARIO_YA_INACTIVO');
                }

                $this->bloquearAdministradoresActivos();

                if ($this->esUltimoAdminActivo($usuario)) {
                    throw new \RuntimeException('ULTIMO_ADMIN_ACTIVO');
                }

                $usuario->forceFill([
                    'activo' => false,
                    'remember_token' => null,
                ])->save();
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

        $this->bitacora(
            'Desactivar usuario interno',
            "Se desactivó el usuario {$usuario->nombre} ({$usuario->email}).",
            'Usuarios',
            $usuario
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente. No fue eliminado para conservar trazabilidad.');
    }

    public function reactivar(Usuario $usuario): RedirectResponse
    {
        if ($usuario->activo) {
            return redirect()->route('usuarios.index')
                ->with('error', 'El usuario ya está activo.');
        }

        $usuario->forceFill(['activo' => true])->save();

        $this->bitacora(
            'Reactivar usuario interno',
            "Se reactivó el usuario {$usuario->nombre} ({$usuario->email}).",
            'Usuarios',
            $usuario
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario reactivado correctamente.');
    }



    public function generarPasswordTemporal(Usuario $usuario): RedirectResponse
    {
        if (! $usuario->activo) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede generar una contraseña temporal para un usuario desactivado. Primero reactiva la cuenta.');
        }

        $temporal = 'IDEJ-'.Str::upper(Str::random(4)).'-'.Str::random(4).'!'.random_int(10, 99);
        $hashPassword = Hash::make($temporal);

        $usuario->forceFill([
            'password' => $hashPassword,
            'password_changed_at' => now(),
            'must_change_password' => true,
            'temporary_password_generated_at' => now(),
            'temporary_password_expires_at' => now()->addDays(7),
            'remember_token' => null,
        ])->save();

        $usuario->registrarPasswordEnHistorial($hashPassword);

        $this->bitacora(
            'Generar contraseña temporal',
            "Se generó una contraseña temporal para el usuario {$usuario->nombre} ({$usuario->email}). El usuario deberá cambiarla al iniciar sesión.",
            'Usuarios',
            $usuario
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Contraseña temporal generada para '.$usuario->nombre.': '.$temporal.' Cópiala ahora; por seguridad no se volverá a mostrar. El usuario deberá cambiarla al iniciar sesión.');
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
