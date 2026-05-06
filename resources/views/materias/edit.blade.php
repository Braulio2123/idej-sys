@extends('layouts.app')

@section('title', 'Editar materia')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-2xl shadow border border-slate-100 p-6">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Editar materia</h1>
    <p class="text-sm text-slate-500 mb-6">Actualiza los datos académicos de la materia.</p>

    <form method="POST" action="{{ route('materias.update', $materia) }}">
        @method('PUT')
        @include('materias._form')
    </form>
</div>
@endsection
