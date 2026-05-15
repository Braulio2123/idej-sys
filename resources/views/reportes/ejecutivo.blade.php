@extends('layouts.app')

@section('title', 'Reporte Ejecutivo')

@section('content')
<style>
    .reporte-ejecutivo-chart-box {
        position: relative;
        width: 100%;
        height: 260px;
        max-height: 260px;
        overflow: hidden;
    }

    .reporte-ejecutivo-chart-box canvas {
        display: block;
        width: 100% !important;
        height: 260px !important;
        max-height: 260px !important;
    }

    @media (max-width: 768px) {
        .reporte-ejecutivo-chart-box {
            height: 220px;
            max-height: 220px;
        }

        .reporte-ejecutivo-chart-box canvas {
            height: 220px !important;
            max-height: 220px !important;
        }
    }
</style>
@php
    $money = fn($value) => '$' . number_format((float) $value, 2);
    $badge = [
        'critica' => 'bg-red-100 text-red-800 border-red-200',
        'alta' => 'bg-orange-100 text-orange-800 border-orange-200',
        'media' => 'bg-amber-100 text-amber-800 border-amber-200',
        'baja' => 'bg-blue-100 text-blue-800 border-blue-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-900 text-white shadow-lg shadow-blue-900/20">
                    <i class='bx bx-bar-chart-alt-2 text-2xl'></i>
                </span>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Reporte Ejecutivo para Dirección</h2>
                    <p class="text-sm text-slate-500">Vista gerencial de ingresos, adeudos, prospectos, becas, convenios, solicitudes docentes y operación académica.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reportes.ejecutivo.export-csv', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                <i class='bx bx-file-export text-lg'></i>
                Exportar CSV
            </a>
            <a href="{{ route('centro-control.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-900">
                <i class='bx bx-radar text-lg'></i>
                Centro de Control
            </a>
        </div>
    </div>

    <form method="GET" class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-6">
        <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Rango</label>
            <select name="rango" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                @foreach(['hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'trimestre' => 'Últimos 3 meses', 'anio' => 'Este año', 'personalizado' => 'Personalizado'] as $key => $label)
                    <option value="{{ $key }}" @selected($rango === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde', $fechaDesde->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta', $fechaHasta->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2 flex items-end gap-2">
            <button class="flex-1 rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-800">
                Aplicar
            </button>
            <a href="{{ route('reportes.ejecutivo') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                Limpiar
            </a>
        </div>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-700">Periodo analizado</p>
                <p class="text-sm text-slate-600">{{ $fechaDesde->format('d/m/Y') }} al {{ $fechaHasta->format('d/m/Y') }}</p>
            </div>
            <p class="text-xs text-slate-400">Consulta generada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-xs font-semibold uppercase text-emerald-700">Ingresos del periodo</p>
            <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $money($finanzas['ingresos_periodo']) }}</p>
            <p class="mt-1 text-xs text-emerald-700">{{ $finanzas['pagos_activos_count'] }} pago(s) activo(s)</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <p class="text-xs font-semibold uppercase text-red-700">Adeudo vencido</p>
            <p class="mt-2 text-3xl font-bold text-red-900">{{ $money($finanzas['adeudo_vencido']) }}</p>
            <p class="mt-1 text-xs text-red-700">Adeudo total: {{ $money($finanzas['adeudo_total']) }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-xs font-semibold uppercase text-blue-700">Prospectos nuevos</p>
            <p class="mt-2 text-3xl font-bold text-blue-900">{{ number_format($prospectos['nuevos_periodo']) }}</p>
            <p class="mt-1 text-xs text-blue-700">Conversión periodo: {{ $prospectos['conversion_periodo'] }}%</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase text-amber-700">Solicitudes docentes pendientes</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($docentes['pendientes'] + $docentes['autorizadas']) }}</p>
            <p class="mt-1 text-xs text-amber-700">Monto autorizado pendiente: {{ $money($docentes['autorizadas_monto']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Ingresos y cargos generados</h3>
                    <p class="text-xs text-slate-500">Serie mensual del periodo seleccionado.</p>
                </div>
            </div>
            <div class="reporte-ejecutivo-chart-box">
                <canvas id="reporteEjecutivoChart" aria-label="Gráfica de ingresos y cargos generados"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-slate-900">Alertas ejecutivas</h3>
            <p class="mb-4 text-xs text-slate-500">Puntos que requieren seguimiento.</p>

            <div class="space-y-3">
                @forelse($alertas as $alerta)
                    <a href="{{ $alerta['ruta'] ?? '#' }}" class="block rounded-xl border p-3 {{ $badge[$alerta['severidad']] ?? $badge['baja'] }}">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold">{{ $alerta['titulo'] }}</p>
                            <span class="rounded-full bg-white/70 px-2 py-0.5 text-[10px] font-bold uppercase">{{ $alerta['severidad'] }}</span>
                        </div>
                        <p class="mt-1 text-xs opacity-90">{{ $alerta['detalle'] }}</p>
                    </a>
                @empty
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                        No hay alertas ejecutivas relevantes para este corte.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Finanzas</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Cargos generados</p><p class="font-bold text-slate-900">{{ $money($finanzas['cargos_generados_periodo']) }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Recuperación</p><p class="font-bold text-slate-900">{{ $finanzas['porcentaje_recuperacion'] }}%</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Pagos cancelados</p><p class="font-bold text-slate-900">{{ $finanzas['pagos_cancelados_count'] }} · {{ $money($finanzas['pagos_cancelados_monto']) }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Cortes con diferencia</p><p class="font-bold text-slate-900">{{ $finanzas['cortes_con_diferencia'] }} · {{ $money($finanzas['diferencia_total_periodo']) }}</p></div>
            </div>
            <div class="mt-5">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Adeudo por programa</p>
                <div class="space-y-2">
                    @forelse($finanzas['adeudo_por_programa'] as $fila)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <span class="text-slate-700">{{ $fila->programa }}</span>
                            <span class="font-semibold text-slate-900">{{ $money($fila->adeudo) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Sin adeudos por programa.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Alumnos y prospectos</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Alumnos totales</p><p class="font-bold text-slate-900">{{ number_format($alumnos['total']) }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Alumnos nuevos</p><p class="font-bold text-slate-900">{{ number_format($alumnos['nuevos_periodo']) }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Prospectos activos</p><p class="font-bold text-slate-900">{{ number_format($prospectos['activos']) }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Contactos vencidos</p><p class="font-bold text-slate-900">{{ number_format($prospectos['vencidos']) }}</p></div>
            </div>
            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Estatus financiero</p>
                    @foreach($alumnos['estatus_financiero'] as $fila)
                        <div class="mb-2 flex justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm"><span>{{ $fila->estatus_financiero ?: 'Sin estatus' }}</span><strong>{{ $fila->total }}</strong></div>
                    @endforeach
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Prospectos por estatus</p>
                    @foreach($prospectos['por_estatus'] as $fila)
                        <div class="mb-2 flex justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm"><span>{{ $fila->estatus ?: 'Sin estatus' }}</span><strong>{{ $fila->total }}</strong></div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Becas y convenios</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Becas activas</span><strong>{{ number_format($becasConvenios['becas_activas']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Becas por vencer 30 días</span><strong>{{ number_format($becasConvenios['becas_por_vencer_30']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Promedio de beca</span><strong>{{ $becasConvenios['promedio_beca'] }}%</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Convenios activos</span><strong>{{ number_format($becasConvenios['convenios_activos']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Parcialidades vencidas</span><strong>{{ number_format($becasConvenios['parcialidades_vencidas']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Monto vencido convenio</span><strong>{{ $money($becasConvenios['monto_parcialidades_vencidas']) }}</strong></div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Solicitudes docentes</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Pendientes</span><strong>{{ $docentes['pendientes'] }} · {{ $money($docentes['pendientes_monto']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Observadas</span><strong>{{ $docentes['observadas'] }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Autorizadas</span><strong>{{ $docentes['autorizadas'] }} · {{ $money($docentes['autorizadas_monto']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Pagadas periodo</span><strong>{{ $docentes['pagadas_periodo'] }} · {{ $money($docentes['pagadas_monto_periodo']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-red-50 px-3 py-2 text-red-800"><span>Vencidas sin pago</span><strong>{{ $docentes['vencidas_sin_pago'] }} · {{ $money($docentes['vencidas_sin_pago_monto']) }}</strong></div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-900">Operación académica</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Sesiones hoy</span><strong>{{ number_format($operacion['sesiones_hoy']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Clases principales 30 días</span><strong>{{ number_format($operacion['sesiones_principales_30']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Educación Continua 30 días</span><strong>{{ number_format($operacion['sesiones_educacion_continua_30']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Sesiones incompletas</span><strong>{{ number_format($operacion['sesiones_incompletas']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Canceladas sin reposición</span><strong>{{ number_format($operacion['canceladas_sin_reposicion']) }}</strong></div>
                <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2"><span>Cursos activos</span><strong>{{ number_format($operacion['cursos_activos']) }}</strong></div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ejecutivoCtx = document.getElementById('reporteEjecutivoChart');
    if (ejecutivoCtx) {
        new Chart(ejecutivoCtx, {
            type: 'bar',
            data: {
                labels: @json($graficas['labels']),
                datasets: [
                    { label: 'Ingresos', data: @json($graficas['ingresos']) },
                    { label: 'Cargos generados', data: @json($graficas['cargos']) }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 150,
                layout: { padding: 8 },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12 }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { maxTicksLimit: 6 } },
                    x: { ticks: { maxRotation: 0, autoSkip: true } }
                }
            }
        });
    }
</script>
@endpush
