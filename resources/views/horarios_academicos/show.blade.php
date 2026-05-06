@extends('layouts.app')

@section('title', 'Detalle de horario')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">{{ $horario->materia->nombre ?? 'Materia' }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $horario->grupo->nombre ?? 'Grupo' }} · {{ $horario->dia_semana }} · {{ $horario->horario }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('horarios_academicos.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Regresar</a>
                <a href="{{ route('horarios_academicos.edit', $horario) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Editar</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Grupo</p>
                <p class="font-semibold text-slate-800">{{ $horario->grupo->nombre ?? '—' }}</p>
                <p class="text-sm text-slate-500">{{ $horario->grupo->programa->nombre ?? 'Sin programa' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Docente</p>
                <p class="font-semibold text-slate-800">{{ $horario->docente->nombre_completo ?? '—' }}</p>
                <p class="text-sm text-slate-500">{{ $horario->docente->area_especialidad ?? '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Día y hora</p>
                <p class="font-semibold text-slate-800">{{ $horario->dia_semana }}</p>
                <p class="text-sm text-slate-500">{{ $horario->horario }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Aula</p>
                <p class="font-semibold text-slate-800">{{ $horario->aula ?? '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Modalidad</p>
                <p class="font-semibold text-slate-800">{{ $horario->modalidad }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <p class="text-xs text-slate-500">Estatus</p>
                <p class="font-semibold text-slate-800">{{ $horario->estatus }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 md:col-span-3">
                <p class="text-xs text-slate-500">Vigencia</p>
                <p class="font-semibold text-slate-800">
                    {{ $horario->fecha_inicio ? $horario->fecha_inicio->format('d/m/Y') : 'Sin inicio definido' }}
                    —
                    {{ $horario->fecha_fin ? $horario->fecha_fin->format('d/m/Y') : 'Sin fin definido' }}
                </p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 md:col-span-3">
                <p class="text-xs text-slate-500">Observaciones</p>
                <p class="text-slate-700 whitespace-pre-line">{{ $horario->observaciones ?: 'Sin observaciones.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
