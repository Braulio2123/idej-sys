@php
    use App\Models\SolicitudPagoDocente;

    $isEdit = $solicitud->exists;
    $fechas = old('fechas_clase', $solicitud->fechas_clase ?? []);
    if (!is_array($fechas) || count($fechas) === 0) $fechas = [''];

    $calendariosPayload = $calendarioMaterias->map(function ($cm) {
        $cal = $cm->calendario;
        $grupo = $cal?->grupo;
        $programa = $grupo?->programa;
        $nivel = (string) ($programa?->nivel ?? '');
        $tipo = str_contains(mb_strtolower($nivel), 'doctor') ? SolicitudPagoDocente::TIPO_DOCTORADO
            : (str_contains(mb_strtolower($nivel), 'maestr') ? SolicitudPagoDocente::TIPO_MAESTRIA : SolicitudPagoDocente::TIPO_LICENCIATURA);
        $fechasActivas = $cm->sesiones
            ->filter(fn ($s) => $s->fecha && \Carbon\Carbon::parse($s->fecha)->lte(today()) && !in_array($s->estatus, ['Cancelada', 'Suspendida'], true))
            ->pluck('fecha')->map(fn ($f) => \Carbon\Carbon::parse($f)->format('Y-m-d'))->unique()->sort()->values();

        return [
            'id' => $cm->id,
            'docente_id' => $cm->docente_id,
            'tipo_clase' => $tipo,
            'programa_grupo' => trim(($programa?->nombre ?? '').($grupo?->nombre ? ' · '.$grupo->nombre : '')),
            'materia_actividad' => $cm->nombre_materia,
            'periodo' => $cal?->periodo ?? '',
            'modalidad' => $cal?->modalidad ?? '',
            'fechas' => $fechasActivas,
        ];
    })->values();

    $cursosPayload = $cursos->map(function ($curso) {
        $esDiplomado = str_contains(mb_strtolower((string) $curso->tipo.' '.$curso->nombre), 'diplomado');
        $sesionesActivas = $curso->sesiones
            ->filter(fn ($s) => $s->fecha && \Carbon\Carbon::parse($s->fecha)->lte(today()) && $s->estatus !== 'Cancelada')
            ->map(fn ($s) => [
                'fecha' => \Carbon\Carbon::parse($s->fecha)->format('Y-m-d'),
                'docente_id' => $s->docente_id,
                'horas' => (float) ($s->duracion_horas ?: $s->calcularDuracion()),
            ])->values();

        return [
            'id' => $curso->id,
            'tipo_clase' => $esDiplomado ? SolicitudPagoDocente::TIPO_DIPLOMADO : SolicitudPagoDocente::TIPO_CURSO,
            'programa_grupo' => trim(($curso->tipo ?? 'Curso').' · '.$curso->nombre),
            'materia_actividad' => $curso->nombre,
            'periodo' => trim(($curso->fecha_inicio ? \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') : '').' - '.($curso->fecha_fin ? \Carbon\Carbon::parse($curso->fecha_fin)->format('d/m/Y') : '')),
            'modalidad' => $curso->modalidad ?? '',
            'sesiones' => $sesionesActivas,
        ];
    })->values();
@endphp

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-300 bg-red-100 px-4 py-3 text-red-700">
        <p class="mb-1 font-bold">Revisa la información:</p>
        <ul class="list-inside list-disc space-y-1 text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
    <p class="font-bold">Responsabilidad de Coordinación Académica</p>
    <p class="mt-1">Registra al docente, el tipo de clase y las fechas realmente impartidas. No captures precios, tarifas, montos ni fechas de pago; Coordinación Administrativa realizará esa valoración.</p>
</div>

<form method="POST" action="{{ $isEdit ? route('solicitudes_pago.update', $solicitud) : route('solicitudes_pago.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="mb-4 text-lg font-bold text-slate-800">1. Docente y tipo de clase</h2>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Docente *</label>
                <select name="docente_id" id="docente_id" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Selecciona docente</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}" @selected(old('docente_id', $solicitud->docente_id) == $docente->id)>{{ $docente->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tipo de clase *</label>
                <select name="tipo_clase" id="tipo_clase" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Selecciona tipo</option>
                    @foreach($tiposClase as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo_clase', $solicitud->tipo_clase) === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Origen del registro *</label>
                <select name="origen" id="origen" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    @foreach($origenes as $origen)
                        <option value="{{ $origen }}" @selected(old('origen', $solicitud->origen ?? SolicitudPagoDocente::ORIGEN_MANUAL) === $origen)>{{ $origen }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Modalidad</label>
                <select name="modalidad" id="modalidad" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Sin especificar</option>
                    @foreach($modalidades as $modalidad)
                        <option value="{{ $modalidad }}" @selected(old('modalidad', $solicitud->modalidad) === $modalidad)>{{ $modalidad }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-bold text-slate-800">2. Relación académica opcional</h2>
        <p class="mb-4 text-sm text-slate-500">Seleccionar una materia o curso puede completar automáticamente los datos y las fechas ya programadas. Verifica que sean clases efectivamente impartidas.</p>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div id="bloque-calendario">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materia / calendario relacionado</label>
                <select name="calendario_materia_id" id="calendario_materia_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Sin relación</option>
                    @foreach($calendarioMaterias as $cm)
                        <option value="{{ $cm->id }}" @selected(old('calendario_materia_id', $solicitud->calendario_materia_id) == $cm->id)>
                            {{ $cm->calendario?->nombre ?? 'Calendario' }} · {{ $cm->calendario?->grupo?->nombre ?? 'Grupo' }} · {{ $cm->nombre_materia }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="bloque-curso">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Curso o diplomado relacionado</label>
                <select name="curso_id" id="curso_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Sin relación</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" @selected(old('curso_id', $solicitud->curso_id) == $curso->id)>{{ $curso->tipo ?? 'Curso' }} · {{ $curso->nombre }}</option>
                    @endforeach
                </select>
                <p id="curso-sesiones-ayuda" class="mt-1 text-xs text-slate-500">Las fechas se filtrarán por el docente seleccionado.</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">3. Fechas en que impartió clase *</h2>
                <p class="text-sm text-slate-500">Registra únicamente fechas ya impartidas. No repitas fechas.</p>
            </div>
            <button type="button" id="agregar-fecha" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Agregar fecha</button>
        </div>
        <div id="lista-fechas" class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($fechas as $fecha)
                <div class="fila-fecha flex items-center gap-2">
                    <input type="date" name="fechas_clase[]" value="{{ $fecha }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <button type="button" class="quitar-fecha rounded-lg bg-red-100 px-3 py-2 font-bold text-red-700" aria-label="Quitar fecha">×</button>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 text-lg font-bold text-slate-800">4. Detalle del servicio docente</h2>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materia, curso o actividad *</label>
                <input type="text" name="materia_actividad" id="materia_actividad" required maxlength="220" value="{{ old('materia_actividad', $solicitud->materia_actividad) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Programa / grupo</label>
                <input type="text" name="programa_grupo" id="programa_grupo" maxlength="180" value="{{ old('programa_grupo', $solicitud->programa_grupo) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Periodo</label>
                <input type="text" name="periodo" id="periodo" maxlength="120" value="{{ old('periodo', $solicitud->periodo) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Horas totales impartidas</label>
                <input type="number" name="horas_totales" id="horas_totales" min="0" max="9999" step="0.25" value="{{ old('horas_totales', $solicitud->horas_totales) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
                <p class="mt-1 text-xs text-slate-500">Solo es un dato académico. CAdmin decide el esquema y el monto.</p>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Observaciones de Coordinación Académica</label>
                <textarea name="observaciones_academica" rows="4" maxlength="1500" class="w-full rounded-xl border-slate-300 px-4 py-2">{{ old('observaciones_academica', $solicitud->observaciones_academica) }}</textarea>
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
        <a href="{{ route('solicitudes_pago.index') }}" class="rounded-xl bg-slate-200 px-5 py-2.5 text-center font-semibold text-slate-700">Cancelar</a>
        <button class="rounded-xl bg-blue-600 px-6 py-2.5 font-semibold text-white shadow hover:bg-blue-700">
            {{ $isEdit ? 'Guardar corrección y reenviar' : 'Enviar a Coordinación Administrativa' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarios = @json($calendariosPayload);
    const cursos = @json($cursosPayload);
    const lista = document.getElementById('lista-fechas');
    const hoy = @json(now()->format('Y-m-d'));

    function crearFila(fecha = '') {
        const row = document.createElement('div');
        row.className = 'fila-fecha flex items-center gap-2';
        row.innerHTML = `<input type="date" name="fechas_clase[]" value="${fecha}" max="${hoy}" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            <button type="button" class="quitar-fecha rounded-lg bg-red-100 px-3 py-2 font-bold text-red-700" aria-label="Quitar fecha">×</button>`;
        lista.appendChild(row);
    }

    function colocarFechas(fechas) {
        lista.innerHTML = '';
        if (!Array.isArray(fechas) || fechas.length === 0) {
            crearFila('');
            return;
        }
        [...new Set(fechas)].sort().forEach(crearFila);
    }

    function removerFila(button) {
        const filas = lista.querySelectorAll('.fila-fecha');
        if (filas.length === 1) {
            filas[0].querySelector('input').value = '';
            return;
        }
        button.closest('.fila-fecha')?.remove();
    }

    document.getElementById('agregar-fecha').addEventListener('click', () => crearFila(''));
    lista.addEventListener('click', e => {
        if (e.target.classList.contains('quitar-fecha')) removerFila(e.target);
    });

    function aplicar(data) {
        if (!data) return;
        if (data.docente_id) document.getElementById('docente_id').value = data.docente_id;
        if (data.tipo_clase) document.getElementById('tipo_clase').value = data.tipo_clase;
        document.getElementById('programa_grupo').value = data.programa_grupo || '';
        document.getElementById('materia_actividad').value = data.materia_actividad || '';
        document.getElementById('periodo').value = data.periodo || '';
        document.getElementById('modalidad').value = data.modalidad || '';
        if (Object.prototype.hasOwnProperty.call(data, 'horas')) {
            document.getElementById('horas_totales').value = data.horas || '';
        }
        if (Object.prototype.hasOwnProperty.call(data, 'fechas')) {
            colocarFechas(data.fechas || []);
        }
    }

    function aplicarCursoSeleccionado() {
        const cursoId = document.getElementById('curso_id').value;
        if (!cursoId) return;

        const curso = cursos.find(item => String(item.id) === String(cursoId));
        if (!curso) return;

        const docenteId = document.getElementById('docente_id').value;
        const sesiones = (curso.sesiones || []).filter(item => docenteId && String(item.docente_id) === String(docenteId));
        const fechas = sesiones.map(item => item.fecha);
        const horas = sesiones.reduce((total, item) => total + Number(item.horas || 0), 0);

        aplicar({...curso, fechas, horas: horas > 0 ? horas.toFixed(2) : ''});
        const ayuda = document.getElementById('curso-sesiones-ayuda');
        if (ayuda) {
            ayuda.textContent = docenteId
                ? (fechas.length ? `${fechas.length} sesión(es) encontradas para el docente seleccionado.` : 'No hay sesiones registradas para este docente en el curso. Puedes corregir la relación o usar origen Manual.')
                : 'Selecciona primero al docente para cargar sus fechas del curso.';
        }
    }

    document.getElementById('calendario_materia_id').addEventListener('change', e => {
        if (!e.target.value) return;
        document.getElementById('origen').value = @json(SolicitudPagoDocente::ORIGEN_CALENDARIO);
        document.getElementById('curso_id').value = '';
        aplicar(calendarios.find(item => String(item.id) === String(e.target.value)));
    });

    document.getElementById('curso_id').addEventListener('change', e => {
        if (!e.target.value) return;
        document.getElementById('origen').value = @json(SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA);
        document.getElementById('calendario_materia_id').value = '';
        aplicarCursoSeleccionado();
    });

    document.getElementById('docente_id').addEventListener('change', () => {
        if (document.getElementById('curso_id').value) aplicarCursoSeleccionado();
    });
});
</script>
@endpush
