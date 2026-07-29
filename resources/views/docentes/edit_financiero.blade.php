@extends('layouts.app')

@section('title', 'Datos financieros del docente')

@section('content')
<div class="max-w-3xl mx-auto mt-6">
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Datos financieros del docente</h1>
                <p class="text-sm text-slate-500 mt-1">Coordinación Administrativa puede actualizar únicamente información fiscal, bancaria y la constancia fiscal.</p>
            </div>
            <a href="{{ route('docentes.show', $docente) }}" class="inline-flex items-center gap-2 text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl">Regresar</a>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 border border-red-200">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 mb-5">
            <p class="font-semibold text-slate-800">{{ $docente->nombre_completo }}</p>
            <p class="text-sm text-slate-500">{{ $docente->area_especialidad }}</p>
        </div>

        <form method="POST" action="{{ route('docentes.financieros.update', $docente) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
                    <input type="text" name="rfc" maxlength="13" value="{{ old('rfc', $docente->rfc) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 uppercase" oninput="this.value=this.value.toUpperCase().slice(0,13)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Número de cuenta</label>
                    <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $docente->numero_cuenta) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Banco</label>
                    <input type="text" name="banco" value="{{ old('banco', $docente->banco) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Constancia de situación fiscal</label>
                @if($docente->constancia_fiscal_path)
                    <a href="{{ route('docentes.documentos.download', [$docente, 'constancia_fiscal']) }}" class="inline-block mb-2 text-sm font-semibold text-amber-800 hover:underline">Descargar archivo actual</a>
                @endif
                <input type="file" name="constancia_fiscal" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
                <p class="text-xs text-slate-500 mt-2">PDF, JPG o PNG. Máximo 5 MB. Dejar vacío conserva el archivo actual.</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('docentes.show', $docente) }}" class="px-4 py-2.5 bg-slate-200 text-slate-700 rounded-xl">Cancelar</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium">Guardar datos financieros</button>
            </div>
        </form>
    </div>
</div>
@endsection
