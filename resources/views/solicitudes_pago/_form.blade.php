@php
    use App\Models\SolicitudPagoDocente;

    $isEdit = $solicitud->exists;
    $selectedOrigen = old('origen', $solicitud->origen ?? SolicitudPagoDocente::ORIGEN_CALENDARIO);
    $puedeCalcularPago = $puedeCalcularPago ?? false;
    $calendarioSesionesSeleccionadas = array_map('intval', old('calendario_sesion_ids', $solicitud->calendario_sesion_ids ?? []));
    $cursoSesionesSeleccionadas = array_map('intval', old('curso_sesion_ids', $solicitud->curso_sesion_ids ?? ($solicitud->curso_sesion_id ? [$solicitud->curso_sesion_id] : [])));

    $formatearFechaSolicitud = function ($fecha) {
        if (! $fecha) return null;
        try { return \Carbon\Carbon::parse($fecha)->format('Y-m-d'); } catch (\Throwable) { return null; }
    };

    $calcularHorasSesion = function ($sesion) {
        try {
            if (! $sesion->hora_inicio || ! $sesion->hora_fin) return 0;
            $inicio = \Carbon\Carbon::parse($sesion->hora_inicio);
            $fin = \Carbon\Carbon::parse($sesion->hora_fin);
            return $fin->greaterThan($inicio) ? round($inicio->diffInMinutes($fin) / 60, 2) : 0;
        } catch (\Throwable) { return 0; }
    };

    $calendarioMateriasPayload = $calendarioMaterias->map(function ($cm) use ($formatearFechaSolicitud, $calcularHorasSesion) {
        $cal = $cm->calendario;
        $grupo = $cal?->grupo;
        $programa = $grupo?->programa;
        $ciclo = $grupo?->cicloEscolar ?? $cal?->cicloEscolar;
        $sesiones = $cm->sesiones->whereNotIn('estatus', [\App\Models\CalendarioSesion::ESTATUS_CANCELADA, \App\Models\CalendarioSesion::ESTATUS_SUSPENDIDA])->values();
        $horas = round($sesiones->sum(fn ($sesion) => $calcularHorasSesion($sesion)), 2);
        $primera = $sesiones->sortBy('fecha')->first();
        $ultima = $sesiones->sortByDesc('fecha')->first();

        return [
            'id' => $cm->id,
            'docente_id' => $cm->docente_id,
            'nivel' => $programa?->nivel ?? $cm->materia?->nivel ?? '',
            'programa_grupo' => trim(($programa?->nombre ?? '').($grupo?->nombre ? ' · '.$grupo->nombre : '')),
            'periodo' => $cal?->periodo ?? $ciclo?->nombre ?? '',
            'materia_actividad' => $cm->nombre_materia,
            'modalidad' => $cal?->modalidad ?? '',
            'numero_sesiones' => $sesiones->count() ?: null,
            'horas_totales' => $horas,
            'fecha_inicio_periodo' => $formatearFechaSolicitud($primera?->fecha ?? $cal?->fecha_inicio),
            'fecha_fin_periodo' => $formatearFechaSolicitud($ultima?->fecha ?? $cal?->fecha_fin),
            'sesiones' => $sesiones->map(fn ($sesion) => [
                'id' => $sesion->id,
                'label' => ($sesion->fecha ? \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') : 'Sin fecha').' · '.substr((string) $sesion->hora_inicio, 0, 5).' - '.substr((string) $sesion->hora_fin, 0, 5),
                'horas' => $calcularHorasSesion($sesion),
                'fecha' => $formatearFechaSolicitud($sesion->fecha),
            ])->values(),
        ];
    })->values();

    $cursosPayload = $cursos->map(fn ($curso) => [
        'id' => $curso->id,
        'nivel' => 'Educación continua',
        'programa_grupo' => trim(($curso->tipo ?? 'Curso').' · '.($curso->nombre ?? '')),
        'periodo' => trim(($curso->fecha_inicio ? \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') : '').' - '.($curso->fecha_fin ? \Carbon\Carbon::parse($curso->fecha_fin)->format('d/m/Y') : '')),
        'materia_actividad' => $curso->nombre,
        'modalidad' => $curso->modalidad ?? '',
        'numero_sesiones' => $curso->sesiones_count ?? null,
        'horas_totales' => $curso->horas_totales,
        'fecha_inicio_periodo' => $formatearFechaSolicitud($curso->fecha_inicio),
        'fecha_fin_periodo' => $formatearFechaSolicitud($curso->fecha_fin),
    ])->values();

    $cursoSesionesPayload = $cursoSesiones->map(fn ($sesion) => [
        'id' => $sesion->id,
        'curso_id' => $sesion->curso_id,
        'docente_id' => $sesion->docente_id,
        'nivel' => 'Educación continua',
        'programa_grupo' => trim(($sesion->curso?->tipo ?? 'Curso').' · '.($sesion->curso?->nombre ?? '')),
        'periodo' => $sesion->fecha ? \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') : '',
        'materia_actividad' => trim(($sesion->curso?->nombre ?? 'Sesión de educación continua').' · '.$sesion->expositor),
        'modalidad' => $sesion->modalidad ?? $sesion->curso?->modalidad ?? '',
        'numero_sesiones' => 1,
        'horas_totales' => $sesion->duracion_horas ?: $sesion->calcularDuracion(),
        'fecha_inicio_periodo' => $formatearFechaSolicitud($sesion->fecha),
        'fecha_fin_periodo' => $formatearFechaSolicitud($sesion->fecha),
    ])->values();
@endphp

@if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-5">
        <p class="font-bold mb-1">Revisa la información:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('solicitudes_pago.update', $solicitud) : route('solicitudes_pago.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">1. Datos del docente y origen del servicio</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Docente *</label>
                <select name="docente_id" id="docente_id" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Selecciona docente</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}" @selected(old('docente_id', $solicitud->docente_id) == $docente->id)>{{ $docente->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Origen *</label>
                <select name="origen" id="origen" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    @foreach($origenes as $origen)
                        <option value="{{ $origen }}" @selected($selectedOrigen === $origen)>{{ $origen }}</option>
                    @endforeach
                </select>
            </div>
            <div id="bloqueCalendario" class="md:col-span-2 {{ $selectedOrigen === SolicitudPagoDocente::ORIGEN_CALENDARIO ? '' : 'hidden' }}">
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Materia de Educación Programática relacionada</label>
                <select name="calendario_materia_id" id="calendario_materia_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Selecciona materia</option>
                    @foreach($calendarioMaterias as $cm)
                        @php
                            $cal = $cm->calendario; $grupo = $cal?->grupo; $programa = $grupo?->programa;
                            $label = ($cal?->nombre ?? 'Calendario').' · '.($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo').' · '.$cm->nombre_materia.' · '.$cm->nombre_docente;
                        @endphp
                        <option value="{{ $cm->id }}" @selected(old('calendario_materia_id', $solicitud->calendario_materia_id) == $cm->id)>{{ $label }}</option>
                    @endforeach
                </select>
                <div id="calendarioSesionesSelector" class="mt-3 rounded-xl border border-slate-200 bg-white p-3 hidden">
                    <p class="text-sm font-semibold text-slate-700">Sesiones impartidas</p>
                    <p class="text-xs text-slate-500 mt-1">Selecciona una o varias sesiones. Si no seleccionas ninguna, se considerarán todas las sesiones activas de la materia.</p>
                    <div id="calendarioSesionesLista" class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm"></div>
                </div>
            </div>
            <div id="bloqueEducacion" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5 {{ $selectedOrigen === SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA ? '' : 'hidden' }}">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Curso de educación continua</label>
                    <select name="curso_id" id="curso_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                        <option value="">Sin curso relacionado</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" @selected(old('curso_id', $solicitud->curso_id) == $curso->id)>{{ $curso->nombre }} · {{ $curso->tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Sesiones impartidas</label>
                    <select name="curso_sesion_ids[]" id="curso_sesion_id" multiple size="6" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                        @foreach($cursoSesiones as $sesion)
                            <option value="{{ $sesion->id }}" @selected(in_array((int) $sesion->id, $cursoSesionesSeleccionadas, true))>{{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->horario }} · {{ $sesion->curso?->nombre }} · {{ $sesion->expositor }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Puedes seleccionar varias sesiones con Ctrl + clic.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">2. Servicio académico a pagar</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Concepto *</label>
                <select name="concepto_pago" id="concepto_pago" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    <option value="">Selecciona concepto</option>
                    @foreach($conceptos as $concepto)<option value="{{ $concepto }}" @selected(old('concepto_pago', $solicitud->concepto_pago) === $concepto)>{{ $concepto }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Nivel *</label>
                <select name="nivel" id="nivel" required class="w-full rounded-xl border-slate-300 bg-slate-100 pointer-events-none px-4 py-2">
                    <option value="">Selecciona nivel</option>
                    @foreach($niveles as $nivel)<option value="{{ $nivel }}" @selected(old('nivel', $solicitud->nivel) === $nivel)>{{ $nivel }}</option>@endforeach
                </select>
            </div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Educación Programática / grupo</label><input type="text" name="programa_grupo" id="programa_grupo" readonly value="{{ old('programa_grupo', $solicitud->programa_grupo) }}" class="w-full rounded-xl border-slate-300 bg-slate-100 px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Periodo</label><input type="text" name="periodo" id="periodo" readonly value="{{ old('periodo', $solicitud->periodo) }}" class="w-full rounded-xl border-slate-300 bg-slate-100 px-4 py-2"></div>
            <div class="md:col-span-2"><label class="text-sm font-semibold text-slate-700 mb-1 block">Materia / actividad *</label><input type="text" name="materia_actividad" id="materia_actividad" required readonly value="{{ old('materia_actividad', $solicitud->materia_actividad) }}" class="w-full rounded-xl border-slate-300 bg-slate-100 px-4 py-2"></div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Modalidad</label>
                <select name="modalidad" id="modalidad" class="w-full rounded-xl border-slate-300 bg-slate-100 pointer-events-none px-4 py-2">
                    <option value="">Selecciona modalidad</option>
                    @foreach($modalidades as $modalidad)<option value="{{ $modalidad }}" @selected(old('modalidad', $solicitud->modalidad) === $modalidad)>{{ $modalidad }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Prioridad *</label>
                <select name="prioridad" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    @foreach($prioridades as $prioridad)<option value="{{ $prioridad }}" @selected(old('prioridad', $solicitud->prioridad ?? 'Normal') === $prioridad)>{{ $prioridad }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>

    @if($puedeCalcularPago)
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">3. Cálculo y fechas de pago</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Número de sesiones</label><input type="number" min="1" name="numero_sesiones" id="numero_sesiones" value="{{ old('numero_sesiones', $solicitud->numero_sesiones) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Horas totales</label><input type="number" step="0.01" min="0" name="horas_totales" id="horas_totales" value="{{ old('horas_totales', $solicitud->horas_totales) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Tarifa unitaria</label><input type="number" step="0.01" min="0" name="tarifa_hora" id="tarifa_hora" value="{{ old('tarifa_hora', $solicitud->tarifa_hora) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Monto a pagar *</label><input type="number" step="0.01" min="1" name="monto" id="monto" required value="{{ old('monto', $solicitud->monto) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Fecha de solicitud *</label><input type="date" name="fecha_solicitud" required value="{{ old('fecha_solicitud', optional($solicitud->fecha_solicitud)->format('Y-m-d') ?: date('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Fecha límite sugerida</label><input type="date" name="fecha_limite_pago" value="{{ old('fecha_limite_pago', optional($solicitud->fecha_limite_pago)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Fecha tentativa de pago</label><input type="date" name="fecha_tentativa_pago" value="{{ old('fecha_tentativa_pago', optional($solicitud->fecha_tentativa_pago)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Inicio del periodo/servicio</label><input type="date" name="fecha_inicio_periodo" id="fecha_inicio_periodo" value="{{ old('fecha_inicio_periodo', optional($solicitud->fecha_inicio_periodo)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
            <div><label class="text-sm font-semibold text-slate-700 mb-1 block">Fin del periodo/servicio</label><input type="date" name="fecha_fin_periodo" id="fecha_fin_periodo" value="{{ old('fecha_fin_periodo', optional($solicitud->fecha_fin_periodo)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2"></div>
        </div>
    </div>
    @else
        <input type="hidden" name="numero_sesiones" id="numero_sesiones" value="{{ old('numero_sesiones', $solicitud->numero_sesiones) }}">
        <input type="hidden" name="horas_totales" id="horas_totales" value="{{ old('horas_totales', $solicitud->horas_totales) }}">
        <input type="hidden" name="tarifa_hora" id="tarifa_hora" value="0">
        <input type="hidden" name="monto" id="monto" value="0">
        <input type="hidden" name="fecha_solicitud" value="{{ old('fecha_solicitud', optional($solicitud->fecha_solicitud)->format('Y-m-d') ?: date('Y-m-d')) }}">
        <input type="hidden" name="fecha_inicio_periodo" id="fecha_inicio_periodo" value="{{ old('fecha_inicio_periodo', optional($solicitud->fecha_inicio_periodo)->format('Y-m-d')) }}">
        <input type="hidden" name="fecha_fin_periodo" id="fecha_fin_periodo" value="{{ old('fecha_fin_periodo', optional($solicitud->fecha_fin_periodo)->format('Y-m-d')) }}">
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5"><h2 class="text-lg font-bold text-blue-950 mb-2">3. Servicio académico registrado</h2><p class="text-sm text-blue-800">Coordinación Académica solo registra sesiones o clases impartidas. CAdmin/Finanzas calculará tarifa, monto y fecha tentativa de pago.</p></div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">4. Observaciones de Coordinación Académica</h2>
        <textarea name="observaciones_academica" rows="4" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Describe clases impartidas, acuerdos con docente, notas para administración, etc.">{{ old('observaciones_academica', $solicitud->observaciones_academica ?? $solicitud->observaciones) }}</textarea>
    </div>

    <div class="flex justify-between items-center gap-4">
        <a href="{{ $isEdit ? route('solicitudes_pago.show', $solicitud) : route('solicitudes_pago.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium transition">Cancelar</a>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition">{{ $isEdit ? 'Guardar cambios' : 'Enviar solicitud' }}</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ORIGEN_CALENDARIO = @json(SolicitudPagoDocente::ORIGEN_CALENDARIO);
    const ORIGEN_EDUCACION = @json(SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA);
    const calendarioMaterias = @json($calendarioMateriasPayload);
    const cursos = @json($cursosPayload);
    const cursoSesiones = @json($cursoSesionesPayload);
    const calendarioSesionesSeleccionadasIniciales = new Set(@json($calendarioSesionesSeleccionadas));

    const origen = document.getElementById('origen');
    const bloqueCalendario = document.getElementById('bloqueCalendario');
    const bloqueEducacion = document.getElementById('bloqueEducacion');
    const docente = document.getElementById('docente_id');
    const calendarioMateria = document.getElementById('calendario_materia_id');
    const calendarioSesionesSelector = document.getElementById('calendarioSesionesSelector');
    const calendarioSesionesLista = document.getElementById('calendarioSesionesLista');
    const curso = document.getElementById('curso_id');
    const cursoSesion = document.getElementById('curso_sesion_id');
    const nivel = document.getElementById('nivel');
    const programaGrupo = document.getElementById('programa_grupo');
    const periodo = document.getElementById('periodo');
    const materiaActividad = document.getElementById('materia_actividad');
    const modalidad = document.getElementById('modalidad');
    const numeroSesiones = document.getElementById('numero_sesiones');
    const horas = document.getElementById('horas_totales');
    const tarifa = document.getElementById('tarifa_hora');
    const monto = document.getElementById('monto');
    const concepto = document.getElementById('concepto_pago');
    const fechaInicio = document.getElementById('fecha_inicio_periodo');
    const fechaFin = document.getElementById('fecha_fin_periodo');

    const setIfValue = (element, value) => { if (element && value !== null && value !== undefined && value !== '') { element.value = value; element.dispatchEvent(new Event('change', { bubbles: true })); } };
    const aplicarDatosServicio = (data) => {
        if (!data) return;
        setIfValue(docente, data.docente_id); setIfValue(nivel, data.nivel); setIfValue(programaGrupo, data.programa_grupo);
        setIfValue(periodo, data.periodo); setIfValue(materiaActividad, data.materia_actividad); setIfValue(modalidad, data.modalidad);
        setIfValue(numeroSesiones, data.numero_sesiones); setIfValue(horas, data.horas_totales); setIfValue(fechaInicio, data.fecha_inicio_periodo); setIfValue(fechaFin, data.fecha_fin_periodo);
        calcularMonto();
    };
    const calcularMonto = () => {
        if (!monto || !tarifa || !concepto) return;
        const h = parseFloat(horas?.value || '0'); const sesiones = parseFloat(numeroSesiones?.value || '0'); const t = parseFloat(tarifa.value || '0'); const c = (concepto.value || '').toLowerCase();
        if (t <= 0) return;
        if (c.includes('continua') && h > 0) { monto.value = (h * t).toFixed(2); return; }
        if (c.includes('programática') || c.includes('programatica')) { monto.value = sesiones > 0 ? (sesiones * t).toFixed(2) : ''; }
    };
    const toggleOrigen = () => { bloqueCalendario?.classList.toggle('hidden', origen.value !== ORIGEN_CALENDARIO); bloqueEducacion?.classList.toggle('hidden', origen.value !== ORIGEN_EDUCACION); };
    const actualizarDesdeSesionesCalendario = () => {
        const checks = Array.from(document.querySelectorAll('.calendario-session-check:checked'));
        if (!checks.length) return;
        setIfValue(numeroSesiones, checks.length);
        setIfValue(horas, checks.reduce((total, check) => total + parseFloat(check.dataset.horas || '0'), 0).toFixed(2)); calcularMonto();
    };
    const renderCalendarioSesiones = (data) => {
        if (!calendarioSesionesSelector || !calendarioSesionesLista) return;
        calendarioSesionesLista.innerHTML = ''; const sesiones = data?.sesiones || []; calendarioSesionesSelector.classList.toggle('hidden', sesiones.length === 0);
        sesiones.forEach((sesion) => {
            const checked = calendarioSesionesSeleccionadasIniciales.has(Number(sesion.id)) ? 'checked' : '';
            calendarioSesionesLista.insertAdjacentHTML('beforeend', `<label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2"><input type="checkbox" name="calendario_sesion_ids[]" value="${sesion.id}" class="calendario-session-check rounded border-slate-300" data-horas="${sesion.horas || 0}" ${checked}><span>${sesion.label}</span></label>`);
        });
        calendarioSesionesLista.querySelectorAll('.calendario-session-check').forEach((check) => check.addEventListener('change', actualizarDesdeSesionesCalendario));
        actualizarDesdeSesionesCalendario();
    };
    const aplicarSesionesEducacionContinua = () => {
        const ids = Array.from(cursoSesion?.selectedOptions || []).map(option => String(option.value));
        const seleccion = cursoSesiones.filter(item => ids.includes(String(item.id))); if (!seleccion.length) return;
        const base = {...seleccion[0]}; base.numero_sesiones = seleccion.length; base.horas_totales = seleccion.reduce((total, item) => total + parseFloat(item.horas_totales || '0'), 0).toFixed(2); base.fecha_inicio_periodo = seleccion[0].fecha_inicio_periodo; base.fecha_fin_periodo = seleccion[seleccion.length - 1].fecha_fin_periodo; if (base.curso_id) setIfValue(curso, base.curso_id); aplicarDatosServicio(base);
    };

    origen?.addEventListener('change', toggleOrigen);
    calendarioMateria?.addEventListener('change', () => { const data = calendarioMaterias.find(item => String(item.id) === String(calendarioMateria.value)); aplicarDatosServicio(data); renderCalendarioSesiones(data); });
    curso?.addEventListener('change', () => aplicarDatosServicio(cursos.find(item => String(item.id) === String(curso.value))));
    cursoSesion?.addEventListener('change', aplicarSesionesEducacionContinua);
    horas?.addEventListener('input', calcularMonto); tarifa?.addEventListener('input', calcularMonto); numeroSesiones?.addEventListener('input', calcularMonto); concepto?.addEventListener('change', calcularMonto);
    if (calendarioMateria?.value) renderCalendarioSesiones(calendarioMaterias.find(item => String(item.id) === String(calendarioMateria.value)));
    if (cursoSesion?.selectedOptions?.length) aplicarSesionesEducacionContinua();
    toggleOrigen();
});
</script>
@endpush
