@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-6">

    <!-- CARD PRINCIPAL -->
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        <!-- TÍTULO -->
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-100 text-blue-600">
                <i class='bx bx-book-add text-3xl'></i>
            </div>

            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Crear Programa Académico</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Registra un nuevo plan académico disponible en la institución
                </p>
            </div>
        </div>

        <!-- FORMULARIO -->
        <form action="{{ route('programas.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre del programa
                </label>

                <input type="text"
                       name="nombre"
                       value="{{ old('nombre') }}"
                       required
                       placeholder="Ej. Licenciatura en Derecho"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">

                @error('nombre')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nivel --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nivel académico
                </label>

                <input type="text"
                       name="nivel"
                       value="{{ old('nivel') }}"
                       placeholder="Ej. Licenciatura, Maestría, Doctorado"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">

                @error('nivel')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- BOTONES -->
            <div class="flex justify-end gap-2 pt-4">

                <a href="{{ route('programas.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700
                          font-medium hover:bg-slate-300 transition">
                    <i class='bx bx-x text-lg'></i>
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white
                               font-medium shadow-md hover:bg-blue-700 transition">
                    <i class='bx bx-save text-lg'></i>
                    Guardar Programa
                </button>

            </div>
        </form>

    </div>

</div>

@endsection
