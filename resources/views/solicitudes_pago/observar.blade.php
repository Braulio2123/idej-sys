@extends('layouts.app')

@section('title', 'Observar Solicitud')

@section('content')
<div class="max-w-2xl mx-auto mt-6">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-slate-200">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Observar / devolver solicitud</h1>
        <p class="text-sm text-slate-500 mb-5">La solicitud volverá a Académica para corrección.</p>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-5 text-sm text-orange-900">
            <p><strong>Solicitud:</strong> {{ $solicitud->folio ?? '#'.$solicitud->id }}</p>
            <p><strong>Docente:</strong> {{ $solicitud->docente->nombre_completo ?? '—' }}</p>
            <p><strong>Monto:</strong> ${{ number_format($solicitud->monto, 2) }}</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('solicitudes_pago.observar', $solicitud) }}">
            @csrf
            @method('PUT')

            <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de observación *</label>
            <textarea name="motivo_observacion" required rows="5" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Ej. Falta especificar fechas, monto no coincide con horas, docente incorrecto, etc.">{{ old('motivo_observacion', $solicitud->motivo_observacion) }}</textarea>

            <div class="flex justify-between mt-6">
                <a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">Cancelar</a>
                <button class="px-6 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold shadow">Marcar como observada</button>
            </div>
        </form>
    </div>
</div>
@endsection
