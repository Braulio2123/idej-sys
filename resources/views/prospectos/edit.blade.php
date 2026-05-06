@extends('layouts.app')

@section('title', 'Editar prospecto')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar prospecto</h1>
        <p class="text-sm text-slate-500 mt-1">Actualiza información comercial o de seguimiento.</p>
    </div>

    <form method="POST" action="{{ route('prospectos.update', $prospecto) }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @csrf
        @method('PUT')
        @include('prospectos._form')
    </form>
</div>
@endsection
