@extends('portal_alumno.layouts.app')

@section('title', 'Calificaciones')
@section('mobile_title', 'Calificaciones')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Mis calificaciones</h2>
        <p class="text-slate-500 mt-1">Vista preparada para conectar el modulo formal de evaluaciones.</p>
    </div>

    <div class="rounded-3xl bg-amber-50 border border-amber-100 p-5 text-amber-900">
        <p class="font-extrabold">Modulo en preparacion</p>
        <p class="text-sm mt-1">El proyecto base todavia no contiene una tabla formal de calificaciones. Esta pantalla ya respeta el flujo del portal y muestra tus materias reales para conectar las evaluaciones despues.</p>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <h3 class="text-xl font-extrabold mb-5">Materias disponibles</h3>
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($materias as $materia)
                <article class="rounded-2xl border border-slate-100 p-5">
                    <h4 class="text-lg font-extrabold text-slate-900">{{ $materia['nombre'] }}</h4>
                    <p class="text-sm text-slate-500 mt-1">{{ $materia['docente'] }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">P1</p>
                            <p class="font-extrabold text-slate-500">Pend.</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">P2</p>
                            <p class="font-extrabold text-slate-500">Pend.</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-400">Final</p>
                            <p class="font-extrabold text-slate-500">Pend.</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 md:col-span-2 xl:col-span-3">No hay materias disponibles para mostrar calificaciones.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
