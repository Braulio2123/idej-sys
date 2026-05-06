@csrf

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
        <p class="font-semibold mb-2">No se pudo guardar el horario:</p>
        <ul class="list-disc ml-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Grupo *</label>
        <select name="grupo_id" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione...</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id', $horario->grupo_id ?? request('grupo_id')) === (string) $grupo->id)>
                    {{ $grupo->nombre }} · {{ $grupo->programa->nombre ?? 'Sin programa' }} · {{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Materia *</label>
        <select name="materia_id" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione...</option>
            @foreach($materias as $materia)
                <option value="{{ $materia->id }}" @selected((string) old('materia_id', $horario->materia_id ?? '') === (string) $materia->id)>
                    {{ $materia->nombre }}{{ $materia->clave ? ' · '.$materia->clave : '' }}{{ $materia->programa ? ' · '.$materia->programa->nombre : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Docente *</label>
        <select name="docente_id" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione...</option>
            @foreach($docentes as $docente)
                <option value="{{ $docente->id }}" @selected((string) old('docente_id', $horario->docente_id ?? request('docente_id')) === (string) $docente->id)>
                    {{ $docente->nombre_completo }} · {{ $docente->area_especialidad }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Día *</label>
        <select name="dia_semana" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione...</option>
            @foreach($dias as $dia)
                <option value="{{ $dia }}" @selected(old('dia_semana', $horario->dia_semana ?? '') === $dia)>{{ $dia }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Hora inicio *</label>
        <input type="time" name="hora_inicio" value="{{ old('hora_inicio', isset($horario) ? substr($horario->hora_inicio, 0, 5) : '') }}" required
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Hora fin *</label>
        <input type="time" name="hora_fin" value="{{ old('hora_fin', isset($horario) ? substr($horario->hora_fin, 0, 5) : '') }}" required
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Aula</label>
        <input type="text" name="aula" value="{{ old('aula', $horario->aula ?? '') }}" placeholder="Aula 1, Aula 5, Zoom, etc."
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad *</label>
        <select name="modalidad" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad }}" @selected(old('modalidad', $horario->modalidad ?? 'Presencial') === $modalidad)>{{ $modalidad }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha inicio</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', isset($horario) && $horario->fecha_inicio ? $horario->fecha_inicio->format('Y-m-d') : '') }}"
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha fin</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', isset($horario) && $horario->fecha_fin ? $horario->fecha_fin->format('Y-m-d') : '') }}"
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus *</label>
        <select name="estatus" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($estatuses as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $horario->estatus ?? 'Activo') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Solo los horarios activos bloquean choques de grupo, docente y aula.</p>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
        <textarea name="observaciones" rows="4" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('observaciones', $horario->observaciones ?? '') }}</textarea>
    </div>
</div>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('horarios_academicos.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</a>
    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow">Guardar horario</button>
</div>
