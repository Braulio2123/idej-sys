@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
<div class="max-w-xl mx-auto bg-white shadow-md rounded-xl p-6 mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">➕ Crear nuevo usuario</h2>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 p-3 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold">Nombre completo</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Contraseña</label>
            <input type="password" name="password" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="w-full border rounded p-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Rol</label>
            <select name="rol_id" class="w-full border rounded p-2" required>
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>
                        {{ $rol->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700">
                Guardar usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
