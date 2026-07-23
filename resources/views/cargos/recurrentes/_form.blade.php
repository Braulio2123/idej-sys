@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del plan *</label>
        <input type="text" name="nombre" value="{{ old('nombre', $plan->nombre ?? '') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Colegiatura mensual Maestría 4">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Concepto de pago *</label>
        <select name="concepto_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Selecciona un concepto</option>
            @foreach($conceptos as $concepto)
                <option value="{{ $concepto->id }}" @selected((string) old('concepto_id', $plan->concepto_id ?? '') === (string) $concepto->id)>
                    {{ $concepto->nombre }} · ${{ number_format($concepto->monto_base, 2) }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Si no capturas monto especial, se usará el monto base del concepto.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Monto especial</label>
        <input type="number" step="0.01" min="0" name="monto" value="{{ old('monto', $plan->monto ?? '') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Opcional">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Aplicar a *</label>
        <select name="alcance" id="alcance" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach(['grupo' => 'Un grupo específico', 'programa' => 'Todo un programa', 'todos' => 'Todos los alumnos activos'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('alcance', $plan->alcance ?? 'grupo') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div data-alcance-programa>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Programa</label>
        <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Selecciona programa</option>
            @foreach($programas as $programa)
                <option value="{{ $programa->id }}" @selected((string) old('programa_id', $plan->programa_id ?? '') === (string) $programa->id)>{{ $programa->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div data-alcance-grupo>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Grupo</label>
        <select name="grupo_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">Selecciona grupo</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id', $plan->grupo_id ?? '') === (string) $grupo->id)>
                    {{ $grupo->nombre }} · {{ $grupo->programa->nombre ?? 'Sin programa' }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Día de vencimiento *</label>
        <input type="number" min="1" max="28" name="dia_vencimiento" value="{{ old('dia_vencimiento', $plan->dia_vencimiento ?? 10) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        <p class="text-xs text-slate-500 mt-1">Se limita del 1 al 28 para evitar errores en febrero.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Frecuencia *</label>
        <select name="frecuencia_meses" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach([1 => 'Mensual', 2 => 'Cada 2 meses', 3 => 'Trimestral', 4 => 'Cada 4 meses', 6 => 'Semestral', 12 => 'Anual'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected((int) old('frecuencia_meses', $plan->frecuencia_meses ?? 1) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', isset($plan) ? $plan->fecha_inicio?->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha fin</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', isset($plan) ? $plan->fecha_fin?->format('Y-m-d') : '') }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción operativa</label>
        <textarea name="descripcion" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Colegiatura mensual generada automáticamente">{{ old('descripcion', $plan->descripcion ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-200 p-4">
        <input type="checkbox" name="activo" value="1" @checked(old('activo', $plan->activo ?? true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
        <div>
            <p class="text-sm font-semibold text-slate-700">Plan activo</p>
            <p class="text-xs text-slate-500">Si se desactiva, el sistema no generará nuevos cargos.</p>
        </div>
    </div>

    <div class="flex items-center gap-3 rounded-xl bg-blue-50 border border-blue-200 p-4">
        <input type="checkbox" name="enviar_recordatorio_email" value="1" @checked(old('enviar_recordatorio_email', $plan->enviar_recordatorio_email ?? true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
        <div>
            <p class="text-sm font-semibold text-blue-900">Incluir en recordatorios por correo</p>
            <p class="text-xs text-blue-700">El envío real depende de Configuración institucional y SMTP.</p>
        </div>
    </div>
</div>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('cargos.recurrentes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</a>
    <button class="px-6 py-2.5 rounded-xl bg-blue-700 text-white font-semibold hover:bg-blue-800">Guardar plan</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alcance = document.getElementById('alcance');
        const programa = document.querySelector('[data-alcance-programa]');
        const grupo = document.querySelector('[data-alcance-grupo]');

        function actualizar() {
            if (!alcance) return;
            programa.classList.toggle('hidden', alcance.value !== 'programa');
            grupo.classList.toggle('hidden', alcance.value !== 'grupo');
        }

        alcance?.addEventListener('change', actualizar);
        actualizar();
    });
</script>
