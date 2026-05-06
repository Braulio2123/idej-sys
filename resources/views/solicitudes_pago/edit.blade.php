@extends('layouts.app')

@section('title', 'Editar Solicitud de Pago')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white shadow-lg rounded-2xl p-6 border border-slate-200">

        <!-- Título -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                <i class='bx bx-edit text-3xl text-amber-600'></i>
                Editar Solicitud de Pago
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Actualiza los datos de la solicitud seleccionada.
            </p>
        </div>

        <!-- ERRORES -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORM -->
        <form method="POST"
              action="{{ route('solicitudes_pago.update', $solicitud) }}"
              class="grid grid-cols-1 md:grid-cols-2 gap-6">

            @csrf
            @method('PUT')

            <!-- Docente (NO editable) -->
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700 mb-1 block">Docente</label>

                <input type="text"
                    disabled
                    value="{{ $solicitud->docente->nombre_completo }}"
                    class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-2 text-slate-600 cursor-not-allowed">

                <input type="hidden" name="docente_id" value="{{ $solicitud->docente_id }}">
            </div>

            <!-- Nivel -->
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Nivel</label>
                <select name="nivel"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="Licenciatura" {{ $solicitud->nivel === 'Licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                    <option value="Maestría" {{ $solicitud->nivel === 'Maestría' ? 'selected' : '' }}>Maestría</option>
                    <option value="Doctorado" {{ $solicitud->nivel === 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                </select>
            </div>

            <!-- Monto -->
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Monto</label>
                <input type="number"
                    step="0.01"
                    name="monto"
                    value="{{ $solicitud->monto }}"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-700 mb-1 block">Observaciones</label>
                <textarea name="observaciones"
                          rows="4"
                          class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2
                                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $solicitud->observaciones }}</textarea>
            </div>

            <!-- Botones -->
            <div class="md:col-span-2 flex justify-between mt-4">

                <!-- Regresar -->
                <a href="{{ route('solicitudes_pago.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium transition">
                    Cancelar
                </a>

                <!-- Guardar -->
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700
                               text-white font-semibold shadow-md transition">
                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
