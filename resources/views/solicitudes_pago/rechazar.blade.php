@extends('layouts.app')

@section('title', 'Rechazar solicitud docente')

@section('content')
<div class="mx-auto mt-6 max-w-2xl">
    <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-xl">
        <h1 class="text-2xl font-bold text-slate-800">No ejecutar la solicitud {{ $solicitud->folio }}</h1>
        <p class="mt-2 text-sm text-slate-500">La decisión y su motivo se conservarán en el historial y se notificarán automáticamente a Coordinación Académica.</p>

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-inside list-disc text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="my-5 rounded-xl bg-slate-50 p-4 text-sm">
            <p><strong>Docente:</strong> {{ $solicitud->docente->nombre_completo }}</p>
            <p><strong>Tipo:</strong> {{ $solicitud->tipo_clase }}</p>
            <p><strong>Actividad:</strong> {{ $solicitud->materia_actividad }}</p>
        </div>

        <form method="POST" action="{{ route('solicitudes_pago.rechazar', $solicitud) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block font-semibold text-slate-700">Motivo de no aprobación *</label>
                <textarea name="motivo_rechazo" required minlength="8" maxlength="1500" rows="6" class="w-full rounded-xl border-slate-300 px-4 py-2">{{ old('motivo_rechazo') }}</textarea>
            </div>
            <div class="flex justify-between gap-3">
                <a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="rounded-xl bg-slate-200 px-5 py-2.5 font-semibold text-slate-700">Cancelar</a>
                <button class="rounded-xl bg-red-600 px-6 py-2.5 font-semibold text-white hover:bg-red-700">Rechazar y notificar</button>
            </div>
        </form>
    </div>
</div>
@endsection
