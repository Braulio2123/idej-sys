<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bitácora - {{ configInstitucional('nombre_corto', 'IDEJ') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; padding: 10px 0 16px; border-bottom: 2px solid #004080; }
        .header img { width: 75px; margin-bottom: 5px; }
        .title { font-size: 18px; font-weight: bold; color: #004080; }
        .subtitle { font-size: 11px; color: #666; }
        .filters { margin: 12px 0; font-size: 10px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #004080; color: white; padding: 6px; font-size: 9px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #ddd; vertical-align: top; }
        tr:nth-child(even) { background: #f5f7fa; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #004080; padding: 5px 0; }
    </style>
</head>
<body>
    @php($configuracion = configuracionInstitucional())
    <div class="header">
        @if(file_exists($configuracion->logoPathPdf()))
            <img src="{{ $configuracion->logoPathPdf() }}" alt="{{ $configuracion->nombre_corto }}">
        @endif
        <div class="title">Reporte de Bitácora del Sistema</div>
        <div class="subtitle">{{ $configuracion->nombre_institucion }}</div>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong><br>
        Usuario ID: {{ $filtros['usuario'] ?? 'Todos' }} ·
        Módulo: {{ $filtros['modulo'] ?? 'Todos' }} ·
        Acción: {{ $filtros['accion'] ?? 'Todas' }} ·
        Desde: {{ $filtros['fecha_inicio'] ?? '—' }} ·
        Hasta: {{ $filtros['fecha_fin'] ?? '—' }} ·
        Búsqueda: {{ $filtros['buscar'] ?? 'Ninguna' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bitacoras as $b)
                <tr>
                    <td>{{ ($b->fecha_evento ?? $b->created_at)?->format('d/m/Y H:i') }}</td>
                    <td>{{ $b->usuario->nombre ?? 'Sistema' }}</td>
                    <td>{{ $b->modulo ?? 'Sistema' }}</td>
                    <td>{{ $b->accion ?? $b->tipo }}</td>
                    <td>{{ $b->descripcion }}</td>
                    <td>{{ $b->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px;">No hay registros con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $configuracion->nombre_corto }} · Sistema Administrativo · Reporte generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
