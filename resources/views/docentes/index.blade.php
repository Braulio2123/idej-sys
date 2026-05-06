@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto mt-6">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- Título + botón --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-chalkboard text-3xl text-blue-600'></i>
                    Gestión de Docentes
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Administración general de docentes registrados en el sistema
                </p>
            </div>

            <a href="{{ route('docentes.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                      text-white px-5 py-2.5 rounded-xl font-medium shadow-md transition">
                <i class='bx bx-user-plus text-xl'></i>
                Registrar Docente
            </a>
        </div>

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
                {{ session('success') }}
            </div>
        @endif


        {{-- FILTROS --}}
        <form method="GET"
              action="{{ route('docentes.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

            {{-- Buscar --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Buscar por nombre o correo</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Ejemplo: Juan Pérez"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Estatus --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estatus</label>
                <select name="estatus"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Todos --</option>
                    <option value="Pendiente de Datos" {{ request('estatus') == 'Pendiente de Datos' ? 'selected' : '' }}>
                        Pendiente de Datos
                    </option>
                    <option value="Activo" {{ request('estatus') == 'Activo' ? 'selected' : '' }}>
                        Activo
                    </option>
                </select>
            </div>

            {{-- Botón --}}
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium shadow transition">
                    Filtrar
                </button>
            </div>
        </form>


        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left">Nombre</th>
                        <th class="py-3 px-4 text-left">Especialidad</th>
                        <th class="py-3 px-4 text-left">Correo</th>
                        <th class="py-3 px-4 text-left">Teléfono</th>
                        <th class="py-3 px-4 text-left">Estatus</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($docentes as $docente)
                        <tr class="hover:bg-slate-50/70 transition">

                            {{-- Nombre --}}
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $docente->nombre_completo }}
                            </td>

                            {{-- Especialidad --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $docente->area_especialidad }}
                            </td>

                            {{-- Email --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $docente->email ?? '—' }}
                            </td>

                            {{-- Teléfono --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $docente->telefono ?? '—' }}
                            </td>

                            {{-- Estatus --}}
                            <td class="py-3 px-4">
                                @if($docente->estatus === 'Pendiente de Datos')
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Pendiente de Datos
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Activo
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-3">

                                    {{-- Ver --}}
                                    <a href="{{ route('docentes.show', $docente) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium transition">
                                        Ver
                                    </a>

                                    {{-- Editar --}}
                                    <a href="{{ route('docentes.edit', $docente) }}"
                                       class="text-amber-600 hover:text-amber-800 font-medium transition">
                                        Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ route('docentes.destroy', $docente) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este docente?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800 font-medium transition">
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-slate-500">
                                No hay docentes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-6">
            {{ $docentes->links() }}
        </div>

    </div>
</div>

@endsection
