@extends('layouts.app')

@section('title', 'Editar calendario académico')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Editar calendario académico</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $calendario->nombre }}</p>
    </div>

    <form method="POST" action="{{ route('calendarios_academicos.update', $calendario) }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @method('PUT')
        @include('calendarios_academicos._form')
    </form>
</div>
@endsection
