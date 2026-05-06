@extends('layouts.app')

@section('title', $curso->nombre)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">{{ $curso->nombre }}</h1>
                <p class="text-slate-500">{{ $curso->tipo }} · {{ $curso->modalidad }} · {{ $curso->estatus }}</p>
                <p class="text-sm text-slate-400 mt-1">{{ optional($curso->fecha_inicio)->format('d/m/Y') ?? 'Sin inicio' }} - {{ optional($curso->fecha_fin)->format('d/m/Y') ?? 'Sin fin' }} · Responsable: {{ $curso->responsable->nombre ?? 'Sin responsable' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('educacion_continua.edit', $curso) }}" class="px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold">Editar</a>
                <a href="{{ route('educacion_continua.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold">Volver</a>
            </div>
        </div>
    </div>

    @if(session('success')) <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">{{ $errors->first() }}</div> @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Horas requeridas</p>
            <p class="text-3xl font-bold text-indigo-700">{{ number_format($curso->horas_totales, 2) }}h</p>
        </div>
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Horas programadas</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($horasProgramadas, 2) }}h</p>
        </div>
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Horas impartidas</p>
            <p class="text-3xl font-bold text-green-700">{{ number_format($horasImpartidas, 2) }}h</p>
        </div>
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-5">
            <p class="text-sm text-slate-500">Inscritos activos</p>
            <p class="text-3xl font-bold text-amber-700">{{ $inscritosActivos }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-slate-900">Sesiones del curso</h2>
                <span class="text-xs text-slate-500">{{ $curso->sesiones->count() }} sesión(es)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="text-left px-3 py-2">Fecha</th>
                            <th class="text-left px-3 py-2">Horario</th>
                            <th class="text-left px-3 py-2">Expositor</th>
                            <th class="text-left px-3 py-2">Aula/Liga</th>
                            <th class="text-left px-3 py-2">Equipo</th>
                            <th class="text-right px-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($curso->sesiones as $sesion)
                        <tr class="border-b last:border-b-0">
                            <td class="px-3 py-3">
                                <p class="font-semibold">{{ $sesion->fecha->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $sesion->estatus }} · {{ number_format($sesion->duracion_horas, 2) }}h</p>
                            </td>
                            <td class="px-3 py-3">{{ $sesion->horario }}</td>
                            <td class="px-3 py-3">{{ $sesion->expositor }}</td>
                            <td class="px-3 py-3">{{ $sesion->aula_liga ?? 'Sin aula/liga' }}<br><span class="text-xs text-slate-500">{{ $sesion->modalidad }}</span></td>
                            <td class="px-3 py-3 text-xs">{{ $sesion->requiere_equipo ? implode(', ', $sesion->equipo_requerido ?? []) : 'No' }}</td>
                            <td class="px-3 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('educacion_continua.sesiones.asistencia', [$curso, $sesion]) }}" class="text-green-700 font-semibold hover:underline">Asistencia</a>
                                <form method="POST" action="{{ route('educacion_continua.sesiones.destroy', [$curso, $sesion]) }}" class="inline" onsubmit="return confirm('¿Eliminar sesión?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 font-semibold hover:underline ml-2">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8 text-center text-slate-500">Aún no hay sesiones programadas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Agregar sesión</h2>
            <form method="POST" action="{{ route('educacion_continua.sesiones.store', $curso) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-slate-700">Fecha</label>
                    <input type="date" name="fecha" required class="w-full rounded-xl border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="text-sm font-semibold text-slate-700">Inicio</label><input type="time" name="hora_inicio" required class="w-full rounded-xl border-slate-300"></div>
                    <div><label class="text-sm font-semibold text-slate-700">Fin</label><input type="time" name="hora_fin" required class="w-full rounded-xl border-slate-300"></div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Docente IDEJ</label>
                    <select name="docente_id" class="w-full rounded-xl border-slate-300">
                        <option value="">Sin docente del catálogo</option>
                        @foreach($docentes as $docente)<option value="{{ $docente->id }}">{{ $docente->nombre_completo }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-sm font-semibold text-slate-700">Expositor externo</label><input type="text" name="expositor_nombre" class="w-full rounded-xl border-slate-300"></div>
                <div><label class="text-sm font-semibold text-slate-700">Aula / liga</label><input type="text" name="aula_liga" class="w-full rounded-xl border-slate-300"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Modalidad</label>
                        <select name="modalidad" class="w-full rounded-xl border-slate-300">@foreach($modalidades as $modalidad)<option value="{{ $modalidad }}" @selected($curso->modalidad === $modalidad)>{{ $modalidad }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Estatus</label>
                        <select name="estatus" class="w-full rounded-xl border-slate-300">@foreach($estatusesSesion as $estatus)<option value="{{ $estatus }}">{{ $estatus }}</option>@endforeach</select>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Equipo</label>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        @foreach($equipos as $equipo)
                            <label class="text-xs flex gap-1 items-center"><input type="checkbox" name="equipo_requerido[]" value="{{ $equipo }}"> {{ $equipo }}</label>
                        @endforeach
                    </div>
                </div>
                <textarea name="observaciones" rows="2" class="w-full rounded-xl border-slate-300" placeholder="Observaciones"></textarea>
                <button class="w-full rounded-xl bg-indigo-600 text-white font-semibold py-2">Agregar sesión</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-slate-900">Participantes</h2>
                <span class="text-xs text-slate-500">{{ $curso->inscritos->count() }} registro(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100"><tr><th class="text-left px-3 py-2">Participante</th><th class="text-left px-3 py-2">Tipo</th><th class="text-left px-3 py-2">Horas</th><th class="text-left px-3 py-2">Avance</th><th class="text-right px-3 py-2">Estatus</th></tr></thead>
                    <tbody>
                    @forelse($curso->inscritos as $inscrito)
                        <tr class="border-b last:border-b-0">
                            <td class="px-3 py-3"><p class="font-semibold">{{ $inscrito->nombre }}</p><p class="text-xs text-slate-500">{{ $inscrito->correo ?? 'Sin correo' }} · {{ $inscrito->telefono ?? 'Sin teléfono' }}</p></td>
                            <td class="px-3 py-3">{{ $inscrito->tipo_participante }}</td>
                            <td class="px-3 py-3">{{ number_format($inscrito->horasAsistidas(), 2) }} / {{ number_format($curso->horas_totales, 2) }}h</td>
                            <td class="px-3 py-3">{{ number_format($inscrito->porcentajeAvance(), 1) }}%</td>
                            <td class="px-3 py-3 text-right">{{ $inscrito->estatus }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">No hay participantes inscritos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6" x-data="{ tipo: 'Externo' }">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Inscribir participante</h2>
            <form method="POST" action="{{ route('educacion_continua.inscritos.store', $curso) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-slate-700">Tipo</label>
                    <select name="tipo_participante" x-model="tipo" class="w-full rounded-xl border-slate-300">
                        @foreach($tiposParticipante as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach
                    </select>
                </div>
                <div x-show="tipo === 'Alumno'">
                    <label class="text-sm font-semibold text-slate-700">Alumno</label>
                    <select name="alumno_id" class="w-full rounded-xl border-slate-300"><option value="">Selecciona alumno</option>@foreach($alumnos as $alumno)<option value="{{ $alumno->id }}">{{ $alumno->nombre_completo }}</option>@endforeach</select>
                </div>
                <div x-show="tipo === 'Prospecto'">
                    <label class="text-sm font-semibold text-slate-700">Prospecto</label>
                    <select name="prospecto_id" class="w-full rounded-xl border-slate-300"><option value="">Selecciona prospecto</option>@foreach($prospectos as $prospecto)<option value="{{ $prospecto->id }}">{{ $prospecto->nombre_completo }}</option>@endforeach</select>
                </div>
                <div x-show="tipo === 'Externo'" class="space-y-3">
                    <input type="text" name="nombre_externo" class="w-full rounded-xl border-slate-300" placeholder="Nombre externo">
                    <input type="email" name="correo_externo" class="w-full rounded-xl border-slate-300" placeholder="Correo externo">
                    <input type="text" name="telefono_externo" class="w-full rounded-xl border-slate-300" placeholder="Teléfono externo">
                </div>
                <input type="date" name="fecha_inscripcion" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-300">
                <textarea name="observaciones" rows="2" class="w-full rounded-xl border-slate-300" placeholder="Observaciones"></textarea>
                <button class="w-full rounded-xl bg-emerald-600 text-white font-semibold py-2">Inscribir</button>
            </form>
        </div>
    </div>
</div>
@endsection
