@extends('layouts.app')

@section('title', 'Crear Educación Programática')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-100 text-blue-600">
                <i class='bx bx-book-add text-3xl'></i>
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Crear Educación Programática</h1>
                <p class="text-xs text-slate-500 mt-1">Registra un plan académico con datos útiles para operación, reportes y seguimiento.</p>
            </div>
        </div>

        <form action="{{ route('programas.store') }}" method="POST" class="space-y-5">
            @include('programas._form')
        </form>
    </div>
</div>
@endsection
