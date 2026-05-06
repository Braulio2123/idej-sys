@csrf

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg shadow-sm">
        <p class="font-semibold mb-2">Revisa los datos capturados:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo de documento</label>
        <select name="tipo_documento" required class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
            @foreach($tiposDocumento as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo_documento', $requisito->tipo_documento) === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Programa específico</label>
        <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
            <option value="">General o por nivel</option>
            @foreach($programas as $programa)
                <option value="{{ $programa->id }}" @selected((string) old('programa_id', $requisito->programa_id) === (string) $programa->id)>
                    {{ $programa->nombre }}{{ $programa->nivel ? ' · '.$programa->nivel : '' }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Si eliges un programa, el nivel se ignora.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nivel académico</label>
        <select name="nivel" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
            <option value="">General para todos</option>
            @foreach($niveles as $nivel)
                <option value="{{ $nivel }}" @selected(old('nivel', $requisito->nivel) === $nivel)>{{ $nivel }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Orden</label>
        <input type="number" name="orden" min="0" max="999" value="{{ old('orden', $requisito->orden ?? 0) }}" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción / criterio de revisión</label>
        <textarea name="descripcion" rows="4" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Ej. Debe ser legible, vigente y coincidir con el nombre del alumno.">{{ old('descripcion', $requisito->descripcion) }}</textarea>
    </div>

    <div class="flex flex-wrap gap-5 md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="obligatorio" value="0">
            <input type="checkbox" name="obligatorio" value="1" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('obligatorio', $requisito->obligatorio ?? true))>
            Documento obligatorio
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('activo', $requisito->activo ?? true))>
            Activo
        </label>
    </div>
</div>

<div class="flex justify-end gap-2 mt-6">
    <a href="{{ route('requisitos_documentales.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-semibold hover:bg-slate-300">
        Cancelar
    </a>
    <button class="px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-semibold hover:bg-cyan-700 shadow">
        Guardar requisito
    </button>
</div>
