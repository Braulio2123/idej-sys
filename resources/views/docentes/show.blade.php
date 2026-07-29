@extends('layouts.app')

@section('content')
@php
    use App\Models\Rol;
    $usuarioActual = Auth::user();
    $puedeGestionarDocentes = usuarioTienePermiso('docentes.gestionar');
    $puedeGestionarDatosFinancieros = Auth::user()?->tieneRol(\App\Models\Rol::ADMIN, \App\Models\Rol::CADMIN) ?? false;
    $puedeVerDatosFiscales = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN) ?? false;
@endphp

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

                @if($puedeGestionarDocentes)
                    <a href="{{ route('docentes.edit', $docente) }}" class="inline-flex items-center gap-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition shadow-sm"><i class='bx bx-edit-alt text-lg'></i>Editar datos académicos</a>
                @endif
                @if($puedeGestionarDatosFinancieros)
                    <a href="{{ route('docentes.financieros.edit', $docente) }}" class="inline-flex items-center gap-2 text-sm bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl transition shadow-sm"><i class='bx bx-money text-lg'></i>Datos financieros</a>
                @endif

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

            @if($puedeVerDatosFiscales)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm">
                    <p class="text-xs text-amber-700">RFC</p>
                    <p class="text-lg text-slate-700">{{ $docente->rfc ?? '—' }}</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm">
                    <p class="text-xs text-amber-700">Número de cuenta</p>
                    <p class="text-lg text-slate-700">{{ $docente->numero_cuenta ?? '—' }}</p>
                    <p class="text-xs text-amber-700 mt-2">Banco</p>
                    <p class="text-lg text-slate-700">{{ $docente->banco ?? '—' }}</p>
                </div>
            @endif

            <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documentos privados</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if(Auth::user()?->tieneRol(\App\Models\Rol::ADMIN, \App\Models\Rol::ACADEMICA) && $docente->curriculum_path)
                        <a href="{{ route('docentes.documentos.download', [$docente, 'curriculum']) }}" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Curriculum</a>
                    @endif
                    @if(Auth::user()?->tieneRol(\App\Models\Rol::ADMIN, \App\Models\Rol::ACADEMICA) && $docente->titulo_cedula_path)
                        <a href="{{ route('docentes.documentos.download', [$docente, 'titulo_cedula']) }}" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Título y cédula</a>
                    @endif
                    @if($puedeVerDatosFiscales && $docente->constancia_fiscal_path)
                        <a href="{{ route('docentes.documentos.download', [$docente, 'constancia_fiscal']) }}" class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">Constancia fiscal</a>
                    @endif
                    @if(! $docente->curriculum_path && ! $docente->titulo_cedula_path && (! $puedeVerDatosFiscales || ! $docente->constancia_fiscal_path))
                        <span class="text-sm text-slate-500">No hay documentos disponibles para tu rol.</span>
                    @endif
                </div>
            </div>

            {{-- Estatus --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs text-slate-500">Estatus</p>

                @if($docente->estatus === \App\Models\Docente::ESTATUS_PENDIENTE)
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-xs font-semibold">
                        Pendiente de Datos
                    </span>
                @elseif($docente->estatus === \App\Models\Docente::ESTATUS_INACTIVO)
                    <span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-lg text-xs font-semibold">
                        Inactivo
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
