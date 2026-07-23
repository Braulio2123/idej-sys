<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato de pago docente {{ $solicitud->folio }}</title>
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
        .folio { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        .section-title { background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px; font-weight: bold; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; font-size: 8.5px; text-transform: uppercase; color: #374151; }
        .label { font-size: 8.5px; color: #6b7280; text-transform: uppercase; }
        .value { font-size: 11px; font-weight: bold; color: #111827; }
        .right { text-align: right; }
        .center { text-align: center; }
        .muted { color: #6b7280; }
        .small { font-size: 8.5px; }
        .amount { font-size: 18px; color: #047857; font-weight: bold; }
        .note { border: 1px solid #d1d5db; padding: 7px; min-height: 36px; }
        .signatures { margin-top: 28px; width: 100%; border-collapse: collapse; }
        .signatures td { border: none; width: 50%; text-align: center; padding: 34px 24px 0 24px; }
        .signature-line { border-top: 1px solid #111827; padding-top: 5px; }
        .footer { position: fixed; left: 34px; right: 34px; bottom: 18px; border-top: 1px solid #d1d5db; padding-top: 5px; font-size: 8px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
@php
    $configuracion = configuracionInstitucional();
    $logoPath = $configuracion->logoPathPdf();
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
                <div class="subtitle">Formato interno de pago a docente generado desde {{ $configuracion->nombre_corto }}-SYS</div>
                <div class="subtitle">Documento para control administrativo y soporte de egreso institucional</div>
            </td>
            <td style="width: 165px;">
                <div class="box">
                    <div class="small muted">PAGO DOCENTE</div>
                    <div class="folio">{{ $solicitud->folio ?? '#'.$solicitud->id }}</div>
                    <div class="small">{{ $solicitud->estatus }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-title">Datos del pago</div>
<table>
    <tr>
        <td><div class="label">Docente</div><div class="value">{{ $solicitud->docente->nombre_completo ?? '—' }}</div></td>
        <td><div class="label">Fecha de pago</div><div class="value">{{ optional($solicitud->fecha_pago)->format('d/m/Y') ?? '—' }}</div></td>
        <td><div class="label">Método</div><div class="value">{{ $solicitud->metodo_pago ?? '—' }}</div></td>
    </tr>
    <tr>
        <td><div class="label">Referencia / folio</div><div class="value">{{ $solicitud->referencia_pago ?? '—' }}</div></td>
        <td><div class="label">Banco / cuenta</div><div class="value">{{ $solicitud->banco_pago ?? '—' }}</div></td>
        <td><div class="label">Procesado por</div><div class="value">{{ $solicitud->procesadoPor->nombre ?? '—' }}</div></td>
    </tr>
</table>

<div class="section-title">Importe pagado</div>
<table>
    <tr>
        <td class="center">
            <div class="label">Monto autorizado y registrado como pagado</div>
            <div class="amount">${{ number_format((float) $solicitud->monto, 2) }}</div>
        </td>
    </tr>
</table>

<div class="section-title">Servicio académico relacionado</div>
<table>
    <tr>
        <td><div class="label">Origen</div><div class="value">{{ $solicitud->origen ?? 'Manual' }}</div></td>
        <td><div class="label">Concepto</div><div class="value">{{ $solicitud->concepto_pago ?? '—' }}</div></td>
        <td><div class="label">Nivel</div><div class="value">{{ $solicitud->nivel ?? '—' }}</div></td>
    </tr>
    <tr>
        <td colspan="2"><div class="label">Educación Programática / grupo</div><div class="value">{{ $solicitud->programa_grupo ?? '—' }}</div></td>
        <td><div class="label">Periodo</div><div class="value">{{ $solicitud->periodo ?? '—' }}</div></td>
    </tr>
    <tr>
        <td colspan="3"><div class="label">Materia / actividad</div><div class="value">{{ $solicitud->materia_actividad ?? '—' }}</div></td>
    </tr>
</table>

<div class="section-title">Cálculo base</div>
<table>
    <tr>
        <th>Sesiones</th>
        <th>Horas totales</th>
        <th>Tarifa por hora</th>
        <th>Fecha solicitud</th>
        <th>Fecha autorización</th>
    </tr>
    <tr>
        <td class="center">{{ $solicitud->numero_sesiones ?? '—' }}</td>
        <td class="center">{{ $solicitud->horas_totales ?? '—' }}</td>
        <td class="right">{{ $solicitud->tarifa_hora ? '$'.number_format((float) $solicitud->tarifa_hora, 2) : '—' }}</td>
        <td class="center">{{ optional($solicitud->fecha_solicitud)->format('d/m/Y') ?? '—' }}</td>
        <td class="center">{{ optional($solicitud->fecha_autorizacion)->format('d/m/Y H:i') ?? '—' }}</td>
    </tr>
</table>

<div class="section-title">Trazabilidad</div>
<table>
    <tr>
        <td><div class="label">Solicitado por</div><div class="value">{{ $solicitud->creadoPor->nombre ?? '—' }}</div></td>
        <td><div class="label">Autorizado por</div><div class="value">{{ $solicitud->autorizadoPor->nombre ?? '—' }}</div></td>
        <td><div class="label">Comprobante adjunto</div><div class="value">{{ $solicitud->comprobante_pago_original ?: 'Sin archivo adjunto' }}</div></td>
    </tr>
</table>

@if($solicitud->observaciones_administracion || $solicitud->observaciones_academica)
    <div class="section-title">Observaciones</div>
    <div class="note">
        @if($solicitud->observaciones_administracion)
            <strong>Administración/Finanzas:</strong> {{ $solicitud->observaciones_administracion }}<br>
        @endif
        @if($solicitud->observaciones_academica)
            <strong>Académica:</strong> {{ $solicitud->observaciones_academica }}
        @endif
    </div>
@endif

<table class="signatures">
    <tr>
        <td><div class="signature-line">Responsable de autorización</div></td>
        <td><div class="signature-line">Responsable de pago</div></td>
    </tr>
</table>

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }}. Este documento es un soporte administrativo interno; no sustituye CFDI ni comprobante fiscal.
</div>
</body>
</html>
