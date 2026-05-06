@extends('layouts.app')

@section('title', 'Registrar Pago')

@section('content')

<div class="max-w-xl mx-auto bg-white shadow-lg rounded-xl p-6 border border-slate-200">

    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i class="bx bx-money text-green-600 text-3xl"></i>
        Registrar Pago – Solicitud #{{ $solicitud->id }}
    </h2>

    {{-- INFORMACIÓN DEL DOCENTE --}}
    <div class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
        <p class="text-gray-700 text-sm">
            <strong>Docente:</strong>
            {{ $solicitud->docente->nombre_completo }}
        </p>

        <p class="text-gray-700 text-sm mt-1">
            <strong>Monto a pagar:</strong>
            ${{ number_format($solicitud->monto, 2) }}
        </p>

        <p class="text-gray-700 text-sm mt-1">
            <strong>Fecha de solicitud:</strong>
            {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}
        </p>
    </div>

    {{-- FORMULARIO --}}
    <form method="POST" action="{{ route('solicitudes_pago.pagar', $solicitud->id) }}">
        @csrf
        @method('PUT')

        {{-- Fecha de Pago --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Fecha de Pago</label>
            <input type="date"
                   name="fecha_pago"
                   required
                   value="{{ old('fecha_pago', date('Y-m-d')) }}"
                   class="w-full p-2 border rounded-lg">
        </div>

        {{-- Referencia --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Referencia / Comprobante (opcional)</label>
            <input type="text"
                   name="referencia"
                   value="{{ old('referencia') }}"
                   class="w-full p-2 border rounded-lg">
        </div>

        {{-- Observaciones --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Observaciones</label>
            <textarea name="observaciones"
                      class="w-full p-2 border rounded-lg"
                      rows="3">{{ old('observaciones') }}</textarea>
        </div>

        {{-- BOTONES --}}
        <div class="flex justify-between mt-6">

            <a href="{{ route('solicitudes_pago.show', $solicitud->id) }}"
               class="text-gray-700 hover:underline">
                ← Cancelar
            </a>

            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 shadow">
                Registrar Pago
            </button>

        </div>

    </form>

</div>

@endsection
