@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-4">Crear Grupo Académico</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('grupos.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="font-semibold">Nombre del Grupo</label>
            <input type="text" name="nombre" class="w-full border p-2 rounded"
                   value="{{ old('nombre') }}" required>
        </div>

        <div>
            <label class="font-semibold">Ciclo Escolar</label>
            <select name="ciclo_escolar_id" class="w-full border p-2 rounded" required>
                <option value="">Seleccione...</option>
                @foreach($ciclos as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="font-semibold">Programa Académico</label>
            <select name="programa_id" class="w-full border p-2 rounded" required>
                <option value="">Seleccione...</option>
                @foreach($programas as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="font-semibold">Semestre / Cuatrimestre</label>
            <input type="number" name="semestre_o_cuatrimestre" min="1" max="12"
                   class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="font-semibold">Turno</label>
            <select name="turno" class="w-full border p-2 rounded" required>
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Sabatino">Sabatino</option>
                <option value="Mixto">Mixto</option>
            </select>
        </div>

        <div>
            <label class="font-semibold">Aula</label>
            <input type="text" name="aula" class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="font-semibold">Cupo Máximo</label>
            <input type="number" name="cupo_maximo" min="1" max="60"
                   class="w-full border p-2 rounded" required>
        </div>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Guardar
        </button>
    </form>
</div>
@endsection
