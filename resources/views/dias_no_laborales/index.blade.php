@extends('layouts.app')

@section('title', 'Días no laborales')

@section('content')
@php $puedeGestionarCalendarios = usuarioTienePermiso('calendarios.gestionar'); @endphp
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Días no laborales</h1>
        <p class="text-sm text-slate-500 mt-1">Carga anual de días oficiales y registro de fechas institucionales internas del IDEJ. Si una fecha afecta sesiones ya programadas, el sistema notifica a las áreas involucradas.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 p-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    @if($puedeGestionarCalendarios)
        <form method="POST" action="{{ route('dias_no_laborales.cargar-oficiales') }}" class="bg-amber-50 rounded-2xl shadow border border-amber-200 p-5 space-y-3">
            @csrf
            <h2 class="font-bold text-amber-900">Carga anual oficial</h2>
            <p class="text-sm text-amber-800">Carga los días oficiales base del año seleccionado sin duplicar fechas existentes.</p>
            <input type="number" name="anio" min="2020" max="2100" value="{{ $anio ?? now()->year }}" class="w-full rounded-xl border-amber-300">
            <button class="rounded-xl bg-amber-600 text-white px-4 py-2 hover:bg-amber-700">Cargar días oficiales</button>
        </form>
    @endif

        <form method="GET" action="{{ route('dias_no_laborales.index') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-5 space-y-3">
            <h2 class="font-bold text-slate-800">Filtrar año</h2>
            <input type="number" name="anio" min="2020" max="2100" value="{{ $anio ?? now()->year }}" class="w-full rounded-xl border-slate-300">
            <button class="rounded-xl bg-slate-800 text-white px-4 py-2 hover:bg-slate-900">Ver año</button>
        </form>

        <div class="bg-blue-50 rounded-2xl shadow border border-blue-100 p-5 text-sm text-blue-900">
            <h2 class="font-bold">Criterio operativo</h2>
            <p class="mt-2">Los días de tipo <strong>Ley</strong> son carga anual base. IDEJ debe agregar manualmente fechas <strong>Institucionales</strong> o <strong>Internas</strong> cuando haya suspensiones propias.</p>
        </div>
    </div>

    @if($puedeGestionarCalendarios)
    <form method="POST" action="{{ route('dias_no_laborales.store') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full rounded-xl border-slate-300">
            @error('fecha') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full rounded-xl border-slate-300" placeholder="Ej. Suspensión institucional">
            @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
            <select name="tipo" class="w-full rounded-xl border-slate-300">
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo', 'Institucional') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700 pb-2">
            <input type="checkbox" name="activo" value="1" checked class="rounded border-slate-300">
            Activo
        </label>
        <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Agregar</button>
    </form>
    @endif

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
                                @if($puedeGestionarCalendarios)
                                <form method="POST" action="{{ route('dias_no_laborales.destroy', $dia) }}" onsubmit="return confirm('¿Eliminar este día no laboral?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 font-semibold hover:underline">Eliminar</button>
                                </form>
                                @else
                                    <span class="text-xs text-slate-500">Solo consulta</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay días no laborales registrados para este año.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $dias->links() }}
</div>
@endsection
