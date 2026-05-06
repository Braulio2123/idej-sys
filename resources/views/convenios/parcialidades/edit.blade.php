@extends('layouts.app')

@section('content')

<div class="max-w-xl mx-auto bg-white shadow-lg rounded-lg p-6">

    <h2 class="text-xl font-bold mb-4">
        Editar Parcialidad #{{ $parcialidad->id }}
    </h2>

    <form action="{{ route('parcialidades.update', [$convenio, $parcialidad]) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Monto --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">
                Monto de la Parcialidad
            </label>
            <input type="number" step="0.01" name="monto_parcialidad"
                   class="w-full border rounded p-2"
                   value="{{ old('monto_parcialidad', $parcialidad->monto_parcialidad) }}"
                   required>
        </div>

        {{-- Fecha --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">
                Fecha de Vencimiento
            </label>
            <input type="date" name="fecha_vencimiento"
                   class="w-full border rounded p-2"
                   value="{{ old('fecha_vencimiento', $parcialidad->fecha_vencimiento->format('Y-m-d')) }}"
                   required>
        </div>

        {{-- Botones --}}
        <div class="flex justify-between mt-6">
            <a href="{{ route('parcialidades.index', $convenio) }}"
               class="text-gray-600 hover:underline">
                ← Volver
            </a>

            <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Guardar Cambios
            </button>
        </div>

    </form>

</div>

@endsection
