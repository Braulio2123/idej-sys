@extends('layouts.app')

@section('title', 'Crear calendario académico')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Crear calendario académico</h1>
        <p class="text-sm text-slate-500 mt-1">Calendario por fechas exactas, no por días semanales recurrentes.</p>
    </div>

    <form method="POST" action="{{ route('calendarios_academicos.store') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @include('calendarios_academicos._form')
    </form>
</div>
@endsection
