@extends('layouts.app')

@section('title', 'Reportes Financieros')

@section('content')

<div class="max-w-7xl mx-auto mt-6 mb-10">

    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class="bx bx-line-chart text-3xl text-emerald-600"></i>
                    Reporte Financiero – IDEJ
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Análisis de cargos, pagos y adeudos con filtros por ciclo, programa y grupo.
                </p>
            </div>

            {{-- BOTONES EXPORTAR --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reportes.export-excel', request()->query()) }}"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700
                          text-white px-4 py-2 rounded-xl text-sm font-medium shadow">
                    <i class="bx bx-file-export text-lg"></i>
                    Exportar Excel (CSV)
                </a>

                <a href="{{ route('reportes.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900
                          text-white px-4 py-2 rounded-xl text-sm font-medium shadow">
                    <i class="bx bx-file text-lg"></i>
                    Exportar PDF
                </a>
            </div>
        </div>

        {{-- FILTROS --}}
        <form method="GET" class="mb-8 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 text-sm">

            {{-- Ciclo --}}
            <div>
                <label class="block text-slate-600 mb-1">Ciclo escolar</label>
                <select name="ciclo_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}"
                            {{ $filtros['ciclo_id'] == $ciclo->id ? 'selected' : '' }}>
                            {{ $ciclo->nombre ?? $ciclo->descripcion ?? 'Ciclo '.$ciclo->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Programa --}}
            <div>
                <label class="block text-slate-600 mb-1">Programa</label>
                <select name="programa_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    @foreach($programas as $programa)
                        <option value="{{ $programa->id }}"
                            {{ $filtros['programa_id'] == $programa->id ? 'selected' : '' }}>
                            {{ $programa->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Grupo --}}
            <div>
                <label class="block text-slate-600 mb-1">Grupo</label>
                <select name="grupo_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}"
                            {{ $filtros['grupo_id'] == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->nombre }} – {{ $grupo->programa->nombre ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Fecha desde --}}
            <div>
                <label class="block text-slate-600 mb-1">Desde</label>
                <input type="date" name="fecha_desde"
                       value="{{ $filtros['fecha_desde'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label class="block text-slate-600 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta"
                       value="{{ $filtros['fecha_hasta'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            {{-- Estatus cargo (fila extra abajo) --}}
            <div>
                <label class="block text-slate-600 mb-1">Estatus de cargos</label>
                <select name="estatus_cargo"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2">
                    @php
                        $ec = $filtros['estatus_cargo'] ?? 'Todos';
                    @endphp
                    <option value="Todos"      {{ $ec === 'Todos' ? 'selected' : '' }}>Todos</option>
                    <option value="Pendiente"  {{ $ec === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Pagado"     {{ $ec === 'Pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="En convenio"{{ $ec === 'En convenio' ? 'selected' : '' }}>En convenio</option>
                </select>
            </div>

            {{-- Botón aplicar --}}
            <div class="md:col-span-2 lg:col-span-1 flex items-end">
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium shadow">
                    Aplicar filtros
                </button>
            </div>

        </form>

        {{-- TARJETAS DE TOTALES --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 text-sm">

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-sm">
                <p class="text-blue-700 text-xs font-semibold">Total de cargos generados</p>
                <p class="mt-2 text-2xl font-bold text-blue-900">
                    ${{ number_format($totalCargos, 2) }}
                </p>
            </div>

            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
                <p class="text-emerald-700 text-xs font-semibold">Total de pagos recibidos</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">
                    ${{ number_format($totalPagos, 2) }}
                </p>
            </div>

            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl shadow-sm">
                <p class="text-rose-700 text-xs font-semibold">Adeudo pendiente</p>
                <p class="mt-2 text-2xl font-bold text-rose-900">
                    ${{ number_format($totalAdeudo, 2) }}
                </p>
            </div>

        </div>

        {{-- GRÁFICAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">
                    Ingresos vs Adeudos por mes
                </h3>
                <canvas id="chartIngresosAdeudos" class="w-full h-64"></canvas>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">
                    Resumen numérico
                </h3>
                <ul class="text-sm text-slate-700 space-y-1">
                    <li>📌 Registros de cargos: <strong>{{ $cargos->count() }}</strong></li>
                    <li>💵 Registros de pagos: <strong>{{ $pagos->count() }}</strong></li>
                    <li>📅 Periodo analizado: <strong>{{ $filtros['fecha_desde'] }} a {{ $filtros['fecha_hasta'] }}</strong></li>
                </ul>
            </div>
        </div>

        {{-- TABLA DETALLE DE CARGOS Y PAGOS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">

            {{-- CARGOS --}}
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm bg-white">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                        <i class="bx bx-receipt text-lg text-slate-600"></i>
                        Cargos filtrados
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left">Alumno</th>
                                <th class="px-3 py-2 text-left">Programa</th>
                                <th class="px-3 py-2 text-right">Monto</th>
                                <th class="px-3 py-2 text-right">Adeudo</th>
                                <th class="px-3 py-2 text-left">Estatus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($cargos as $cargo)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2">
                                        {{ $cargo->alumno->nombre_completo ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $cargo->alumno->grupo->programa->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        ${{ number_format($cargo->monto_original, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        ${{ number_format($cargo->monto_adeudo, 2) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $cargo->estatus }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-3 text-center text-slate-400">
                                        Sin cargos en el periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGOS --}}
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm bg-white">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                        <i class="bx bx-credit-card text-lg text-slate-600"></i>
                        Pagos filtrados
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left">Alumno</th>
                                <th class="px-3 py-2 text-left">Programa</th>
                                <th class="px-3 py-2 text-right">Monto</th>
                                <th class="px-3 py-2 text-left">Fecha pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pagos as $pago)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2">
                                        {{ $pago->alumno->nombre_completo ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $pago->alumno->grupo->programa->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        ${{ number_format($pago->monto_total_pagado, 2) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $pago->fecha_pago?->format('d/m/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-3 text-center text-slate-400">
                                        Sin pagos en el periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- CHART.JS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartIngresosAdeudos').getContext('2d');

    const labels  = @json($labelsMeses);
    const ingresos = @json($dataIngresos);
    const adeudos  = @json($dataAdeudos);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ingresos (pagos)',
                    data: ingresos,
                    tension: 0.3,
                    borderWidth: 2,
                },
                {
                    label: 'Adeudos (saldo cargos)',
                    data: adeudos,
                    tension: 0.3,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { font: { size: 11 } }
                }
            },
            scales: {
                y: {
                    ticks: { font: { size: 10 } }
                },
                x: {
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
</script>

@endsection
