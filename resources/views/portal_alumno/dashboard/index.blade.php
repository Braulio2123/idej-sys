@extends('portal_alumno.layouts.app')

@section('title', 'Panel del alumno')
@section('mobile_title', 'Inicio')

@section('content')
@php
    $tieneAdeudo = (float) $totalAdeudo > 0;
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-12 -bottom-12 h-44 w-44 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm font-bold text-amber-200">
                    Hola, {{ $alumno->nombre_corto }}
                </p>

                <h2 class="text-3xl md:text-4xl font-extrabold mt-1">
                    Inicio
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Resumen rápido de tu actividad académica, avisos importantes y estado financiero.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[220px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Matrícula</p>
                <p class="text-2xl font-extrabold mt-1">{{ $alumno->matricula }}</p>
                <p class="text-xs text-blue-100 mt-1">
                    {{ $alumno->grupo->nombre ?? 'Sin grupo asignado' }}
                </p>
            </div>
        </div>
    </section>

    <section class="grid lg:grid-cols-[1.1fr_.9fr] gap-5">
        <div class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Horario de hoy</h3>
                    <p class="text-sm text-slate-500">{{ $diaActual }}</p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                    <i class='bx bx-calendar text-2xl'></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($horariosHoy as $horario)
                    <article class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex justify-between gap-3">
                            <div>
                                <p class="font-extrabold text-slate-900">
                                    {{ $horario->materia->nombre ?? 'Materia no disponible' }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ $horario->docente->nombre_completo ?? 'Docente pendiente' }}
                                </p>
                            </div>

                            <span class="text-sm font-extrabold text-[#0f2a5f] whitespace-nowrap">
                                {{ $horario->horario }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 mt-2">
                            Aula: {{ $horario->aula ?? 'Pendiente' }} · {{ $horario->modalidad }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl bg-slate-50 p-5 text-slate-500 text-sm">
                        No hay clases registradas para hoy.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Estado financiero</h3>
                    <p class="text-sm text-slate-500">Resumen de tus cargos pendientes.</p>
                </div>

                <div class="h-12 w-12 rounded-2xl {{ $tieneAdeudo ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <i class='bx {{ $tieneAdeudo ? 'bx-error-circle' : 'bx-check-circle' }} text-2xl'></i>
                </div>
            </div>

            <div class="rounded-3xl {{ $tieneAdeudo ? 'bg-red-50' : 'bg-emerald-50' }} p-5">
                <p class="text-xs font-bold uppercase {{ $tieneAdeudo ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ $tieneAdeudo ? 'Adeudo pendiente' : 'Sin adeudo activo' }}
                </p>

                <p class="text-3xl font-extrabold text-slate-950 mt-1">
                    ${{ number_format((float) $totalAdeudo, 2) }}
                </p>

                <p class="text-sm text-slate-600 mt-2">
                    @if($cargosVencidos > 0)
                        Tienes {{ $cargosVencidos }} cargo(s) vencido(s).
                    @elseif($proximoVencimiento)
                        Próximo vencimiento: {{ $proximoVencimiento->fecha_vencimiento?->format('d/m/Y') }}.
                    @else
                        No se detectan cargos pendientes por el momento.
                    @endif
                </p>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">
                    Para revisar el detalle de pagos y adeudos, usa la sección <strong>Finanzas</strong> desde la navegación principal.
                </p>
            </div>
        </div>
    </section>

    <section class="grid lg:grid-cols-[.85fr_1.15fr] gap-5">
        <div class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Avisos recientes</h3>
                    <p class="text-sm text-slate-500">Comunicados institucionales importantes.</p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-bell text-2xl'></i>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($avisos as $aviso)
                    <article class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-xs font-bold uppercase text-amber-600">
                            {{ $aviso->categoria }} · {{ $aviso->prioridad }}
                        </p>

                        <h4 class="font-extrabold text-slate-900 mt-1">
                            {{ $aviso->titulo }}
                        </h4>

                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">
                            {{ $aviso->contenido }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl bg-slate-50 p-5 text-slate-500 text-sm">
                        No hay avisos activos por el momento.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-[#0f2a5f] text-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold">Próximas sesiones</h3>
                    <p class="text-blue-100 text-sm">
                        Clases o actividades programadas en calendario académico.
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-white/10 text-amber-300 flex items-center justify-center">
                    <i class='bx bx-calendar-event text-2xl'></i>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-3">
                @forelse($proximasSesiones as $sesion)
                    <article class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                        <p class="text-xs font-bold text-amber-200">
                            {{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->horario }}
                        </p>

                        <h4 class="font-extrabold mt-1">
                            {{ $sesion->calendarioMateria->nombre_materia ?? 'Materia pendiente' }}
                        </h4>

                        <p class="text-sm text-blue-100">
                            Aula: {{ $sesion->aula ?? 'Pendiente' }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl bg-white/10 p-5 text-blue-100 text-sm md:col-span-2">
                        No hay sesiones próximas registradas.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
