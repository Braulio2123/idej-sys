@extends('layouts.app')

@php
    use App\Models\Rol;
@endphp

@section('title', 'Detalle de Solicitud')

@section('content')

<div class="max-w-4xl mx-auto mt-6">

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="bg-white/90 backdrop-blur shadow-xl rounded-2xl p-6 border border-slate-200">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-file text-3xl text-blue-600'></i>
                    Solicitud #{{ $solicitud->id }}
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Detalles completos de la solicitud de pago
                </p>
            </div>

            <a href="{{ route('solicitudes_pago.index') }}"
               class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300
                      text-slate-800 px-4 py-2.5 rounded-xl font-medium shadow-md transition">
                ← Volver
            </a>
        </div>

        {{-- MENSAJES --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 border border-red-200">
                {{ session('error') }}
            </div>
        @endif


        {{-- INFORMACIÓN PRINCIPAL --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            {{-- DOCENTE --}}
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Docente</p>
                <p class="text-lg font-semibold text-slate-800 mt-1">
                    {{ $solicitud->docente->nombre ?? 'No disponible' }}
                </p>
            </div>

            {{-- NIVEL --}}
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Nivel</p>
                <p class="text-lg font-semibold text-slate-800 mt-1">
                    {{ $solicitud->nivel }}
                </p>
            </div>

            {{-- MONTO --}}
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Monto Solicitado</p>
                <p class="text-2xl font-bold text-green-700 mt-1">
                    ${{ number_format($solicitud->monto, 2) }}
                </p>
            </div>

            {{-- ESTATUS --}}
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Estatus</p>

                <span class="
                    inline-block mt-1 px-4 py-1.5 rounded-lg text-xs font-semibold
                    @if($solicitud->estatus === 'Pendiente') bg-amber-100 text-amber-700
                    @elseif($solicitud->estatus === 'Aprobada') bg-blue-100 text-blue-700
                    @elseif($solicitud->estatus === 'Pagada') bg-green-100 text-green-700
                    @elseif($solicitud->estatus === 'Rechazada') bg-red-100 text-red-700
                    @endif
                ">
                    {{ $solicitud->estatus }}
                </span>
            </div>

        </div>


        {{-- FECHAS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Fecha de Solicitud</p>
                <p class="font-semibold text-slate-800 mt-1">
                    {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}
                </p>
            </div>

            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
                <p class="text-slate-500 text-xs">Fecha de Pago</p>
                <p class="font-semibold text-slate-800 mt-1">
                    {{ $solicitud->fecha_pago?->format('d/m/Y') ?? '—' }}
                </p>
            </div>

        </div>


        {{-- CREADO POR --}}
        <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm mb-6">
            <p class="text-slate-500 text-xs">Creado por</p>
            <p class="font-semibold text-slate-800 mt-1">
                {{ $solicitud->creadoPor->nombre ?? 'No disponible' }}
            </p>
        </div>

        {{-- PROCESADO POR --}}
        @if($solicitud->procesadoPor)
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl shadow-sm mb-6">
                <p class="text-slate-500 text-xs">Procesado por</p>
                <p class="font-semibold text-slate-800 mt-1">
                    {{ $solicitud->procesadoPor->nombre }}
                </p>
            </div>
        @endif


        {{-- OBSERVACIONES --}}
        @if($solicitud->observaciones)
            <div class="p-5 bg-white border border-slate-200 rounded-xl shadow-sm mb-8">
                <p class="text-slate-500 text-xs mb-1">Observaciones</p>
                <p class="text-slate-700 font-medium">{{ $solicitud->observaciones }}</p>
            </div>
        @endif


        {{-- ACCIONES POR ROL --}}
        <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-inner p-5 flex flex-wrap gap-3 mt-6">

            {{-- EDITAR --}}
            @if(auth()->user()?->rolClave() === Rol::ADMIN && $solicitud->estatus === 'Pendiente')
                <a href="{{ route('solicitudes_pago.edit', $solicitud) }}"
                   class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow">
                    Editar
                </a>
            @endif

            {{-- APROBAR --}}
            @if(in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::ACADEMICA], true) &&
                $solicitud->estatus === 'Pendiente')
                <form method="POST" action="{{ route('solicitudes_pago.aprobar', $solicitud->id) }}">
                    @csrf
                    @method('PUT')
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                        Aprobar
                    </button>
                </form>
            @endif

            {{-- PAGAR --}}
            @if(in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) &&
                $solicitud->estatus === 'Aprobada')
                <a href="{{ route('solicitudes_pago.form_pagar', $solicitud->id) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                    Registrar Pago
                </a>
            @endif

            {{-- ELIMINAR --}}
            @if(auth()->user()?->rolClave() === Rol::ADMIN)
                <form method="POST"
                      action="{{ route('solicitudes_pago.destroy', $solicitud) }}"
                      onsubmit="return confirm('¿Seguro de eliminar esta solicitud?');">

                    @csrf
                    @method('DELETE')

                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
                        Eliminar
                    </button>
                </form>
            @endif

        </div>

    </div>
</div>

@endsection
