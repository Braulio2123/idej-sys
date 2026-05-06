@extends('layouts.app')

@section('title', 'Cancelar pago')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Cancelar pago #{{ $pago->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                Alumno: <strong>{{ $alumno->nombre_completo }}</strong> · Folio: <strong>{{ $pago->folio_recibo ?? 'Sin folio' }}</strong>
            </p>
        </div>
        <a href="{{ route('alumnos.pagos.index', $alumno) }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">
            ← Volver a pagos
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <p class="font-semibold mb-1">No se pudo cancelar el pago.</p>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($pago->estaCancelado())
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
            <p class="font-bold">Este pago ya fue cancelado.</p>
            <p class="text-sm mt-1">Cancelado por: {{ $pago->canceladoPor->nombre ?? '—' }} · Fecha: {{ optional($pago->fecha_cancelacion)->format('d/m/Y H:i') }}</p>
            <p class="text-sm mt-2"><strong>Motivo:</strong> {{ $pago->motivo_cancelacion ?? '—' }}</p>
        </div>
    @endif

    @if($pago->corteCaja && $pago->corteCaja->estaCerrada())
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-5 py-4 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="font-bold">Este pago pertenece a una caja cerrada.</p>
                <p class="text-sm mt-1">La cancelación directa solo está permitida mientras la caja está abierta. Para cajas cerradas usa ajuste administrativo.</p>
            </div>
            @if(! $pago->estaCancelado())
                <a href="{{ route('alumnos.pagos.ajuste-cancelacion.confirmar', [$alumno, $pago]) }}" class="px-4 py-2 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 text-center">Ir a ajuste</a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-sm text-slate-500">Monto recibido</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">${{ number_format($pago->monto_total_pagado, 2) }}</p>
            <p class="text-xs text-slate-500 mt-2">Método: {{ $pago->metodo_pago }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-sm text-slate-500">Total aplicado a adeudos</p>
            <p class="text-3xl font-bold text-indigo-700 mt-1">${{ number_format($totalAplicado, 2) }}</p>
            <p class="text-xs text-slate-500 mt-2">Cargos y parcialidades relacionados.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-sm text-slate-500">Saldo a favor generado</p>
            <p class="text-3xl font-bold text-green-700 mt-1">${{ number_format($saldoAFavorGenerado, 2) }}</p>
            <p class="text-xs text-slate-500 mt-2">Se descontará del alumno si existe saldo suficiente.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b bg-slate-50">
            <h2 class="text-lg font-bold text-slate-800">Movimientos que se revertirán</h2>
            <p class="text-sm text-slate-500 mt-1">El pago no será eliminado. Quedará marcado como cancelado y el recibo seguirá disponible con leyenda de cancelación.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="p-3 text-left">Tipo</th>
                        <th class="p-3 text-left">Concepto</th>
                        <th class="p-3 text-center">Vencimiento</th>
                        <th class="p-3 text-right">Monto aplicado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pago->cargos as $cargo)
                        <tr class="border-t">
                            <td class="p-3 font-semibold">Cargo</td>
                            <td class="p-3">
                                {{ $cargo->concepto->nombre ?? 'Cargo' }}
                                <div class="text-xs text-slate-500">{{ $cargo->descripcion_cargo }}</div>
                            </td>
                            <td class="p-3 text-center">{{ optional($cargo->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                            <td class="p-3 text-right font-semibold">${{ number_format((float) ($cargo->pivot->monto_aplicado ?? 0), 2) }}</td>
                        </tr>
                    @endforeach

                    @foreach($pago->parcialidades as $parcialidad)
                        <tr class="border-t">
                            <td class="p-3 font-semibold">Parcialidad</td>
                            <td class="p-3">
                                Convenio #{{ $parcialidad->convenio_id }}
                                <div class="text-xs text-slate-500">{{ $parcialidad->convenio->descripcion ?? 'Parcialidad de convenio' }}</div>
                            </td>
                            <td class="p-3 text-center">{{ optional($parcialidad->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                            <td class="p-3 text-right font-semibold">${{ number_format((float) ($parcialidad->pivot->monto_aplicado ?? 0), 2) }}</td>
                        </tr>
                    @endforeach

                    @if($pago->cargos->isEmpty() && $pago->parcialidades->isEmpty())
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-500">El pago no tiene cargos o parcialidades relacionados.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('alumnos.pagos.cancelar', [$alumno, $pago]) }}" method="POST" class="bg-white rounded-2xl border border-red-200 shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Motivo de cancelación <span class="text-red-600">*</span></label>
            <textarea name="motivo_cancelacion" rows="4" required minlength="10" maxlength="1000" class="w-full rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500" placeholder="Ejemplo: pago capturado al alumno equivocado, monto incorrecto, transferencia no procedente, etc.">{{ old('motivo_cancelacion') }}</textarea>
            <p class="text-xs text-slate-500 mt-1">Este motivo quedará guardado en auditoría y en el recibo cancelado.</p>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-2">
            <p class="text-sm text-red-700 font-semibold">Esta acción revierte el pago y recalcula los adeudos del alumno.</p>
            <button type="submit"
                    @if($pago->estaCancelado() || ($pago->corteCaja && $pago->corteCaja->estaCerrada())) disabled @endif
                    onclick="return confirm('¿Confirmas la cancelación del pago #{{ $pago->id }}? Esta acción no elimina el pago, pero sí revierte sus efectos financieros.')"
                    class="px-5 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Confirmar cancelación
            </button>
        </div>
    </form>
</div>
@endsection
