@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📅 Ciclos Escolares</h1>

        <a href="{{ route('ciclos_escolares.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
            + Nuevo Ciclo
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($ciclos->isEmpty())
        <p class="text-gray-600">No hay ciclos registrados.</p>
    @else
        <table class="min-w-full text-sm border">
            <thead class="bg-indigo-600 text-white text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Periodo</th>
                    <th class="px-4 py-3">Inscripción</th>
                    <th class="px-4 py-3">Clases</th>
                    <th class="px-4 py-3">Activo</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ciclos as $ciclo)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">{{ $ciclo->nombre }}</td>
                    <td class="px-4 py-3">{{ $ciclo->tipo_periodo }}</td>
                    <td class="px-4 py-3">
                        {{ $ciclo->fecha_inicio_inscripcion }} a {{ $ciclo->fecha_fin_inscripcion }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $ciclo->fecha_inicio_clases }} a {{ $ciclo->fecha_fin_clases }}
                    </td>
                    <td class="px-4 py-3">
                        @if($ciclo->activo)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Activo</span>
                        @else
                            <span class="px-2 py-1 bg-gray-200 text-gray-600 rounded text-xs">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('ciclos_escolares.edit', $ciclo) }}" class="text-blue-600 hover:underline">Editar</a>

                        <form action="{{ route('ciclos_escolares.destroy', $ciclo) }}" method="POST"
                              class="inline"
                              onsubmit="return confirm('¿Eliminar ciclo escolar?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline ml-2">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $ciclos->links() }}
        </div>
    @endif

</div>
@endsection
