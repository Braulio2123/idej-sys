<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de Pago - {{ configInstitucional('nombre_corto', 'IDEJ') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222;">
    @php($configuracion = configuracionInstitucional())
    <h2>Hola, {{ $alumno->nombre_completo }} 👋</h2>

    <p>
        Esperamos que te encuentres muy bien. Te recordamos que tienes
        <strong>pagos pendientes</strong> en {{ $configuracion->nombre_institucion }} ({{ $configuracion->nombre_corto }}).
    </p>

    <p>
        Te invitamos a acercarte a <strong>Recepción</strong> para regularizar tu situación
        o comunicarte con nosotros si ya realizaste tu pago para actualizar tu estatus.
    </p>

    <p style="margin-top:16px;">
        — Equipo {{ $configuracion->nombre_corto }}-SYS<br>
        Recepción / Coordinación Administrativa
    </p>

    <hr style="margin:24px 0;">
    <small>Este es un mensaje automático. Por favor no respondas a este correo.</small>
</body>
</html>
