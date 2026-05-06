@csrf

@php
    $sesionesIniciales = old('sesiones') ? array_values(array_filter(old('sesiones'), fn($s) => !empty($s['fecha'] ?? null))) : $sesionesSeleccionadas;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Materia *</label>
        <select name="materia_id" class="w-full rounded-xl border-slate-300">
            <option value="">Selecciona materia</option>
            @foreach($materias as $materia)
                <option value="{{ $materia->id }}" @selected((string) old('materia_id', $calendarioMateria->materia_id) === (string) $materia->id)>{{ $materia->nombre }}</option>
            @endforeach
        </select>
        @error('materia_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Docente *</label>
        <select name="docente_id" class="w-full rounded-xl border-slate-300">
            <option value="">Selecciona docente</option>
            @foreach($docentes as $docente)
                <option value="{{ $docente->id }}" @selected((string) old('docente_id', $calendarioMateria->docente_id) === (string) $docente->id)>{{ $docente->nombre_completo }}</option>
            @endforeach
        </select>
        @error('docente_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Orden *</label>
        <input type="number" name="orden" value="{{ old('orden', $calendarioMateria->orden ?: 1) }}" min="1" class="w-full rounded-xl border-slate-300">
        @error('orden') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus de materia *</label>
        <select name="estatus" class="w-full rounded-xl border-slate-300">
            @foreach($estatusesMateria as $estatus)
                <option value="{{ $estatus }}" @selected(old('estatus', $calendarioMateria->estatus ?: 'Programada') === $estatus)>{{ $estatus }}</option>
            @endforeach
        </select>
        @error('estatus') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Aula / liga general</label>
        <input type="text" name="aula" value="{{ old('aula', $aula) }}" class="w-full rounded-xl border-slate-300" placeholder="Aula, Zoom, Meet...">
        <p class="text-xs text-slate-500 mt-1">Se aplica a todas las sesiones de esta materia. Si una reposición cambia de aula/liga, se modifica desde la sesión reprogramada.</p>
        @error('aula') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Modalidad *</label>
        <select name="modalidad" class="w-full rounded-xl border-slate-300">
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad }}" @selected(old('modalidad', $modalidadSeleccionada) === $modalidad)>{{ $modalidad }}</option>
            @endforeach
        </select>
        @error('modalidad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo de sesión *</label>
        <select name="tipo_sesion" class="w-full rounded-xl border-slate-300">
            @foreach($tiposSesion as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo_sesion', $tipoSesionSeleccionado) === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
        @error('tipo_sesion') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-indigo-950">Planeador de fechas por calendario</h2>
            <p class="text-sm text-indigo-800 mt-1">
                Este calendario es <strong>{{ $calendario->tipo_calendario ?? 'Personalizado' }}</strong>. Solo permite elegir <strong>{{ $textoDiasPermitidosTipo }}</strong>. Las fechas bloqueadas por este mismo calendario no se pueden repetir en otra materia.
            </p>
        </div>
        <label class="inline-flex items-start gap-3 text-sm text-indigo-900 font-semibold bg-white/70 border border-indigo-200 rounded-xl p-3">
            <input id="usarReglasIdej" type="checkbox" name="usar_horario_idej" value="1" class="mt-1 rounded border-indigo-300" @checked(old('usar_horario_idej', true))>
            <span>
                Sugerir horarios IDEJ automáticamente
                <span class="block font-normal text-indigo-800 mt-1">
                    Viernes de posgrado 17:00-21:00; sábado 08:00-13:00; licenciatura matutina/vespertina de lunes a viernes. Puedes ajustar cada sesión.
                </span>
            </span>
        </label>
    </div>

    <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="rounded-xl bg-white/80 border border-indigo-200 p-3">
            <p class="text-xs font-bold text-indigo-900 uppercase">Rango de planeación visible</p>
            <p class="text-sm text-indigo-800 mt-1">{{ $rangoPlaneacionTexto }}</p>
            <p class="text-[11px] text-indigo-700 mt-1">El rango pertenece al calendario completo; no se reduce por las materias ya guardadas.</p>
        </div>
        <div class="rounded-xl bg-white/80 border border-indigo-200 p-3">
            <p class="text-xs font-bold text-indigo-900 uppercase">Regla de días</p>
            <p class="text-sm text-indigo-800 mt-1">{{ $textoDiasPermitidosTipo }}</p>
            <p class="text-[11px] text-indigo-700 mt-1">Para una reposición fuera del patrón, cancela/reprograma la sesión desde el detalle del calendario.</p>
        </div>
        <div class="rounded-xl bg-white/80 border border-indigo-200 p-3">
            <p class="text-xs font-bold text-indigo-900 uppercase mb-2">Horarios precargados</p>
            <div class="flex flex-col gap-2">
                <select id="bulkPreset" class="rounded-xl border-slate-300 text-sm">
                    <option value="">Selecciona un horario para aplicar</option>
                    @foreach($horariosPredefinidos as $horario)
                        <option value="{{ $horario['inicio'] }}|{{ $horario['fin'] }}">{{ $horario['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" id="applyBulkPreset" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Aplicar a seleccionadas</button>
            </div>
        </div>
    </div>

    @error('sesiones') <p class="text-sm text-red-600 mt-3">{{ $message }}</p> @enderror
    @error('fechas_texto') <p class="text-sm text-red-600 mt-3">{{ $message }}</p> @enderror

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 space-y-5">
            @foreach($mesesPlaneador as $mes)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-3">{{ $mes['titulo'] }}</h3>
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-slate-500 mb-2">
                        <div>L</div><div>M</div><div>M</div><div>J</div><div>V</div><div>S</div><div>D</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($mes['dias'] as $dia)
                            @if($dia === null)
                                <div class="h-20 rounded-xl bg-slate-50"></div>
                            @else
                                @php
                                    $fecha = $dia['fecha'];
                                    $baseCount = $conteosPorFecha[$fecha] ?? 0;
                                    $esNoLaboral = array_key_exists($fecha, $diasNoLaborales);
                                    $bloqueadaMismoCalendario = in_array($fecha, $fechasBloqueadasMismoCalendario ?? [], true);
                                    $diaNoPermitido = !in_array((int) $dia['dia_semana'], $diasPermitidosTipo, true);
                                @endphp

                                <button
                                    type="button"
                                    class="calendar-day relative h-20 rounded-xl border p-2 text-left transition hover:border-indigo-400 hover:bg-indigo-50
                                        {{ $esNoLaboral ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }}
                                        {{ $bloqueadaMismoCalendario ? 'border-red-300 bg-red-50 cursor-not-allowed opacity-80' : '' }}
                                        {{ $diaNoPermitido ? 'border-slate-200 bg-slate-100 cursor-not-allowed opacity-60' : '' }}"
                                    data-date="{{ $fecha }}"
                                    data-day="{{ $dia['dia_semana'] }}"
                                    data-base-count="{{ $baseCount }}"
                                    data-blocked-same-calendar="{{ $bloqueadaMismoCalendario ? '1' : '0' }}"
                                    data-disallowed-day="{{ $diaNoPermitido ? '1' : '0' }}"
                                    data-non-working="{{ $esNoLaboral ? '1' : '0' }}"
                                    data-non-working-name="{{ $esNoLaboral ? $diasNoLaborales[$fecha] : '' }}"
                                >
                                    <div class="flex justify-between items-start gap-1">
                                        <span class="font-bold text-slate-800">{{ $dia['dia'] }}</span>

                                        <span class="selected-mark hidden text-[10px] font-bold text-white bg-indigo-600 rounded-full px-1.5 py-0.5">Sel.</span>

                                        @if($bloqueadaMismoCalendario)
                                            <span class="blocked-mark text-[10px] font-bold text-white bg-red-600 rounded-full px-1.5 py-0.5">Ocup.</span>
                                        @elseif($diaNoPermitido)
                                            <span class="blocked-mark text-[10px] font-bold text-white bg-slate-500 rounded-full px-1.5 py-0.5">No</span>
                                        @endif
                                    </div>

                                    <div class="mt-2 space-y-1">
                                        <p class="count-label text-[11px] {{ $bloqueadaMismoCalendario ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                            {{ $baseCount }} clase{{ $baseCount == 1 ? '' : 's' }}
                                        </p>

                                        @if($bloqueadaMismoCalendario)
                                            <p class="text-[10px] font-semibold text-red-700 truncate">Ya usada aquí</p>
                                        @elseif($diaNoPermitido)
                                            <p class="text-[10px] font-semibold text-slate-600 truncate">No aplica</p>
                                        @endif

                                        @if($esNoLaboral)
                                            <p class="text-[10px] font-semibold text-amber-700 truncate">No laboral</p>
                                        @endif
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="xl:col-span-1">
            <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <h3 class="font-bold text-slate-800">Sesiones seleccionadas</h3>
                        <p class="text-xs text-slate-500 mt-1">Cada sesión puede tener horario diferente.</p>
                    </div>
                    <span id="selectedTotal" class="text-xs font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">0</span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="py-2 text-left">Fecha</th>
                                <th class="py-2 text-left">Horario rápido</th>
                                <th class="py-2 text-left">Inicio</th>
                                <th class="py-2 text-left">Fin</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="selectedSessionsBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>

                <p id="emptySessionsMessage" class="text-sm text-slate-500 text-center py-6">Aún no has seleccionado fechas.</p>

                <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
                    <p class="font-bold text-slate-700 mb-1">Lectura:</p>
                    <p><strong>No aplica</strong>: ese día no corresponde al tipo de calendario.</p>
                    <p class="mt-1"><strong>Ocup.</strong>: esa fecha ya pertenece a otra materia de este mismo calendario.</p>
                    <p class="mt-1"><strong>Conteo</strong>: solo muestra calendarios compatibles con este tipo, para no mezclar posgrado con licenciatura.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="calendarPreviewTooltip" class="hidden fixed z-50 w-80 max-w-[90vw] rounded-2xl border border-slate-200 bg-white shadow-xl p-3 text-xs text-slate-700 pointer-events-none"></div>

<div class="mt-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones de la materia</label>
    <textarea name="observaciones" rows="4" class="w-full rounded-xl border-slate-300" placeholder="Indicaciones de Coordinación Académica, aclaraciones, notas del docente...">{{ old('observaciones', $calendarioMateria->observaciones) }}</textarea>
    @error('observaciones') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
</div>

<div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
    <label class="inline-flex items-start gap-3 text-sm text-amber-900 font-semibold">
        <input type="checkbox" name="permitir_no_laboral" value="1" class="mt-1 rounded border-amber-300" @checked(old('permitir_no_laboral'))>
        <span>
            Autorizar excepción en día no laboral
            <span class="block font-normal text-amber-800 mt-1">
                Úsalo solo si Dirección/Coordinación Académica autorizó clase en un día inhábil. Esto no sirve para saltarse el patrón del calendario; las reposiciones fuera del patrón se hacen desde Reprogramar sesión.
            </span>
        </span>
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('calendarios_academicos.show', $calendario) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</a>
    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Guardar materia y fechas</button>
</div>

@php
    $jsTipoCalendario = $calendario->tipo_calendario ?? 'Personalizado';
    $jsInitialSessions = $sesionesIniciales ?? [];
    $jsBaseCounts = $conteosPorFecha ?? [];
    $jsDiasNoLaborales = $diasNoLaborales ?? [];
    $jsHorariosPredefinidos = $horariosPredefinidos ?? [];
    $jsPreviewSesionesPorFecha = $previewSesionesPorFecha ?? [];
    $jsFechasBloqueadasMismoCalendario = $fechasBloqueadasMismoCalendario ?? [];
    $jsDiasPermitidosTipo = $diasPermitidosTipo ?? [1, 2, 3, 4, 5, 6, 7];
    $jsTextoDiasPermitidos = $textoDiasPermitidosTipo ?? 'cualquier día';
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tipoCalendario = @json($jsTipoCalendario);
    const initialSessions = @json($jsInitialSessions);
    const baseCounts = @json($jsBaseCounts);
    const diasNoLaborales = @json($jsDiasNoLaborales);
    const horariosPredefinidos = @json($jsHorariosPredefinidos);
    const previewSesionesPorFecha = @json($jsPreviewSesionesPorFecha);
    const fechasBloqueadasMismoCalendario = new Set(@json($jsFechasBloqueadasMismoCalendario));
    const diasPermitidosRaw = @json($jsDiasPermitidosTipo);
    const diasPermitidos = new Set(diasPermitidosRaw.map(Number));
    const textoDiasPermitidos = @json($jsTextoDiasPermitidos);

    const tbody = document.getElementById('selectedSessionsBody');
    const selectedTotal = document.getElementById('selectedTotal');
    const emptyMessage = document.getElementById('emptySessionsMessage');
    const usarReglas = document.getElementById('usarReglasIdej');
    const bulkPreset = document.getElementById('bulkPreset');
    const applyBulkPreset = document.getElementById('applyBulkPreset');
    const tooltip = document.getElementById('calendarPreviewTooltip');
    const dayButtons = Array.from(document.querySelectorAll('.calendar-day'));

    let sessions = [];

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const dayName = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][date.getDay()];
    };

    const formatDate = (dateString) => {
        const [y, m, d] = dateString.split('-');
        return `${d}/${m}/${y}`;
    };

    const jsDayToIso = (jsDay) => jsDay === 0 ? 7 : jsDay;

    const isAllowedDate = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        return diasPermitidos.has(jsDayToIso(date.getDay()));
    };

    const defaultHours = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        const jsDay = date.getDay();

        if (tipoCalendario === 'Posgrado viernes-sábado') {
            if (jsDay === 5) return { inicio: '17:00', fin: '21:00' };
            if (jsDay === 6) return { inicio: '08:00', fin: '13:00' };
        }

        if (tipoCalendario === 'Licenciatura sabatina' && jsDay === 6) {
            return { inicio: '08:00', fin: '13:00' };
        }

        if (tipoCalendario === 'Licenciatura matutina' && [1,2,3,4,5].includes(jsDay)) {
            return { inicio: '09:00', fin: '12:00' };
        }

        if (tipoCalendario === 'Licenciatura vespertina' && [1,2,3,4,5].includes(jsDay)) {
            return { inicio: '18:00', fin: '21:00' };
        }

        return { inicio: '', fin: '' };
    };

    const presetOptionsHtml = (selectedInicio = '', selectedFin = '') => {
        const current = `${selectedInicio}|${selectedFin}`;
        const options = ['<option value="">Manual</option>'];

        horariosPredefinidos.forEach((horario) => {
            const value = `${horario.inicio}|${horario.fin}`;
            const selected = value === current ? 'selected' : '';
            options.push(`<option value="${value}" ${selected}>${escapeHtml(horario.label)}</option>`);
        });

        return options.join('');
    };

    const sortSessions = () => {
        sessions.sort((a, b) => `${a.fecha} ${a.hora_inicio || ''}`.localeCompare(`${b.fecha} ${b.hora_inicio || ''}`));
    };

    const findSessionIndex = (dateString) => sessions.findIndex((s) => s.fecha === dateString);

    const selectedCountByDate = () => sessions.reduce((acc, s) => {
        acc[s.fecha] = (acc[s.fecha] || 0) + 1;
        return acc;
    }, {});

    const previewHtml = (dateString) => {
        const items = previewSesionesPorFecha[dateString] || [];
        const selectedCount = sessions.filter((s) => s.fecha === dateString).length;
        const total = Number(baseCounts[dateString] || 0) + selectedCount;
        const allowed = isAllowedDate(dateString);

        let html = `
            <div class="font-bold text-slate-900">${formatDate(dateString)}</div>
            <div class="mt-1 text-slate-500">${total} clase${total === 1 ? '' : 's'} compatible${total === 1 ? '' : 's'} en esta fecha.</div>
        `;

        if (!allowed) {
            html += `<div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-700">No se puede seleccionar: este calendario solo permite ${escapeHtml(textoDiasPermitidos)}.</div>`;
        }

        if (!items.length && selectedCount === 0) {
            html += '<div class="mt-2 text-slate-500">No hay clases compatibles asignadas en esta fecha.</div>';
            return html;
        }

        if (items.length) {
            html += '<div class="mt-2 space-y-2 max-h-72 overflow-y-auto">';
            items.forEach((item) => {
                const badge = item.mismo_calendario
                    ? '<span class="inline-flex px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-bold text-[10px]">Mismo calendario</span>'
                    : '<span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">Otro calendario compatible</span>';

                html += `
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-900">${escapeHtml(item.materia)}</p>
                            ${badge}
                        </div>
                        <p class="mt-1 text-slate-600">${escapeHtml(item.docente)}</p>
                        <p class="mt-1 text-slate-500">${escapeHtml(item.horario)} · ${escapeHtml(item.aula)} · ${escapeHtml(item.modalidad)}</p>
                        <p class="mt-1 text-slate-500">${escapeHtml(item.calendario)}</p>
                        <p class="text-slate-500">${escapeHtml(item.grupo)}</p>
                    </div>
                `;
            });
            html += '</div>';
        }

        if (selectedCount > 0) {
            html += `<div class="mt-2 rounded-xl border border-indigo-200 bg-indigo-50 p-2 text-indigo-800">${selectedCount} sesión${selectedCount === 1 ? '' : 'es'} seleccionada${selectedCount === 1 ? '' : 's'} en esta captura.</div>`;
        }

        return html;
    };

    const showTooltip = (button, event) => {
        if (!tooltip) return;
        tooltip.innerHTML = previewHtml(button.dataset.date);
        tooltip.classList.remove('hidden');
        moveTooltip(event);
    };

    const moveTooltip = (event) => {
        if (!tooltip || tooltip.classList.contains('hidden')) return;

        const padding = 14;
        const tooltipWidth = tooltip.offsetWidth || 320;
        const tooltipHeight = tooltip.offsetHeight || 240;
        let left = event.clientX + padding;
        let top = event.clientY + padding;

        if (left + tooltipWidth > window.innerWidth - padding) left = event.clientX - tooltipWidth - padding;
        if (top + tooltipHeight > window.innerHeight - padding) top = event.clientY - tooltipHeight - padding;

        tooltip.style.left = `${Math.max(padding, left)}px`;
        tooltip.style.top = `${Math.max(padding, top)}px`;
    };

    const hideTooltip = () => tooltip?.classList.add('hidden');

    const updateCalendarVisuals = () => {
        const selectedCounts = selectedCountByDate();

        dayButtons.forEach((button) => {
            const date = button.dataset.date;
            const selectedCount = selectedCounts[date] || 0;
            const total = Number(baseCounts[date] || 0) + selectedCount;
            const blocked = fechasBloqueadasMismoCalendario.has(date) && selectedCount === 0;
            const disallowed = button.dataset.disallowedDay === '1';

            const mark = button.querySelector('.selected-mark');
            const label = button.querySelector('.count-label');

            button.classList.toggle('ring-2', selectedCount > 0);
            button.classList.toggle('ring-indigo-500', selectedCount > 0);
            button.classList.toggle('bg-indigo-100', selectedCount > 0);
            button.classList.toggle('cursor-not-allowed', blocked || disallowed);
            button.classList.toggle('opacity-80', blocked);
            button.classList.toggle('opacity-60', disallowed && selectedCount === 0);

            if (mark) mark.classList.toggle('hidden', selectedCount === 0);

            if (label) {
                label.textContent = blocked
                    ? `${total} clase${total === 1 ? '' : 's'} · ocupada`
                    : `${total} clase${total === 1 ? '' : 's'}`;
            }
        });
    };

    const renderRows = () => {
        sortSessions();
        tbody.innerHTML = '';

        sessions.forEach((session, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2 pr-2 align-top">
                    <input type="hidden" name="sesiones[${index}][fecha]" value="${escapeHtml(session.fecha)}">
                    <p class="font-bold text-slate-800">${formatDate(session.fecha)}</p>
                    <p class="text-[11px] text-slate-500">${dayName(session.fecha)}</p>
                    ${diasNoLaborales[session.fecha] ? `<p class="text-[10px] text-amber-700 font-semibold">${escapeHtml(diasNoLaborales[session.fecha])}</p>` : ''}
                </td>
                <td class="py-2 pr-2 align-top"><select class="w-36 rounded-lg border-slate-300 text-xs session-preset" data-index="${index}">${presetOptionsHtml(session.hora_inicio || '', session.hora_fin || '')}</select></td>
                <td class="py-2 pr-2 align-top"><input type="time" name="sesiones[${index}][hora_inicio]" value="${escapeHtml(session.hora_inicio || '')}" class="w-24 rounded-lg border-slate-300 text-xs session-time" data-index="${index}" data-field="hora_inicio"></td>
                <td class="py-2 pr-2 align-top"><input type="time" name="sesiones[${index}][hora_fin]" value="${escapeHtml(session.hora_fin || '')}" class="w-24 rounded-lg border-slate-300 text-xs session-time" data-index="${index}" data-field="hora_fin"></td>
                <td class="py-2 text-right align-top"><button type="button" class="remove-session text-red-600 font-bold hover:underline" data-index="${index}">Quitar</button></td>
            `;
            tbody.appendChild(tr);
        });

        selectedTotal.textContent = sessions.length;
        emptyMessage.classList.toggle('hidden', sessions.length > 0);
        updateCalendarVisuals();
    };

    dayButtons.forEach((button) => {
        button.addEventListener('mouseenter', (event) => showTooltip(button, event));
        button.addEventListener('mousemove', moveTooltip);
        button.addEventListener('mouseleave', hideTooltip);
        button.addEventListener('focus', (event) => showTooltip(button, event));
        button.addEventListener('blur', hideTooltip);

        button.addEventListener('click', () => {
            const fecha = button.dataset.date;
            const existing = findSessionIndex(fecha);

            if (existing >= 0) {
                sessions.splice(existing, 1);
                renderRows();
                return;
            }

            if (button.dataset.disallowedDay === '1') {
                alert(`Esta fecha no corresponde al tipo de calendario. Solo se permite seleccionar ${textoDiasPermitidos}.`);
                return;
            }

            if (fechasBloqueadasMismoCalendario.has(fecha)) {
                alert('Esta fecha ya está ocupada por otra materia dentro de este mismo calendario. Pasa el puntero sobre la fecha para ver qué materia la ocupa.');
                return;
            }

            const suggested = usarReglas.checked ? defaultHours(fecha) : { inicio: '', fin: '' };
            sessions.push({ fecha, hora_inicio: suggested.inicio, hora_fin: suggested.fin, observaciones: '' });
            renderRows();
        });
    });

    tbody.addEventListener('input', (event) => {
        if (!event.target.classList.contains('session-time')) return;
        const index = Number(event.target.dataset.index);
        const field = event.target.dataset.field;
        if (sessions[index]) sessions[index][field] = event.target.value;
    });

    tbody.addEventListener('change', (event) => {
        if (!event.target.classList.contains('session-preset')) return;
        const index = Number(event.target.dataset.index);
        if (!sessions[index] || !event.target.value) return;
        const [inicio, fin] = event.target.value.split('|');
        sessions[index].hora_inicio = inicio;
        sessions[index].hora_fin = fin;
        renderRows();
    });

    applyBulkPreset.addEventListener('click', () => {
        if (!bulkPreset.value || sessions.length === 0) return;
        const [inicio, fin] = bulkPreset.value.split('|');
        sessions = sessions.map((session) => ({ ...session, hora_inicio: inicio, hora_fin: fin }));
        renderRows();
    });

    tbody.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-session')) return;
        const index = Number(event.target.dataset.index);
        sessions.splice(index, 1);
        renderRows();
    });

    sessions = initialSessions.map((session) => {
        const suggested = defaultHours(session.fecha);
        return {
            fecha: session.fecha,
            hora_inicio: session.hora_inicio || suggested.inicio || '',
            hora_fin: session.hora_fin || suggested.fin || '',
            observaciones: session.observaciones || '',
        };
    });

    renderRows();
});
</script>
@endpush
