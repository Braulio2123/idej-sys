@extends('portal_alumno.layouts.app')

@section('title', 'Perfil')
@section('mobile_title', 'Perfil')

@section('content')
@php
    $inicial = strtoupper(substr((string) $alumno->nombre_completo, 0, 1));

    $estatusAcademicoClase = match($alumno->estatus_academico) {
        'Activo' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'Baja Temporal' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'Suspendido' => 'bg-red-50 text-red-700 ring-red-100',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };

    $estatusFinancieroClase = match($alumno->estatus_financiero) {
        'Al Corriente' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'Becado' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'En Convenio' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'Con Adeudo' => 'bg-red-50 text-red-700 ring-red-100',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };

    $portalActivo = (bool) $alumno->portal_activo;
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] bg-[#0f2a5f] text-white p-5 md:p-7 portal-card">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-amber-300/20"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-[1.7rem] bg-white text-[#0f2a5f] flex items-center justify-center text-4xl font-extrabold shadow-lg">
                    {{ $inicial }}
                </div>

                <div>
                    <p class="text-sm font-bold text-amber-200">
                        {{ $alumno->matricula }}
                    </p>

                    <h2 class="text-2xl md:text-4xl font-extrabold mt-1 leading-tight">
                        {{ $alumno->nombre_completo }}
                    </h2>

                    <p class="text-blue-100 mt-1">
                        {{ $alumno->grupo->programa->nombre ?? 'Programa no asignado' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold ring-1 ring-white/15">
                    <i class='bx bx-user-check mr-1 text-amber-300'></i>
                    {{ $alumno->estatus_academico ?? 'Sin estatus' }}
                </span>

                <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold ring-1 ring-white/15">
                    <i class='bx bx-wallet mr-1 text-amber-300'></i>
                    {{ $alumno->estatus_financiero ?? 'Sin estatus financiero' }}
                </span>
            </div>
        </div>
    </section>

    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-5">
        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Información académica</h3>
                    <p class="text-sm text-slate-500">
                        Datos principales registrados por control escolar.
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                    <i class='bx bxs-graduation text-2xl'></i>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Matrícula</p>
                    <p class="font-extrabold text-slate-950 mt-1">{{ $alumno->matricula }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Grupo</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->nombre ?? 'Sin grupo asignado' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Programa</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->programa->nombre ?? 'Sin programa asignado' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Nivel</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->programa->nivel ?? 'No definido' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Ciclo escolar</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->cicloEscolar->nombre ?? $alumno->grupo->cicloEscolar->nombre ?? 'No definido' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Periodo</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->semestre_o_cuatrimestre ?? 'No definido' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Turno</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->turno ?? 'No definido' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Aula</p>
                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->grupo->aula ?? 'No definida' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-950">Estado del alumno</h3>
                    <p class="text-sm text-slate-500">
                        Resumen académico, financiero y del acceso al portal.
                    </p>
                </div>

                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class='bx bx-info-circle text-2xl'></i>
                </div>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Estatus académico</p>

                    <span class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 {{ $estatusAcademicoClase }}">
                        {{ $alumno->estatus_academico ?? 'No definido' }}
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Estatus financiero</p>

                    <span class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 {{ $estatusFinancieroClase }}">
                        {{ $alumno->estatus_financiero ?? 'No definido' }}
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Condición</p>

                    <p class="font-extrabold text-slate-950 mt-1">
                        {{ $alumno->condicion_alumno ?? 'Normal' }}
                    </p>

                    @if((int) $alumno->beca_porcentaje > 0)
                        <p class="text-sm text-slate-500 mt-1">
                            Beca registrada: {{ (int) $alumno->beca_porcentaje }}%
                        </p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">Acceso al portal</p>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-extrabold ring-1 {{ $portalActivo ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-700 ring-red-100' }}">
                            {{ $portalActivo ? 'Activo' : 'Inactivo' }}
                        </span>

                        <span class="text-xs text-slate-500">
                            Último acceso:
                            {{ $alumno->portal_ultimo_acceso_at?->format('d/m/Y H:i') ?? 'Sin registro' }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </div>

     <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Contacto</h3>
                <p class="text-sm text-slate-500">
                    Información registrada para comunicación institucional.
                </p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-[#0f2a5f] flex items-center justify-center">
                <i class='bx bx-envelope text-2xl'></i>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Correo</p>
                <p class="font-extrabold text-slate-950 mt-1 break-words">
                    {{ $alumno->correo ?? 'Correo no registrado' }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Teléfono</p>
                <p class="font-extrabold text-slate-950 mt-1">
                    {{ $alumno->telefono ?? 'Teléfono no registrado' }}
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-5 portal-card">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class='bx bx-lock-alt text-xl'></i>
            </div>

            <div>
                <h3 class="font-extrabold text-slate-950">Información de solo consulta</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Por seguridad, los datos personales, académicos y financieros no se modifican desde este portal. Si detectas información incorrecta, comunícate con control escolar o administración del IDEJ.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
