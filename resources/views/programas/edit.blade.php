@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-6">

    <!-- CABECERA -->
    <div class="flex items-center gap-3 mb-6">
        <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow">
            <i class='bx bx-edit text-3xl'></i>
        </div>
        <div>
            <h1 class="text-2xl font-semibold text-slate-800 leading-tight">
                Editar Programa Académico
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Modifica la información del programa registrado
            </p>
        </div>
    </div>

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">

        <form action="{{ route('programas.update', $programa) }}" method="POST" class="space-y-5">
            @method('PUT')
            @include('programas._form')
        </form>

    </div>

</div>

@endsection
