@csrf

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
        <p class="font-semibold mb-2">Revisa los siguientes puntos:</p>
        <ul class="list-disc ml-5 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de la materia *</label>
        <input type="text" name="nombre" value="{{ old('nombre', $materia->nombre ?? '') }}" required
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Clave</label>
        <input type="text" name="clave" value="{{ old('clave', $materia->clave ?? '') }}"
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Programa</label>
        <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">General / sin programa específico</option>
            @foreach($programas as $programa)
                <option value="{{ $programa->id }}" @selected((string) old('programa_id', $materia->programa_id ?? '') === (string) $programa->id)>
                    {{ $programa->nombre }}{{ $programa->nivel ? ' · '.$programa->nivel : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nivel</label>
        <input type="text" name="nivel" value="{{ old('nivel', $materia->nivel ?? '') }}" placeholder="Licenciatura, Maestría, Doctorado..."
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Semestre / cuatrimestre</label>
        <input type="number" min="1" max="12" name="semestre_o_cuatrimestre" value="{{ old('semestre_o_cuatrimestre', $materia->semestre_o_cuatrimestre ?? '') }}"
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Créditos</label>
        <input type="number" min="0" max="99" name="creditos" value="{{ old('creditos', $materia->creditos ?? '') }}"
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Horas teóricas</label>
        <input type="number" min="0" max="99" name="horas_teoricas" value="{{ old('horas_teoricas', $materia->horas_teoricas ?? 0) }}" required
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Horas prácticas</label>
        <input type="number" min="0" max="99" name="horas_practicas" value="{{ old('horas_practicas', $materia->horas_practicas ?? 0) }}" required
               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus *</label>
        <select name="estatus" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            @foreach(['Activa', 'Inactiva'] as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $materia->estatus ?? 'Activa') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción / objetivo</label>
        <textarea name="descripcion" rows="4" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $materia->descripcion ?? '') }}</textarea>
    </div>
</div>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('materias.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</a>
    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow">Guardar materia</button>
</div>
