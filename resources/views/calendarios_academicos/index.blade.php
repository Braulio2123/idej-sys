@extends('layouts.app')

@section('title', 'Calendarios académicos')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Calendarios académicos</h1>
            <p class="text-sm text-slate-500 mt-1">Planeación por fechas fijas: materias, docentes, sesiones, aulas y días no laborales.</p>
        </div>
        <a href="{{ route('calendarios_academicos.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">+ Nuevo calendario</a>
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow border border-slate-100 p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <select name="grupo_id" class="rounded-xl border-slate-300">
            <option value="">Todos los grupos</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected(request('grupo_id') == $grupo->id)>{{ $grupo->nombre }}</option>
            @endforeach
        </select>
        <input type="text" name="periodo" value="{{ request('periodo') }}" class="rounded-xl border-slate-300" placeholder="Periodo, ej. 2026 A">
        <select name="estatus" class="rounded-xl border-slate-300">
            <option value="">Todos los estatus</option>
            @foreach($estatuses as $estatus)
                <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-slate-800 text-white px-4 py-2">Filtrar</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($calendarios as $calendario)
            <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
                <div class="flex justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $calendario->nombre }}</h2>
                        <p class="text-sm text-slate-500">{{ $calendario->grupo->nombre ?? 'Sin grupo' }} · {{ $calendario->periodo ?? 'Sin periodo' }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $calendario->tipo_calendario ?? 'Personalizado' }}</p>
                    </div>
                    <span class="h-fit px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $calendario->estatus }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-500">Sesiones</p>
                        <p class="text-xl font-bold text-slate-800">{{ $calendario->sesiones_count }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-500">Rango</p>
                        <p class="font-semibold text-slate-800">{{ $calendario->rango_fechas }}</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3 text-sm font-semibold">
                    <a href="{{ route('calendarios_academicos.show', $calendario) }}" class="text-indigo-700 hover:underline">Ver</a>
                    <a href="{{ route('calendarios_academicos.edit', $calendario) }}" class="text-blue-700 hover:underline">Editar</a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl shadow border border-slate-100 p-8 text-center text-slate-500">No hay calendarios académicos registrados.</div>
        @endforelse
    </div>

    {{ $calendarios->links() }}
</div>
@endsection
