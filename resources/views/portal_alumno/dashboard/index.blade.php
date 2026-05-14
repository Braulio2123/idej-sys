@extends('portal_alumno.layouts.app')

@section('title', 'Panel del alumno')
@section('mobile_title', 'Inicio')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-[#0f2a5f]">Hola, {{ $alumno->nombre_corto }}</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Panel academico</h2>
            <p class="text-slate-500 mt-1">Resumen rapido de tu informacion como alumno IDEJ.</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-4 portal-card">
            <p class="text-xs font-bold text-slate-400 uppercase">Matricula</p>
            <p class="text-lg font-extrabold text-[#0f2a5f]">{{ $alumno->matricula }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-4 gap-4">
        <div class="rounded-3xl bg-white p-5 portal-card">
            <i class='bx bx-group text-3xl text-[#0f2a5f]'></i>
            <p class="text-xs font-bold text-slate-400 uppercase mt-3">Grupo</p>
            <p class="text-lg font-extrabold">{{ $alumno->grupo->nombre ?? 'Sin grupo' }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 portal-card">
            <i class='bx bx-book-open text-3xl text-[#0f2a5f]'></i>
            <p class="text-xs font-bold text-slate-400 uppercase mt-3">Programa</p>
            <p class="text-lg font-extrabold">{{ $alumno->grupo->programa->nombre ?? 'Sin programa' }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 portal-card">
            <i class='bx bx-check-shield text-3xl text-[#0f2a5f]'></i>
            <p class="text-xs font-bold text-slate-400 uppercase mt-3">Estatus academico</p>
            <p class="text-lg font-extrabold">{{ $alumno->estatus_academico ?? 'No definido' }}</p>
        </div>
        <div class="rounded-3xl bg-white p-5 portal-card">
            <i class='bx bx-wallet text-3xl text-[#0f2a5f]'></i>
            <p class="text-xs font-bold text-slate-400 uppercase mt-3">Estatus financiero</p>
            <p class="text-lg font-extrabold">{{ $alumno->estatus_financiero ?? 'No definido' }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-[1.2fr_.8fr] gap-5">
        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Horario de hoy</h3>
                    <p class="text-sm text-slate-500">{{ $diaActual }}</p>
                </div>
                <a href="{{ route('portal.alumno.horario') }}" class="text-sm font-bold text-[#0f2a5f]">Ver todo</a>
            </div>

            @forelse($horariosHoy as $horario)
                <article class="rounded-2xl border border-slate-100 p-4 mb-3">
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="font-extrabold text-slate-900">{{ $horario->materia->nombre ?? 'Materia no disponible' }}</p>
                            <p class="text-sm text-slate-500">{{ $horario->docente->nombre_completo ?? 'Docente pendiente' }}</p>
                        </div>
                        <span class="text-sm font-extrabold text-[#0f2a5f] whitespace-nowrap">{{ $horario->horario }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Aula: {{ $horario->aula ?? 'Pendiente' }} · {{ $horario->modalidad }}</p>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-slate-500 text-sm">No hay clases registradas para hoy.</div>
            @endforelse
        </section>

        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Avisos recientes</h3>
                    <p class="text-sm text-slate-500">Comunicados institucionales</p>
                </div>
                <a href="{{ route('portal.alumno.avisos') }}" class="text-sm font-bold text-[#0f2a5f]">Ver avisos</a>
            </div>

            @forelse($avisos as $aviso)
                <article class="rounded-2xl border border-slate-100 p-4 mb-3">
                    <p class="text-xs font-bold uppercase text-amber-600">{{ $aviso->categoria }} · {{ $aviso->prioridad }}</p>
                    <h4 class="font-extrabold text-slate-900">{{ $aviso->titulo }}</h4>
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $aviso->contenido }}</p>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-slate-500 text-sm">No hay avisos activos por el momento.</div>
            @endforelse
        </section>
    </div>

    <section class="rounded-3xl bg-[#0f2a5f] text-white p-5 md:p-6 portal-card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-xl font-extrabold">Proximas sesiones</h3>
                <p class="text-blue-100 text-sm">Clases o actividades programadas en calendario academico.</p>
            </div>
            <a href="{{ route('portal.alumno.horario') }}" class="inline-flex items-center justify-center rounded-2xl bg-white text-[#0f2a5f] px-4 py-3 text-sm font-extrabold">Consultar horario</a>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3 mt-5">
            @forelse($proximasSesiones as $sesion)
                <article class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="text-xs font-bold text-amber-200">{{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->horario }}</p>
                    <h4 class="font-extrabold mt-1">{{ $sesion->calendarioMateria->nombre_materia ?? 'Materia pendiente' }}</h4>
                    <p class="text-sm text-blue-100">Aula: {{ $sesion->aula ?? 'Pendiente' }}</p>
                </article>
            @empty
                <div class="rounded-2xl bg-white/10 p-5 text-blue-100 text-sm md:col-span-2 xl:col-span-3">No hay sesiones proximas registradas.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
