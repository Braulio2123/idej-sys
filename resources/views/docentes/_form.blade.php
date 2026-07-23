<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo *</label>
        <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $docente->nombre_completo ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email', $docente->email ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono', $docente->telefono ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Área de especialidad *</label>
        <input type="text" name="area_especialidad" value="{{ old('area_especialidad', $docente->area_especialidad ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
            <input type="text" name="rfc" maxlength="13" value="{{ old('rfc', $docente->rfc ?? '') }}" oninput="this.value = this.value.toUpperCase().slice(0, 13)" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-slate-500 mt-1">Máximo 13 caracteres.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Número de cuenta</label>
            <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $docente->numero_cuenta ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Banco</label>
            <input type="text" name="banco" value="{{ old('banco', $docente->banco ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <h2 class="font-bold text-slate-800 mb-3">Documentación para pagos docentes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Curriculum</label>
                <input type="file" name="curriculum" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Título y cédula del último grado</label>
                <input type="file" name="titulo_cedula" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Constancia de situación fiscal</label>
                <input type="file" name="constancia_fiscal" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-3">PDF, JPG o PNG. Máximo 5 MB por archivo. El domicilio no se solicita porque no es necesario para este flujo.</p>
    </div>
</div>
