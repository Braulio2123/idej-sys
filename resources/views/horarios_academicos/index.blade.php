@extends('layouts.app')

@section('title', 'Horarios académicos')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Horarios académicos</h1>
            <p class="text-sm text-slate-500">Asignación formal de materia, docente, grupo, aula y horario.</p>
        </div>
        <a href="{{ route('horarios_academicos.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">
            + Nuevo horario
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-2xl shadow border border-slate-100 p-4 grid grid-cols-1 md:grid-cols-5 gap-4">
        <select name="grupo_id" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los grupos</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected((string) request('grupo_id') === (string) $grupo->id)>{{ $grupo->nombre }}</option>
            @endforeach
        </select>
        <select name="docente_id" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los docentes</option>
            @foreach($docentes as $docente)
                <option value="{{ $docente->id }}" @selected((string) request('docente_id') === (string) $docente->id)>{{ $docente->nombre_completo }}</option>
            @endforeach
        </select>
        <select name="dia_semana" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los días</option>
            @foreach($dias as $dia)
                <option value="{{ $dia }}" @selected(request('dia_semana') === $dia)>{{ $dia }}</option>
            @endforeach
        </select>
        <select name="estatus" class="rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todos los estatus</option>
            @foreach(['Activo','Suspendido','Finalizado'] as $estatus)
                <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 text-white rounded-xl px-4 py-2 hover:bg-slate-900">Filtrar</button>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Día / hora</th>
                        <th class="px-4 py-3 text-left">Grupo</th>
                        <th class="px-4 py-3 text-left">Materia</th>
                        <th class="px-4 py-3 text-left">Docente</th>
                        <th class="px-4 py-3 text-center">Aula</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($horarios as $horario)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $horario->dia_semana }}</p>
                                <p class="text-xs text-slate-500">{{ $horario->horario }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $horario->grupo->nombre ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $horario->grupo->programa->nombre ?? 'Sin programa' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $horario->materia->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $horario->docente->nombre_completo ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $horario->aula ?? '—' }}<br><span class="text-xs">{{ $horario->modalidad }}</span></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $horario->estatus === 'Activo' ? 'bg-green-100 text-green-700' : ($horario->estatus === 'Suspendido' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $horario->estatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('horarios_academicos.show', $horario) }}" class="text-indigo-700 hover:underline font-semibold">Ver</a>
                                <a href="{{ route('horarios_academicos.edit', $horario) }}" class="text-blue-700 hover:underline font-semibold ml-3">Editar</a>
                                <form action="{{ route('horarios_academicos.destroy', $horario) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este horario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline font-semibold ml-3">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay horarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $horarios->links() }}
</div>
@endsection
