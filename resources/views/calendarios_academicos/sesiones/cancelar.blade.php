@extends('layouts.app')

@section('title', 'Cancelar sesión académica')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Cancelar sesión</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $calendario->nombre }} · {{ $sesion->calendarioMateria->nombre_materia }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-4">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Cancelar una sesión no la elimina. Queda registrada como cancelada para conservar historial académico y permitir seguimiento de reposiciones.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Fecha original</p>
                <p class="font-semibold text-slate-800">{{ $sesion->fecha->format('d/m/Y') }} · {{ $sesion->dia_semana }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Horario</p>
                <p class="font-semibold text-slate-800">{{ $sesion->horario }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Docente</p>
                <p class="font-semibold text-slate-800">{{ $sesion->calendarioMateria->nombre_docente }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Aula / liga</p>
                <p class="font-semibold text-slate-800">{{ $sesion->aula ?: 'Sin aula/liga' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('calendarios_academicos.sesiones.cancelar.store', [$calendario, $sesion]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de cancelación *</label>
                <textarea name="motivo_cancelacion" rows="5" class="w-full rounded-xl border-slate-300" required placeholder="Ejemplo: el docente avisó indisponibilidad, se repondrá en fecha posterior...">{{ old('motivo_cancelacion') }}</textarea>
                @error('motivo_cancelacion') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('calendarios_academicos.show', $calendario) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Regresar</a>
                <button class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700">Cancelar sesión</button>
            </div>
        </form>
    </div>
</div>
@endsection
