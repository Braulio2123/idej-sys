@extends('layouts.app')

@section('title', 'Notificaciones internas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-slate-800">Notificaciones internas</h2>
            <p class="text-sm text-slate-500 mt-1">
                Avisos operativos del sistema según tu rol y responsabilidades.
            </p>
        </div>

        <form action="{{ route('notificaciones.leer-todas') }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class='bx bx-check-double text-lg'></i>
                Marcar todas como leídas
            </button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pendientes</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ $resumen['pendientes'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs uppercase tracking-wide text-red-600">Críticas</p>
            <p class="mt-1 text-3xl font-bold text-red-700">{{ $resumen['criticas'] }}</p>
        </div>
        <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4">
            <p class="text-xs uppercase tracking-wide text-orange-600">Altas</p>
            <p class="mt-1 text-3xl font-bold text-orange-700">{{ $resumen['altas'] }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-xs uppercase tracking-wide text-blue-600">Visibles</p>
            <p class="mt-1 text-3xl font-bold text-blue-700">{{ $resumen['todas'] }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('notificaciones.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="pendientes" @selected($estado === 'pendientes')>Pendientes</option>
                    <option value="leidas" @selected($estado === 'leidas')>Leídas</option>
                    <option value="todas" @selected($estado === 'todas')>Todas</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Severidad</label>
                <select name="severidad" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <option value="critica" @selected($severidad === 'critica')>Crítica</option>
                    <option value="alta" @selected($severidad === 'alta')>Alta</option>
                    <option value="media" @selected($severidad === 'media')>Media</option>
                    <option value="baja" @selected($severidad === 'baja')>Baja</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="space-y-3">
        @forelse($notificaciones as $notificacion)
            <div class="rounded-2xl border {{ $notificacion->leida_at ? 'border-slate-200 bg-white' : 'border-blue-200 bg-blue-50/40' }} p-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $notificacion->clasesSeveridad() }}">
                                {{ $notificacion->etiquetaSeveridad() }}
                            </span>
                            @if($notificacion->modulo)
                                <span class="text-xs font-medium text-slate-500">{{ $notificacion->modulo }}</span>
                            @endif
                            @if(! $notificacion->leida_at)
                                <span class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-bold text-white">Nueva</span>
                            @endif
                        </div>

                        <h3 class="mt-2 text-base font-semibold text-slate-800">{{ $notificacion->titulo }}</h3>
                        @if($notificacion->mensaje)
                            <p class="mt-1 text-sm text-slate-600">{{ $notificacion->mensaje }}</p>
                        @endif
                        <p class="mt-2 text-xs text-slate-400">
                            Generada: {{ $notificacion->created_at?->format('d/m/Y H:i') }}
                            @if($notificacion->leida_at)
                                · Leída: {{ $notificacion->leida_at->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        @if($notificacion->url)
                            <a href="{{ $notificacion->url }}" class="inline-flex items-center gap-1 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                Abrir
                                <i class='bx bx-link-external'></i>
                            </a>
                        @endif

                        @if($notificacion->leida_at)
                            <form action="{{ route('notificaciones.no-leida', $notificacion) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    Marcar no leída
                                </button>
                            </form>
                        @else
                            <form action="{{ route('notificaciones.leer', $notificacion) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-xl border border-green-300 px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-50">
                                    Marcar leída
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('notificaciones.archivar', $notificacion) }}" method="POST" onsubmit="return confirm('¿Archivar esta notificación? Ya no aparecerá en tu lista normal.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                Archivar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                    <i class='bx bx-bell-off text-3xl'></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-700">No hay notificaciones para mostrar</h3>
                <p class="mt-1 text-sm text-slate-500">Cuando el sistema detecte avisos operativos, aparecerán aquí.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $notificaciones->links() }}
    </div>
</div>
@endsection
