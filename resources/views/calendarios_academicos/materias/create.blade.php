@extends('layouts.app')

@section('title', 'Agregar materia al calendario')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Agregar materia y fechas</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $calendario->nombre }} · {{ $calendario->grupo->nombre ?? 'Sin grupo' }} · {{ $calendario->tipo_calendario ?? 'Personalizado' }}</p>
    </div>

    <form method="POST" action="{{ route('calendarios_academicos.materias.store', $calendario) }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @include('calendarios_academicos.materias._form')
    </form>
</div>
@endsection
