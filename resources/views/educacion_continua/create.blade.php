@extends('layouts.app')
@section('title', 'Nuevo curso')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-900">Nuevo curso de Educación Continua</h1>
        <p class="text-slate-500">Crea cursos especiales independientes del calendario académico principal.</p>
    </div>
    @if($errors->any()) <div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('educacion_continua.store') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @csrf
        @include('educacion_continua._form')
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('educacion_continua.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold">Cancelar</a>
            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold">Guardar curso</button>
        </div>
    </form>
</div>
@endsection
