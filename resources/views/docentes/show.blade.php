@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto mt-6">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-id-card text-3xl text-blue-600'></i>
                    Información del Docente
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Detalle completo del docente registrado en el sistema
                </p>
            </div>

            <div class="flex gap-3">

                {{-- Botón regresar --}}
                <a href="{{ route('docentes.index') }}"
                   class="inline-flex items-center gap-2 text-sm bg-slate-100 hover:bg-slate-200
                          text-slate-700 px-4 py-2 rounded-xl transition shadow-sm">
                    <i class='bx bx-arrow-back text-lg'></i>
                    Regresar
                </a>

                {{-- Botón editar --}}
                <a href="{{ route('docentes.edit', $docente) }}"
                   class="inline-flex items-center gap-2 text-sm bg-amber-600 hover:bg-amber-700
                          text-white px-4 py-2 rounded-xl transition shadow-sm">
                    <i class='bx bx-edit-alt text-lg'></i>
                    Editar
                </a>

            </div>
        </div>


        {{-- INFORMACIÓN DEL DOCENTE --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Nombre completo --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Nombre completo</p>
                <p class="text-lg font-semibold text-slate-800">{{ $docente->nombre_completo }}</p>
            </div>

            {{-- Especialidad --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Área de especialidad</p>
                <p class="text-lg font-semibold text-slate-800">{{ $docente->area_especialidad }}</p>
            </div>

            {{-- Email --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Correo electrónico</p>
                <p class="text-lg text-slate-700">{{ $docente->email ?? '—' }}</p>
            </div>

            {{-- Teléfono --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Teléfono</p>
                <p class="text-lg text-slate-700">{{ $docente->telefono ?? '—' }}</p>
            </div>

            {{-- Domicilio --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm md:col-span-2">
                <p class="text-xs text-slate-500">Domicilio</p>
                <p class="text-lg text-slate-700">
                    {{ $docente->domicilio ?? '—' }}
                </p>
            </div>

            {{-- RFC --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">RFC</p>
                <p class="text-lg text-slate-700">{{ $docente->rfc ?? '—' }}</p>
            </div>

            {{-- Número de cuenta --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Número de cuenta</p>
                <p class="text-lg text-slate-700">{{ $docente->numero_cuenta ?? '—' }}</p>
            </div>

            {{-- Estatus --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Estatus</p>

                @if($docente->estatus === 'Pendiente de Datos')
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-xs font-semibold">
                        Pendiente de Datos
                    </span>
                @else
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold">
                        Activo
                    </span>
                @endif
            </div>

            {{-- Creado por --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Registrado por</p>
                <p class="text-lg text-slate-700">
                    {{ $docente->creador->nombre ?? '—' }}

                </p>
            </div>

        </div>


        {{-- SEPARADOR --}}
        <div class="my-8 border-t border-slate-200"></div>


        {{-- METADATOS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

            <div>
                <p class="text-xs text-slate-500">Fecha de registro</p>
                <p class="font-medium text-slate-700">
                    {{ $docente->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">Última actualización</p>
                <p class="font-medium text-slate-700">
                    {{ $docente->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>

        </div>

    </div>
</div>

    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200 mt-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Calendario académico asignado</h2>
                <p class="text-sm text-slate-500">Materias y sesiones vinculadas a este docente por fechas exactas.</p>
            </div>
            <a href="{{ route('calendarios_academicos.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">Ver calendarios</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Calendario / grupo</th>
                        <th class="px-4 py-3 text-left">Materia</th>
                        <th class="px-4 py-3 text-center">Sesiones</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($docente->calendarioMaterias as $calMateria)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $calMateria->calendario->nombre ?? 'Calendario no disponible' }}</p>
                                <p class="text-xs text-slate-500">{{ $calMateria->calendario->grupo->nombre ?? 'Grupo no disponible' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $calMateria->nombre_materia }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calMateria->sesiones->count() }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $calMateria->estatus }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($calMateria->calendario)
                                    <a href="{{ route('calendarios_academicos.show', $calMateria->calendario) }}" class="text-indigo-700 font-semibold hover:underline">Ver</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Este docente aún no tiene calendario académico asignado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


@endsection
