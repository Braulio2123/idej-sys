@extends('layouts.app')

@section('title', 'Centro de Control Operativo')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-900 text-white shadow-lg shadow-blue-900/20">
                    <i class='bx bx-radar text-2xl'></i>
                </span>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-700">Operación IDEJ</p>
                    <h1 class="text-2xl font-bold text-slate-900">Centro de Control Operativo</h1>
                </div>
            </div>
            <p class="mt-3 max-w-4xl text-sm text-slate-600">
                Revisa conflictos y pendientes antes de que afecten la operación diaria: empalmes de docente, aula o liga, sesiones incompletas, cancelaciones sin reposición y solicitudes docentes pendientes.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('agenda-operativa.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                <i class='bx bx-calendar-star'></i>
                Ver agenda
            </a>
            <a href="{{ route('solicitudes_pago.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-amber-300">
                <i class='bx bx-money-withdraw'></i>
                Solicitudes docentes
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('centro-control.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Rango</label>
                <select name="rango" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
                    @foreach($rangos as $valor => $label)
                        <option value="{{ $valor }}" @selected($rangoSeleccionado === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio->toDateString() }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin->toDateString() }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Origen</label>
                <select name="origen" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
                    @foreach($origenes as $valor => $label)
                        <option value="{{ $valor }}" @selected($origen === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <label class="inline-flex min-h-[42px] flex-1 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700">
                    <input type="checkbox" name="solo_criticos" value="1" @checked($soloCriticos) class="rounded border-slate-300 text-red-600 focus:ring-red-600">
                    Solo críticos
                </label>
                <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-blue-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesiones revisadas</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($resumen['sesiones_revisadas']) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alertas totales</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($resumen['alertas_total']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Incluye advertencias operativas</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Críticas</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ number_format($resumen['criticas']) }}</p>
            <p class="mt-1 text-xs text-red-700/80">Requieren atención prioritaria</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Medias</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($resumen['medias']) }}</p>
            <p class="mt-1 text-xs text-amber-700/80">Riesgo operativo moderado</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Bajas</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ number_format($resumen['bajas']) }}</p>
            <p class="mt-1 text-xs text-blue-700/80">Seguimiento recomendado</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Resumen por tipo de alerta</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $alertasPorTipo->count() }} tipos</span>
            </div>

            @if($alertasPorTipo->isEmpty())
                <div class="rounded-xl border border-green-100 bg-green-50 p-4 text-sm text-green-800">
                    No se detectaron alertas en el rango seleccionado.
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach($alertasPorTipo as $tipo => $total)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold text-slate-700">{{ $tipo }}</span>
                            <span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-slate-900 shadow-sm">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Indicadores clave</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><span class="text-slate-500">Docente empalmado</span><strong>{{ $resumen['conflictos_docente'] }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Aula/liga empalmada</span><strong>{{ $resumen['conflictos_lugar'] }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Sesiones incompletas</span><strong>{{ $resumen['incompletas'] }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Canceladas sin reposición</span><strong>{{ $resumen['cancelaciones_sin_reposicion'] }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Solicitudes con seguimiento</span><strong>{{ $resumen['solicitudes'] }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Solicitudes por generar</span><strong>{{ $resumen['pendientes_generar_solicitud'] }}</strong></div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Alertas operativas</h2>
                    <p class="text-sm text-slate-500">Ordenadas por severidad. Primero aparecen los riesgos que pueden afectar operación, pago o atención al alumno.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ $alertas->count() }} alertas</span>
            </div>
        </div>

        @if($alertas->isEmpty())
            <div class="p-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700">
                    <i class='bx bx-check-shield text-3xl'></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900">Sin alertas para este rango</h3>
                <p class="mt-2 text-sm text-slate-500">No se detectaron conflictos de agenda, sesiones incompletas ni pendientes críticos en la selección actual.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($alertas as $alerta)
                    @php
                        $sev = $alerta['severidad'] ?? 'baja';
                        $badgeClass = match($sev) {
                            'alta' => 'bg-red-100 text-red-700 border-red-200',
                            'media' => 'bg-amber-100 text-amber-700 border-amber-200',
                            default => 'bg-blue-100 text-blue-700 border-blue-200',
                        };
                    @endphp

                    <div class="p-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                                        {{ $sev }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $alerta['tipo'] }}</span>
                                    <span class="text-xs text-slate-500">
                                        {{ $alerta['fecha'] ? \Carbon\Carbon::parse($alerta['fecha'])->format('d/m/Y') : 'Sin fecha' }} · {{ $alerta['hora'] ?? 'Sin horario' }}
                                    </span>
                                </div>
                                <h3 class="mt-3 text-base font-bold text-slate-900">{{ $alerta['titulo'] }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $alerta['detalle'] }}</p>
                            </div>

                            <div class="shrink-0 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recurso</p>
                                <p class="mt-1 max-w-xs truncate font-bold text-slate-900">{{ $alerta['recurso'] ?? 'No definido' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                            @foreach($alerta['items'] ?? [] as $item)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $item['origen_label'] ?? 'Origen' }}</p>
                                            <p class="mt-1 font-bold text-slate-900">{{ $item['titulo'] ?? 'Sin título' }}</p>
                                            <p class="text-sm text-slate-500">{{ $item['subtitulo'] ?? '' }}</p>
                                        </div>
                                        @if(!empty($item['url']))
                                            <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-blue-700 shadow-sm hover:bg-blue-50">
                                                Abrir
                                                <i class='bx bx-link-external'></i>
                                            </a>
                                        @endif
                                    </div>
                                    <div class="mt-3 grid gap-2 text-xs text-slate-600 md:grid-cols-2">
                                        <p><span class="font-semibold text-slate-800">Grupo/curso:</span> {{ $item['grupo_curso'] ?? 'No definido' }}</p>
                                        <p><span class="font-semibold text-slate-800">Docente:</span> {{ $item['docente'] ?? 'No definido' }}</p>
                                        <p><span class="font-semibold text-slate-800">Lugar:</span> {{ $item['lugar'] ?? 'No definido' }}</p>
                                        <p><span class="font-semibold text-slate-800">Estatus:</span> {{ $item['estatus'] ?? 'No definido' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(!empty($alerta['acciones']))
                            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Acciones sugeridas</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($alerta['acciones'] as $accion)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">{{ $accion }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
