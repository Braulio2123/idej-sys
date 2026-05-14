@extends('portal_alumno.layouts.app')

@section('title', 'Materias')
@section('mobile_title', 'Materias')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Mis materias</h2>
        <p class="text-slate-500 mt-1">Materias vinculadas a tu grupo y calendario academico.</p>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <h3 class="text-xl font-extrabold mb-5">Materias de calendario</h3>
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($materiasCalendario as $item)
                <article class="rounded-2xl border border-slate-100 p-5 hover:border-[#0f2a5f]/30 transition">
                    <p class="text-xs font-bold uppercase text-amber-600">Orden {{ $item->orden }} · {{ $item->estatus }}</p>
                    <h4 class="text-lg font-extrabold text-slate-900 mt-1">{{ $item->nombre_materia }}</h4>
                    <p class="text-sm text-slate-500 mt-2">Docente: {{ $item->nombre_docente }}</p>
                    <p class="text-xs text-slate-400 mt-3">Calendario: {{ $item->calendario->nombre ?? 'No definido' }}</p>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500 md:col-span-2 xl:col-span-3">No hay materias de calendario registradas para tu grupo.</div>
            @endforelse
        </div>
    </section>

    @if($materiasHorario->isNotEmpty())
        <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
            <h3 class="text-xl font-extrabold mb-5">Materias detectadas por horario semanal</h3>
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($materiasHorario as $horario)
                    <article class="rounded-2xl border border-slate-100 p-5">
                        <h4 class="text-lg font-extrabold text-slate-900">{{ $horario->materia->nombre ?? 'Materia no disponible' }}</h4>
                        <p class="text-sm text-slate-500 mt-2">Docente: {{ $horario->docente->nombre_completo ?? 'Pendiente' }}</p>
                        <p class="text-xs text-slate-400 mt-3">Modalidad: {{ $horario->modalidad }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
