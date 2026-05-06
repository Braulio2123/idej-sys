@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-4">Editar Ciclo Escolar</h1>

    <form action="{{ route('ciclos_escolares.update', $ciclo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="font-semibold">Nombre</label>
            <input type="text" name="nombre" class="w-full border p-2 rounded"
                   value="{{ $ciclo->nombre }}" required>
        </div>

        <div class="mb-3">
            <label class="font-semibold">Tipo de periodo</label>
            <select name="tipo_periodo" class="w-full border p-2 rounded">
                <option value="Cuatrimestral" @selected($ciclo->tipo_periodo=='Cuatrimestral')>Cuatrimestral</option>
                <option value="Semestral" @selected($ciclo->tipo_periodo=='Semestral')>Semestral</option>
                <option value="Anual" @selected($ciclo->tipo_periodo=='Anual')>Anual</option>
                <option value="Otro" @selected($ciclo->tipo_periodo=='Otro')>Otro</option>
            </select>
        </div>

        <h2 class="font-bold mt-4 mb-2">Inscripciones</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Inicio</label>
                <input type="date" name="fecha_inicio_inscripcion"
                       class="w-full border p-2 rounded"
                       value="{{ $ciclo->fecha_inicio_inscripcion }}" required>
            </div>
            <div>
                <label>Fin</label>
                <input type="date" name="fecha_fin_inscripcion"
                       class="w-full border p-2 rounded"
                       value="{{ $ciclo->fecha_fin_inscripcion }}" required>
            </div>
        </div>

        <h2 class="font-bold mt-4 mb-2">Clases</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Inicio</label>
                <input type="date" name="fecha_inicio_clases"
                       class="w-full border p-2 rounded"
                       value="{{ $ciclo->fecha_inicio_clases }}" required>
            </div>
            <div>
                <label>Fin</label>
                <input type="date" name="fecha_fin_clases"
                       class="w-full border p-2 rounded"
                       value="{{ $ciclo->fecha_fin_clases }}" required>
            </div>
        </div>

        <div class="mt-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="activo" value="1"
                       @checked($ciclo->activo)
                       class="mr-2">
                Marcar como ciclo activo
            </label>
        </div>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded mt-4">
            Guardar cambios
        </button>
    </form>
</div>
@endsection
