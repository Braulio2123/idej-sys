@extends('layouts.app')

@section('title', 'Abrir Caja')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Abrir Caja</h2>
        <p class="text-sm text-slate-500 mt-1">Abre tu caja antes de registrar pagos del día.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cortes-caja.store') }}" class="bg-white border border-slate-200 rounded-xl p-6 space-y-5 shadow-sm">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Saldo inicial en efectivo</label>
            <input type="number" step="0.01" min="0" name="saldo_inicial" value="{{ old('saldo_inicial', '0.00') }}" class="w-full rounded-lg border-slate-300" required>
            <p class="text-xs text-slate-500 mt-1">Captura el efectivo físico con el que inicia la caja, si aplica.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones de apertura</label>
            <textarea name="observaciones_apertura" rows="4" class="w-full rounded-lg border-slate-300" placeholder="Ej: Caja inicia sin fondo, cambio recibido, observaciones internas.">{{ old('observaciones_apertura') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('cortes-caja.index') }}" class="px-4 py-2 rounded-lg border text-slate-700 font-semibold hover:bg-slate-50">Cancelar</a>
            <button class="px-5 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Abrir caja</button>
        </div>
    </form>
</div>
@endsection
