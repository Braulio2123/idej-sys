@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="max-w-6xl mx-auto bg-white shadow-md rounded-xl p-6 mt-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
            <p class="text-sm text-gray-500">Administración de accesos internos, roles por área y estado de cuentas.</p>
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

    <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
        <strong>Regla de producción:</strong> los usuarios ya no se eliminan físicamente. Se desactivan para conservar trazabilidad de pagos, cajas, becas, solicitudes y bitácora.
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Nombre</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Rol</th>
                    <th class="p-3 text-left">Estado</th>
                    <th class="p-3 text-left">Último acceso</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr class="border-b hover:bg-gray-50 {{ ! $u->activo ? 'bg-slate-50 text-slate-500' : '' }}">
                        <td class="p-3">{{ $u->id }}</td>
                        <td class="p-3 font-medium text-gray-800">{{ $u->nombre }}</td>
                        <td class="p-3 text-gray-600">{{ $u->email }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs bg-slate-100 text-slate-700">
                                {{ $u->rol->nombre ?? 'Sin rol' }}
                            </span>
                            <div class="text-xs text-gray-400 mt-1">{{ $u->rol->clave ?? '—' }}</div>
                        </td>
                        <td class="p-3">
                            @if($u->activo)
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Activo</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Desactivado</span>
                            @endif
                        </td>
                        <td class="p-3 text-gray-500 whitespace-nowrap">
                            {{ $u->ultimo_acceso_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="p-3 text-center space-x-2 whitespace-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}" class="text-indigo-600 font-semibold">Editar</a>

                            @if($u->activo)
                                <form action="{{ route('usuarios.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Desactivar este usuario? No se eliminará su historial.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 font-semibold">Desactivar</button>
                                </form>
                            @else
                                <form action="{{ route('usuarios.reactivar', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Reactivar este usuario?');">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-emerald-700 font-semibold">Reactivar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-5 text-center text-gray-500">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
