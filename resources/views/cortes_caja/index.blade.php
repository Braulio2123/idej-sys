@extends('layouts.app')

@section('title', 'Cortes de Caja')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Cortes de Caja</h2>
            <p class="text-sm text-slate-500">Control diario de ingresos por usuario, método de pago y cierre operativo.</p>
        </div>

        @if($cajaAbierta)
            <a href="{{ route('cortes-caja.show', $cajaAbierta) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700">
                Ver mi caja abierta #{{ $cajaAbierta->id }}
            </a>
        @else
            <a href="{{ route('cortes-caja.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                Abrir caja
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">{{ session('info') }}</div>
    @endif

    <form method="GET" action="{{ route('cortes-caja.index') }}" class="bg-slate-50 border border-slate-200 rounded-xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Estatus</label>
            <select name="estatus" class="w-full rounded-lg border-slate-300 text-sm">
                <option value="">Todos</option>
                <option value="Abierta" @selected(request('estatus') === 'Abierta')>Abierta</option>
                <option value="Cerrada" @selected(request('estatus') === 'Cerrada')>Cerrada</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Usuario</label>
            <select name="usuario_id" class="w-full rounded-lg border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected((string) request('usuario_id') === (string) $usuario->id)>
                        {{ $usuario->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full rounded-lg border-slate-300 text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button class="w-full px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900">Filtrar</button>
            <a href="{{ route('cortes-caja.index') }}" class="px-4 py-2 bg-white border rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto border border-slate-200 rounded-xl">
        <table class="w-full text-sm bg-white">
            <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">Corte</th>
                    <th class="p-3 text-left">Usuario</th>
                    <th class="p-3 text-center">Apertura</th>
                    <th class="p-3 text-center">Cierre</th>
                    <th class="p-3 text-right">Pagos</th>
                    <th class="p-3 text-right">Total sistema</th>
                    <th class="p-3 text-right">Diferencia</th>
                    <th class="p-3 text-center">Estatus</th>
                    <th class="p-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cortes as $corte)
                    <tr class="border-t hover:bg-slate-50">
                        <td class="p-3 font-semibold">#{{ $corte->id }}</td>
                        <td class="p-3">{{ $corte->usuario->nombre ?? '—' }}</td>
                        <td class="p-3 text-center">{{ optional($corte->fecha_apertura)->format('d/m/Y H:i') }}</td>
                        <td class="p-3 text-center">{{ optional($corte->fecha_cierre)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="p-3 text-right">{{ $corte->pagos_count }}</td>
                        <td class="p-3 text-right font-semibold">${{ number_format($corte->total_sistema, 2) }}</td>
                        <td class="p-3 text-right {{ ($corte->diferencia_total ?? 0) == 0 ? 'text-slate-600' : 'text-red-600 font-semibold' }}">
                            {{ $corte->diferencia_total !== null ? '$'.number_format($corte->diferencia_total, 2) : '—' }}
                        </td>
                        <td class="p-3 text-center">
                            @if($corte->estatus === 'Abierta')
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Abierta</span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-700 text-xs font-semibold">Cerrada</span>
                            @endif
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <a href="{{ route('cortes-caja.show', $corte) }}" class="text-indigo-700 font-semibold hover:underline">Ver</a>
                            @if($corte->estatus === 'Abierta')
                                <a href="{{ route('cortes-caja.cierre', $corte) }}" class="text-red-700 font-semibold hover:underline">Cerrar</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-500">No hay cortes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $cortes->links() }}
</div>
@endsection
