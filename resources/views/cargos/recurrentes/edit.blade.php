@extends('layouts.app')

@section('title', 'Editar plan recurrente')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Editar plan de cargos recurrentes</h1>
    <p class="text-sm text-slate-500 mb-6">Los cambios aplican hacia futuras generaciones. El historial anterior se conserva.</p>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Revisa la información:</p>
            <ul class="list-disc pl-5 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cargos.recurrentes.update', $plan) }}" class="rounded-2xl border border-slate-200 bg-white p-5">
        @method('PUT')
        @include('cargos.recurrentes._form')
    </form>
</div>
@endsection
