@extends('layouts.app')

@section('title', 'Cerrar Caja')

@section('content')
@php
    $efectivoEsperado = (float) $corteCaja->saldo_inicial + (float) $totalesActuales['efectivo_sistema'];
    $totalEsperado = (float) $corteCaja->saldo_inicial + (float) $totalesActuales['total_sistema'];
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Cerrar Caja #{{ $corteCaja->id }}</h2>
            <p class="text-sm text-slate-500">Revisa los importes del sistema y captura lo realmente reportado.</p>
        </div>
        <a href="{{ route('cortes-caja.show', $corteCaja) }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">← Volver</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Saldo inicial</p>
            <p class="text-2xl font-bold">${{ number_format($corteCaja->saldo_inicial, 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Efectivo cobrado</p>
            <p class="text-2xl font-bold text-green-700">${{ number_format($totalesActuales['efectivo_sistema'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Total ingresos sistema</p>
            <p class="text-2xl font-bold text-indigo-700">${{ number_format($totalesActuales['total_sistema'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-amber-50 border-amber-200">
            <p class="text-sm text-slate-500">Efectivo esperado</p>
            <p class="text-2xl font-bold text-amber-700">${{ number_format($efectivoEsperado, 2) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('cortes-caja.cerrar', $corteCaja) }}" class="bg-white border border-slate-200 rounded-xl p-6 space-y-5 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Efectivo físico reportado</label>
                <input type="number" step="0.01" min="0" name="efectivo_reportado" value="{{ old('efectivo_reportado', number_format($efectivoEsperado, 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
                <p class="text-xs text-slate-500 mt-1">Incluye saldo inicial + efectivo cobrado.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Transferencias reportadas</label>
                <input type="number" step="0.01" min="0" name="transferencia_reportado" value="{{ old('transferencia_reportado', number_format($totalesActuales['transferencia_sistema'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tarjeta reportada</label>
                <input type="number" step="0.01" min="0" name="tarjeta_reportado" value="{{ old('tarjeta_reportado', number_format($totalesActuales['tarjeta_sistema'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
            <p><strong>Total esperado incluyendo saldo inicial:</strong> ${{ number_format($totalEsperado, 2) }}</p>
            <p class="mt-1">Si el importe reportado no coincide, el sistema guardará la diferencia para revisión administrativa.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones de cierre</label>
            <textarea name="observaciones_cierre" rows="4" class="w-full rounded-lg border-slate-300" placeholder="Ej: diferencia por cambio, transferencia pendiente de confirmar, pago duplicado por revisar.">{{ old('observaciones_cierre') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('cortes-caja.show', $corteCaja) }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">Cancelar</a>
            <button class="px-5 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">Cerrar caja</button>
        </div>
    </form>
</div>
@endsection
