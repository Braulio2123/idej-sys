@extends('portal_alumno.layouts.app')

@section('title', 'Perfil')
@section('mobile_title', 'Perfil')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Mi perfil</h2>
        <p class="text-slate-500 mt-1">Informacion academica registrada en control escolar.</p>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="flex flex-col md:flex-row md:items-center gap-5 border-b border-slate-100 pb-6 mb-6">
            <div class="h-20 w-20 rounded-3xl bg-[#0f2a5f] text-white flex items-center justify-center text-3xl font-extrabold">
                {{ strtoupper(substr($alumno->nombre_completo, 0, 1)) }}
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-[#0f2a5f]">{{ $alumno->matricula }}</p>
                <h3 class="text-2xl font-extrabold text-slate-950">{{ $alumno->nombre_completo }}</h3>
                <p class="text-slate-500">{{ $alumno->correo ?? 'Correo no registrado' }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Telefono</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->telefono ?? 'No registrado' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Grupo</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->grupo->nombre ?? 'Sin grupo' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Programa</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->grupo->programa->nombre ?? 'Sin programa' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Ciclo escolar</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->cicloEscolar->nombre ?? $alumno->grupo->cicloEscolar->nombre ?? 'No definido' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Estatus academico</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->estatus_academico ?? 'No definido' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase text-slate-400">Condicion</p>
                <p class="font-extrabold text-slate-900 mt-1">{{ $alumno->condicion_alumno ?? 'Normal' }}</p>
            </div>
        </div>
    </section>
</div>
@endsection
