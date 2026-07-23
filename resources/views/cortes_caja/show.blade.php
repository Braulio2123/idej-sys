@extends('layouts.app')

@section('title', 'Corte de Caja')

@section('content')
@php
    use App\Models\Rol;
    use App\Models\MovimientoCaja;
    $usuarioActual = Auth::user();
    $puedeCancelarPagos = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;

    $efectivoEsperado = (float) $corteCaja->saldo_inicial + (float) $totalesActuales['efectivo_sistema'];
    $totalEsperado = (float) $corteCaja->saldo_inicial + (float) $totalesActuales['total_sistema'];
    $resumenAjustes = $resumenAjustes ?? [
        'efectivo_ajustes' => 0,
        'transferencia_ajustes' => 0,
        'tarjeta_ajustes' => 0,
        'total_ajustes' => 0,
        'cantidad_ajustes' => 0,
    ];
    $resumenMovimientos = $resumenMovimientos ?? [
        'entradas_efectivo' => 0,
        'salidas_efectivo' => 0,
        'neto_efectivo' => 0,
        'entradas_total' => 0,
        'salidas_total' => 0,
        'neto_total' => 0,
        'cantidad' => 0,
    ];
    $efectivoEsperado = $efectivoEsperado + (float) $resumenMovimientos['neto_efectivo'];
    $totalEsperado = $totalEsperado + (float) $resumenMovimientos['neto_total'];
    $totalNetoAjustado = (float) $totalesActuales['total_sistema'] + (float) $resumenAjustes['total_ajustes'] + (float) $resumenMovimientos['neto_total'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Corte de Caja #{{ $corteCaja->id }}</h2>
            <p class="text-sm text-slate-500">
                Usuario: <strong>{{ $corteCaja->usuario->nombre ?? '—' }}</strong> · Apertura: {{ optional($corteCaja->fecha_apertura)->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cortes-caja.index') }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">← Volver</a>
            @if($corteCaja->estaCerrada())
                <a href="{{ route('cortes-caja.pdf', $corteCaja) }}" target="_blank" class="px-4 py-2 rounded-lg bg-slate-700 text-white font-semibold hover:bg-slate-800">PDF oficial</a>
            @endif
            @if($corteCaja->estaAbierta())
                <a href="{{ route('cortes-caja.cierre', $corteCaja) }}" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">Cerrar caja</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">{{ session('info') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Saldo inicial</p>
            <p class="text-2xl font-bold text-slate-800">${{ number_format($corteCaja->saldo_inicial, 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Efectivo cobrado</p>
            <p class="text-2xl font-bold text-green-700">${{ number_format($totalesActuales['efectivo_sistema'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Transferencias</p>
            <p class="text-2xl font-bold text-blue-700">${{ number_format($totalesActuales['transferencia_sistema'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Tarjeta</p>
            <p class="text-2xl font-bold text-purple-700">${{ number_format($totalesActuales['tarjeta_sistema'], 2) }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Total ingresos</p>
            <p class="text-2xl font-bold text-indigo-700">${{ number_format($totalesActuales['total_sistema'], 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl border bg-slate-50">
            <p class="text-sm text-slate-500">Efectivo esperado en caja</p>
            <p class="text-2xl font-bold text-slate-800">${{ number_format($efectivoEsperado, 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Saldo inicial + pagos en efectivo.</p>
        </div>
        <div class="p-5 rounded-xl border bg-slate-50">
            <p class="text-sm text-slate-500">Pagos registrados</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totalesActuales['cantidad_pagos'] }}</p>
        </div>
        <div class="p-5 rounded-xl border bg-slate-50">
            <p class="text-sm text-slate-500">Estatus</p>
            @if($corteCaja->estaAbierta())
                <p class="text-2xl font-bold text-green-700">Abierta</p>
            @else
                <p class="text-2xl font-bold text-slate-700">Cerrada</p>
                <p class="text-xs text-slate-500 mt-1">{{ optional($corteCaja->fecha_cierre)->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Entradas operativas</p>
            <p class="text-2xl font-bold text-green-700">${{ number_format($resumenMovimientos['entradas_total'], 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Cambio adicional u otros ingresos no vinculados a colegiaturas.</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Salidas operativas</p>
            <p class="text-2xl font-bold text-red-700">${{ number_format($resumenMovimientos['salidas_total'], 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Compras menores, agua, papelería o egresos autorizados.</p>
        </div>
        <div class="p-5 rounded-xl border bg-white">
            <p class="text-sm text-slate-500">Neto de movimientos</p>
            <p class="text-2xl font-bold {{ $resumenMovimientos['neto_total'] < 0 ? 'text-red-700' : 'text-green-700' }}">${{ number_format($resumenMovimientos['neto_total'], 2) }}</p>
            <p class="text-xs text-slate-500 mt-1">Impacta el efectivo esperado al cerrar caja.</p>
        </div>
    </div>

    @if($corteCaja->estaAbierta())
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Registrar entrada o salida de caja</h3>
            <p class="text-sm text-slate-500 mb-4">Usa este apartado para cambio, compras menores autorizadas o salidas operativas. Los pagos de alumnos siguen registrándose desde el expediente del alumno. Cada registro conserva el usuario que realizó la operación; evita usar cuentas compartidas.</p>

            <form method="POST" action="{{ route('cortes-caja.movimientos.store', $corteCaja) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo *</label>
                    <select name="tipo" required class="w-full rounded-lg border-slate-300">
                        @foreach(MovimientoCaja::tipos() as $tipoMovimiento)
                            <option value="{{ $tipoMovimiento }}" @selected(old('tipo') === $tipoMovimiento)>{{ $tipoMovimiento }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Concepto *</label>
                    <select name="concepto" required class="w-full rounded-lg border-slate-300">
                        @foreach(MovimientoCaja::conceptosSugeridos() as $conceptoMovimiento)
                            <option value="{{ $conceptoMovimiento }}" @selected(old('concepto') === $conceptoMovimiento)>{{ $conceptoMovimiento }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Monto *</label>
                    <input type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required class="w-full rounded-lg border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Método *</label>
                    <select name="metodo_pago" required class="w-full rounded-lg border-slate-300">
                        @foreach(MovimientoCaja::metodosPago() as $metodoMovimiento)
                            <option value="{{ $metodoMovimiento }}" @selected(old('metodo_pago', 'Efectivo') === $metodoMovimiento)>{{ $metodoMovimiento }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Referencia</label>
                    <input type="text" name="referencia" value="{{ old('referencia') }}" class="w-full rounded-lg border-slate-300" placeholder="Ticket, autorización, referencia bancaria o nota interna">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Comprobante</label>
                    <input type="file" name="comprobante" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-slate-300 bg-slate-50">
                    <p class="text-xs text-slate-500 mt-1">Opcional. PDF o imagen, máximo 4 MB.</p>
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Ej. Compra de agua para recepción, salida autorizada por coordinación, cambio adicional recibido.">{{ old('observaciones') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button class="px-5 py-2 rounded-lg bg-cyan-600 text-white font-semibold hover:bg-cyan-700">Guardar movimiento</button>
                </div>
            </form>
        </div>
    @endif

    @if($corteCaja->movimientos->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Entradas y salidas de caja</h3>
                    <p class="text-xs text-slate-500">Los folios internos de movimientos pueden no ser consecutivos si existen cancelaciones o registros históricos.</p>
                </div>
                <span class="text-xs text-slate-500">{{ $corteCaja->movimientos->count() }} movimiento(s), incluyendo cancelados</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Movimiento</th>
                            <th class="p-3 text-center">Fecha</th>
                            <th class="p-3 text-center">Método</th>
                            <th class="p-3 text-left">Referencia</th>
                            <th class="p-3 text-left">Usuario</th>
                            <th class="p-3 text-center">Estatus</th>
                            <th class="p-3 text-right">Monto</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corteCaja->movimientos->sortByDesc('fecha_movimiento') as $movimiento)
                            <tr class="border-t hover:bg-slate-50 {{ $movimiento->estaCancelado() ? 'bg-red-50 text-slate-500' : '' }}">
                                <td class="p-3">
                                    <p class="font-semibold">{{ $movimiento->tipo }} · {{ $movimiento->concepto }}</p>
                                    <p class="text-[11px] text-slate-400">Folio interno de movimiento: #{{ $movimiento->id }}</p>
                                    @if($movimiento->observaciones)
                                        <p class="text-xs text-slate-500 mt-1">{{ $movimiento->observaciones }}</p>
                                    @endif
                                    @if($movimiento->motivo_cancelacion)
                                        <p class="text-xs text-red-600 mt-1">Cancelado: {{ $movimiento->motivo_cancelacion }}</p>
                                    @endif
                                </td>
                                <td class="p-3 text-center">{{ optional($movimiento->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                                <td class="p-3 text-center">{{ $movimiento->metodo_pago }}</td>
                                <td class="p-3">{{ $movimiento->referencia ?? '—' }}</td>
                                <td class="p-3">{{ $movimiento->usuario->nombre ?? '—' }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $movimiento->estaCancelado() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $movimiento->estatus }}</span>
                                </td>
                                <td class="p-3 text-right font-bold {{ $movimiento->tipo === MovimientoCaja::TIPO_SALIDA ? 'text-red-700' : 'text-green-700' }}">
                                    {{ $movimiento->tipo === MovimientoCaja::TIPO_SALIDA ? '-' : '+' }}${{ number_format($movimiento->monto, 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($corteCaja->estaAbierta() && ! $movimiento->estaCancelado())
                                        <form method="POST" action="{{ route('cortes-caja.movimientos.cancelar', [$corteCaja, $movimiento]) }}" onsubmit="return confirm('¿Cancelar este movimiento de caja?');" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="motivo_cancelacion" value="Cancelado por corrección operativa antes del cierre de caja.">
                                            <button class="text-red-700 hover:underline font-semibold">Cancelar</button>
                                        </form>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($corteCaja->estaCerrada())
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 rounded-xl border bg-white">
                <p class="text-sm text-slate-500">Efectivo reportado</p>
                <p class="text-xl font-bold">${{ number_format($corteCaja->efectivo_reportado, 2) }}</p>
            </div>
            <div class="p-5 rounded-xl border bg-white">
                <p class="text-sm text-slate-500">Transferencia reportada</p>
                <p class="text-xl font-bold">${{ number_format($corteCaja->transferencia_reportado, 2) }}</p>
            </div>
            <div class="p-5 rounded-xl border bg-white">
                <p class="text-sm text-slate-500">Tarjeta reportada</p>
                <p class="text-xl font-bold">${{ number_format($corteCaja->tarjeta_reportado, 2) }}</p>
            </div>
            <div class="p-5 rounded-xl border {{ (float)$corteCaja->diferencia_total === 0.0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <p class="text-sm text-slate-500">Diferencia total</p>
                <p class="text-xl font-bold {{ (float)$corteCaja->diferencia_total === 0.0 ? 'text-green-700' : 'text-red-700' }}">${{ number_format($corteCaja->diferencia_total, 2) }}</p>
            </div>
        </div>
    @endif

    @if($corteCaja->estaCerrada() && (int) $resumenAjustes['cantidad_ajustes'] > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-amber-900">Ajustes administrativos aplicados</h3>
                    <p class="text-sm text-amber-800 mt-1">Estos movimientos documentan correcciones posteriores al cierre sin modificar el corte original.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-amber-700 uppercase font-semibold">Neto ajustado</p>
                    <p class="text-2xl font-bold {{ $totalNetoAjustado < 0 ? 'text-red-700' : 'text-amber-900' }}">${{ number_format($totalNetoAjustado, 2) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-white border rounded-lg p-4">
                    <p class="text-xs text-slate-500">Cantidad de ajustes</p>
                    <p class="text-xl font-bold text-slate-800">{{ $resumenAjustes['cantidad_ajustes'] }}</p>
                </div>
                <div class="bg-white border rounded-lg p-4">
                    <p class="text-xs text-slate-500">Ajuste efectivo</p>
                    <p class="text-xl font-bold {{ $resumenAjustes['efectivo_ajustes'] < 0 ? 'text-red-700' : 'text-green-700' }}">${{ number_format($resumenAjustes['efectivo_ajustes'], 2) }}</p>
                </div>
                <div class="bg-white border rounded-lg p-4">
                    <p class="text-xs text-slate-500">Ajuste transferencia</p>
                    <p class="text-xl font-bold {{ $resumenAjustes['transferencia_ajustes'] < 0 ? 'text-red-700' : 'text-blue-700' }}">${{ number_format($resumenAjustes['transferencia_ajustes'], 2) }}</p>
                </div>
                <div class="bg-white border rounded-lg p-4">
                    <p class="text-xs text-slate-500">Ajuste tarjeta</p>
                    <p class="text-xl font-bold {{ $resumenAjustes['tarjeta_ajustes'] < 0 ? 'text-red-700' : 'text-purple-700' }}">${{ number_format($resumenAjustes['tarjeta_ajustes'], 2) }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h3 class="text-lg font-bold text-slate-800 mb-3">Observaciones</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="font-semibold text-slate-600">Apertura</p>
                <p class="text-slate-500 mt-1">{{ $corteCaja->observaciones_apertura ?: 'Sin observaciones.' }}</p>
            </div>
            <div>
                <p class="font-semibold text-slate-600">Cierre</p>
                <p class="text-slate-500 mt-1">{{ $corteCaja->observaciones_cierre ?: 'Sin observaciones.' }}</p>
            </div>
        </div>
    </div>

    @if($corteCaja->ajustes->isNotEmpty())
        <div class="bg-white border border-amber-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b bg-amber-50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-amber-900">Ajustes administrativos</h3>
                <span class="text-xs text-amber-700">{{ $corteCaja->ajustes->count() }} movimiento(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Ajuste</th>
                            <th class="p-3 text-left">Tipo</th>
                            <th class="p-3 text-left">Alumno</th>
                            <th class="p-3 text-center">Pago</th>
                            <th class="p-3 text-center">Fecha</th>
                            <th class="p-3 text-left">Usuario</th>
                            <th class="p-3 text-right">Monto</th>
                            <th class="p-3 text-left">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corteCaja->ajustes->sortByDesc('fecha_aplicacion') as $ajuste)
                            <tr class="border-t hover:bg-amber-50/40">
                                <td class="p-3 font-semibold">#{{ $ajuste->id }}</td>
                                <td class="p-3">{{ $ajuste->tipo }}</td>
                                <td class="p-3">
                                    @if($ajuste->alumno)
                                        <a href="{{ route('alumnos.show', $ajuste->alumno) }}" class="text-indigo-700 font-semibold hover:underline">{{ $ajuste->alumno->nombre_completo }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    @if($ajuste->pago && $ajuste->alumno)
                                        <a href="{{ route('alumnos.pagos.recibo', [$ajuste->alumno, $ajuste->pago]) }}" target="_blank" class="text-slate-700 hover:underline font-semibold">#{{ $ajuste->pago->id }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-3 text-center">{{ optional($ajuste->fecha_aplicacion)->format('d/m/Y H:i') }}</td>
                                <td class="p-3">{{ $ajuste->usuario->nombre ?? '—' }}</td>
                                <td class="p-3 text-right font-bold {{ $ajuste->esNegativo() ? 'text-red-700' : 'text-green-700' }}">${{ number_format($ajuste->monto_ajuste, 2) }}</td>
                                <td class="p-3 max-w-md text-slate-600">{{ $ajuste->motivo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800">Pagos incluidos en el corte</h3>
            <span class="text-xs text-slate-500">{{ $totalesActuales['cantidad_pagos'] }} pago(s) al cierre · {{ $corteCaja->pagos->where('estatus', 'Cancelado')->count() }} cancelado(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="p-3 text-left">Pago</th>
                        <th class="p-3 text-left">Alumno</th>
                        <th class="p-3 text-center">Fecha</th>
                        <th class="p-3 text-center">Método</th>
                        <th class="p-3 text-left">Referencia</th>
                        <th class="p-3 text-center">Estatus</th>
                        <th class="p-3 text-center">Recibo</th>
                        <th class="p-3 text-center">Acciones</th>
                        <th class="p-3 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($corteCaja->pagos->sortByDesc('created_at') as $pago)
                        <tr class="border-t hover:bg-slate-50 {{ $pago->estaCancelado() ? 'bg-red-50 text-slate-500' : '' }}">
                            <td class="p-3 font-semibold">#{{ $pago->id }}</td>
                            <td class="p-3">
                                @if($pago->alumno)
                                    <a href="{{ route('alumnos.show', $pago->alumno) }}" class="text-indigo-700 font-semibold hover:underline">{{ $pago->alumno->nombre_completo }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3 text-center">{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
                            <td class="p-3 text-center">{{ $pago->metodo_pago }}</td>
                            <td class="p-3">{{ $pago->referencia_principal ?? '—' }}</td>
                            <td class="p-3 text-center">
                                @if($pago->estaCancelado())
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Cancelado</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Activo</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($pago->alumno)
                                    <a href="{{ route('alumnos.pagos.recibo', [$pago->alumno, $pago]) }}" target="_blank" class="text-slate-700 hover:underline font-semibold">PDF</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($puedeCancelarPagos && ! $pago->estaCancelado() && $corteCaja->estaAbierta() && $pago->alumno)
                                    <a href="{{ route('alumnos.pagos.cancelar.confirmar', [$pago->alumno, $pago]) }}" class="text-red-600 hover:underline font-semibold">Cancelar</a>
                                @elseif($puedeCancelarPagos && ! $pago->estaCancelado() && $corteCaja->estaCerrada() && $pago->alumno)
                                    <a href="{{ route('alumnos.pagos.ajuste-cancelacion.confirmar', [$pago->alumno, $pago]) }}" class="text-amber-600 hover:underline font-semibold">Ajuste</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-semibold {{ $pago->estaCancelado() ? 'line-through' : '' }}">${{ number_format($pago->monto_total_pagado, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-500">Esta caja todavía no tiene pagos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
