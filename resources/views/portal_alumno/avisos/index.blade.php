@extends('portal_alumno.layouts.app')

@section('title', 'Avisos')
@section('mobile_title', 'Avisos')

@section('content')
@php
    $avisosPagina = $avisos->getCollection();
    $avisosUrgentes = $avisosPagina->where('prioridad', 'urgente')->count();
    $avisosImportantes = $avisosPagina->where('prioridad', 'importante')->count();
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm font-bold text-amber-200">
                    {{ $alumno->grupo->nombre ?? 'Portal Alumno' }}
                </p>

                <h2 class="text-3xl md:text-4xl font-extrabold mt-1">
                    Avisos
                </h2>

                <p class="text-blue-100 mt-2 max-w-2xl">
                    Consulta comunicados institucionales, recordatorios e información relevante para tu grupo.
                </p>
            </div>

            <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-4 min-w-[230px]">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Avisos visibles</p>

                <p class="text-4xl font-extrabold mt-1">
                    {{ $avisos->total() }}
                </p>

                <p class="text-xs text-blue-100 mt-2">
                    {{ $avisosUrgentes > 0 ? $avisosUrgentes . ' urgente(s) en esta página' : 'Sin urgentes en esta página' }}
                </p>
            </div>
        </div>
    </section>

    <div class="grid md:grid-cols-3 gap-4">
        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Total visibles</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $avisos->total() }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                    <i class='bx bx-bell text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Urgentes</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $avisosUrgentes }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                    <i class='bx bx-error-circle text-2xl'></i>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 portal-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Importantes</p>
                    <p class="text-3xl font-extrabold text-slate-950 mt-1">
                        {{ $avisosImportantes }}
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-info-circle text-2xl'></i>
                </div>
            </div>
        </section>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Comunicados recientes</h3>
                <p class="text-sm text-slate-500">
                    Avisos publicados para todos los alumnos o para tu grupo.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-message-square-detail text-2xl'></i>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($avisos as $aviso)
                @php
                    $prioridadClase = match($aviso->prioridad) {
                        'urgente' => 'bg-red-50 text-red-700 ring-red-100',
                        'importante' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        default => 'bg-blue-50 text-[#0f2a5f] ring-blue-100',
                    };

                    $bordeClase = match($aviso->prioridad) {
                        'urgente' => 'border-red-100 bg-red-50/40',
                        'importante' => 'border-amber-100 bg-amber-50/30',
                        default => 'border-slate-100 bg-white',
                    };

                    $fechaAviso = $aviso->visible_desde ?? $aviso->created_at;

                    $destinoTexto = $aviso->destino_tipo === 'grupo'
                        ? 'Dirigido a tu grupo'
                        : 'Aviso general';
                @endphp

                <article class="rounded-3xl border {{ $bordeClase }} p-5">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1.5 text-[11px] font-extrabold ring-1 {{ $prioridadClase }}">
                                    {{ ucfirst((string) $aviso->prioridad) }}
                                </span>

                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                                    {{ $aviso->categoria ?? 'General' }}
                                </span>

                                <span class="rounded-full bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-500 ring-1 ring-slate-100">
                                    {{ $destinoTexto }}
                                </span>
                            </div>

                            <h4 class="text-xl font-extrabold text-slate-950 mt-3 leading-tight">
                                {{ $aviso->titulo }}
                            </h4>
                        </div>

                        <div class="shrink-0 md:text-right">
                            <p class="text-xs font-bold uppercase text-slate-400">
                                Publicado
                            </p>

                            <p class="text-sm font-extrabold text-slate-700 mt-1">
                                {{ $fechaAviso?->format('d/m/Y') ?? 'Sin fecha' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-white/70 p-4">
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ $aviso->contenido }}
                        </p>
                    </div>

                    @if($aviso->visible_hasta)
                        <p class="text-xs text-slate-400 mt-3">
                            Visible hasta: {{ $aviso->visible_hasta->format('d/m/Y') }}
                        </p>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl bg-slate-50 p-6 text-center">
                    <i class='bx bx-bell-off text-4xl text-slate-400'></i>

                    <h4 class="font-extrabold text-slate-900 mt-2">
                        No hay avisos activos
                    </h4>

                    <p class="text-sm text-slate-500 mt-1">
                        Cuando exista un comunicado vigente, aparecerá en esta sección.
                    </p>
                </div>
            @endforelse
        </div>

        @if($avisos->hasPages())
            <div class="mt-6">
                {{ $avisos->links() }}
            </div>
        @endif
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class='bx bx-info-circle text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información institucional</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Los avisos son publicados por el IDEJ y pueden estar dirigidos a todos los alumnos o específicamente a tu grupo. Revisa esta sección con frecuencia para mantenerte informado.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
