@csrf

<div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 mb-5 text-sm text-indigo-900">
    <p class="font-bold">Educación Programática</p>
    <p class="mt-1">Este módulo corresponde a Licenciatura, Maestría y Doctorado. La duración se captura únicamente en semestres.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-slate-700">Clave interna</label>
        <input type="text" name="clave" value="{{ old('clave', $programa->clave) }}" placeholder="Ej. DER-LIC"
               class="mt-1 w-full rounded-xl border-slate-300 text-sm">
        @error('clave') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Nivel académico</label>
        <select name="nivel" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
            <option value="">Selecciona nivel</option>
            @foreach(['Licenciatura', 'Maestría', 'Doctorado'] as $nivel)
                <option value="{{ $nivel }}" @selected(old('nivel', $programa->nivel) === $nivel)>{{ $nivel }}</option>
            @endforeach
        </select>
        @error('nivel') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700">Nombre de Educación Programática *</label>
        <input type="text" name="nombre" value="{{ old('nombre', $programa->nombre) }}" required
               class="mt-1 w-full rounded-xl border-slate-300 text-sm">
        @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Modalidad</label>
        <select name="modalidad" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
            <option value="">No especificada</option>
            @foreach(['Presencial', 'Virtual', 'Mixta'] as $modalidad)
                <option value="{{ $modalidad }}" @selected(old('modalidad', $programa->modalidad) === $modalidad)>{{ $modalidad }}</option>
            @endforeach
        </select>
        @error('modalidad') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Duración en semestres</label>
        <input type="number" name="duracion_periodos" min="1" max="20" value="{{ old('duracion_periodos', $programa->duracion_periodos) }}" placeholder="Ej. 8"
               class="mt-1 w-full rounded-xl border-slate-300 text-sm">
        <p class="text-xs text-slate-500 mt-1">IDEJ trabaja Educación Programática únicamente por semestres.</p>
        @error('duracion_periodos') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700">Horario institucional</label>
        <div class="mt-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            Viernes de 05:00 p.m. a 09:00 p.m. y sábados de 08:00 a.m. a 01:00 p.m.
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700">Descripción operativa</label>
        <textarea name="descripcion" rows="3" class="mt-1 w-full rounded-xl border-slate-300 text-sm" placeholder="Notas académicas, requisitos generales o uso operativo de la Educación Programática.">{{ old('descripcion', $programa->descripcion) }}</textarea>
        @error('descripcion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $programa->activo ?? true)) class="rounded border-slate-300">
            Activa para nuevas operaciones
        </label>
        <p class="text-xs text-slate-500 mt-1">Si ya tiene historial, se recomienda inactivarla en vez de eliminarla.</p>
    </div>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-xl bg-red-50 border border-red-200 p-3 text-sm text-red-700">
        Revisa los datos marcados antes de guardar.
    </div>
@endif

<div class="flex justify-end gap-2 mt-6">
    <a href="{{ route('programas.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100">Cancelar</a>
    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">Guardar Educación Programática</button>
</div>
