@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="max-w-xl mx-auto bg-white shadow-md rounded-xl p-6 mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">✏ Editar usuario</h2>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 p-3 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">
                Nueva contraseña <span class="text-gray-500 text-xs">(opcional)</span>
            </label>
            <input type="password" name="password" class="w-full border rounded p-2">
        </div>

        <div>
            <label class="block text-sm font-semibold">
                Confirmar nueva contraseña <span class="text-gray-500 text-xs">(opcional)</span>
            </label>
            <input type="password" name="password_confirmation" class="w-full border rounded p-2">
        </div>

        <div>
            <label class="block text-sm font-semibold">Rol</label>
            <select name="rol_id" class="w-full border rounded p-2" required>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario->rol_id) == $rol->id)>
                        {{ $rol->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
                Actualizar usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
