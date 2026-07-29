@extends('layouts.app')

@section('title', 'Nueva solicitud de pago docente')

@section('content')
<div class="max-w-6xl mx-auto mt-6">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-slate-200">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                <i class='bx bx-receipt text-3xl text-blue-600'></i>
                Nueva solicitud de pago a docente
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Coordinación Académica registra las fechas y el tipo de clase. Coordinación Administrativa define el monto, programa una fecha tentativa y ejecuta o rechaza el pago.
            </p>
        </div>

        @include('solicitudes_pago._form')
    </div>
</div>
@endsection
