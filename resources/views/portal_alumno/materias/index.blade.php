@extends('portal_alumno.layouts.app')

@section('title', 'Materias')
@section('mobile_title', 'Materias')

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
                    Mis materias
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta tus materias, docentes, sesiones próximas y horarios registrados para tu grupo.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Programa</p>

                <p class="text-lg font-extrabold mt-1 leading-tight">
                    {{ $alumno->grupo->programa->nombre ?? 'Programa no asignado' }}
                </p>

                <p class="text-xs text-blue-100 mt-2">
                    {{ $alumno->grupo->cicloEscolar->nombre ?? 'Ciclo no definido' }}
                </p>
            </div>
        </div>
    </section>

    <div class="grid md:grid-cols-4 gap-4">
        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Materias</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $totalMaterias }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                    <i class='bx bx-book-open text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Docentes</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $totalDocentes }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-user-voice text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Modalidades</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $totalModalidades }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class='bx bx-laptop text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Próximas sesiones</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $totalProximasSesiones }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class='bx bx-calendar-check text-2xl'></i>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Materias del calendario</h3>
                <p class="text-sm text-slate-500">
                    Materias vinculadas al calendario académico de tu grupo.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-book-open text-2xl'></i>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-4">
            @forelse($materiasCalendario as $item)
                @php
                    $horariosMateria = $item->materia_id
                        ? ($horariosPorMateria->get($item->materia_id) ?? collect())
                        : collect();

                    $estatusClase = match($item->estatus) {
                        'Confirmada' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                        'Impartida' => 'bg-blue-50 text-blue-700 ring-blue-100',
                        'Programada' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        default => 'bg-slate-100 text-slate-600 ring-slate-200',
                    };
                @endphp

                <article class="rounded-3xl border border-slate-100 p-5 hover:border-[#0f2a5f]/30 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase text-amber-600">
                                Orden {{ $item->orden ?? 'N/A' }}
                            </p>

                            <h4 class="text-lg font-extrabold text-slate-950 mt-1 leading-tight">
                                {{ $item->nombre_materia }}
                            </h4>

                            <p class="text-sm text-slate-500 mt-2">
                                <i class='bx bx-user-voice mr-1'></i>
                                {{ $item->nombre_docente }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-extrabold ring-1 {{ $estatusClase }}">
                            {{ $item->estatus }}
                        </span>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Sesiones activas</p>
                            <p class="text-2xl font-extrabold text-slate-950 mt-1">
                                {{ $item->sesiones_activas_count }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Calendario</p>
                            <p class="font-extrabold text-slate-950 mt-1 line-clamp-2">
                                {{ $item->calendario->nombre ?? 'No definido' }}
                            </p>
                        </div>
                    </div>

                    @if($horariosMateria->isNotEmpty())
                        <div class="mt-4">
                            <p class="text-xs font-bold uppercase text-slate-400 mb-2">
                                Horario semanal
                            </p>

                            <div class="flex flex-wrap gap-2">
                                @foreach($horariosMateria as $horario)
                                    <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-[#0f2a5f]">
                                        {{ $horario->dia_semana }} · {{ $horario->horario }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($item->sesiones->isNotEmpty())
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <p class="text-xs font-bold uppercase text-slate-400 mb-2">
                                Próximas sesiones
                            </p>

                            <div class="space-y-2">
                                @foreach($item->sesiones->take(2) as $sesion)
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-sm font-extrabold text-slate-900">
                                            {{ $sesion->dia_semana }} {{ $sesion->fecha?->format('d/m/Y') }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $sesion->horario }} · {{ $sesion->modalidad ?? 'Modalidad pendiente' }}
                                            · Aula: {{ $sesion->aula ?? 'Pendiente' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl bg-slate-50 p-6 text-center xl:col-span-2">
                    <i class='bx bx-book-reader text-4xl text-slate-400'></i>

                    <h4 class="font-extrabold text-slate-900 mt-2">
                        No hay materias de calendario registradas
                    </h4>

                    <p class="text-sm text-slate-500 mt-1">
                        Cuando el área académica vincule materias al calendario de tu grupo, aparecerán aquí.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Horario semanal por materia</h3>
                <p class="text-sm text-slate-500">
                    Bloques de clase detectados en el horario de tu grupo.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-time-five text-2xl'></i>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-4">
            @forelse($materiasHorario as $horario)
                @php
                    $horariosMateria = $horario->materia_id
                        ? ($horariosPorMateria->get($horario->materia_id) ?? collect())
                        : collect();
                @endphp

                <article class="rounded-2xl border border-slate-100 p-4">
                    <h4 class="font-extrabold text-slate-950">
                        {{ $horario->materia->nombre ?? 'Materia no disponible' }}
                    </h4>

                    <p class="text-sm text-slate-500 mt-1">
                        {{ $horario->docente->nombre_completo ?? 'Docente pendiente' }}
                    </p>

                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($horariosMateria as $bloque)
                            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                                {{ $bloque->dia_semana }} · {{ $bloque->horario }}
                            </span>
                        @endforeach
                    </div>

                    <p class="text-xs text-slate-400 mt-3">
                        Modalidad: {{ $horario->modalidad ?? 'No definida' }}
                    </p>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 xl:col-span-2">
                    No hay materias detectadas en el horario semanal.
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
                    Las materias, docentes y horarios son administrados por el área académica del IDEJ. Si detectas información incorrecta o incompleta, comunícate con control escolar.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
