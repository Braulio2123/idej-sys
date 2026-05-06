@csrf

<div class="mb-4">
    <label class="block text-gray-700 font-semibold">Nombre del Programa</label>
    <input type="text" name="nombre"
           value="{{ old('nombre', $programa->nombre) }}"
           class="w-full border border-gray-300 rounded p-2 focus:ring-indigo-500 focus:border-indigo-500"
           required>
</div>

<div class="mb-4">
    <label class="block text-gray-700 font-semibold">Nivel académico</label>
    <input type="text" name="nivel"
           value="{{ old('nivel', $programa->nivel) }}"
           placeholder="Ej. Licenciatura, Maestría, Doctorado"
           class="w-full border border-gray-300 rounded p-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex justify-end gap-2 mt-4">
    <a href="{{ route('programas.index') }}"
       class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100">
        Cancelar
    </a>

    <button type="submit"
            class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
        Guardar
    </button>
</div>
