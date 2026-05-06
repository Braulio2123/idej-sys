@extends('layouts.app')

@section('title', 'Detalle de grupo')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Grupo: {{ $grupo->nombre }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $grupo->programa->nombre ?? 'Sin programa' }} · {{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('grupos.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Regresar</a>
                <a href="{{ route('grupos.edit', $grupo) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Editar grupo</a>
                <a href="{{ route('calendarios_academicos.create', ['grupo_id' => $grupo->id]) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700">+ Crear calendario</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Ciclo</p><p class="font-semibold text-slate-800">{{ $grupo->cicloEscolar->nombre ?? '—' }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Periodo</p><p class="font-semibold text-slate-800">{{ $grupo->semestre_o_cuatrimestre }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Turno</p><p class="font-semibold text-slate-800">{{ $grupo->turno }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Aula base</p><p class="font-semibold text-slate-800">{{ $grupo->aula ?? '—' }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Cupo</p><p class="font-semibold text-slate-800">{{ $grupo->alumnos->count() }} / {{ $grupo->cupo_maximo }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Calendarios académicos del grupo</h2>
                <p class="text-sm text-slate-500">Planeación por fechas exactas asignadas a este grupo.</p>
            </div>
            <a href="{{ route('calendarios_academicos.index', ['grupo_id' => $grupo->id]) }}" class="text-sm text-indigo-700 font-semibold hover:underline">Ver en calendarios</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Calendario</th>
                        <th class="px-4 py-3 text-center">Periodo</th>
                        <th class="px-4 py-3 text-center">Materias</th>
                        <th class="px-4 py-3 text-center">Rango</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grupo->calendariosAcademicos as $calendario)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $calendario->nombre }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calendario->periodo ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calendario->materiasCalendario->count() }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calendario->rango_fechas }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calendario->estatus }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('calendarios_academicos.show', $calendario) }}" class="text-indigo-700 font-semibold hover:underline">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Este grupo aún no tiene calendarios académicos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">Alumnos del grupo</h2>
        @if($grupo->alumnos->isEmpty())
            <p class="text-slate-500">No hay alumnos asignados a este grupo.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($grupo->alumnos as $alumno)
                    <a href="{{ route('alumnos.show', $alumno) }}" class="block rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                        <p class="font-semibold text-slate-800">{{ $alumno->nombre_completo }}</p>
                        <p class="text-xs text-slate-500">{{ $alumno->matricula }} · {{ $alumno->estatus_academico }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
