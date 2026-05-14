@php
    use App\Models\SolicitudPagoDocente;

    $isEdit = $solicitud->exists;
    $selectedOrigen = old('origen', $solicitud->origen ?? SolicitudPagoDocente::ORIGEN_MANUAL);
@endphp

@if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-5">
        <p class="font-bold mb-1">Revisa la información:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('solicitudes_pago.update', $solicitud) : route('solicitudes_pago.store') }}" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">1. Datos del docente y origen del servicio</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Docente *</label>
                <select name="docente_id" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Selecciona docente</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}" @selected(old('docente_id', $solicitud->docente_id) == $docente->id)>
                            {{ $docente->nombre_completo }}
                        </option>
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
                <p class="text-xs text-slate-500 mt-1">Sirve para rastrear si el pago viene del calendario principal, educación continua o una captura manual.</p>
            </div>

            <div id="bloqueCalendario" class="md:col-span-2 {{ $selectedOrigen === SolicitudPagoDocente::ORIGEN_CALENDARIO ? '' : 'hidden' }}">
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Materia de calendario principal relacionada</label>
                <select name="calendario_materia_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                    <option value="">Sin relación directa</option>
                    @foreach($calendarioMaterias as $cm)
                        @php
                            $cal = $cm->calendario;
                            $grupo = $cal?->grupo;
                            $programa = $grupo?->programa;
                            $label = ($cal?->nombre ?? 'Calendario').' · '.($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo').' · '.$cm->nombre_materia.' · '.$cm->nombre_docente;
                        @endphp
                        <option value="{{ $cm->id }}" @selected(old('calendario_materia_id', $solicitud->calendario_materia_id) == $cm->id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div id="bloqueEducacion" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5 {{ $selectedOrigen === SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA ? '' : 'hidden' }}">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Curso de educación continua</label>
                    <select name="curso_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                        <option value="">Sin curso relacionado</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" @selected(old('curso_id', $solicitud->curso_id) == $curso->id)>
                                {{ $curso->nombre }} · {{ $curso->tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1 block">Sesión específica de curso</label>
                    <select name="curso_sesion_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                        <option value="">Sin sesión específica</option>
                        @foreach($cursoSesiones as $sesion)
                            <option value="{{ $sesion->id }}" @selected(old('curso_sesion_id', $solicitud->curso_sesion_id) == $sesion->id)>
                                {{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->horario }} · {{ $sesion->curso?->nombre }} · {{ $sesion->expositor }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">2. Servicio académico a pagar</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Concepto *</label>
                <select name="concepto_pago" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    <option value="">Selecciona concepto</option>
                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto }}" @selected(old('concepto_pago', $solicitud->concepto_pago) === $concepto)>{{ $concepto }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Nivel *</label>
                <select name="nivel" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    <option value="">Selecciona nivel</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel }}" @selected(old('nivel', $solicitud->nivel) === $nivel)>{{ $nivel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Programa / grupo</label>
                <input type="text" name="programa_grupo" value="{{ old('programa_grupo', $solicitud->programa_grupo) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Ej. Maestría 4 · Grupo 2-A">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Periodo</label>
                <input type="text" name="periodo" value="{{ old('periodo', $solicitud->periodo) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Ej. 2026 A / 2025B-2026A">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Materia / actividad *</label>
                <input type="text" name="materia_actividad" required value="{{ old('materia_actividad', $solicitud->materia_actividad) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Ej. Derecho Constitucional / MasterClass / MASC sesión 4">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Modalidad</label>
                <select name="modalidad" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    <option value="">Selecciona modalidad</option>
                    @foreach($modalidades as $modalidad)
                        <option value="{{ $modalidad }}" @selected(old('modalidad', $solicitud->modalidad) === $modalidad)>{{ $modalidad }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Prioridad *</label>
                <select name="prioridad" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2">
                    @foreach($prioridades as $prioridad)
                        <option value="{{ $prioridad }}" @selected(old('prioridad', $solicitud->prioridad ?? 'Normal') === $prioridad)>{{ $prioridad }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">3. Cálculo y fechas de pago</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Número de sesiones</label>
                <input type="number" min="1" name="numero_sesiones" value="{{ old('numero_sesiones', $solicitud->numero_sesiones) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Horas totales</label>
                <input type="number" step="0.01" min="0" name="horas_totales" id="horas_totales" value="{{ old('horas_totales', $solicitud->horas_totales) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Tarifa por hora</label>
                <input type="number" step="0.01" min="0" name="tarifa_hora" id="tarifa_hora" value="{{ old('tarifa_hora', $solicitud->tarifa_hora) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Monto a pagar *</label>
                <input type="number" step="0.01" min="1" name="monto" id="monto" required value="{{ old('monto', $solicitud->monto) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
                <p class="text-xs text-slate-500 mt-1">Puedes capturarlo directo o calcularlo con horas × tarifa.</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Fecha de solicitud *</label>
                <input type="date" name="fecha_solicitud" required value="{{ old('fecha_solicitud', optional($solicitud->fecha_solicitud)->format('Y-m-d') ?: date('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Fecha límite sugerida</label>
                <input type="date" name="fecha_limite_pago" value="{{ old('fecha_limite_pago', optional($solicitud->fecha_limite_pago)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Inicio del periodo/servicio</label>
                <input type="date" name="fecha_inicio_periodo" value="{{ old('fecha_inicio_periodo', optional($solicitud->fecha_inicio_periodo)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1 block">Fin del periodo/servicio</label>
                <input type="date" name="fecha_fin_periodo" value="{{ old('fecha_fin_periodo', optional($solicitud->fecha_fin_periodo)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2">
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-bold text-slate-800 mb-4">4. Observaciones de Coordinación Académica</h2>

        <textarea name="observaciones_academica" rows="4" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-2" placeholder="Describe qué se está solicitando, clases impartidas, acuerdos con docente, notas para administración, etc.">{{ old('observaciones_academica', $solicitud->observaciones_academica ?? $solicitud->observaciones) }}</textarea>
    </div>

    <div class="flex justify-between items-center gap-4">
        <a href="{{ $isEdit ? route('solicitudes_pago.show', $solicitud) : route('solicitudes_pago.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium transition">
            Cancelar
        </a>

        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition">
            {{ $isEdit ? 'Guardar cambios' : 'Enviar solicitud' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const origen = document.getElementById('origen');
    const bloqueCalendario = document.getElementById('bloqueCalendario');
    const bloqueEducacion = document.getElementById('bloqueEducacion');
    const horas = document.getElementById('horas_totales');
    const tarifa = document.getElementById('tarifa_hora');
    const monto = document.getElementById('monto');

    const toggleOrigen = () => {
        bloqueCalendario.classList.toggle('hidden', origen.value !== @json(SolicitudPagoDocente::ORIGEN_CALENDARIO));
        bloqueEducacion.classList.toggle('hidden', origen.value !== @json(SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA));
    };

    const calcularMonto = () => {
        const h = parseFloat(horas.value || '0');
        const t = parseFloat(tarifa.value || '0');
        if (h > 0 && t > 0) {
            monto.value = (h * t).toFixed(2);
        }
    };

    origen.addEventListener('change', toggleOrigen);
    horas.addEventListener('input', calcularMonto);
    tarifa.addEventListener('input', calcularMonto);

    toggleOrigen();
});
</script>
@endpush
