<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de caja #{{ $corteCaja->id }}</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.35; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 72px; height: auto; }
        .institution { font-size: 15px; font-weight: bold; text-align: center; }
        .subtitle { font-size: 9px; text-align: center; color: #4b5563; margin-top: 2px; }
        .box { border: 1px solid #111827; padding: 8px; text-align: center; }
        .folio { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .section-title { background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px; font-weight: bold; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; font-size: 8.5px; text-transform: uppercase; color: #374151; }
        .label { font-size: 8.5px; color: #6b7280; text-transform: uppercase; }
        .value { font-size: 11px; font-weight: bold; color: #111827; }
        .right { text-align: right; }
        .center { text-align: center; }
        .green { color: #047857; }
        .red { color: #b91c1c; }
        .muted { color: #6b7280; }
        .small { font-size: 8.5px; }
        .signatures { margin-top: 24px; width: 100%; border-collapse: collapse; }
        .signatures td { border: none; width: 50%; text-align: center; padding: 30px 24px 0 24px; }
        .signature-line { border-top: 1px solid #111827; padding-top: 5px; }
        .footer { position: fixed; left: 34px; right: 34px; bottom: 18px; border-top: 1px solid #d1d5db; padding-top: 5px; font-size: 8px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
@php
    $configuracion = configuracionInstitucional();
    $logoPath = $configuracion->logoPathPdf();
    $esperados = [
        'Efectivo' => round((float) $corteCaja->saldo_inicial + (float) $totalesActuales['efectivo_sistema'] + (float) $resumenMovimientos['neto_efectivo'], 2),
        'Transferencia' => round((float) $totalesActuales['transferencia_sistema'] + (float) $resumenMovimientos['neto_transferencia'], 2),
        'Tarjeta' => round((float) $totalesActuales['tarjeta_sistema'] + (float) $resumenMovimientos['neto_tarjeta'], 2),
        'Otros métodos' => round((float) $resumenMovimientos['neto_otro'], 2),
    ];
    $reportados = [
        'Efectivo' => (float) $corteCaja->efectivo_reportado,
        'Transferencia' => (float) $corteCaja->transferencia_reportado,
        'Tarjeta' => (float) $corteCaja->tarjeta_reportado,
        'Otros métodos' => (float) $corteCaja->otro_reportado,
    ];
    $diferencias = [
        'Efectivo' => (float) $corteCaja->diferencia_efectivo,
        'Transferencia' => (float) $corteCaja->diferencia_transferencia,
        'Tarjeta' => (float) $corteCaja->diferencia_tarjeta,
        'Otros métodos' => (float) $corteCaja->diferencia_otro,
    ];
    $totalEsperado = round(array_sum($esperados), 2);
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 90px;">
                @if($configuracion->recibo_mostrar_logo && file_exists($logoPath))
                    <img class="logo" src="{{ $logoPath }}" alt="{{ $configuracion->nombre_corto }}">
                @else
                    <strong>{{ $configuracion->nombre_corto }}</strong>
                @endif
            </td>
            <td>
                <div class="institution">{{ $configuracion->nombre_institucion }}</div>
                <div class="subtitle">Corte oficial de caja generado desde {{ $configuracion->nombre_corto }}-SYS</div>
                <div class="subtitle">Documento interno para control financiero y revisión administrativa</div>
            </td>
            <td style="width: 160px;">
                <div class="box">
                    <div class="small muted">CORTE DE CAJA</div>
                    <div class="folio">#{{ $corteCaja->id }}</div>
                    <div class="small">{{ $corteCaja->estatus }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-title">Datos del corte</div>
<table>
    <tr>
        <td><div class="label">Usuario responsable</div><div class="value">{{ $corteCaja->usuario->nombre ?? '—' }}</div></td>
        <td><div class="label">Apertura</div><div class="value">{{ optional($corteCaja->fecha_apertura)->format('d/m/Y H:i') }}</div></td>
        <td><div class="label">Cierre</div><div class="value">{{ optional($corteCaja->fecha_cierre)->format('d/m/Y H:i') }}</div></td>
        <td><div class="label">Pagos activos</div><div class="value">{{ $totalesActuales['cantidad_pagos'] }}</div></td>
    </tr>
</table>

<div class="section-title">Resumen financiero</div>
<table>
    <tr>
        <th>Concepto</th>
        <th class="right">Importe</th>
        <th>Concepto</th>
        <th class="right">Importe</th>
    </tr>
    <tr>
        <td>Saldo inicial</td><td class="right">${{ number_format($corteCaja->saldo_inicial, 2) }}</td>
        <td>Pagos activos</td><td class="right">{{ $totalesActuales['cantidad_pagos'] }}</td>
    </tr>
    <tr>
        <td>Total cobrado por pagos</td><td class="right">${{ number_format($totalesActuales['total_sistema'], 2) }}</td>
        <td>Neto entradas/salidas</td><td class="right {{ $resumenMovimientos['neto_total'] < 0 ? 'red' : 'green' }}">${{ number_format($resumenMovimientos['neto_total'], 2) }}</td>
    </tr>
    <tr>
        <td>Total esperado</td><td class="right">${{ number_format($totalEsperado, 2) }}</td>
        <td>Total reportado</td><td class="right">${{ number_format($corteCaja->total_reportado, 2) }}</td>
    </tr>
    <tr>
        <td>Diferencia total</td>
        <td class="right {{ abs((float) $corteCaja->diferencia_total) < 0.01 ? 'green' : 'red' }}">${{ number_format($corteCaja->diferencia_total, 2) }}</td>
        <td>Movimientos aplicados</td><td class="right">{{ $resumenMovimientos['cantidad'] }}</td>
    </tr>
</table>

<div class="section-title">Conciliación por método</div>
<table>
    <tr>
        <th>Método</th><th class="right">Esperado</th><th class="right">Reportado</th><th class="right">Diferencia</th>
    </tr>
    @foreach($esperados as $metodo => $esperado)
        <tr>
            <td>{{ $metodo }}</td>
            <td class="right">${{ number_format($esperado, 2) }}</td>
            <td class="right">${{ number_format($reportados[$metodo], 2) }}</td>
            <td class="right {{ abs((float) $diferencias[$metodo]) < 0.01 ? 'green' : 'red' }}">${{ number_format($diferencias[$metodo], 2) }}</td>
        </tr>
    @endforeach
</table>

@if($corteCaja->movimientos->isNotEmpty())
    <div class="section-title">Entradas y salidas operativas</div>
    <table>
        <tr>
            <th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Método</th><th class="right">Monto</th><th>Estatus</th>
        </tr>
        @foreach($corteCaja->movimientos->sortBy('fecha_movimiento') as $movimiento)
            <tr>
                <td>{{ optional($movimiento->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                <td>{{ $movimiento->tipo }}</td>
                <td>{{ $movimiento->concepto }}</td>
                <td>{{ $movimiento->metodo_pago }}</td>
                <td class="right {{ $movimiento->esSalida() ? 'red' : 'green' }}">{{ $movimiento->esSalida() ? '-' : '+' }}${{ number_format($movimiento->monto, 2) }}</td>
                <td>{{ $movimiento->estatus }}</td>
            </tr>
        @endforeach
    </table>
@endif

<div class="section-title">Pagos incluidos</div>
<table>
    <tr>
        <th>Pago</th><th>Alumno</th><th>Fecha</th><th>Método</th><th>Referencia</th><th>Estatus</th><th class="right">Monto</th>
    </tr>
    @foreach($corteCaja->pagos->sortBy('fecha_pago') as $pago)
        <tr>
            <td>#{{ $pago->id }}</td>
            <td>{{ $pago->alumno->nombre_completo ?? '—' }}</td>
            <td>{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
            <td>{{ $pago->metodo_pago }}</td>
            <td>{{ $pago->referencia_principal ?? '—' }}</td>
            <td>{{ $pago->estatus }}</td>
            <td class="right">${{ number_format($pago->monto_total_pagado, 2) }}</td>
        </tr>
    @endforeach
</table>

@if($corteCaja->observaciones_cierre)
    <div class="section-title">Observaciones de cierre</div>
    <div style="border: 1px solid #d1d5db; padding: 7px;">{{ $corteCaja->observaciones_cierre }}</div>
@endif

<table class="signatures">
    <tr>
        <td><div class="signature-line">Responsable de caja</div></td>
        <td><div class="signature-line">Revisión administrativa</div></td>
    </tr>
</table>

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }}. Este documento es un comprobante interno de control administrativo; no sustituye CFDI.
</div>
</body>
</html>
