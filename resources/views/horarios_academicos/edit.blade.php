@extends('layouts.app')

@section('title', 'Editar horario')

@section('content')
<div class="max-w-6xl mx-auto bg-white rounded-2xl shadow border border-slate-100 p-6">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Editar horario académico</h1>
    <p class="text-sm text-slate-500 mb-6">Actualiza la asignación académica y valida choques de horario.</p>

    <form method="POST" action="{{ route('horarios_academicos.update', $horario) }}">
        @method('PUT')
        @include('horarios_academicos._form')
    </form>
</div>
@endsection
