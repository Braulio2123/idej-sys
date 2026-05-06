@extends('layouts.app')

@section('title', 'Ajuste administrativo de pago')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white border border-amber-200 rounded-2xl shadow p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Ajuste administrativo</p>
                <h1 class="text-2xl font-bold text-slate-800 mt-1">Cancelar pago de caja cerrada</h1>
                <p class="text-sm text-slate-500 mt-2">
                    Este flujo cancela el pago y revierte el adeudo del alumno, pero conserva intacto el cierre original de caja. El movimiento queda documentado como ajuste negativo.
                </p>
            </div>
            <a href="{{ route('alumnos.pagos.index', $alumno) }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">← Volver a pagos</a>
        </div>
    </div>

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">{{ session('info') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <p class="font-semibold mb-1">Revisa la información capturada:</p>
            <ul class="list-disc ml-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow p-6 space-y-5">
            <h2 class="text-lg font-bold text-slate-800">Datos del pago</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="p-4 rounded-xl bg-slate-50 border">
                    <p class="text-slate-500">Alumno</p>
                    <p class="font-bold text-slate-800">{{ $alumno->nombre_completo }}</p>
                    <p class="text-xs text-slate-500">Matrícula: {{ $alumno->matricula ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border">
                    <p class="text-slate-500">Pago</p>
                    <p class="font-bold text-slate-800">#{{ $pago->id }} · {{ $pago->folio_recibo ?? 'Sin folio' }}</p>
                    <p class="text-xs text-slate-500">Fecha: {{ optional($pago->fecha_pago)->format('d/m/Y') }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border">
                    <p class="text-slate-500">Monto recibido</p>
                    <p class="text-2xl font-bold text-red-700">${{ number_format($pago->monto_total_pagado, 2) }}</p>
                    <p class="text-xs text-slate-500">Ajuste a registrar: -${{ number_format($pago->monto_total_pagado, 2) }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border">
                    <p class="text-slate-500">Corte de caja</p>
                    <p class="font-bold text-slate-800">#{{ $pago->corteCaja?->id ?? '—' }} · {{ $pago->corteCaja?->estatus ?? '—' }}</p>
                    <p class="text-xs text-slate-500">Cierre: {{ optional($pago->corteCaja?->fecha_cierre)->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="border rounded-xl p-4">
                    <p class="font-semibold text-slate-700 mb-2">Aplicado a cargos</p>
                    <p class="text-xl font-bold text-slate-800">${{ number_format($totalAplicado, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Este monto volverá como adeudo del alumno.</p>
                </div>
                <div class="border rounded-xl p-4">
                    <p class="font-semibold text-slate-700 mb-2">Saldo a favor generado</p>
                    <p class="text-xl font-bold text-slate-800">${{ number_format($saldoAFavorGenerado, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Se descontará del saldo a favor si aún está disponible.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('alumnos.pagos.ajuste-cancelacion', [$alumno, $pago]) }}" class="bg-white border border-red-200 rounded-2xl shadow p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-bold text-red-700">Confirmar ajuste</h2>
                <p class="text-sm text-slate-500 mt-1">Este movimiento requiere autorización administrativa y quedará en bitácora.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo del ajuste *</label>
                <textarea name="motivo_ajuste" rows="6" required class="w-full rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500" placeholder="Ejemplo: Se registró la transferencia al alumno incorrecto y la caja ya fue cerrada...">{{ old('motivo_ajuste') }}</textarea>
                <p class="text-xs text-slate-500 mt-1">Mínimo 15 caracteres. Debe explicar por qué se corrige una caja cerrada.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones internas</label>
                <textarea name="observaciones" rows="4" class="w-full rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500" placeholder="Datos adicionales, autorización verbal, referencia documental, etc.">{{ old('observaciones') }}</textarea>
            </div>

            <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 p-4 text-sm">
                <p class="font-bold">Advertencia</p>
                <p class="mt-1">El pago será marcado como cancelado, el recibo saldrá como cancelado y se generará un ajuste negativo en el corte cerrado.</p>
            </div>

            <button type="submit" class="w-full px-4 py-3 rounded-lg bg-red-700 text-white font-bold hover:bg-red-800">
                Aplicar ajuste y cancelar pago
            </button>
        </form>
    </div>
</div>
@endsection
