<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol_id' => $validated['rol_id'],
            'activo' => true,
            'password_changed_at' => now(),
        ]);

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
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        $rolAnterior = $usuario->rol?->clave ?? 'Sin rol';
        $emailAnterior = $usuario->email;

        $usuario->nombre = $validated['nombre'];
        $usuario->email = $validated['email'];
        $usuario->rol_id = $validated['rol_id'];

        if (! empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
            $usuario->password_changed_at = now();
        }

        $usuario->save();
        $usuario->load('rol');

        $detallePassword = ! empty($validated['password']) ? ' También se actualizó su contraseña.' : '';

        $this->bitacora(
            'Actualizar usuario interno',
            "Se actualizó el usuario {$usuario->nombre}. Email anterior: {$emailAnterior}. Rol anterior: {$rolAnterior}. Rol actual: {$usuario->rol?->clave}.{$detallePassword}",
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

        if (! $usuario->activo) {
            return redirect()->route('usuarios.index')
                ->with('error', 'El usuario ya estaba desactivado.');
        }

        $usuario->forceFill([
            'activo' => false,
            'remember_token' => null,
        ])->save();

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
}
