@extends('layouts.app')

@php
    use App\Models\Rol;
@endphp

@section('title', 'Solicitudes de Pago')

@section('content')

<div class="max-w-7xl mx-auto mt-6">

    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-file text-3xl text-blue-600'></i>
                    Solicitudes de Pago a Docentes
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Administración general de las solicitudes registradas
                </p>
            </div>

            {{-- BOTÓN CREAR --}}
            @if(in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION], true))
                <a href="{{ route('solicitudes_pago.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                          text-white px-5 py-2.5 rounded-xl font-medium shadow-md transition">
                    <i class='bx bx-file-plus text-xl'></i>
                    Nueva Solicitud
                </a>
            @endif
        </div>

        {{-- MENSAJE DE ÉXITO --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 border border-green-200">
                {{ session('success') }}
            </div>
        @endif


        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left">Docente</th>
                        <th class="py-3 px-4 text-left">Nivel</th>
                        <th class="py-3 px-4 text-left">Monto</th>
                        <th class="py-3 px-4 text-left">Solicitud</th>
                        <th class="py-3 px-4 text-left">Pago</th>
                        <th class="py-3 px-4 text-left">Creado por</th>
                        <th class="py-3 px-4 text-left">Procesado por</th>
                        <th class="py-3 px-4 text-left">Estatus</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($solicitudes as $solicitud)
                        <tr class="hover:bg-slate-50/70 transition">

                            {{-- DOCENTE --}}
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $solicitud->docente->nombre ?? '—' }}
                            </td>

                            {{-- NIVEL --}}
                            <td class="py-3 px-4">
                                {{ $solicitud->nivel ?? '—' }}
                            </td>

                            {{-- MONTO --}}
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                ${{ number_format($solicitud->monto, 2) }}
                            </td>

                            {{-- FECHA SOLICITUD --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- FECHA PAGO --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $solicitud->fecha_pago?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- CREADO POR --}}
                            <td class="py-3 px-4">
                                {{ $solicitud->creadoPor->nombre ?? '—' }}
                            </td>

                            {{-- PROCESADO POR --}}
                            <td class="py-3 px-4">
                                {{ $solicitud->procesadoPor->nombre ?? '—' }}
                            </td>

                            {{-- ESTATUS --}}
                            <td class="py-3 px-4">

                                @if($solicitud->estatus === 'Pendiente')
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Pendiente
                                    </span>

                                @elseif($solicitud->estatus === 'Aprobada')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Aprobada
                                    </span>

                                @elseif($solicitud->estatus === 'Pagada')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Pagada
                                    </span>

                                @elseif($solicitud->estatus === 'Rechazada')
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-semibold">
                                        Rechazada
                                    </span>
                                @endif

                            </td>

                            {{-- ACCIONES --}}
                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-3">

                                    {{-- VER --}}
                                    <a href="{{ route('solicitudes_pago.show', $solicitud) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium transition">
                                        Ver
                                    </a>

                                    {{-- EDITAR (solo Admin y Pendiente) --}}
                                    @if(auth()->user()?->rolClave() === Rol::ADMIN &&
                                        $solicitud->estatus === 'Pendiente')

                                        <a href="{{ route('solicitudes_pago.edit', $solicitud) }}"
                                           class="text-amber-600 hover:text-amber-800 font-medium transition">
                                            Editar
                                        </a>
                                    @endif

                                    {{-- APROBAR --}}
                                    @if(in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::ACADEMICA], true) &&
                                        $solicitud->estatus === 'Pendiente')

                                        <form method="POST"
                                              action="{{ route('solicitudes_pago.aprobar', $solicitud->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <button type="submit"
                                                    class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                Aprobar
                                            </button>
                                        </form>
                                    @endif

                                    {{-- PAGAR --}}
                                    @if(in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) &&
                                        $solicitud->estatus === 'Aprobada')

                                        <a href="{{ route('solicitudes_pago.form_pagar', $solicitud->id) }}"
                                           class="text-green-600 hover:text-green-800 font-medium">
                                            Pagar
                                        </a>
                                    @endif

                                    {{-- ELIMINAR --}}
                                    @if(auth()->user()?->rolClave() === Rol::ADMIN)
                                        <form action="{{ route('solicitudes_pago.destroy', $solicitud) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Seguro de eliminar esta solicitud?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 font-medium">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="py-5 text-center text-slate-500">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="mt-6">
            {{ $solicitudes->links() }}
        </div>

    </div>
</div>

@endsection
