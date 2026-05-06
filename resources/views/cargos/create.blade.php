@extends('layouts.app')

@section('title', 'Registrar Cargo')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('alumnos.show', $alumno) }}" class="text-blue-700 hover:underline font-semibold">← Volver al expediente</a>
        <h1 class="text-3xl font-bold text-slate-900 mt-2">Registrar nuevo cargo</h1>
        <p class="text-slate-600">Alumno: <strong>{{ $alumno->nombre_completo }}</strong> · Matrícula: {{ $alumno->matricula }}</p>
    </div>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-4 border border-red-200">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow">
        @if($becaActiva)
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-4">
                <p class="font-bold">Beca vigente detectada: {{ $becaActiva->porcentaje }}%</p>
                <p class="text-sm">Se aplicará automáticamente solo si el concepto seleccionado es becable. Vigencia: {{ $becaActiva->fecha_inicio?->format('d/m/Y') }} → {{ $becaActiva->fecha_fin?->format('d/m/Y') ?? 'Indefinida' }}.</p>
            </div>
        @else
            <div class="mb-5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl p-4 text-sm">
                Este alumno no tiene beca vigente. El cargo se generará sin descuento institucional.
            </div>
        @endif

        <form method="POST" action="{{ route('alumnos.cargos.store', $alumno) }}" class="space-y-5">
            @csrf

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Concepto</label>
                <select name="concepto_id" id="concepto_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="">-- Selecciona un concepto --</option>
                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto->id }}" data-monto="{{ $concepto->monto_base }}" data-becable="{{ $concepto->es_becable ? '1' : '0' }}">
                            {{ $concepto->nombre }} — ${{ number_format($concepto->monto_base, 2) }} {{ $concepto->es_becable ? '(Becable)' : '(No becable)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="infoConcepto" class="hidden grid grid-cols-1 md:grid-cols-4 gap-3 border border-slate-200 rounded-xl p-4 bg-slate-50">
                <div>
                    <p class="text-xs uppercase text-slate-500">Monto base</p>
                    <p class="font-bold text-slate-900">$<span id="montoBase">0.00</span></p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Concepto</p>
                    <p class="font-bold text-slate-900" id="tipoConcepto">-</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Descuento beca</p>
                    <p class="font-bold text-emerald-700">$<span id="descuentoBeca">0.00</span></p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Adeudo a generar</p>
                    <p class="font-bold text-blue-700">$<span id="montoFinal">0.00</span></p>
                </div>
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Descripción</label>
                <input type="text" name="descripcion_cargo" value="{{ old('descripcion_cargo') }}" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Colegiatura mayo 2026" required>
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Monto base real</label>
                <input type="number" step="0.01" name="monto_original" id="monto_original" value="{{ old('monto_original') }}" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                <p class="text-xs text-slate-500 mt-1">Guarda el monto real del concepto. El sistema calculará el adeudo final si hay beca vigente y el concepto es becable.</p>
            </div>

            <div>
                <label class="block font-semibold mb-1 text-slate-700">Fecha de vencimiento</label>
                <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('alumnos.show', $alumno) }}" class="px-5 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">Cancelar</a>
                <button type="submit" class="bg-blue-700 text-white px-5 py-2 rounded-xl hover:bg-blue-800 font-semibold shadow">
                    Guardar cargo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('concepto_id');
    const info = document.getElementById('infoConcepto');
    const montoBase = document.getElementById('montoBase');
    const tipo = document.getElementById('tipoConcepto');
    const descuentoBeca = document.getElementById('descuentoBeca');
    const montoFinal = document.getElementById('montoFinal');
    const inputMonto = document.getElementById('monto_original');
    const becaPorcentaje = {{ $becaActiva?->porcentaje ?? 0 }};

    function recalcular() {
        const selected = select.options[select.selectedIndex];
        const selectedMonto = parseFloat(selected?.getAttribute('data-monto')) || 0;
        const monto = parseFloat(inputMonto.value || selectedMonto || 0);
        const becable = selected?.getAttribute('data-becable') === '1';
        const descuento = (becaPorcentaje > 0 && becable) ? monto * (becaPorcentaje / 100) : 0;
        const final = Math.max(monto - descuento, 0);

        if (select.value) {
            info.classList.remove('hidden');
        }

        montoBase.textContent = monto.toFixed(2);
        tipo.textContent = becable ? 'Becable' : 'No becable';
        descuentoBeca.textContent = descuento.toFixed(2);
        montoFinal.textContent = final.toFixed(2);
    }

    select.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const monto = parseFloat(selected?.getAttribute('data-monto')) || 0;
        if (monto > 0) {
            inputMonto.value = monto.toFixed(2);
        }
        recalcular();
    });

    inputMonto.addEventListener('input', recalcular);
});
</script>
@endpush
