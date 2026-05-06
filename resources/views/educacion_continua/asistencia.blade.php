@extends('layouts.app')

@section('title', 'Asistencia')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <h1 class="text-3xl font-bold text-slate-900">Asistencia de sesión</h1>
        <p class="text-slate-500">{{ $curso->nombre }} · {{ $sesion->fecha->format('d/m/Y') }} · {{ $sesion->horario }} · {{ number_format($sesion->duracion_horas, 2) }}h</p>
    </div>

    @if($errors->any()) <div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">{{ $errors->first() }}</div> @endif

    <form method="POST" action="{{ route('educacion_continua.sesiones.asistencia.store', [$curso, $sesion]) }}" class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        @csrf
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-indigo-600 text-white"><tr><th class="text-left px-3 py-2">Participante</th><th class="text-left px-3 py-2">Estatus</th><th class="text-left px-3 py-2">Horas</th><th class="text-left px-3 py-2">Observaciones</th></tr></thead>
                <tbody>
                @forelse($inscritos as $i => $inscrito)
                    @php $asistencia = $inscrito->asistencias->first(); @endphp
                    <tr class="border-b last:border-b-0">
                        <td class="px-3 py-3">
                            <input type="hidden" name="asistencias[{{ $i }}][curso_inscrito_id]" value="{{ $inscrito->id }}">
                            <p class="font-semibold">{{ $inscrito->nombre }}</p>
                            <p class="text-xs text-slate-500">{{ $inscrito->tipo_participante }}</p>
                        </td>
                        <td class="px-3 py-3">
                            <select name="asistencias[{{ $i }}][estatus]" class="rounded-xl border-slate-300">
                                @foreach($estatuses as $estatus)
                                    <option value="{{ $estatus }}" @selected(($asistencia->estatus ?? 'Asistió') === $estatus)>{{ $estatus }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3"><input type="number" step="0.5" min="0" name="asistencias[{{ $i }}][horas_acreditadas]" value="{{ $asistencia->horas_acreditadas ?? $sesion->duracion_horas }}" class="w-24 rounded-xl border-slate-300"></td>
                        <td class="px-3 py-3"><input type="text" name="asistencias[{{ $i }}][observaciones]" value="{{ $asistencia->observaciones ?? '' }}" class="w-full rounded-xl border-slate-300"></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No hay participantes inscritos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5 flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="marcar_impartida" value="1"> Marcar sesión como impartida</label>
            <div class="flex gap-3">
                <a href="{{ route('educacion_continua.show', $curso) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold">Volver</a>
                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold">Guardar asistencia</button>
            </div>
        </div>
    </form>
</div>
@endsection
