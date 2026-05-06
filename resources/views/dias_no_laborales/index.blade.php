@extends('layouts.app')

@section('title', 'Días no laborales')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Días no laborales</h1>
        <p class="text-sm text-slate-500 mt-1">Catálogo usado para advertir o bloquear fechas al armar calendarios académicos.</p>
    </div>

    <form method="POST" action="{{ route('dias_no_laborales.store') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full rounded-xl border-slate-300">
            @error('fecha') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full rounded-xl border-slate-300" placeholder="Ej. Día del Trabajo">
            @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
            <select name="tipo" class="w-full rounded-xl border-slate-300">
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo', 'Ley') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700 pb-2">
            <input type="checkbox" name="activo" value="1" checked class="rounded border-slate-300">
            Activo
        </label>
        <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Agregar</button>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Activo</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dias as $dia)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $dia->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $dia->nombre }}</td>
                            <td class="px-4 py-3 text-center">{{ $dia->tipo }}</td>
                            <td class="px-4 py-3 text-center">{{ $dia->activo ? 'Sí' : 'No' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('dias_no_laborales.destroy', $dia) }}" onsubmit="return confirm('¿Eliminar este día no laboral?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 font-semibold hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay días no laborales registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $dias->links() }}
</div>
@endsection
