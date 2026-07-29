@extends('layouts.app')

@section('title', 'Registrar Docente')

@section('content')
@php($docente = new \App\Models\Docente())
<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2"><i class='bx bx-user-plus text-3xl text-blue-600'></i> Registrar Docente</h1>
                <p class="text-xs text-slate-500 mt-1">Captura datos académicos, fiscales y bancarios necesarios para pago docente.</p>
            </div>
            <a href="{{ route('docentes.index') }}" class="inline-flex items-center gap-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl transition shadow-sm"><i class='bx bx-arrow-back text-lg'></i> Regresar</a>
        </div>
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 border border-red-200"><ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('docentes.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('docentes._form')
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('docentes.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-medium shadow-sm transition">Cancelar</a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium shadow-md transition">Guardar Docente</button>
            </div>
        </form>
    </div>
</div>
@endsection
