@extends('layouts.app')

@section('title', 'Educación Programática')

@section('content')
@php $puedeGestionarCatalogos = usuarioTienePermiso('catalogos_academicos.gestionar'); @endphp

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 p-4 bg-green-100 text-green-800 border border-green-300 rounded-xl shadow">
            <i class='bx bx-check-circle text-2xl'></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow">
                <i class='bx bx-book text-3xl'></i>
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 leading-tight">
                    Educación Programática
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Oferta de Educación Programática disponible para grupos, prospectos, requisitos y reportes.</p>
            </div>
        </div>

        @if($puedeGestionarCatalogos)
        <a href="{{ route('programas.create') }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl shadow-md font-medium transition">
            <i class='bx bx-plus text-xl'></i>
            Nueva Educación Programática
        </a>
        @endif
    </div>

    {{-- Si no hay programas --}}
    @if($programas->isEmpty())
        <div class="bg-white border border-slate-200 shadow rounded-xl p-6 text-center text-slate-500">
            No hay Educación Programática registrada.
        </div>

    @else
        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-2xl shadow-md border border-slate-200">
            <table class="min-w-full text-sm text-left text-slate-700">
                <thead class="bg-indigo-600 text-white uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-5 py-3">Clave</th>
                        <th class="px-5 py-3">Nombre de Educación Programática</th>
                        <th class="px-5 py-3">Nivel</th>
                        <th class="px-5 py-3">Modalidad</th>
                        <th class="px-5 py-3">Duración</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($programas as $programa)
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-5 py-3 text-slate-600">{{ $programa->clave ?: '—' }}</td>

                            <td class="px-5 py-3 font-medium text-slate-800">
                                {{ $programa->nombre }}
                            </td>

                            <td class="px-5 py-3 text-slate-600">
                                {{ $programa->nivel ?? '—' }}
                            </td>

                            <td class="px-5 py-3 text-slate-600">{{ $programa->modalidad ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $programa->duracion_periodos ? $programa->duracion_periodos.' semestre(s)' : '—' }}</td>
                            <td class="px-5 py-3">
                                @if($programa->activo ?? true)
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Activo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-600">Inactivo</span>
                                @endif
                            </td>

                            <td class="px-5 py-3 text-center">
                                <div class="flex justify-center items-center gap-3">

                                    <!-- Editar -->
                                    @if($puedeGestionarCatalogos)
                                    <a href="{{ route('programas.edit', $programa) }}"
                                       class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 hover:underline text-sm font-medium">
                                        <i class='bx bx-edit text-lg'></i>
                                        Editar
                                    </a>

                                    <!-- Eliminar -->
                                    <form action="{{ route('programas.destroy', $programa) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar esta Educación Programática? Si ya tiene historial, se inactivará para conservar evidencia.')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 hover:underline text-sm font-medium">
                                            <i class='bx bx-trash text-lg'></i>
                                            Eliminar
                                        </button>
                                    </form>
                                    @else
                                        <span class="text-xs text-slate-500">Solo consulta</span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-5">
            {{ $programas->links() }}
        </div>
    @endif

</div>

@endsection
