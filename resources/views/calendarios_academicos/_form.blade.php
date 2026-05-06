@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del calendario *</label>
        <input type="text" name="nombre" value="{{ old('nombre', $calendario->nombre) }}" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. Calendario 2026 A - Maestría 4 RM">
        @error('nombre') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Grupo *</label>
        <select name="grupo_id" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Selecciona un grupo</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id', $calendario->grupo_id) === (string) $grupo->id)>
                    {{ $grupo->nombre }} — {{ $grupo->programa->nombre ?? 'Sin programa' }}
                </option>
            @endforeach
        </select>
        @error('grupo_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Ciclo escolar</label>
        <select name="ciclo_escolar_id" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Sin ciclo específico</option>
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->id }}" @selected((string) old('ciclo_escolar_id', $calendario->ciclo_escolar_id) === (string) $ciclo->id)>{{ $ciclo->nombre }}</option>
            @endforeach
        </select>
        @error('ciclo_escolar_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Periodo</label>
        <input type="text" name="periodo" value="{{ old('periodo', $calendario->periodo) }}" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. 2026 A">
        @error('periodo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>


    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo de calendario IDEJ *</label>
        <select name="tipo_calendario" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($tiposCalendario as $tipoCalendario)
                <option value="{{ $tipoCalendario }}" @selected(old('tipo_calendario', $calendario->tipo_calendario ?? 'Personalizado') === $tipoCalendario)>{{ $tipoCalendario }}</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Este catálogo está fijo en el sistema para calendarios principales del IDEJ. Controla días permitidos, conteos compatibles y horarios sugeridos. Cursos, masterclass, MASC y oratoria deben manejarse después en Educación Continua, no mezclados con este calendario principal.</p>
        @error('tipo_calendario') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad *</label>
        <select name="modalidad" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad }}" @selected(old('modalidad', $calendario->modalidad ?? 'Presencial') === $modalidad)>{{ $modalidad }}</option>
            @endforeach
        </select>
        @error('modalidad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus *</label>
        <select name="estatus" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($estatuses as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $calendario->estatus ?? 'Borrador') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        @error('estatus') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha inicio</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($calendario->fecha_inicio)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('fecha_inicio') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha fin</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($calendario->fecha_fin)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('fecha_fin') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
    <textarea name="observaciones" rows="4" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Notas del grupo, condiciones de apertura, indicaciones de coordinación académica...">{{ old('observaciones', $calendario->observaciones) }}</textarea>
    @error('observaciones') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('calendarios_academicos.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</a>
    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Guardar calendario</button>
</div>
