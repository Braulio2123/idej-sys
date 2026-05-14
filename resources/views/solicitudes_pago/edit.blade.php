@extends('layouts.app')

@section('title', 'Editar solicitud de pago docente')

@section('content')
<div class="max-w-6xl mx-auto mt-6">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-slate-200">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                <i class='bx bx-edit text-3xl text-amber-600'></i>
                Editar solicitud {{ $solicitud->folio ?? '#'.$solicitud->id }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Solo puede corregirse mientras esté pendiente u observada. Si estaba observada, al guardar vuelve a revisión administrativa.
            </p>
        </div>

        @include('solicitudes_pago._form')
    </div>
</div>
@endsection
