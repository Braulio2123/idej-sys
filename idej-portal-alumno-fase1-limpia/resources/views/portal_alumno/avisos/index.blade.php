@extends('portal_alumno.layouts.app')

@section('title', 'Avisos')
@section('mobile_title', 'Avisos')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Avisos institucionales</h2>
        <p class="text-slate-500 mt-1">Comunicados generales o dirigidos a tu grupo.</p>
    </div>

    <section class="rounded-3xl bg-white p-5 md:p-6 portal-card">
        <div class="space-y-4">
            @forelse($avisos as $aviso)
                <article class="rounded-2xl border border-slate-100 p-5">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase {{ $aviso->prioridad === 'urgente' ? 'text-red-600' : ($aviso->prioridad === 'importante' ? 'text-amber-600' : 'text-[#0f2a5f]') }}">
                                {{ $aviso->categoria }} · {{ ucfirst($aviso->prioridad) }}
                            </p>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-1">{{ $aviso->titulo }}</h3>
                        </div>
                        <p class="text-xs text-slate-400">{{ $aviso->visible_desde?->format('d/m/Y') ?? $aviso->created_at?->format('d/m/Y') }}</p>
                    </div>
                    <p class="text-sm text-slate-600 mt-4 leading-relaxed">{{ $aviso->contenido }}</p>
                </article>
            @empty
                <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">No hay avisos activos por el momento.</div>
            @endforelse
        </div>

        <div class="mt-5">
            {{ $avisos->links() }}
        </div>
    </section>
</div>
@endsection
