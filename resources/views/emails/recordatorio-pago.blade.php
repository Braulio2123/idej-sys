<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de pago - {{ configInstitucional('nombre_corto', 'IDEJ') }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f6fb; font-family:Arial, sans-serif; color:#1f2937;">
@php($configuracion = configuracionInstitucional())
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb; padding:24px 0;">
    <tr>
        <td align="center">
            <table width="640" cellpadding="0" cellspacing="0" style="max-width:640px; width:100%; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:#1E3A8A; color:#ffffff; padding:22px 28px;">
                        <h1 style="margin:0; font-size:20px;">{{ $configuracion->nombre_corto }} · Recordatorio de pago</h1>
                        <p style="margin:6px 0 0; font-size:13px; color:#dbeafe;">{{ $configuracion->nombre_institucion }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="font-size:15px; line-height:1.6; margin:0 0 14px;">Hola, <strong>{{ $alumno->nombre_completo }}</strong>.</p>
                        <p style="font-size:15px; line-height:1.6; margin:0 0 18px;">
                            Te compartimos un recordatorio institucional sobre cargos pendientes registrados en tu expediente financiero.
                        </p>

                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px 16px; margin-bottom:18px;">
                            <p style="margin:0; font-size:13px; color:#1d4ed8;">Saldo pendiente aproximado</p>
                            <p style="margin:4px 0 0; font-size:26px; font-weight:bold; color:#1e3a8a;">${{ number_format($totalAdeudo, 2) }}</p>
                            @if($primerVencimiento)
                                <p style="margin:4px 0 0; font-size:13px; color:#475569;">Primer vencimiento: {{ $primerVencimiento->format('d/m/Y') }}</p>
                            @endif
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-bottom:18px;">
                            <thead>
                                <tr style="background:#f8fafc; color:#475569; font-size:12px;">
                                    <th align="left" style="padding:10px; border-bottom:1px solid #e5e7eb;">Concepto</th>
                                    <th align="left" style="padding:10px; border-bottom:1px solid #e5e7eb;">Vencimiento</th>
                                    <th align="right" style="padding:10px; border-bottom:1px solid #e5e7eb;">Pendiente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cargos as $cargo)
                                    <tr style="font-size:13px; color:#334155;">
                                        <td style="padding:10px; border-bottom:1px solid #f1f5f9;">{{ $cargo->concepto->nombre ?? $cargo->descripcion_cargo }}</td>
                                        <td style="padding:10px; border-bottom:1px solid #f1f5f9;">{{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                        <td align="right" style="padding:10px; border-bottom:1px solid #f1f5f9; font-weight:bold;">${{ number_format((float) $cargo->monto_adeudo, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p style="font-size:15px; line-height:1.6; margin:0 0 14px;">
                            Si ya realizaste tu pago, comunícate con Recepción o Finanzas para validar tu comprobante y actualizar tu estatus.
                        </p>
                        <p style="font-size:15px; line-height:1.6; margin:0;">
                            Atentamente,<br>
                            <strong>{{ $configuracion->nombre_corto }} · Recepción / Finanzas</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc; padding:16px 28px; color:#64748b; font-size:12px; line-height:1.5;">
                        Este es un mensaje automático de control administrativo. No sustituye un estado de cuenta oficial ni un recibo de pago.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
