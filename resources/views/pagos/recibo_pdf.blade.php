<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de pago {{ $pago->folio_recibo }}</title>
    <style>
        @page { margin: 28px 34px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.35;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .logo { width: 82px; height: auto; }
        .institution { font-size: 16px; font-weight: bold; text-align: center; color: #111827; }
        .subtitle { font-size: 10px; text-align: center; color: #4b5563; margin-top: 3px; }
        .receipt-box {
            border: 1px solid #111827;
            padding: 8px 10px;
            text-align: center;
            width: 170px;
            margin-left: auto;
        }
        .receipt-title { font-size: 12px; font-weight: bold; letter-spacing: .5px; }
        .receipt-folio { font-size: 14px; font-weight: bold; color: #991b1b; margin-top: 4px; }
        .section-title {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-weight: bold;
            color: #111827;
            margin-top: 12px;
            margin-bottom: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .info-table td {
            border: 1px solid #d1d5db;
            padding: 6px 7px;
            vertical-align: top;
        }
        .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .3px; }
        .value { font-size: 11px; color: #111827; font-weight: bold; margin-top: 2px; }
        .amount-box {
            border: 2px solid #111827;
            padding: 10px;
            text-align: right;
            margin: 12px 0;
        }
        .amount-label { font-size: 11px; color: #4b5563; }
        .amount-value { font-size: 24px; font-weight: bold; color: #065f46; }
        .detail-table th,
        .detail-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }
        .detail-table th {
            background: #e5e7eb;
            font-size: 9px;
            text-transform: uppercase;
            color: #374151;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
        .small { font-size: 9px; }
        .signatures {
            width: 100%;
            margin-top: 26px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            border: none;
            text-align: center;
            padding: 28px 18px 0 18px;
        }
        .signature-line {
            border-top: 1px solid #111827;
            padding-top: 5px;
            font-size: 10px;
        }
        .footer {
            position: fixed;
            left: 34px;
            right: 34px;
            bottom: 18px;
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
            font-size: 8.5px;
            color: #6b7280;
            text-align: center;
        }
        .badge {
            display: inline-block;
            border: 1px solid #9ca3af;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            color: #374151;
        }
        .cancelled-banner {
            border: 2px solid #991b1b;
            color: #991b1b;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 1px;
            padding: 8px;
            margin-bottom: 12px;
        }
        .cancelled-note {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #7f1d1d;
            padding: 7px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
@php
    $configuracion = configuracionInstitucional();
    $logoPath = $configuracion->logoPathPdf();
    $programa = optional(optional($alumno->grupo)->programa)->nombre;
    $grupo = optional($alumno->grupo)->nombre;
    $direccionInstitucional = $configuracion->direccionCompleta();
@endphp

@if($pago->estaCancelado())
    <div class="cancelled-banner">RECIBO CANCELADO</div>
@endif

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 95px;">
                @if($configuracion->recibo_mostrar_logo && file_exists($logoPath))
                    <img class="logo" src="{{ $logoPath }}" alt="{{ $configuracion->nombre_corto }}">
                @else
                    <div style="font-weight:bold;font-size:18px;">{{ $configuracion->nombre_corto }}</div>
                @endif
            </td>
            <td>
                <div class="institution">{{ $configuracion->nombre_institucion }}</div>
                <div class="subtitle">Recibo interno de pago generado desde {{ $configuracion->nombre_corto }}-SYS</div>
                <div class="subtitle">{{ $configuracion->recibo_leyenda ?: 'Documento administrativo para control escolar y financiero' }}</div>
                @if($direccionInstitucional)
                    <div class="subtitle">{{ $direccionInstitucional }}</div>
                @endif
                @if($configuracion->telefono_principal || $configuracion->correo_contacto)
                    <div class="subtitle">{{ collect([$configuracion->telefono_principal, $configuracion->correo_contacto])->filter()->implode(' · ') }}</div>
                @endif
            </td>
            <td style="width: 190px;">
                <div class="receipt-box">
                    <div class="receipt-title">{{ $pago->estaCancelado() ? 'RECIBO CANCELADO' : 'RECIBO DE PAGO' }}</div>
                    <div class="receipt-folio">{{ $pago->folio_recibo }}</div>
                    <div class="small muted">Pago ID: {{ $pago->id }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-title">Datos del alumno</div>
<table class="info-table">
    <tr>
        <td style="width: 38%;">
            <div class="label">Alumno</div>
            <div class="value">{{ $alumno->nombre_completo }}</div>
        </td>
        <td style="width: 18%;">
            <div class="label">Matrícula</div>
            <div class="value">{{ $alumno->matricula ?? '—' }}</div>
        </td>
        <td style="width: 22%;">
            <div class="label">Programa</div>
            <div class="value">{{ $programa ?? '—' }}</div>
        </td>
        <td style="width: 22%;">
            <div class="label">Grupo</div>
            <div class="value">{{ $grupo ?? '—' }}</div>
        </td>
    </tr>
</table>

<div class="section-title">Datos del pago</div>
<table class="info-table">
    <tr>
        <td>
            <div class="label">Fecha de pago</div>
            <div class="value">{{ optional($pago->fecha_pago)->format('d/m/Y') }}</div>
        </td>
        <td>
            <div class="label">Método</div>
            <div class="value">{{ $pago->metodo_pago }}</div>
        </td>
        <td>
            <div class="label">Referencia</div>
            <div class="value">{{ $pago->referencia_principal ?? '—' }}</div>
        </td>
        <td>
            <div class="label">Corte de caja</div>
            <div class="value">{{ $pago->corteCaja ? '#'.$pago->corteCaja->id : 'Sin corte' }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="label">Recibió</div>
            <div class="value">{{ $pago->usuario->nombre ?? '—' }}</div>
        </td>
        <td>
            <div class="label">Banco emisor</div>
            <div class="value">{{ $pago->banco_emisor ?? '—' }}</div>
        </td>
        <td>
            <div class="label">Clave rastreo / autorización</div>
            <div class="value">{{ $pago->clave_rastreo ?: ($pago->numero_autorizacion ?: '—') }}</div>
        </td>
        <td>
            <div class="label">Fecha de emisión</div>
            <div class="value">{{ optional($pago->recibo_emitido_at)->format('d/m/Y H:i') }}</div>
        </td>
    </tr>
</table>

@if($pago->estaCancelado())
    <div class="cancelled-note">
        <strong>Cancelado por:</strong> {{ $pago->canceladoPor->nombre ?? '—' }} ·
        <strong>Fecha de cancelación:</strong> {{ optional($pago->fecha_cancelacion)->format('d/m/Y H:i') ?? '—' }}<br>
        <strong>Motivo:</strong> {{ $pago->motivo_cancelacion ?? '—' }}
    </div>
@endif

<div class="amount-box">
    <div class="amount-label">Monto total recibido</div>
    <div class="amount-value">${{ number_format((float) $pago->monto_total_pagado, 2) }}</div>
</div>

<div class="section-title">Aplicación del pago</div>
<table class="detail-table">
    <thead>
        <tr>
            <th style="width: 12%;">Tipo</th>
            <th>Concepto / descripción</th>
            <th style="width: 18%;">Vencimiento</th>
            <th style="width: 18%;" class="text-right">Monto aplicado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pago->cargos as $cargo)
            <tr>
                <td class="text-center">Cargo</td>
                <td>
                    <strong>{{ $cargo->concepto->nombre ?? 'Cargo' }}</strong><br>
                    <span class="small muted">{{ $cargo->descripcion_cargo ?? 'Sin descripción adicional.' }}</span>
                </td>
                <td class="text-center">{{ optional($cargo->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                <td class="text-right">${{ number_format((float) ($cargo->pivot->monto_aplicado ?? 0), 2) }}</td>
            </tr>
        @empty
        @endforelse

        @forelse($pago->parcialidades as $parcialidad)
            <tr>
                <td class="text-center">Parcialidad</td>
                <td>
                    <strong>Convenio #{{ $parcialidad->convenio_id }}</strong><br>
                    <span class="small muted">{{ $parcialidad->convenio->descripcion ?? 'Parcialidad de convenio.' }}</span>
                </td>
                <td class="text-center">{{ optional($parcialidad->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                <td class="text-right">${{ number_format((float) ($parcialidad->pivot->monto_aplicado ?? 0), 2) }}</td>
            </tr>
        @empty
        @endforelse

        @if($saldoAFavorGenerado > 0)
            <tr>
                <td class="text-center">Saldo</td>
                <td><strong>Saldo a favor generado</strong><br><span class="small muted">Excedente no aplicado a cargos o parcialidades.</span></td>
                <td class="text-center">—</td>
                <td class="text-right">${{ number_format($saldoAFavorGenerado, 2) }}</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Total aplicado</th>
            <th class="text-right">${{ number_format($totalAplicado, 2) }}</th>
        </tr>
        @if($saldoAFavorGenerado > 0)
            <tr>
                <th colspan="3" class="text-right">Saldo a favor generado</th>
                <th class="text-right">${{ number_format($saldoAFavorGenerado, 2) }}</th>
            </tr>
        @endif
        <tr>
            <th colspan="3" class="text-right">Total recibido</th>
            <th class="text-right">${{ number_format((float) $pago->monto_total_pagado, 2) }}</th>
        </tr>
    </tfoot>
</table>

@if($pago->observaciones)
    <div class="section-title">Observaciones</div>
    <table class="info-table">
        <tr>
            <td>{{ $pago->observaciones }}</td>
        </tr>
    </table>
@endif

<table class="signatures">
    <tr>
        <td><div class="signature-line">{{ $configuracion->recibo_firma_recibio }}</div></td>
        <td><div class="signature-line">{{ $configuracion->recibo_firma_conformidad }}</div></td>
    </tr>
</table>

<p class="small muted" style="margin-top: 16px; text-align:center;">
    <span class="badge">UUID interno: {{ $pago->recibo_uuid }}</span>
    <span class="badge">Versión: {{ $pago->recibo_version }}</span>
</p>

<div class="footer">
    {{ $configuracion->recibo_nota_fiscal ?: 'Este recibo es un comprobante interno de control administrativo. No sustituye CFDI ni documento fiscal.' }} @if($pago->estaCancelado()) Este documento fue cancelado y se conserva únicamente como evidencia histórica. @endif Generado el {{ now()->format('d/m/Y H:i') }}.
</div>
</body>
</html>
