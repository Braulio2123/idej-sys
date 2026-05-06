@extends('layouts.app')

@section('title', 'Detalle de Bitácora')

@section('content')
<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white shadow-lg rounded-2xl p-6 border border-slate-200">
        <h1 class="text-2xl font-semibold text-slate-800 mb-1">Detalle de Bitácora</h1>
        <p class="text-xs text-slate-500 mb-6">Información técnica y funcional del evento registrado.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">Usuario</span>
                <p class="text-slate-800 font-medium">{{ $bitacora->usuario->nombre ?? 'Sistema' }}</p>
                <p class="text-xs text-slate-500">{{ $bitacora->usuario->email ?? '—' }}</p>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">Fecha</span>
                <p class="text-slate-800 font-medium">
                    {{ ($bitacora->fecha_evento ?? $bitacora->created_at)?->format('d/m/Y H:i:s') }}
                </p>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">Módulo</span>
                <p class="text-slate-800 font-medium">{{ $bitacora->modulo ?? 'Sistema' }}</p>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">Acción</span>
                <p class="text-slate-800 font-medium">{{ $bitacora->accion ?? $bitacora->tipo }}</p>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">IP</span>
                <p class="text-slate-800 font-medium">{{ $bitacora->ip_address ?? '—' }}</p>
            </div>

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="block text-xs text-slate-500">Método / URL</span>
                <p class="text-slate-800 font-medium">{{ $bitacora->metodo_http ?? '—' }}</p>
                <p class="text-xs text-slate-500 break-all">{{ $bitacora->url ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-5">
            <span class="block text-xs text-slate-500 mb-1">Descripción</span>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-slate-700 text-sm leading-relaxed">
                {!! nl2br(e($bitacora->descripcion ?? 'Sin descripción.')) !!}
            </div>
        </div>

        <div class="mt-5">
            <span class="block text-xs text-slate-500 mb-1">User Agent</span>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-slate-600 text-xs break-all">
                {{ $bitacora->user_agent ?? '—' }}
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('bitacoras.index') }}" class="inline-block bg-slate-600 text-white px-5 py-2 rounded-xl hover:bg-slate-700 transition shadow">
                ← Volver
            </a>
        </div>
    </div>
</div>
@endsection
