@extends('layouts.app')

@section('title', 'Editar Grupo Académico')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl shadow border border-slate-100">
    <h1 class="text-2xl font-bold mb-2">Editar Grupo Académico</h1>
    <p class="text-sm text-slate-500 mb-4">El aula no se captura aquí. Sistemas la asigna posteriormente para preparar equipo y espacios.</p>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('grupos.update', $grupo) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="font-semibold">Nombre del grupo</label>
            <input type="text" name="nombre" class="w-full border p-2 rounded-xl" value="{{ old('nombre', $grupo->nombre) }}" required>
        </div>

        <div>
            <label class="font-semibold">Ciclo escolar</label>
            <select name="ciclo_escolar_id" class="w-full border p-2 rounded-xl" required>
                @foreach($ciclos as $c)
                    <option value="{{ $c->id }}" @selected(old('ciclo_escolar_id', $grupo->ciclo_escolar_id) == $c->id)>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="font-semibold">Educación Programática</label>
            <select name="programa_id" id="programa_id" class="w-full border p-2 rounded-xl" required>
                @foreach($programas as $p)
                    <option value="{{ $p->id }}" data-semestres="{{ $p->duracion_periodos ?: 0 }}" @selected(old('programa_id', $grupo->programa_id) == $p->id)>{{ $p->nombre }}{{ $p->nivel ? ' · '.$p->nivel : '' }}</option>
                @endforeach
            </select>
            <p id="duracionProgramaHelp" class="text-xs text-slate-500 mt-1"></p>
        </div>

        <div>
            <label class="font-semibold">Semestre</label>
            <input type="number" name="semestre_o_cuatrimestre" id="semestreInput" min="1" max="20" class="w-full border p-2 rounded-xl" value="{{ old('semestre_o_cuatrimestre', $grupo->semestre_o_cuatrimestre) }}" required>
            <p class="text-xs text-slate-500 mt-1">IDEJ trabaja únicamente por semestres.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-bold text-slate-800">Horario institucional</p>
            <p class="mt-1">Viernes de 05:00 p.m. a 09:00 p.m. y sábados de 08:00 a.m. a 01:00 p.m.</p>
            <input type="hidden" name="turno" value="Mixto">
        </div>

        <div>
            <label class="font-semibold">Cupo máximo</label>
            <input type="number" name="cupo_maximo" min="1" max="60" class="w-full border p-2 rounded-xl" value="{{ old('cupo_maximo', $grupo->cupo_maximo) }}" required>
        </div>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700">Actualizar grupo</button>
    </form>
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
