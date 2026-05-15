@extends('portal_alumno.layouts.app')

@section('title', 'Finanzas')
@section('mobile_title', 'Finanzas')

@section('content')
@php
    $tieneAdeudo = (float) $totalAdeudo > 0;
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm font-bold text-amber-200">
                    {{ $alumno->matricula }}
                </p>

                <h2 class="text-3xl md:text-4xl font-extrabold mt-1">
                    Mis finanzas
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta tus adeudos, próximos vencimientos e historial reciente de pagos.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Estado actual</p>

                <p class="text-3xl font-extrabold mt-1">
                    {{ $tieneAdeudo ? 'Con adeudo' : 'Al corriente' }}
                </p>

                <p class="text-xs text-blue-100 mt-2">
                    {{ $alumno->grupo->nombre ?? 'Sin grupo asignado' }}
                </p>
            </div>
        </div>
    </section>

    <div class="grid md:grid-cols-3 gap-4">
        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Adeudo actual</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        ${{ number_format((float) $totalAdeudo, 2) }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl {{ $tieneAdeudo ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <i class='bx {{ $tieneAdeudo ? 'bx-error-circle' : 'bx-check-circle' }} text-2xl'></i>
                </div>
            </div>

            <p class="text-sm text-slate-500 mt-3">
                {{ $tieneAdeudo ? 'Tienes cargos pendientes registrados.' : 'No tienes adeudos activos registrados.' }}
            </p>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Pagado registrado</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        ${{ number_format((float) $totalPagado, 2) }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class='bx bx-receipt text-2xl'></i>
                </div>
            </div>

            <p class="text-sm text-slate-500 mt-3">
                Total de pagos activos registrados en el sistema.
            </p>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Próximo vencimiento</p>

                    @php
                        $fechaProximoVencimiento = $proximoVencimiento?->fecha_vencimiento
                            ? \Carbon\Carbon::parse($proximoVencimiento->fecha_vencimiento)
                            : null;
                    @endphp

                    <p class="text-2xl font-extrabold text-slate-950 mt-1">
                        {{ $fechaProximoVencimiento ? $fechaProximoVencimiento->format('d/m/Y') : 'Sin vencimiento' }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-calendar-event text-2xl'></i>
                </div>
            </div>

            <p class="text-sm text-slate-500 mt-3">
                {{ $cargosVencidos > 0 ? $cargosVencidos . ' cargo(s) vencido(s).' : 'No hay cargos vencidos.' }}
            </p>
        </section>
    </div>

    <section class="grid xl:grid-cols-[1.15fr_.85fr] gap-5">
        <div class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Adeudos y cargos pendientes</h3>
                    <p class="text-sm text-slate-500">
                        Cargos activos registrados en tu cuenta.
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600">
                    {{ $cargos->count() }} cargo(s)
                </span>
            </div>

            <div class="space-y-3">
                @forelse($cargos as $cargo)
                    @php
                        $fechaVencimiento = $cargo->fecha_vencimiento
                            ? \Carbon\Carbon::parse($cargo->fecha_vencimiento)
                            : null;

                        $estaVencido = $fechaVencimiento && $fechaVencimiento->lt(now()->startOfDay());
                    @endphp

                    <article class="rounded-3xl border {{ $estaVencido ? 'border-red-100 bg-red-50/50' : 'border-slate-100 bg-white' }} p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-lg font-extrabold text-slate-950 leading-tight">
                                        {{ $cargo->descripcion_cargo }}
                                    </h4>

                                    <span class="rounded-full px-3 py-1.5 text-[11px] font-extrabold ring-1 {{ $estaVencido ? 'bg-red-100 text-red-700 ring-red-200' : 'bg-blue-50 text-[#0f2a5f] ring-blue-100' }}">
                                        {{ $estaVencido ? 'Vencido' : $cargo->estatus }}
                                    </span>
                                </div>

                                <p class="text-sm text-slate-500 mt-2">
                                    {{ $cargo->concepto->nombre ?? 'Concepto no disponible' }}
                                </p>

                                <p class="text-xs text-slate-400 mt-2">
                                    Vence:
                                    {{ $fechaVencimiento ? $fechaVencimiento->format('d/m/Y') : 'Sin fecha registrada' }}
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p class="text-xs font-bold uppercase text-slate-400">Saldo pendiente</p>
                                <p class="text-2xl font-extrabold text-slate-950 mt-1">
                                    ${{ number_format((float) $cargo->monto_adeudo, 2) }}
                                </p>

                                @if((float) $cargo->monto_original !== (float) $cargo->monto_adeudo)
                                    <p class="text-xs text-slate-400 mt-1">
                                        Cargo original: ${{ number_format((float) $cargo->monto_original, 2) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl bg-slate-50 p-6 text-center">
                        <i class='bx bx-check-shield text-4xl text-emerald-500'></i>

                        <h4 class="font-extrabold text-slate-900 mt-2">
                            No tienes adeudos activos
                        </h4>

                        <p class="text-sm text-slate-500 mt-1">
                            Cuando exista un cargo pendiente, aparecerá aquí.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-[#0f2a5f] text-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold">Historial reciente</h3>
                    <p class="text-sm text-blue-100">
                        Últimos pagos activos registrados.
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-white/10 text-amber-300 flex items-center justify-center">
                    <i class='bx bx-receipt text-2xl'></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($pagos as $pago)
                    @php
                        $fechaPago = $pago->fecha_pago
                            ? \Carbon\Carbon::parse($pago->fecha_pago)
                            : null;
                    @endphp

                    <article class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-amber-200">
                                    {{ $fechaPago ? $fechaPago->format('d/m/Y') : 'Sin fecha' }}
                                    ·
                                    {{ $pago->metodo_pago ?? 'Método no registrado' }}
                                </p>

                                <h4 class="font-extrabold mt-1">
                                    {{ $pago->folio_recibo ? 'Recibo ' . $pago->folio_recibo : 'Pago registrado' }}
                                </h4>

                                <p class="text-sm text-blue-100 mt-1">
                                    {{ $pago->referencia_principal ? 'Ref: ' . $pago->referencia_principal : 'Sin referencia bancaria' }}
                                </p>
                            </div>

                            <p class="font-extrabold text-lg whitespace-nowrap">
                                ${{ number_format((float) $pago->monto_total_pagado, 2) }}
                            </p>
                        </div>

                        @if($pago->cargos->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($pago->cargos->take(3) as $cargoPagado)
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold text-blue-50">
                                        {{ $cargoPagado->concepto->nombre ?? $cargoPagado->descripcion_cargo }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl bg-white/10 p-5 text-blue-100 text-sm">
                        Aún no hay pagos registrados para mostrar.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center shrink-0">
                <i class='bx bx-info-circle text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información financiera de consulta</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Este apartado es solo informativo. Si detectas un pago faltante, un cargo incorrecto o necesitas aclarar tu estado financiero, comunícate directamente con el área administrativa del IDEJ.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
