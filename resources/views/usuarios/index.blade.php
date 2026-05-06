@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="max-w-6xl mx-auto bg-white shadow-md rounded-xl p-6 mt-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
            <p class="text-sm text-gray-500">Administración de accesos internos y roles por área.</p>
        </div>

        <a href="{{ route('usuarios.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700">
            + Crear nuevo usuario
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 p-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 p-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Nombre</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Rol</th>
                    <th class="p-3 text-left">Clave</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $u->id }}</td>
                        <td class="p-3 font-medium text-gray-800">{{ $u->nombre }}</td>
                        <td class="p-3 text-gray-600">{{ $u->email }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs bg-slate-100 text-slate-700">
                                {{ $u->rol->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="p-3 text-gray-500">{{ $u->rol->clave ?? '—' }}</td>
                        <td class="p-3 text-center space-x-2 whitespace-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}" class="text-indigo-600 font-semibold">Editar</a>

                            <form action="{{ route('usuarios.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar usuario?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 font-semibold">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center text-gray-500">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
