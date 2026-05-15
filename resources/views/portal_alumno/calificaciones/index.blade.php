@extends('portal_alumno.layouts.app')

@section('title', 'Calificaciones')
@section('mobile_title', 'Calificaciones')

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
                    Mis calificaciones
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta el estado de tus materias y calificaciones publicadas por el área académica.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Materias detectadas</p>

                <p class="text-4xl font-extrabold mt-1">
                    {{ $totalMaterias }}
                </p>

                <p class="text-xs text-blue-100 mt-2">
                    {{ $alumno->grupo->programa->nombre ?? 'Programa no asignado' }}
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class='bx bx-info-circle text-2xl'></i>
            </div>

            <div>
                <h3 class="text-lg font-extrabold text-slate-950">
                    Calificaciones pendientes de publicación
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    Esta sección ya está preparada dentro del Portal Alumno. Cuando el sistema interno tenga calificaciones registradas y publicadas, aparecerán aquí de forma automática.
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Materias</h3>
                <p class="text-sm text-slate-500">
                    Materias asociadas a tu grupo académico.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-bar-chart-alt-2 text-2xl'></i>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-4">
            @forelse($materias as $materia)
                <article class="rounded-3xl border border-slate-100 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase text-amber-600">
                                {{ $materia['origen'] }}
                            </p>

                            <h4 class="text-lg font-extrabold text-slate-950 mt-1 leading-tight">
                                {{ $materia['nombre'] }}
                            </h4>

                            <p class="text-sm text-slate-500 mt-2">
                                <i class='bx bx-user-voice mr-1'></i>
                                {{ $materia['docente'] }}
                            </p>

                            <p class="text-xs text-slate-400 mt-2">
                                {{ $materia['calendario'] }}
                            </p>
                        </div>

                        <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-extrabold text-slate-600 ring-1 ring-slate-200">
                            Pendiente
                        </span>
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Estado de calificación
                        </p>

                        <p class="font-extrabold text-slate-950 mt-1">
                            Pendiente de publicación
                        </p>

                        <p class="text-sm text-slate-500 mt-1">
                            Aún no hay una calificación publicada para esta materia.
                        </p>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl bg-slate-50 p-6 text-center xl:col-span-2">
                    <i class='bx bx-book-reader text-4xl text-slate-400'></i>

                    <h4 class="font-extrabold text-slate-900 mt-2">
                        No hay materias disponibles
                    </h4>

                    <p class="text-sm text-slate-500 mt-1">
                        Cuando tengas materias asociadas a tu grupo, aparecerán aquí.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center shrink-0">
                <i class='bx bx-lock-alt text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información de solo consulta</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Las calificaciones serán administradas por el área académica del IDEJ. Este portal solo mostrará la información cuando esté disponible para el alumno.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
