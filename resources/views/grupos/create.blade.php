@extends('layouts.app')

@section('title', 'Crear Grupo Académico')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl shadow border border-slate-100">
    <h1 class="text-2xl font-bold mb-2">Crear Grupo Académico</h1>
    <p class="text-sm text-slate-500 mb-4">Los grupos se crean a partir de Educación Programática registrada. El aula será asignada posteriormente por Sistemas.</p>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($programas->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <p class="font-bold">Antes de crear grupos debes registrar Educación Programática.</p>
            <p class="text-sm mt-1">Este formulario depende de Licenciatura, Maestría o Doctorado registrados previamente.</p>
            <a href="{{ route('programas.create') }}" class="inline-flex mt-4 px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold hover:bg-amber-700">Registrar Educación Programática</a>
        </div>
    @else
        <form action="{{ route('grupos.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="font-semibold">Nombre del grupo</label>
                <input type="text" name="nombre" class="w-full border p-2 rounded-xl" value="{{ old('nombre') }}" required>
            </div>

            <div>
                <label class="font-semibold">Ciclo escolar</label>
                <select name="ciclo_escolar_id" class="w-full border p-2 rounded-xl" required>
                    <option value="">Seleccione...</option>
                    @foreach($ciclos as $c)
                        <option value="{{ $c->id }}" @selected(old('ciclo_escolar_id') == $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold">Educación Programática</label>
                <select name="programa_id" id="programa_id" class="w-full border p-2 rounded-xl" required>
                    <option value="">Seleccione...</option>
                    @foreach($programas as $p)
                        <option value="{{ $p->id }}" data-semestres="{{ $p->duracion_periodos ?: 0 }}" @selected(old('programa_id') == $p->id)>{{ $p->nombre }}{{ $p->nivel ? ' · '.$p->nivel : '' }}</option>
                    @endforeach
                </select>
                <p id="duracionProgramaHelp" class="text-xs text-slate-500 mt-1">Selecciona Educación Programática para validar semestres.</p>
            </div>

            <div>
                <label class="font-semibold">Semestre</label>
                <input type="number" name="semestre_o_cuatrimestre" id="semestreInput" min="1" max="20" class="w-full border p-2 rounded-xl" value="{{ old('semestre_o_cuatrimestre') }}" required>
                <p class="text-xs text-slate-500 mt-1">IDEJ trabaja únicamente por semestres.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <p class="font-bold text-slate-800">Horario institucional de Educación Programática</p>
                <p class="mt-1">Viernes de 05:00 p.m. a 09:00 p.m. y sábados de 08:00 a.m. a 01:00 p.m.</p>
                <input type="hidden" name="turno" value="Mixto">
            </div>

            <div>
                <label class="font-semibold">Cupo máximo</label>
                <input type="number" name="cupo_maximo" min="1" max="60" class="w-full border p-2 rounded-xl" value="{{ old('cupo_maximo', 30) }}" required>
            </div>

            <button class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700">Guardar grupo</button>
        </form>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const programa = document.getElementById('programa_id');
    const semestre = document.getElementById('semestreInput');
    const help = document.getElementById('duracionProgramaHelp');
    const sync = () => {
        const selected = programa?.selectedOptions?.[0];
        const semestres = Number(selected?.dataset?.semestres || 0);
        if (semestres > 0) {
            semestre.max = semestres;
            help.textContent = `Duración registrada: ${semestres} semestre(s).`;
        } else {
            semestre.removeAttribute('max');
            help.textContent = 'Esta Educación Programática no tiene duración registrada. Revisa el catálogo.';
        }
    };
    programa?.addEventListener('change', sync);
    sync();
});
</script>
@endpush
@endsection
