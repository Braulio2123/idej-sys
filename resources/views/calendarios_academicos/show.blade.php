@extends('layouts.app')

@section('title', 'Detalle de calendario académico')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">{{ $calendario->nombre }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $calendario->grupo->nombre ?? 'Sin grupo' }} · {{ $calendario->grupo->programa->nombre ?? 'Sin programa' }} · {{ $calendario->periodo ?? 'Sin periodo' }}
                </p>
                @if($calendario->observaciones)
                    <p class="mt-3 text-sm text-slate-600">{{ $calendario->observaciones }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('calendarios_academicos.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Regresar</a>
                <a href="{{ route('calendarios_academicos.edit', $calendario) }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">Editar</a>
                <a href="{{ route('calendarios_academicos.materias.create', $calendario) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700">+ Agregar materia y fechas</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-6">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Estatus</p><p class="font-semibold text-slate-800">{{ $calendario->estatus }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Modalidad</p><p class="font-semibold text-slate-800">{{ $calendario->modalidad }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Tipo IDEJ</p><p class="font-semibold text-slate-800">{{ $calendario->tipo_calendario ?? 'Personalizado' }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Rango</p><p class="font-semibold text-slate-800">{{ $calendario->rango_fechas }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Materias</p><p class="font-semibold text-slate-800">{{ $calendario->materiasCalendario->count() }}</p></div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200"><p class="text-xs text-slate-500">Sesiones</p><p class="font-semibold text-slate-800">{{ $sesiones->count() }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-xl font-semibold text-slate-800">Materias del calendario</h2>
            <p class="text-sm text-slate-500">Cada materia tiene sus fechas fijas asignadas antes de iniciar el periodo.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Orden</th>
                        <th class="px-4 py-3 text-left">Materia</th>
                        <th class="px-4 py-3 text-left">Docente</th>
                        <th class="px-4 py-3 text-center">Sesiones</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($calendario->materiasCalendario as $materiaCalendario)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $materiaCalendario->orden }}</td>
                            <td class="px-4 py-3 text-slate-800 font-semibold">{{ $materiaCalendario->nombre_materia }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $materiaCalendario->nombre_docente }}</td>
                            <td class="px-4 py-3 text-center">{{ $materiaCalendario->sesiones->count() }}</td>
                            <td class="px-4 py-3 text-center">{{ $materiaCalendario->estatus }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('calendarios_academicos.materias.edit', [$calendario, $materiaCalendario]) }}" class="text-blue-600 font-semibold hover:underline">Editar</a>
                                    <form method="POST" action="{{ route('calendarios_academicos.materias.destroy', [$calendario, $materiaCalendario]) }}" onsubmit="return confirm('¿Eliminar esta materia y sus sesiones?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 font-semibold hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Aún no hay materias asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Conteo de clases por fecha</h2>
                <p class="text-sm text-slate-500">Resumen rápido para detectar días saturados antes de terminar el calendario.</p>
            </div>
            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-full px-3 py-1">{{ $resumenPorFecha->count() }} fechas con clase</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse($resumenPorFecha as $resumen)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">{{ $resumen['fecha']->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $resumen['fecha']->locale('es')->translatedFormat('l') }}</p>
                        </div>
                        <span class="h-fit text-xs font-bold rounded-full px-2 py-1 {{ $resumen['total'] > 1 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                            {{ $resumen['total'] }} clase{{ $resumen['total'] == 1 ? '' : 's' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-2 line-clamp-2">{{ $resumen['materias']->implode(', ') }}</p>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-4 text-center text-slate-500 py-6">Aún no hay fechas asignadas.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-xl font-semibold text-slate-800">Agenda por fechas</h2>
            <p class="text-sm text-slate-500">Vista cronológica de sesiones reales del calendario.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Materia</th>
                        <th class="px-4 py-3 text-left">Docente</th>
                        <th class="px-4 py-3 text-center">Horario</th>
                        <th class="px-4 py-3 text-center">Aula</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sesiones as $sesion)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $sesion->fecha->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $sesion->dia_semana }}</p>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $sesion->calendarioMateria->nombre_materia }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $sesion->calendarioMateria->nombre_docente }}</td>
                            <td class="px-4 py-3 text-center text-slate-700">{{ $sesion->horario }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $sesion->aula ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $sesion->tipo_sesion }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-bold {{ $sesion->estatus === 'Cancelada' ? 'bg-red-100 text-red-700' : ($sesion->sesion_origen_id ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $sesion->estatus }}{{ $sesion->sesion_origen_id ? ' / Reposición' : '' }}
                                </span>
                                @if($sesion->motivo_cancelacion)
                                    <p class="text-[11px] text-red-600 mt-1 line-clamp-2">{{ $sesion->motivo_cancelacion }}</p>
                                @endif
                                @if($sesion->motivo_reprogramacion)
                                    <p class="text-[11px] text-indigo-600 mt-1 line-clamp-2">{{ $sesion->motivo_reprogramacion }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($sesion->estatus !== 'Cancelada')
                                        <a href="{{ route('calendarios_academicos.sesiones.reprogramar', [$calendario, $sesion]) }}" class="text-indigo-600 font-semibold hover:underline">Reprogramar</a>
                                        <a href="{{ route('calendarios_academicos.sesiones.cancelar', [$calendario, $sesion]) }}" class="text-red-600 font-semibold hover:underline">Cancelar</a>
                                    @else
                                        <span class="text-slate-400">Sin acciones</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No hay sesiones programadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
