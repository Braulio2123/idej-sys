@php
    $esEdicion = $prospecto->exists;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
    <div class="xl:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $prospecto->nombre_completo) }}" required maxlength="255"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="Nombre del prospecto">
        @error('nombre_completo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
        <input type="email" name="correo" value="{{ old('correo', $prospecto->correo) }}" maxlength="255"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="correo@ejemplo.com">
        @error('correo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
        <input type="text" name="telefono" value="{{ old('telefono', $prospecto->telefono) }}" maxlength="30"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="Teléfono fijo o celular">
        @error('telefono') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $prospecto->whatsapp) }}" maxlength="30"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="WhatsApp de contacto">
        @error('whatsapp') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Programa de interés</label>
        <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Sin programa específico</option>
            @foreach($programas as $programa)
                <option value="{{ $programa->id }}" @selected((string) old('programa_id', $prospecto->programa_id) === (string) $programa->id)>
                    {{ $programa->nombre }} {{ $programa->nivel ? '— '.$programa->nivel : '' }}
                </option>
            @endforeach
        </select>
        @error('programa_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nivel de interés</label>
        <select name="nivel_interes" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Sin especificar</option>
            @foreach(['Licenciatura', 'Maestría', 'Doctorado', 'Diplomado', 'Curso'] as $nivel)
                <option value="{{ $nivel }}" @selected(old('nivel_interes', $prospecto->nivel_interes) === $nivel)>{{ $nivel }}</option>
            @endforeach
        </select>
        @error('nivel_interes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Medio de contacto</label>
        <select name="medio_contacto" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Sin especificar</option>
            @foreach($mediosContacto as $medio)
                <option value="{{ $medio }}" @selected(old('medio_contacto', $prospecto->medio_contacto) === $medio)>{{ $medio }}</option>
            @endforeach
        </select>
        @error('medio_contacto') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Origen / campaña</label>
        <input type="text" name="origen" value="{{ old('origen', $prospecto->origen) }}" maxlength="120"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="Ej. Facebook Ads, referido, feria, convenio">
        @error('origen') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Asesor responsable</label>
        <select name="asesor_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Sin asignar</option>
            @foreach($asesores as $asesor)
                <option value="{{ $asesor->id }}" @selected((string) old('asesor_id', $prospecto->asesor_id) === (string) $asesor->id)>
                    {{ $asesor->nombre }} {{ $asesor->rol ? '— '.$asesor->rol->nombre : '' }}
                </option>
            @endforeach
        </select>
        @error('asesor_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus *</label>
        <select name="estatus" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach($estatusDisponibles as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $prospecto->estatus ?? 'Nuevo') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        @error('estatus') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad *</label>
        <select name="prioridad" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach($prioridades as $prioridad)
                <option value="{{ $prioridad }}" @selected(old('prioridad', $prospecto->prioridad ?? 'Normal') === $prioridad)>{{ $prioridad }}</option>
            @endforeach
        </select>
        @error('prioridad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de contacto</label>
        <input type="datetime-local" name="fecha_contacto"
               value="{{ old('fecha_contacto', optional($prospecto->fecha_contacto)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('fecha_contacto') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Próximo contacto</label>
        <input type="datetime-local" name="fecha_proximo_contacto"
               value="{{ old('fecha_proximo_contacto', optional($prospecto->fecha_proximo_contacto)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('fecha_proximo_contacto') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
        <textarea name="observaciones" rows="5" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Notas generales del interés, necesidades, dudas, programa o situación del prospecto.">{{ old('observaciones', $prospecto->observaciones) }}</textarea>
        @error('observaciones') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de descarte</label>
        <textarea name="motivo_descarte" rows="5" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Capturar solo si el prospecto queda descartado.">{{ old('motivo_descarte', $prospecto->motivo_descarte) }}</textarea>
        @error('motivo_descarte') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-end gap-3">
    <a href="{{ $esEdicion ? route('prospectos.show', $prospecto) : route('prospectos.index') }}"
       class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold text-center">
        Cancelar
    </a>

    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow">
        {{ $esEdicion ? 'Actualizar prospecto' : 'Guardar prospecto' }}
    </button>
</div>
