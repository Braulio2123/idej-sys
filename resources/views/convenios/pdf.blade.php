<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Convenio de pago IDEJ</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 18px 0 8px; color: #0f172a; }
        .muted { color: #64748b; }
        .box { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; }
        th { background: #f1f5f9; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .signatures { margin-top: 42px; display: table; width: 100%; table-layout: fixed; }
        .sig { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
        .line { border-top: 1px solid #111827; padding-top: 6px; }
    </style>
</head>
<body>
    <h1>Formato institucional de convenio de pago</h1>
    <p class="muted">Instituto de Altos Estudios Jurídicos de Jalisco · Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <div class="box">
        <strong>Alumno:</strong> {{ $alumno->nombre_completo }}<br>
        <strong>Matrícula:</strong> {{ $alumno->matricula ?? 'Sin matrícula' }}<br>
        <strong>Programa:</strong> {{ $alumno->grupo->programa->nombre ?? 'Sin programa asignado' }}<br>
        <strong>Grupo:</strong> {{ $alumno->grupo->nombre ?? 'Sin grupo asignado' }}
    </div>

    <div class="box">
        <strong>Convenio:</strong> #{{ $convenio->id }}<br>
        <strong>Descripción:</strong> {{ $convenio->descripcion }}<br>
        <strong>Total reestructurado:</strong> ${{ number_format($convenio->total_reestructurado, 2) }}<br>
        <strong>Parcialidades:</strong> {{ $convenio->numero_parcialidades }}<br>
        <strong>Estatus:</strong> {{ $convenio->estatus }}
    </div>

    <h2>Cargos incluidos</h2>
    <table>
        <thead>
            <tr><th>Concepto</th><th>Descripción</th><th class="right">Adeudo reestructurado</th></tr>
        </thead>
        <tbody>
            @forelse($convenio->cargos as $cargo)
                <tr>
                    <td>{{ $cargo->concepto->nombre ?? 'Concepto' }}</td>
                    <td>{{ $cargo->descripcion_cargo }}</td>
                    <td class="right">${{ number_format($cargo->pivot->monto_adeudo_original, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="center muted">Sin cargos vinculados en esta versión histórica.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Calendario de parcialidades</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Vencimiento</th><th class="right">Monto</th><th class="right">Adeudo</th><th>Estatus</th></tr>
        </thead>
        <tbody>
            @foreach($convenio->parcialidades->sortBy('fecha_vencimiento') as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td class="right">${{ number_format($p->monto_parcialidad, 2) }}</td>
                    <td class="right">${{ number_format($p->monto_adeudo, 2) }}</td>
                    <td>{{ $p->estatus !== 'Pagado' && $p->fecha_vencimiento && $p->fecha_vencimiento->isPast() ? 'Vencida' : $p->estatus }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="muted" style="margin-top:14px;">Este formato documenta el convenio registrado en IDEJ-SYS. Cualquier recargo, interés o penalización requiere una regla institucional aprobada antes de aplicarse.</p>

    <div class="signatures">
        <div class="sig"><div class="line">Firma del alumno</div></div>
        <div class="sig"><div class="line">Firma autorizada IDEJ</div></div>
    </div>
</body>
</html>
