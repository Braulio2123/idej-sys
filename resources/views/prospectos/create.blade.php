@extends('layouts.app')

@section('title', 'Nuevo prospecto')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar prospecto</h1>
        <p class="text-sm text-slate-500 mt-1">Captura interesados antes de convertirlos en alumnos.</p>
    </div>

    <form method="POST" action="{{ route('prospectos.store') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @csrf
        @include('prospectos._form')
    </form>
</div>
@endsection
