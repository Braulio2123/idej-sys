@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📘 Grupos Académicos</h1>

        <a href="{{ route('grupos.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
           + Nuevo Grupo
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($grupos->isEmpty())
        <p class="text-gray-600">No hay grupos registrados.</p>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
            <thead class="bg-indigo-600 text-white text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Ciclo</th>
                    <th class="px-4 py-3">Programa</th>
                    <th class="px-4 py-3">Nivel</th>
                    <th class="px-4 py-3">Turno</th>
                    <th class="px-4 py-3">Aula</th>
                    <th class="px-4 py-3">Cupo</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach($grupos as $grupo)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">{{ $grupo->nombre }}</td>
                    <td class="px-4 py-3">{{ $grupo->cicloEscolar->nombre }}</td>
                    <td class="px-4 py-3">{{ $grupo->programa->nombre }}</td>
                    <td class="px-4 py-3">{{ $grupo->semestre_o_cuatrimestre }}</td>
                    <td class="px-4 py-3">{{ $grupo->turno }}</td>
                    <td class="px-4 py-3">{{ $grupo->aula ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $grupo->cupo_maximo }}</td>

                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('grupos.show', $grupo) }}" class="text-indigo-600 hover:underline">Ver</a>
                        <a href="{{ route('grupos.edit', $grupo) }}" class="text-blue-600 hover:underline ml-2">Editar</a>

                        <form action="{{ route('grupos.destroy', $grupo) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Eliminar este grupo?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline ml-2">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <div class="mt-4">
        {{ $grupos->links() }}
    </div>
    @endif

</div>
@endsection
