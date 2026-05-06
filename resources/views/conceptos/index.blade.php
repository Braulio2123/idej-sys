@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto px-4 py-6">

    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- TÍTULO PRINCIPAL --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-coin-stack text-3xl text-blue-600'></i>
                    Gestión de Conceptos de Pago
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Administración de conceptos base y reglas de beca
                </p>
            </div>

            <a href="{{ route('conceptos.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                      text-white px-5 py-2.5 rounded-xl font-medium shadow-md transition">
                <i class='bx bx-plus-circle text-xl'></i>
                Crear Nuevo Concepto
            </a>
        </div>

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Aviso informativo --}}
        <div class="mb-6 bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3 items-start shadow-sm">
            <i class='bx bx-info-circle text-2xl text-blue-600'></i>
            <p class="text-sm text-slate-700 leading-relaxed">
                Los conceptos marcados como
                <span class="text-green-700 font-semibold">becables</span>
                permiten aplicar descuentos automáticos según el porcentaje de beca del alumno.
            </p>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left font-semibold">Nombre</th>
                        <th class="py-3 px-4 text-left font-semibold">Monto Base</th>
                        <th class="py-3 px-4 text-center font-semibold">Becable</th>
                        <th class="py-3 px-4 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($conceptos as $concepto)

                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-slate-800 font-medium">
                                {{ $concepto->nombre }}
                            </td>

                            <td class="py-3 px-4 text-slate-700 font-semibold">
                                ${{ number_format($concepto->monto_base, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                @if($concepto->es_becable)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Sí
                                    </span>
                                @else
                                    <span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-lg text-xs font-semibold">
                                        No
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">

                                <div class="inline-flex items-center gap-3">

                                    {{-- Editar --}}
                                    <a href="{{ route('conceptos.edit', $concepto) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1 transition">
                                        <i class='bx bx-edit-alt text-lg'></i> Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ route('conceptos.destroy', $concepto) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este concepto?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 font-medium inline-flex items-center gap-1 transition">
                                            <i class='bx bx-trash text-lg'></i> Eliminar
                                        </button>
                                    </form>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="py-5 text-center text-slate-500">
                                No hay conceptos registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection
