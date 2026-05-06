@extends('layouts.app')

@section('content')

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
                    Programas Académicos
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Listado general de programas registrados</p>
            </div>
        </div>

        <a href="{{ route('programas.create') }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl shadow-md font-medium transition">
            <i class='bx bx-plus text-xl'></i>
            Nuevo Programa
        </a>
    </div>

    {{-- Si no hay programas --}}
    @if($programas->isEmpty())
        <div class="bg-white border border-slate-200 shadow rounded-xl p-6 text-center text-slate-500">
            No hay programas registrados.
        </div>

    @else
        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-2xl shadow-md border border-slate-200">
            <table class="min-w-full text-sm text-left text-slate-700">
                <thead class="bg-indigo-600 text-white uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Nombre del Programa</th>
                        <th class="px-5 py-3">Nivel</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($programas as $programa)
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-5 py-3 text-slate-600">{{ $programa->id }}</td>

                            <td class="px-5 py-3 font-medium text-slate-800">
                                {{ $programa->nombre }}
                            </td>

                            <td class="px-5 py-3 text-slate-600">
                                {{ $programa->nivel ?? '—' }}
                            </td>

                            <td class="px-5 py-3 text-center">
                                <div class="flex justify-center items-center gap-3">

                                    <!-- Editar -->
                                    <a href="{{ route('programas.edit', $programa) }}"
                                       class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 hover:underline text-sm font-medium">
                                        <i class='bx bx-edit text-lg'></i>
                                        Editar
                                    </a>

                                    <!-- Eliminar -->
                                    <form action="{{ route('programas.destroy', $programa) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este programa?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 hover:underline text-sm font-medium">
                                            <i class='bx bx-trash text-lg'></i>
                                            Eliminar
                                        </button>
                                    </form>

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
