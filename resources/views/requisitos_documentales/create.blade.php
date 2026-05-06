@extends('layouts.app')

@section('title', 'Nuevo Requisito Documental')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="mb-6 flex items-center gap-3">
        <div class="h-12 w-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center shadow">
            <i class='bx bx-file-plus text-3xl'></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Nuevo requisito documental</h1>
            <p class="text-sm text-slate-500">Define documentos requeridos para todos, por nivel o por programa.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <form action="{{ route('requisitos_documentales.store') }}" method="POST">
            @include('requisitos_documentales._form')
        </form>
    </div>
</div>
@endsection
