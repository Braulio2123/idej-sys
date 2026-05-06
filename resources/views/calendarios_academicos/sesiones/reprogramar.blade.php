@extends('layouts.app')

@section('title', 'Reprogramar sesión académica')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-800">Reprogramar sesión</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $calendario->nombre }} · {{ $sesion->calendarioMateria->nombre_materia }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-5">
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            La sesión original se marcará como cancelada y se creará una nueva sesión de reposición. Quedará registro de motivo, usuario y fecha de reprogramación.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Fecha original</p>
                <p class="font-semibold text-slate-800">{{ $sesion->fecha->format('d/m/Y') }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Día</p>
                <p class="font-semibold text-slate-800">{{ $sesion->dia_semana }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Horario</p>
                <p class="font-semibold text-slate-800">{{ $sesion->horario }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500 uppercase font-bold">Patrón</p>
                <p class="font-semibold text-slate-800">{{ $textoDiasPermitidos }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('calendarios_academicos.sesiones.reprogramar.store', [$calendario, $sesion]) }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nueva fecha *</label>
                    <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full rounded-xl border-slate-300" required>
                    @error('fecha') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Horario rápido</label>
                    <select id="presetHorario" class="w-full rounded-xl border-slate-300">
                        <option value="">Selecciona horario</option>
                        @foreach($horariosPredefinidos as $horario)
                            <option value="{{ $horario['inicio'] }}|{{ $horario['fin'] }}">{{ $horario['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Hora inicio *</label>
                    <input id="horaInicio" type="time" name="hora_inicio" value="{{ old('hora_inicio', substr((string) $sesion->hora_inicio, 0, 5)) }}" class="w-full rounded-xl border-slate-300" required>
                    @error('hora_inicio') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Hora fin *</label>
                    <input id="horaFin" type="time" name="hora_fin" value="{{ old('hora_fin', substr((string) $sesion->hora_fin, 0, 5)) }}" class="w-full rounded-xl border-slate-300" required>
                    @error('hora_fin') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Aula / liga</label>
                    <input type="text" name="aula" value="{{ old('aula', $sesion->aula) }}" class="w-full rounded-xl border-slate-300">
                    @error('aula') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad *</label>
                    <select name="modalidad" class="w-full rounded-xl border-slate-300">
                        @foreach(['Presencial', 'Virtual', 'Mixta'] as $modalidad)
                            <option value="{{ $modalidad }}" @selected(old('modalidad', $sesion->modalidad) === $modalidad)>{{ $modalidad }}</option>
                        @endforeach
                    </select>
                    @error('modalidad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de reprogramación *</label>
                <textarea name="motivo_reprogramacion" rows="5" class="w-full rounded-xl border-slate-300" required placeholder="Ejemplo: reposición por cancelación del docente / ajuste de calendario...">{{ old('motivo_reprogramacion') }}</textarea>
                @error('motivo_reprogramacion') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <label class="flex items-start gap-3 font-semibold">
                    <input type="checkbox" name="permitir_fuera_patron" value="1" class="mt-1 rounded border-amber-300" @checked(old('permitir_fuera_patron'))>
                    <span>Autorizar reposición fuera del patrón del calendario <span class="block font-normal">Úsalo cuando una clase de viernes/sábado/sabatina/matutina/vespertina se repone en otro día por acuerdo académico.</span></span>
                </label>
                <label class="flex items-start gap-3 font-semibold">
                    <input type="checkbox" name="permitir_no_laboral" value="1" class="mt-1 rounded border-amber-300" @checked(old('permitir_no_laboral'))>
                    <span>Autorizar reposición en día no laboral <span class="block font-normal">Solo si Dirección/Coordinación Académica lo validó.</span></span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('calendarios_academicos.show', $calendario) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Regresar</a>
                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Reprogramar sesión</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const preset = document.getElementById('presetHorario');
    const inicio = document.getElementById('horaInicio');
    const fin = document.getElementById('horaFin');

    preset?.addEventListener('change', () => {
        if (!preset.value) return;
        const [horaInicio, horaFin] = preset.value.split('|');
        inicio.value = horaInicio;
        fin.value = horaFin;
    });
});
</script>
@endpush
