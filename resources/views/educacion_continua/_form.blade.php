@php
    $equipoSeleccionado = old('equipo_requerido', $curso->equipo_requerido ?? []);
    $planeadorEquipoSeleccionado = old('planeador_equipo_requerido', $curso->equipo_requerido ?? []);
    $diasSeleccionados = collect(old('planeador_dias', []))->map(fn ($d) => (int) $d)->all();
    $plantillasJson = $plantillasPlaneador ?? [];
    $horariosJson = $horariosPredefinidos ?? [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del curso *</label>
        <input type="text" name="nombre" value="{{ old('nombre', $curso->nombre) }}" required class="w-full rounded-xl border-slate-300" placeholder="Ej. Curso MASC 2026, Oratoria Jurídica, MasterClass...">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo *</label>
        <select name="tipo" required class="w-full rounded-xl border-slate-300">
            @foreach($tipos as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $curso->tipo) === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad *</label>
        <select name="modalidad" required class="w-full rounded-xl border-slate-300">
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad }}" @selected(old('modalidad', $curso->modalidad ?: 'Presencial') === $modalidad)>{{ $modalidad }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus *</label>
        <select name="estatus" required class="w-full rounded-xl border-slate-300">
            @foreach($estatuses as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $curso->estatus ?: 'Planeado') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Horas totales requeridas *</label>
        <input id="horasTotalesInput" type="number" step="0.5" min="0" name="horas_totales" value="{{ old('horas_totales', $curso->horas_totales ?? 0) }}" required class="w-full rounded-xl border-slate-300">
        <p class="text-xs text-slate-500 mt-1">El planeador generará sesiones hasta llegar o superar este total de horas.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Responsable</label>
        <select name="responsable_id" class="w-full rounded-xl border-slate-300">
            <option value="">Sin responsable</option>
            @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" @selected((string) old('responsable_id', $curso->responsable_id) === (string) $usuario->id)>{{ $usuario->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha inicio *</label>
        <input id="fechaInicioInput" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($curso->fecha_inicio)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300">
        <p class="text-xs text-slate-500 mt-1">Necesaria si vas a generar sesiones automáticas.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha fin</label>
        <input id="fechaFinInput" type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($curso->fecha_fin)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300">
        <p class="text-xs text-slate-500 mt-1">Opcional. Puedes limitar el planeador a esta fecha.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Cupo máximo</label>
        <input type="number" min="1" name="cupo_maximo" value="{{ old('cupo_maximo', $curso->cupo_maximo) }}" class="w-full rounded-xl border-slate-300">
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Costo</label>
        <input type="number" step="0.01" min="0" name="costo" value="{{ old('costo', $curso->costo) }}" class="w-full rounded-xl border-slate-300">
    </div>
</div>

