@extends('layouts.app')

@section('title', 'Materias')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Materias</h1>
            <p class="text-sm text-slate-500">Catálogo académico para asignar clases a grupos y docentes.</p>
        </div>
        <a href="{{ route('materias.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">
            + Nueva materia
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-2xl shadow border border-slate-100 p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por materia o clave"
               class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        <select name="programa_id" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los programas</option>
            @foreach($programas as $programa)
                <option value="{{ $programa->id }}" @selected((string) request('programa_id') === (string) $programa->id)>{{ $programa->nombre }}</option>
            @endforeach
        </select>
        <select name="estatus" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los estatus</option>
            <option value="Activa" @selected(request('estatus') === 'Activa')>Activa</option>
            <option value="Inactiva" @selected(request('estatus') === 'Inactiva')>Inactiva</option>
        </select>
        <button class="bg-slate-800 text-white rounded-xl px-4 py-2 hover:bg-slate-900">Filtrar</button>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Materia</th>
                        <th class="px-4 py-3 text-left">Programa</th>
                        <th class="px-4 py-3 text-center">Nivel/periodo</th>
                        <th class="px-4 py-3 text-center">Horas</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($materias as $materia)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $materia->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $materia->clave ?: 'Sin clave' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $materia->programa->nombre ?? 'General' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ $materia->nivel ?: '—' }}<br>
                                <span class="text-xs">{{ $materia->semestre_o_cuatrimestre ? 'Periodo '.$materia->semestre_o_cuatrimestre : 'Sin periodo' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">T: {{ $materia->horas_teoricas }} · P: {{ $materia->horas_practicas }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $materia->estatus === 'Activa' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $materia->estatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('materias.edit', $materia) }}" class="text-blue-700 hover:underline font-semibold">Editar</a>
                                <form action="{{ route('materias.destroy', $materia) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta materia?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline font-semibold ml-3">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay materias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $materias->links() }}
</div>
@endsection
