@extends('portal_alumno.layouts.app')

@section('title', 'Horario')
@section('mobile_title', 'Horario')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Mi horario</h2>
        <p class="text-slate-500 mt-1">Consulta tus clases por dia y tus proximas sesiones programadas.</p>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <h3 class="text-xl font-extrabold mb-5">Horario semanal</h3>
        <div class="grid lg:grid-cols-7 gap-3">
            @foreach($dias as $dia)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 min-h-36">
                    <p class="font-extrabold text-[#0f2a5f] mb-3">{{ $dia }}</p>
                    @forelse($horariosPorDia[$dia] as $horario)
                        <article class="rounded-xl bg-white p-3 mb-2 shadow-sm">
                            <p class="text-xs font-extrabold text-slate-900">{{ $horario->horario }}</p>
                            <p class="text-sm font-bold text-slate-800 leading-tight mt-1">{{ $horario->materia->nombre ?? 'Materia' }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $horario->aula ?? 'Aula pendiente' }}</p>
                        </article>
                    @empty
                        <p class="text-xs text-slate-400">Sin clases</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <h3 class="text-xl font-extrabold mb-5">Proximas sesiones</h3>
        <div class="space-y-3">
            @forelse($proximasSesiones as $sesion)
                <article class="rounded-2xl border border-slate-100 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase text-[#0f2a5f]">{{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->dia_semana }}</p>
                        <h4 class="font-extrabold text-slate-900">{{ $sesion->calendarioMateria->nombre_materia ?? 'Materia pendiente' }}</h4>
                        <p class="text-sm text-slate-500">{{ $sesion->calendarioMateria->nombre_docente ?? 'Docente pendiente' }}</p>
                    </div>
                    <div class="text-sm font-bold text-slate-700 md:text-right">
                        <p>{{ $sesion->horario }}</p>
                        <p class="text-slate-500">{{ $sesion->aula ?? 'Aula pendiente' }}</p>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">No hay sesiones proximas registradas.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