<div class="mt-5">
    <label class="block text-sm font-semibold text-slate-700 mb-2">Equipo requerido del curso</label>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @foreach($equipos as $equipo)
            <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <input type="checkbox" name="equipo_requerido[]" value="{{ $equipo }}" @checked(in_array($equipo, $equipoSeleccionado ?? [], true))>
                <span>{{ $equipo }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-5" id="planeadorSesiones">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Planeador rápido de sesiones</h2>
            <p class="text-sm text-slate-600 mt-1">Selecciona días de la semana y horarios. El sistema calcula las fechas necesarias hasta cubrir las horas del curso antes de guardar.</p>
        </div>
        <label class="inline-flex items-center gap-2 rounded-xl bg-white border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-800">
            <input type="checkbox" name="planeador_generar_sesiones" id="generarSesionesCheckbox" value="1" @checked(old('planeador_generar_sesiones'))>
            Generar sesiones al guardar
        </label>
    </div>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Plantilla rápida</label>
                    <select id="plantillaPlaneador" class="w-full rounded-xl border-slate-300 bg-white">
                        <option value="">Personalizado</option>
                        @foreach($plantillasPlaneador as $key => $plantilla)
                            <option value="{{ $key }}">{{ $plantilla['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Docente IDEJ</label>
                    <select name="planeador_docente_id" class="w-full rounded-xl border-slate-300 bg-white">
                        <option value="">Sin docente del catálogo</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}" @selected((string) old('planeador_docente_id') === (string) $docente->id)>{{ $docente->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Expositor externo</label>
                    <input type="text" name="planeador_expositor_nombre" value="{{ old('planeador_expositor_nombre') }}" class="w-full rounded-xl border-slate-300 bg-white" placeholder="Opcional">
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="text-left px-3 py-2">Día</th>
                            <th class="text-left px-3 py-2">Horario rápido</th>
                            <th class="text-left px-3 py-2">Inicio</th>
                            <th class="text-left px-3 py-2">Fin</th>
                            <th class="text-left px-3 py-2">Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diasSemana as $numeroDia => $nombreDia)
                            @php
                                $oldInicio = old("planeador_horarios.$numeroDia.hora_inicio", '');
                                $oldFin = old("planeador_horarios.$numeroDia.hora_fin", '');
                            @endphp
                            <tr class="border-b last:border-b-0 day-row" data-day="{{ $numeroDia }}">
                                <td class="px-3 py-3">
                                    <label class="inline-flex items-center gap-2 font-semibold text-slate-700">
                                        <input type="checkbox" name="planeador_dias[]" value="{{ $numeroDia }}" class="day-checkbox rounded border-slate-300" @checked(in_array($numeroDia, $diasSeleccionados, true))>
                                        {{ $nombreDia }}
                                    </label>
                                </td>
                                <td class="px-3 py-3">
                                    <select class="preset-select w-48 rounded-lg border-slate-300 text-xs bg-white">
                                        <option value="">Manual</option>
                                        @foreach($horariosPredefinidos as $horario)
                                            <option value="{{ $horario['inicio'] }}|{{ $horario['fin'] }}" @selected($oldInicio === $horario['inicio'] && $oldFin === $horario['fin'])>{{ $horario['label'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-3">
                                    <input type="time" name="planeador_horarios[{{ $numeroDia }}][hora_inicio]" value="{{ $oldInicio }}" class="time-start w-28 rounded-lg border-slate-300 text-xs bg-white">
                                </td>
                                <td class="px-3 py-3">
                                    <input type="time" name="planeador_horarios[{{ $numeroDia }}][hora_fin]" value="{{ $oldFin }}" class="time-end w-28 rounded-lg border-slate-300 text-xs bg-white">
                                </td>
                                <td class="px-3 py-3 text-slate-600"><span class="duration-label">0.00h</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Aula / liga para las sesiones</label>
                    <input type="text" name="planeador_aula_liga" value="{{ old('planeador_aula_liga') }}" class="w-full rounded-xl border-slate-300 bg-white" placeholder="Ej. Aula 3, Zoom, Auditorio...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad de sesiones</label>
                    <select name="planeador_modalidad" class="w-full rounded-xl border-slate-300 bg-white">
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad }}" @selected(old('planeador_modalidad', $curso->modalidad ?: 'Presencial') === $modalidad)>{{ $modalidad }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus inicial</label>
                    <select name="planeador_estatus" class="w-full rounded-xl border-slate-300 bg-white">
                        @foreach($estatusesSesion as $estatus)
                            <option value="{{ $estatus }}" @selected(old('planeador_estatus', 'Programada') === $estatus)>{{ $estatus }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-3 py-2 text-sm text-slate-700">
                        <input type="checkbox" name="planeador_limitar_a_fecha_fin" id="limitarFechaFin" value="1" @checked(old('planeador_limitar_a_fecha_fin'))>
                        Limitar generación a la fecha fin capturada
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Equipo requerido por sesión</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($equipos as $equipo)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                            <input type="checkbox" name="planeador_equipo_requerido[]" value="{{ $equipo }}" @checked(in_array($equipo, $planeadorEquipoSeleccionado ?? [], true))>
                            <span>{{ $equipo }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <textarea name="planeador_observaciones" rows="2" class="w-full rounded-xl border-slate-300 bg-white" placeholder="Observaciones que se copiarán a las sesiones generadas">{{ old('planeador_observaciones') }}</textarea>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl bg-white border border-slate-200 p-4">
                <h3 class="font-bold text-slate-900">Resumen antes de guardar</h3>
                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Sesiones</p>
                        <p id="previewSesionesTotal" class="text-2xl font-bold text-indigo-700">0</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Horas</p>
                        <p id="previewHorasTotal" class="text-2xl font-bold text-blue-700">0.00h</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Objetivo</p>
                        <p id="previewHorasObjetivo" class="text-2xl font-bold text-slate-800">0.00h</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Diferencia</p>
                        <p id="previewDiferencia" class="text-2xl font-bold text-amber-700">0.00h</p>
                    </div>
                </div>
                <div id="previewAlert" class="mt-3 text-xs rounded-xl border p-3 bg-slate-50 text-slate-600">
                    Configura fechas, días y horarios para ver el resumen.
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-4 max-h-[520px] overflow-y-auto">
                <h3 class="font-bold text-slate-900 mb-3">Fechas que se generarán</h3>
                <div id="previewLista" class="space-y-2 text-xs text-slate-600">
                    <p class="text-slate-500">Aún no hay fechas calculadas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones generales del curso</label>
    <textarea name="observaciones" rows="4" class="w-full rounded-xl border-slate-300" placeholder="Notas de operación, requisitos, reglas del curso, indicaciones para sistemas...">{{ old('observaciones', $curso->observaciones) }}</textarea>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const plantillas = @json($plantillasJson);
    const horariosPredefinidos = @json($horariosJson);
    const diasSemana = @json($diasSemana);

    const horasTotalesInput = document.getElementById('horasTotalesInput');
    const fechaInicioInput = document.getElementById('fechaInicioInput');
    const fechaFinInput = document.getElementById('fechaFinInput');
    const limitarFechaFin = document.getElementById('limitarFechaFin');
    const plantillaSelect = document.getElementById('plantillaPlaneador');
    const rows = Array.from(document.querySelectorAll('.day-row'));

    const previewSesionesTotal = document.getElementById('previewSesionesTotal');
    const previewHorasTotal = document.getElementById('previewHorasTotal');
    const previewHorasObjetivo = document.getElementById('previewHorasObjetivo');
    const previewDiferencia = document.getElementById('previewDiferencia');
    const previewAlert = document.getElementById('previewAlert');
    const previewLista = document.getElementById('previewLista');

    const toMinutes = (time) => {
        if (!time || !time.includes(':')) return null;
        const [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    };

    const durationHours = (start, end) => {
        const s = toMinutes(start);
        const e = toMinutes(end);
        if (s === null || e === null || e <= s) return 0;
        return Math.round(((e - s) / 60) * 100) / 100;
    };

    const formatDate = (date) => {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}/${m}/${y}`;
    };

    const isoDate = (date) => date.toISOString().slice(0, 10);

    const isoDay = (date) => {
        const js = date.getDay();
        return js === 0 ? 7 : js;
    };

    const rowData = () => rows.map((row) => {
        const day = Number(row.dataset.day);
        const checkbox = row.querySelector('.day-checkbox');
        const start = row.querySelector('.time-start').value;
        const end = row.querySelector('.time-end').value;
        const duration = durationHours(start, end);
        row.querySelector('.duration-label').textContent = duration.toFixed(2) + 'h';
        return { day, checked: checkbox.checked, start, end, duration };
    }).filter((item) => item.checked);

    const setAlert = (type, message) => {
        previewAlert.className = 'mt-3 text-xs rounded-xl border p-3 ' + (
            type === 'ok' ? 'bg-green-50 text-green-700 border-green-200' :
            type === 'warn' ? 'bg-amber-50 text-amber-700 border-amber-200' :
            type === 'error' ? 'bg-red-50 text-red-700 border-red-200' :
            'bg-slate-50 text-slate-600 border-slate-200'
        );
        previewAlert.textContent = message;
    };

    const calculatePreview = () => {
        const objetivo = Number(horasTotalesInput.value || 0);
        const startValue = fechaInicioInput.value;
        const endValue = fechaFinInput.value;
        const selectedRows = rowData();

        previewHorasObjetivo.textContent = objetivo.toFixed(2) + 'h';
        previewSesionesTotal.textContent = '0';
        previewHorasTotal.textContent = '0.00h';
        previewDiferencia.textContent = '0.00h';
        previewLista.innerHTML = '<p class="text-slate-500">Aún no hay fechas calculadas.</p>';

        if (!startValue) {
            setAlert('warn', 'Captura una fecha de inicio para calcular sesiones.');
            return;
        }

        if (objetivo <= 0) {
            setAlert('warn', 'Captura las horas totales requeridas del curso.');
            return;
        }

        if (selectedRows.length === 0) {
            setAlert('warn', 'Selecciona al menos un día de la semana.');
            return;
        }

        const invalid = selectedRows.find((item) => item.duration <= 0);
        if (invalid) {
            setAlert('error', 'Todos los días seleccionados deben tener un horario válido.');
            return;
        }

        let current = new Date(startValue + 'T00:00:00');
        let limit = limitarFechaFin.checked && endValue
            ? new Date(endValue + 'T00:00:00')
            : new Date(current.getFullYear() + 2, current.getMonth(), current.getDate());

        if (limit < current) {
            setAlert('error', 'La fecha fin no puede ser anterior a la fecha inicio.');
            return;
        }

        const byDay = Object.fromEntries(selectedRows.map((item) => [item.day, item]));
        const sessions = [];
        let hours = 0;
        let safety = 0;

        while (current <= limit && hours < objetivo && safety < 800) {
            const day = isoDay(current);
            if (byDay[day]) {
                const item = byDay[day];
                sessions.push({
                    date: new Date(current),
                    day,
                    start: item.start,
                    end: item.end,
                    duration: item.duration,
                });
                hours += item.duration;
            }
            current.setDate(current.getDate() + 1);
            safety++;
        }

        previewSesionesTotal.textContent = sessions.length;
        previewHorasTotal.textContent = hours.toFixed(2) + 'h';
        previewDiferencia.textContent = (hours - objetivo).toFixed(2) + 'h';

        if (!sessions.length) {
            setAlert('error', 'No se encontraron fechas con los días seleccionados dentro del rango.');
            return;
        }

        if (hours < objetivo) {
            setAlert('warn', `Con el rango actual solo se programan ${hours.toFixed(2)}h de ${objetivo.toFixed(2)}h. Amplía el periodo, agrega días o aumenta la duración.`);
        } else if (hours > objetivo) {
            setAlert('warn', `El plan cubre ${hours.toFixed(2)}h. Excede el objetivo por ${(hours - objetivo).toFixed(2)}h porque la última sesión completa rebasa el total.`);
        } else {
            setAlert('ok', 'El plan cubre exactamente las horas requeridas.');
        }

        previewLista.innerHTML = sessions.map((session, index) => `
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-bold text-slate-800">${index + 1}. ${formatDate(session.date)}</p>
                    <span class="text-[11px] rounded-full bg-indigo-100 text-indigo-700 px-2 py-0.5 font-semibold">${diasSemana[session.day]}</span>
                </div>
                <p class="mt-1 text-slate-600">${session.start} - ${session.end} · ${session.duration.toFixed(2)}h</p>
            </div>
        `).join('');
    };

    rows.forEach((row) => {
        const preset = row.querySelector('.preset-select');
        const start = row.querySelector('.time-start');
        const end = row.querySelector('.time-end');
        const checkbox = row.querySelector('.day-checkbox');

        preset.addEventListener('change', () => {
            if (!preset.value) {
                calculatePreview();
                return;
            }
            const [inicio, fin] = preset.value.split('|');
            start.value = inicio;
            end.value = fin;
            checkbox.checked = true;
            calculatePreview();
        });

        [start, end, checkbox].forEach((el) => el.addEventListener('input', calculatePreview));
        [start, end, checkbox].forEach((el) => el.addEventListener('change', calculatePreview));
    });

    plantillaSelect.addEventListener('change', () => {
        const plantilla = plantillas[plantillaSelect.value];
        if (!plantilla) return;

        rows.forEach((row) => {
            const day = Number(row.dataset.day);
            const checked = plantilla.dias.includes(day);
            const checkbox = row.querySelector('.day-checkbox');
            const start = row.querySelector('.time-start');
            const end = row.querySelector('.time-end');
            const preset = row.querySelector('.preset-select');

            checkbox.checked = checked;
            if (checked && plantilla.horarios[day]) {
                start.value = plantilla.horarios[day].inicio;
                end.value = plantilla.horarios[day].fin;
                const presetValue = `${start.value}|${end.value}`;
                preset.value = Array.from(preset.options).some((option) => option.value === presetValue) ? presetValue : '';
            }
        });

        calculatePreview();
    });

    [horasTotalesInput, fechaInicioInput, fechaFinInput, limitarFechaFin].forEach((el) => {
        el.addEventListener('input', calculatePreview);
        el.addEventListener('change', calculatePreview);
    });

    calculatePreview();
});
</script>
@endpush
