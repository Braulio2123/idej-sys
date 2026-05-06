@extends('layouts.app')

@section('title', 'Cancelar Beca')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <a href="{{ route('alumnos.becas.index', $alumno) }}" class="text-blue-700 hover:underline font-semibold">← Volver al historial de becas</a>

    <div class="bg-white rounded-2xl border border-red-100 shadow p-6 mt-4">
        <h1 class="text-3xl font-bold text-red-700">Cancelar beca</h1>
        <p class="text-slate-600 mt-2">Alumno: <strong>{{ $alumno->nombre_completo }}</strong></p>

        <div class="mt-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-900">
            Estás por cancelar la beca <strong>{{ $beca->tipo }}</strong> del <strong>{{ $beca->porcentaje }}%</strong>, vigente desde {{ $beca->fecha_inicio?->format('d/m/Y') }} hasta {{ $beca->fecha_fin?->format('d/m/Y') ?? 'fecha indefinida' }}.
            Los cargos ya generados conservarán su historial de descuento aplicado.
        </div>

        @if ($errors->any())
            <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('alumnos.becas.cancelar', [$alumno, $beca]) }}" class="mt-5">
            @csrf
            @method('PUT')

            <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de cancelación</label>
            <textarea name="motivo_cancelacion" rows="5" required minlength="10" class="w-full rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500" placeholder="Explica claramente por qué se cancela la beca. Esta información quedará en bitácora.">{{ old('motivo_cancelacion') }}</textarea>

            <div class="mt-5 flex justify-end gap-2">
                <a href="{{ route('alumnos.becas.index', $alumno) }}" class="px-5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">Regresar</a>
                <button class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow" onclick="return confirm('¿Confirmas la cancelación de esta beca?');">Cancelar beca</button>
            </div>
        </form>
    </div>
</div>
@endsection
