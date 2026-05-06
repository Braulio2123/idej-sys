@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-md">

    {{-- TITULO --}}
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center space-x-2">
        <span>🧑‍🎓 Registrar Alumno</span>
    </h1>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 border border-red-300">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li class="mt-1">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumnos.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- MATRÍCULA --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Matrícula</label>
            <input type="text" name="matricula"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('matricula') }}" required>
        </div>

        {{-- NOMBRE --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nombre Completo</label>
            <input type="text" name="nombre_completo"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('nombre_completo') }}" required>
        </div>

        {{-- CORREO --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Correo</label>
            <input type="email" name="correo"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('correo') }}" required>
        </div>

        {{-- TELÉFONO --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Teléfono</label>
            <input type="text" name="telefono"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                value="{{ old('telefono') }}">
        </div>

        {{-- ESTATUS FINANCIERO --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Estatus Financiero</label>
            <select name="estatus_financiero"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                required>
                <option value="">Seleccione...</option>
                <option value="Al Corriente">Al Corriente</option>
                <option value="Con Adeudo">Con Adeudo</option>
                <option value="En Convenio">En Convenio</option>
                <option value="Becado">Becado</option>
            </select>
        </div>

        {{-- ESTATUS ACADÉMICO --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Estatus Académico</label>
            <select name="estatus_academico"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                required>
                <option value="">Seleccione...</option>
                <option value="Activo">Activo</option>
                <option value="Baja Temporal">Baja Temporal</option>
                <option value="Suspendido">Suspendido</option>
            </select>
        </div>

        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg p-4 text-sm">
            Las becas ya no se capturan desde el alta del alumno. Primero registra al alumno y después usa su expediente financiero en <strong>Becas</strong>, con autorización y vigencia.
        </div>

        {{-- GRUPO --}}
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Grupo Académico (opcional)</label>
            <select name="grupo_id"
                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sin grupo asignado</option>

                @foreach($grupos as $grupo)
                    <option value="{{ $grupo->id }}" @selected(old('grupo_id') == $grupo->id)>
                        {{ $grupo->nombre }} —
                        {{ $grupo->programa->nombre ?? 'Sin programa' }} —
                        ({{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BOTÓN --}}
        <div class="pt-4">
            <button type="submit"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700
                       transition font-medium shadow">
                Guardar Alumno
            </button>
        </div>

    </form>
</div>
@endsection
