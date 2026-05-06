{{-- resources/views/reportes/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero {{ configInstitucional('nombre_corto', 'IDEJ') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        .titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .subtitulo {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .totales {
            margin: 10px 0 15px 0;
        }
        .totales table {
            width: 100%;
        }
        .totales td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .totales .label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #999;
            padding: 4px 6px;
        }
        th {
            background-color: #e5e5e5;
            font-weight: bold;
        }
        .seccion-titulo {
            font-weight: bold;
            margin: 8px 0 4px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>

    @php($configuracion = configuracionInstitucional())
    <div class="titulo">{{ $configuracion->nombre_institucion }}</div>
    <div class="subtitulo">
        Reporte Financiero de Cargos y Pagos<br>
        Periodo: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
        al
        {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
    </div>

    <div class="totales">
        <table>
            <tr>
                <td class="label">Total de cargos generados:</td>
                <td>${{ number_format($totalCargos, 2) }}</td>
                <td class="label">Total de pagos recibidos:</td>
                <td>${{ number_format($totalPagos, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Adeudo pendiente:</td>
                <td>${{ number_format($totalAdeudo, 2) }}</td>
                <td class="label">Fecha de generación:</td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    {{-- CARGOS --}}
    <div class="seccion-titulo">Detalle de Cargos</div>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Programa</th>
                <th>Grupo</th>
                <th>Monto</th>
                <th>Adeudo</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cargos as $cargo)
                <tr>
                    <td>{{ $cargo->alumno->nombre_completo ?? 'N/A' }}</td>
                    <td>{{ $cargo->alumno->grupo->programa->nombre ?? 'N/A' }}</td>
                    <td>{{ $cargo->alumno->grupo->nombre ?? 'N/A' }}</td>
                    <td>${{ number_format($cargo->monto_original, 2) }}</td>
                    <td>${{ number_format($cargo->monto_adeudo, 2) }}</td>
                    <td>{{ $cargo->estatus }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No se encontraron cargos en el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PAGOS --}}
    <div class="seccion-titulo">Detalle de Pagos</div>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Programa</th>
                <th>Grupo</th>
                <th>Monto</th>
                <th>Fecha de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $pago)
                <tr>
                    <td>{{ $pago->alumno->nombre_completo ?? 'N/A' }}</td>
                    <td>{{ $pago->alumno->grupo->programa->nombre ?? 'N/A' }}</td>
                    <td>{{ $pago->alumno->grupo->nombre ?? 'N/A' }}</td>
                    <td>${{ number_format($pago->monto_total_pagado, 2) }}</td>
                    <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No se encontraron pagos en el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
