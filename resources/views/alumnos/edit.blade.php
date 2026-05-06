@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Alumno</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumnos.update', $alumno) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-gray-700 font-semibold">Nombre Completo</label>
            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno->nombre_completo) }}" class="w-full border rounded p-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold">Correo</label>
                <input type="email" name="correo" value="{{ old('correo', $alumno->correo) }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $alumno->telefono) }}" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold">Condición del Alumno</label>
                <select name="condicion_alumno" class="w-full border rounded p-2">
                    <option value="Normal"     @selected($alumno->condicion_alumno == 'Normal')>Normal</option>
                    <option value="Becado"     @selected($alumno->condicion_alumno == 'Becado')>Becado</option>
                    <option value="En Convenio"@selected($alumno->condicion_alumno == 'En Convenio')>En Convenio</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold">Beca vigente</label>
                <div class="w-full border rounded p-2 bg-gray-100 text-gray-700">
                    {{ $alumno->beca_porcentaje }}%
                    <a href="{{ route('alumnos.becas.index', $alumno) }}" class="ml-2 text-indigo-700 hover:underline font-semibold">Gestionar becas</a>
                </div>
                <p class="text-xs text-gray-500 mt-1">La beca se administra desde el módulo institucional de becas, no desde edición simple del alumno.</p>
            </div>
        </div>

        {{-- SELECT DE GRUPO --}}
        <div>
            <label class="block text-gray-700 font-semibold">Grupo Académico (opcional)</label>
            <select name="grupo_id" class="w-full border-gray-300 rounded p-2">
                <option value="">Sin grupo asignado</option>

                @foreach($grupos as $grupo)
                    <option value="{{ $grupo->id }}"
                        @selected(old('grupo_id', $alumno->grupo_id) == $grupo->id)>
                        {{ $grupo->nombre }} —
                        {{ $grupo->programa->nombre ?? 'Sin programa' }} —
                        ({{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Estatus Financiero</label>
            <input type="text" readonly value="{{ $alumno->estatus_financiero }}"
                   class="w-full border rounded p-2 bg-gray-100 text-gray-700">
        </div>

        <div>
            <label class="block font-semibold mb-1">Estatus Académico</label>
            <select name="estatus_academico" class="w-full border rounded p-2">
                <option value="Activo"        @selected($alumno->estatus_academico == 'Activo')>Activo</option>
                <option value="Baja Temporal" @selected($alumno->estatus_academico == 'Baja Temporal')>Baja Temporal</option>
                <option value="Suspendido"    @selected($alumno->estatus_academico == 'Suspendido')>Suspendido</option>
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Actualizar</button>
        </div>
    </form>
</div>

@endsection
