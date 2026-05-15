@extends('portal_alumno.layouts.app')

@section('title', 'Horario')
@section('mobile_title', 'Horario')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm font-bold text-amber-200">
                    {{ $alumno->grupo->nombre ?? 'Sin grupo asignado' }}
                </p>

                <h2 class="text-3xl md:text-4xl font-extrabold mt-1">
                    Mi horario
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta tus clases de la semana y las próximas sesiones registradas para tu grupo.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Hoy</p>
                <p class="text-3xl font-extrabold mt-1">{{ $diaActual }}</p>
                <p class="text-xs text-blue-100 mt-2">
                    {{ $horariosHoy->count() }} clase(s) registrada(s)
                </p>
            </div>
        </div>
    </section>

    <div class="grid md:grid-cols-3 gap-4">
        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Clases hoy</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $horariosHoy->count() }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                    <i class='bx bx-calendar-check text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Semana</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $totalClasesSemana }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        clases en {{ $totalDiasConClase }} día(s)
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-time-five text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Próxima sesión</p>

                    @if($proximaSesion)
                        <p class="text-xl font-extrabold text-slate-950 mt-1">
                            {{ $proximaSesion->fecha?->format('d/m/Y') }}
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            {{ $proximaSesion->horario }}
                        </p>
                    @else
                        <p class="text-xl font-extrabold text-slate-950 mt-1">
                            Sin registro
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            No hay sesiones próximas
                        </p>
                    @endif
                </div>

                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class='bx bx-calendar-event text-2xl'></i>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Clases de hoy</h3>
                <p class="text-sm text-slate-500">
                    {{ $diaActual }} · {{ now()->format('d/m/Y') }}
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-calendar-star text-2xl'></i>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            @forelse($horariosHoy as $horario)
                <article class="rounded-3xl border border-slate-100 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase text-amber-600">
                                {{ $horario->horario }}
                            </p>

                            <h4 class="text-lg font-extrabold text-slate-950 mt-1">
                                {{ $horario->materia->nombre ?? 'Materia no disponible' }}
                            </h4>

                            <p class="text-sm text-slate-500 mt-2">
                                <i class='bx bx-user-voice mr-1'></i>
                                {{ $horario->docente->nombre_completo ?? 'Docente pendiente' }}
                            </p>
                        </div>

                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1.5 text-xs font-extrabold text-[#0f2a5f] ring-1 ring-blue-100">
                            {{ $horario->modalidad ?? 'Modalidad pendiente' }}
                        </span>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">Aula</p>
                        <p class="font-extrabold text-slate-950 mt-1">
                            {{ $horario->aula ?? 'Aula pendiente' }}
                        </p>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl bg-slate-50 p-6 text-center lg:col-span-2">
                    <i class='bx bx-coffee text-4xl text-slate-400'></i>

                    <h4 class="font-extrabold text-slate-900 mt-2">
                        No tienes clases registradas para hoy
                    </h4>

                    <p class="text-sm text-slate-500 mt-1">
                        Revisa el horario semanal para consultar tus próximos días de clase.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Horario semanal</h3>
                <p class="text-sm text-slate-500">
                    Distribución de clases por día.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-grid-alt text-2xl'></i>
            </div>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-7 gap-3">
            @foreach($dias as $dia)
                @php
                    $esHoy = $dia === $diaActual;
                    $clasesDia = $horariosPorDia[$dia] ?? collect();
                @endphp

                <div class="rounded-3xl border {{ $esHoy ? 'border-[#0f2a5f] bg-blue-50/60' : 'border-slate-100 bg-slate-50' }} p-4 min-h-40">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <p class="font-extrabold {{ $esHoy ? 'text-[#0f2a5f]' : 'text-slate-800' }}">
                            {{ $dia }}
                        </p>

                        @if($esHoy)
                            <span class="rounded-full bg-[#0f2a5f] px-2.5 py-1 text-[10px] font-extrabold text-white">
                                Hoy
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @forelse($clasesDia as $horario)
                            <article class="rounded-2xl bg-white p-3 shadow-sm">
                                <p class="text-xs font-extrabold text-[#0f2a5f]">
                                    {{ $horario->horario }}
                                </p>

                                <p class="text-sm font-extrabold text-slate-900 leading-tight mt-1">
                                    {{ $horario->materia->nombre ?? 'Materia no disponible' }}
                                </p>

                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $horario->aula ?? 'Aula pendiente' }}
                                </p>

                                <p class="text-[11px] text-slate-400 mt-1">
                                    {{ $horario->modalidad ?? 'Modalidad pendiente' }}
                                </p>
                            </article>
                        @empty
                            <p class="text-xs text-slate-400">
                                Sin clases
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Próximas sesiones</h3>
                <p class="text-sm text-slate-500">
                    Sesiones programadas en el calendario académico.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class='bx bx-calendar-event text-2xl'></i>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($proximasSesiones as $sesion)
                @php
                    $esHoySesion = $sesion->fecha?->isToday();
                @endphp

                <article class="rounded-3xl border {{ $esHoySesion ? 'border-[#0f2a5f] bg-blue-50/50' : 'border-slate-100 bg-white' }} p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-bold uppercase text-[#0f2a5f]">
                                    {{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->dia_semana }}
                                </p>

                                @if($esHoySesion)
                                    <span class="rounded-full bg-[#0f2a5f] px-2.5 py-1 text-[10px] font-extrabold text-white">
                                        Hoy
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-extrabold text-slate-900 mt-1">
                                {{ $sesion->calendarioMateria->nombre_materia ?? 'Materia pendiente' }}
                            </h4>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $sesion->calendarioMateria->nombre_docente ?? 'Docente pendiente' }}
                            </p>
                        </div>

                        <div class="md:text-right">
                            <p class="text-sm font-extrabold text-slate-800">
                                {{ $sesion->horario }}
                            </p>

                            <p class="text-sm text-slate-500">
                                Aula: {{ $sesion->aula ?? 'Pendiente' }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ $sesion->modalidad ?? 'Modalidad pendiente' }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                    No hay sesiones próximas registradas.
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class='bx bx-info-circle text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información académica de consulta</h3>
                <p class="text-sm text-slate-500 mt-1">
                    El horario y las sesiones son administrados por el área académica del IDEJ. Si detectas un cambio no reflejado o información incorrecta, comunícate con control escolar.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
