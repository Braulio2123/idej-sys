@extends('layouts.app')

@section('title', 'Educación Continua')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Educación Continua</h1>
            <p class="text-slate-500">Cursos especiales, MASC, oratoria, masterclass, talleres y eventos medidos por horas.</p>
        </div>
        <a href="{{ route('educacion_continua.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">+ Nuevo curso</a>
    </div>

    @if(session('success')) <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div> @endif

    <form method="GET" class="bg-white rounded-2xl shadow border border-slate-100 p-5 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="buscar" value="{{ request('buscar') }}" class="rounded-xl border-slate-300" placeholder="Buscar curso, tipo o modalidad">
        <select name="tipo" class="rounded-xl border-slate-300">
            <option value="">Todos los tipos</option>
            @foreach($tipos as $tipo)
                <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
        <select name="estatus" class="rounded-xl border-slate-300">
            <option value="">Todos los estatus</option>
            @foreach($estatuses as $estatus)
                <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-slate-900 text-white font-semibold">Filtrar</button>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="text-left px-4 py-3">Curso</th>
                    <th class="text-left px-4 py-3">Tipo</th>
                    <th class="text-left px-4 py-3">Horas</th>
                    <th class="text-left px-4 py-3">Sesiones</th>
                    <th class="text-left px-4 py-3">Inscritos</th>
                    <th class="text-left px-4 py-3">Estatus</th>
                    <th class="text-right px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cursos as $curso)
                    <tr class="border-b last:border-b-0 hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-800">{{ $curso->nombre }}</p>
                            <p class="text-xs text-slate-500">{{ optional($curso->fecha_inicio)->format('d/m/Y') ?? 'Sin inicio' }} - {{ optional($curso->fecha_fin)->format('d/m/Y') ?? 'Sin fin' }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $curso->tipo }} · {{ $curso->modalidad }}</td>
                        <td class="px-4 py-3">{{ number_format($curso->horas_totales, 2) }} h</td>
                        <td class="px-4 py-3">{{ $curso->sesiones_count }}</td>
                        <td class="px-4 py-3">{{ $curso->inscritos_count }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">{{ $curso->estatus }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('educacion_continua.show', $curso) }}" class="text-indigo-700 font-semibold hover:underline">Ver</a>
                            <span class="text-slate-300 mx-1">|</span>
                            <a href="{{ route('educacion_continua.edit', $curso) }}" class="text-amber-700 font-semibold hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay cursos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $cursos->links() }}
</div>
@endsection
