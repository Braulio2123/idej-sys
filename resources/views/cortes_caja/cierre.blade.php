@extends('layouts.app')

@section('title', 'Cerrar Caja')

@section('content')
@php
    $resumenMovimientos = $resumenMovimientos ?? [
        'neto_efectivo' => 0,
        'neto_transferencia' => 0,
        'neto_tarjeta' => 0,
        'neto_otro' => 0,
        'entradas_total' => 0,
        'salidas_total' => 0,
        'neto_total' => 0,
        'cantidad' => 0,
    ];

    $esperados = [
        'efectivo' => round((float) $corteCaja->saldo_inicial + (float) $totalesActuales['efectivo_sistema'] + (float) $resumenMovimientos['neto_efectivo'], 2),
        'transferencia' => round((float) $totalesActuales['transferencia_sistema'] + (float) $resumenMovimientos['neto_transferencia'], 2),
        'tarjeta' => round((float) $totalesActuales['tarjeta_sistema'] + (float) $resumenMovimientos['neto_tarjeta'], 2),
        'otro' => round((float) $resumenMovimientos['neto_otro'], 2),
    ];
    $totalEsperado = round(array_sum($esperados), 2);
@endphp

<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Cerrar Caja #{{ $corteCaja->id }}</h2>
            <p class="text-sm text-slate-500">Confirma cada método por separado. El sistema no compensará una diferencia de un método con otro.</p>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Efectivo esperado</p>
            <p class="text-2xl font-bold text-amber-700">${{ number_format($esperados['efectivo'], 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Incluye saldo inicial.</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Transferencia esperada</p>
            <p class="text-2xl font-bold text-blue-700">${{ number_format($esperados['transferencia'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Tarjeta esperada</p>
            <p class="text-2xl font-bold text-purple-700">${{ number_format($esperados['tarjeta'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Otros métodos</p>
            <p class="text-2xl font-bold text-cyan-700">${{ number_format($esperados['otro'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-slate-50">
            <p class="text-sm text-slate-500">Total esperado</p>
            <p class="text-2xl font-bold text-indigo-700">${{ number_format($totalEsperado, 2) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('cortes-caja.cerrar', $corteCaja) }}" id="form-cierre-caja" class="bg-white border border-slate-200 rounded-xl p-6 space-y-5 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Efectivo físico reportado</label>
                <input type="number" step="0.01" min="0" max="99999999.99" name="efectivo_reportado" id="efectivo_reportado" value="{{ old('efectivo_reportado', number_format($esperados['efectivo'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
                <p class="text-xs text-slate-500 mt-1">Dinero contado físicamente.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Neto confirmado por transferencia</label>
                <input type="number" step="0.01" min="-99999999.99" max="99999999.99" name="transferencia_reportado" id="transferencia_reportado" value="{{ old('transferencia_reportado', number_format($esperados['transferencia'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
                <p class="text-xs text-slate-500 mt-1">Ingresos menos salidas registradas.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Neto confirmado por tarjeta</label>
                <input type="number" step="0.01" min="-99999999.99" max="99999999.99" name="tarjeta_reportado" id="tarjeta_reportado" value="{{ old('tarjeta_reportado', number_format($esperados['tarjeta'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
                <p class="text-xs text-slate-500 mt-1">Conciliación de terminal o proveedor.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Neto confirmado en otros métodos</label>
                <input type="number" step="0.01" min="-99999999.99" max="99999999.99" name="otro_reportado" id="otro_reportado" value="{{ old('otro_reportado', number_format($esperados['otro'], 2, '.', '')) }}" class="w-full rounded-lg border-slate-300" required>
                <p class="text-xs text-slate-500 mt-1">Solo movimientos clasificados como “Otro”.</p>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
            <p><strong>Movimientos registrados:</strong> {{ $resumenMovimientos['cantidad'] }}.</p>
            <p class="mt-1">Entradas: ${{ number_format($resumenMovimientos['entradas_total'], 2) }} · Salidas: ${{ number_format($resumenMovimientos['salidas_total'], 2) }} · Neto: ${{ number_format($resumenMovimientos['neto_total'], 2) }}.</p>
            <p class="mt-1">Una diferencia en cualquier método exige explicación y confirmación, aunque la diferencia total sea cero.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones de cierre</label>
            <textarea name="observaciones_cierre" rows="4" class="w-full rounded-lg border-slate-300" placeholder="Ej.: transferencia pendiente de confirmar, diferencia de efectivo revisada con el responsable.">{{ old('observaciones_cierre') }}</textarea>
            <p class="text-xs text-slate-500 mt-1">Son obligatorias cuando exista cualquier diferencia.</p>
        </div>

        <div id="aviso-diferencia" class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">El cierre presenta una o más diferencias.</p>
            <ul id="detalle-diferencias" class="mt-2 list-disc pl-5 space-y-1"></ul>
            <p class="mt-2">Diferencia total estimada: <strong id="diferencia-total-estimada">$0.00</strong></p>
            <label class="mt-3 flex items-start gap-2">
                <input type="checkbox" name="confirmar_diferencia" value="1" class="mt-1 rounded border-red-300" @checked(old('confirmar_diferencia'))>
                <span>Confirmo que revisé cada método y que las diferencias quedaron explicadas en las observaciones.</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('cortes-caja.show', $corteCaja) }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">Cancelar</a>
            <button class="px-5 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">Cerrar caja</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const esperados = @json($esperados);
        const campos = {
            efectivo: document.getElementById('efectivo_reportado'),
            transferencia: document.getElementById('transferencia_reportado'),
            tarjeta: document.getElementById('tarjeta_reportado'),
            otro: document.getElementById('otro_reportado')
        };
        const etiquetas = {
            efectivo: 'Efectivo',
            transferencia: 'Transferencia',
            tarjeta: 'Tarjeta',
            otro: 'Otros métodos'
        };
        const aviso = document.getElementById('aviso-diferencia');
        const detalle = document.getElementById('detalle-diferencias');
        const diferenciaTexto = document.getElementById('diferencia-total-estimada');
        const formato = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

        function actualizarDiferencia() {
            const diferencias = {};
            let totalReportado = 0;
            let totalEsperado = 0;

            Object.entries(campos).forEach(([metodo, input]) => {
                const reportado = Number.parseFloat(input?.value || '0');
                const esperado = Number.parseFloat(esperados[metodo] || '0');
                diferencias[metodo] = Math.round((reportado - esperado) * 100) / 100;
                totalReportado += reportado;
                totalEsperado += esperado;
            });

            const diferenciaTotal = Math.round((totalReportado - totalEsperado) * 100) / 100;
            const diferenciasVisibles = Object.entries(diferencias).filter(([, diferencia]) => Math.abs(diferencia) >= 0.01);
            const hayDiferencia = diferenciasVisibles.length > 0 || Math.abs(diferenciaTotal) >= 0.01;

            aviso?.classList.toggle('hidden', ! hayDiferencia);

            if (detalle) {
                detalle.innerHTML = '';
                diferenciasVisibles.forEach(([metodo, diferencia]) => {
                    const item = document.createElement('li');
                    item.textContent = `${etiquetas[metodo]}: ${formato.format(diferencia)}`;
                    detalle.appendChild(item);
                });
            }

            if (diferenciaTexto) {
                diferenciaTexto.textContent = formato.format(diferenciaTotal);
            }
        }

        Object.values(campos).forEach((input) => input?.addEventListener('input', actualizarDiferencia));
        actualizarDiferencia();
    });
</script>
@endpush
