@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto mt-6">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- TÍTULO --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-edit-alt text-3xl text-amber-600'></i>
                    Editar Docente
                </h1>
                <p class="text-xs text-slate-500 mt-1">Modifica los datos generales del docente</p>
            </div>

            <a href="{{ route('docentes.index') }}"
               class="inline-flex items-center gap-2 text-sm bg-slate-100 hover:bg-slate-200
                      text-slate-700 px-4 py-2 rounded-xl transition shadow-sm">
                <i class='bx bx-arrow-back text-lg'></i>
                Regresar
            </a>
        </div>


        {{-- ERRORES --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- FORMULARIO --}}
        <form method="POST" action="{{ route('docentes.update', $docente) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nombre completo --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo *</label>
                <input type="text" name="nombre_completo"
                       value="{{ old('nombre_completo', $docente->nombre_completo) }}"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            {{-- Email y Teléfono --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico</label>
                    <input type="email" name="email"
                           value="{{ old('email', $docente->email) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono"
                           value="{{ old('telefono', $docente->telefono) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

            </div>

            {{-- Domicilio --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Domicilio</label>
                <input type="text" name="domicilio"
                       value="{{ old('domicilio', $docente->domicilio) }}"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Especialidad --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Área de especialidad *</label>
                <input type="text" name="area_especialidad"
                       value="{{ old('area_especialidad', $docente->area_especialidad) }}"
                       class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            {{-- RFC y Número de Cuenta --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
                    <input type="text" name="rfc"
                           value="{{ old('rfc', $docente->rfc) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Número de cuenta</label>
                    <input type="text" name="numero_cuenta"
                           value="{{ old('numero_cuenta', $docente->numero_cuenta) }}"
                           class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

            </div>

            {{-- Estatus --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estatus *</label>
                <select name="estatus"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="Pendiente de Datos"
                        {{ old('estatus', $docente->estatus) == 'Pendiente de Datos' ? 'selected' : '' }}>
                        Pendiente de Datos
                    </option>

                    <option value="Activo"
                        {{ old('estatus', $docente->estatus) == 'Activo' ? 'selected' : '' }}>
                        Activo
                    </option>
                </select>
            </div>


            {{-- Botones --}}
            <div class="flex justify-end gap-3 pt-4">

                <a href="{{ route('docentes.index') }}"
                   class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl
                          font-medium shadow-sm transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl
                               font-medium shadow-md transition">
                    Actualizar Docente
                </button>

            </div>

        </form>

    </div>
</div>

@endsection
