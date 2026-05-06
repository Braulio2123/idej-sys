@extends('layouts.app')

@section('content')

<div class="max-w-xl mx-auto px-4 py-6">

    <!-- CARD PRINCIPAL -->
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        <!-- TÍTULO -->
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-100 text-blue-600">
                <i class='bx bx-coin text-3xl'></i>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-slate-800">Crear Nuevo Concepto</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Registra un concepto de pago que podrá ser usado en cargos y facturación interna
                </p>
            </div>
        </div>

        <!-- FORMULARIO -->
        <form action="{{ route('conceptos.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nombre del Concepto --}}
            <div>
                <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre del Concepto
                </label>

                <input type="text"
                       name="nombre"
                       id="nombre"
                       value="{{ old('nombre') }}"
                       placeholder="Ej. Mensualidad, Inscripción, Reinscripción"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">

                @error('nombre')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Monto Base --}}
            <div>
                <label for="monto_base" class="block text-sm font-medium text-slate-700 mb-1">
                    Monto Base
                </label>

                <input type="number"
                       name="monto_base"
                       id="monto_base"
                       step="0.01"
                       value="{{ old('monto_base') }}"
                       placeholder="Ej. 1500.00"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">

                @error('monto_base')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Becable --}}
            <div class="flex items-center gap-2 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox"
                           name="es_becable"
                           value="1"
                           {{ old('es_becable') ? 'checked' : '' }}
                           class="h-5 w-5 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700 font-medium">¿Es becable?</span>
                </label>
            </div>

            {{-- BOTONES --}}
            <div class="flex justify-end gap-2 pt-4">

                <a href="{{ route('conceptos.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700
                          font-medium hover:bg-slate-300 transition">
                    <i class='bx bx-x text-lg'></i>
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white
                               font-medium shadow-md hover:bg-blue-700 transition">
                    <i class='bx bx-save text-lg'></i>
                    Guardar Concepto
                </button>

            </div>
        </form>

    </div>
</div>

@endsection
