@extends('layouts.app')

@section('title', 'Registrar Beca')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('alumnos.becas.index', $alumno) }}" class="text-blue-700 hover:underline font-semibold">← Volver al historial de becas</a>
        <h1 class="text-3xl font-bold text-slate-900 mt-2">Registrar beca institucional</h1>
        <p class="text-slate-600">Alumno: <strong>{{ $alumno->nombre_completo }}</strong> · Matrícula: {{ $alumno->matricula }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
            <p class="font-semibold mb-2">Revisa la información:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow p-6">
        <form method="POST" action="{{ route('alumnos.becas.store', $alumno) }}" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo de beca</label>
                <select name="tipo" required class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo', 'Institucional') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Porcentaje</label>
                <input type="number" name="porcentaje" min="1" max="100" value="{{ old('porcentaje') }}" required class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. 25">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', now()->toDateString()) }}" required class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                <p class="text-xs text-slate-500 mt-1">Déjala vacía si la beca queda indefinida.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Autorizado por</label>
                <select name="autorizado_por_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">No especificado</option>
                    @foreach($usuariosAutorizadores as $usuario)
                        <option value="{{ $usuario->id }}" @selected(old('autorizado_por_id') == $usuario->id)>{{ $usuario->nombre }} — {{ $usuario->rol->nombre ?? 'Sin rol' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo</label>
                <input type="text" name="motivo" value="{{ old('motivo') }}" required maxlength="255" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Apoyo económico autorizado por Dirección">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
                <textarea name="observaciones" rows="4" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Detalles internos, condiciones o documentación relacionada.">{{ old('observaciones') }}</textarea>
            </div>

            <div class="md:col-span-2 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
                <strong>Importante:</strong> esta beca se aplicará automáticamente a cargos becables creados durante su vigencia. No modifica cargos ya pagados ni elimina historial financiero anterior.
            </div>

            <div class="md:col-span-2 flex justify-end gap-2">
                <a href="{{ route('alumnos.becas.index', $alumno) }}" class="px-5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">Cancelar</a>
                <button class="px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-semibold shadow">Guardar beca</button>
            </div>
        </form>
    </div>
</div>
@endsection
