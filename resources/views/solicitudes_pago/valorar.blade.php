@extends('layouts.app')

@php use App\Models\SolicitudPagoDocente; @endphp

@section('title', 'Valorar solicitud docente')

@section('content')
<div class="mx-auto mt-6 max-w-4xl">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="mb-6">
            <h1 class="flex items-center gap-2 text-2xl font-bold text-slate-800">
                <i class="bx bx-calculator text-3xl text-blue-600"></i>
                {{ $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA ? 'Corregir valoración de' : 'Valorar y programar' }} {{ $solicitud->folio }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">Esta etapa corresponde exclusivamente a Coordinación Administrativa.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-300 bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-inside list-disc text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                <p><span class="text-slate-500">Docente</span><br><strong>{{ $solicitud->docente->nombre_completo }}</strong></p>
                <p><span class="text-slate-500">Tipo de clase</span><br><strong>{{ $solicitud->tipo_clase }}</strong></p>
                <p><span class="text-slate-500">Actividad</span><br><strong>{{ $solicitud->materia_actividad }}</strong></p>
                <p><span class="text-slate-500">Sesiones registradas</span><br><strong>{{ count($solicitud->fechas_clase_ordenadas) }}</strong></p>
                <p><span class="text-slate-500">Horas académicas reportadas</span><br><strong>{{ $solicitud->horas_totales ?? 'No especificadas' }}</strong></p>
                <div>
                    <span class="text-slate-500">Fechas impartidas</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($solicitud->fechas_clase_ordenadas as $fecha)
                            <span class="rounded-lg bg-white px-2 py-1 font-semibold text-slate-700">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('solicitudes_pago.valorar', $solicitud) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Esquema de pago *</label>
                    <select name="esquema_pago" id="esquema_pago" required class="w-full rounded-xl border-slate-300 px-4 py-2">
                        <option value="">Selecciona</option>
                        @foreach($esquemasPago as $esquema)
                            <option value="{{ $esquema }}" @selected(old('esquema_pago', $solicitud->esquema_pago) === $esquema)>{{ $esquema }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Tarifa unitaria</label>
                    <input type="number" name="tarifa_unitaria" id="tarifa_unitaria" min="0.01" max="999999.99" step="0.01" inputmode="decimal" value="{{ old('tarifa_unitaria', $solicitud->tarifa_unitaria) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
                    <p id="ayuda-tarifa" class="mt-1 text-xs text-slate-500">Obligatoria por sesión o por hora. En monto fijo se desactiva.</p>
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <label class="block font-semibold text-slate-700">Monto calculado / aprobado *</label>
                        <span id="estado-calculo" class="hidden rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Cálculo automático</span>
                    </div>
                    <input type="number" name="monto" id="monto" required min="1" max="9999999.99" step="0.01" inputmode="decimal" value="{{ old('monto', $solicitud->monto > 0 ? $solicitud->monto : '') }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
                    <p id="calculo-sugerido" class="mt-1 min-h-5 text-xs text-blue-700"></p>
                    <p class="mt-1 text-xs text-slate-500">Por sesión y por hora el total se recalcula también en el servidor. Solo el monto fijo se captura manualmente.</p>
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Fecha tentativa de pago *</label>
                    <input type="date" name="fecha_tentativa_pago" required min="{{ now()->format('Y-m-d') }}" value="{{ old('fecha_tentativa_pago', optional($solicitud->fecha_tentativa_pago)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
                    <p class="mt-1 text-xs text-slate-500">Académica podrá verla para informar al docente. Es tentativa y puede actualizarse.</p>
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Fecha límite administrativa</label>
                    <input type="date" name="fecha_limite_pago" value="{{ old('fecha_limite_pago', optional($solicitud->fecha_limite_pago)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 px-4 py-2">
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Prioridad *</label>
                    <select name="prioridad" required class="w-full rounded-xl border-slate-300 px-4 py-2">
                        @foreach($prioridades as $prioridad)
                            <option value="{{ $prioridad }}" @selected(old('prioridad', $solicitud->prioridad ?? 'Normal') === $prioridad)>{{ $prioridad }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block font-semibold text-slate-700">Observaciones de Coordinación Administrativa</label>
                    <textarea name="observaciones_administracion" rows="4" maxlength="1500" class="w-full rounded-xl border-slate-300 px-4 py-2">{{ old('observaciones_administracion', $solicitud->observaciones_administracion) }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                <a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="rounded-xl bg-slate-200 px-5 py-2.5 text-center font-semibold text-slate-700">Cancelar</a>
                <button class="rounded-xl bg-blue-600 px-6 py-2.5 font-semibold text-white shadow hover:bg-blue-700">{{ $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA ? 'Recalcular, guardar y notificar' : 'Autorizar, programar y notificar' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const esquema = document.getElementById('esquema_pago');
    const tarifa = document.getElementById('tarifa_unitaria');
    const monto = document.getElementById('monto');
    const ayuda = document.getElementById('calculo-sugerido');
    const ayudaTarifa = document.getElementById('ayuda-tarifa');
    const estadoCalculo = document.getElementById('estado-calculo');

    const esquemaSesion = @json(SolicitudPagoDocente::ESQUEMA_SESION);
    const esquemaHora = @json(SolicitudPagoDocente::ESQUEMA_HORA);
    const esquemaFijo = @json(SolicitudPagoDocente::ESQUEMA_FIJO);
    const sesiones = {{ (int) count($solicitud->fechas_clase_ordenadas) }};
    const horas = Number(@json((string) ($solicitud->horas_totales ?? 0)));
    const moneda = new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    });

    let esquemaAnterior = esquema.value;
    let montoFijoTemporal = esquema.value === esquemaFijo ? monto.value : '';

    function numero(valor) {
        const convertido = Number.parseFloat(String(valor ?? '').replace(',', '.'));
        return Number.isFinite(convertido) ? convertido : 0;
    }

    function aplicarModoCalculado(sugerido, detalle) {
        monto.readOnly = true;
        monto.classList.add('bg-slate-100', 'text-slate-800', 'cursor-not-allowed');
        estadoCalculo.classList.remove('hidden');

        if (sugerido > 0) {
            // Siempre sustituye cualquier valor anterior. El servidor repetirá este cálculo.
            monto.value = sugerido.toFixed(2);
            monto.dataset.calculado = '1';
            ayuda.textContent = `${detalle} = ${moneda.format(sugerido)}. Este será el total guardado.`;
            ayuda.className = 'mt-1 min-h-5 text-xs font-medium text-blue-700';
        } else {
            monto.value = '';
            monto.dataset.calculado = '1';
            ayuda.textContent = detalle;
            ayuda.className = 'mt-1 min-h-5 text-xs font-medium text-red-600';
        }
    }

    function aplicarModoFijo() {
        tarifa.required = false;
        tarifa.disabled = true;
        tarifa.value = '';
        tarifa.classList.add('bg-slate-100', 'cursor-not-allowed');

        monto.readOnly = false;
        monto.classList.remove('bg-slate-100', 'text-slate-800', 'cursor-not-allowed');
        estadoCalculo.classList.add('hidden');

        if (monto.dataset.calculado === '1') {
            monto.value = montoFijoTemporal;
        }
        monto.dataset.calculado = '0';
        ayuda.textContent = 'Captura manualmente el monto total aprobado para esta actividad.';
        ayuda.className = 'mt-1 min-h-5 text-xs text-slate-500';
        ayudaTarifa.textContent = 'No se utiliza tarifa unitaria cuando el esquema es monto fijo.';
    }

    function actualizar() {
        const actual = esquema.value;

        if (esquemaAnterior === esquemaFijo && actual !== esquemaFijo) {
            montoFijoTemporal = monto.value;
        }

        if (actual === esquemaFijo) {
            aplicarModoFijo();
            esquemaAnterior = actual;
            return;
        }

        tarifa.disabled = false;
        tarifa.required = actual === esquemaSesion || actual === esquemaHora;
        tarifa.classList.remove('bg-slate-100', 'cursor-not-allowed');
        ayudaTarifa.textContent = actual === esquemaHora
            ? 'Tarifa que se multiplicará por las horas académicas reportadas.'
            : 'Tarifa que se multiplicará por el número de fechas impartidas.';

        const valorTarifa = numero(tarifa.value);

        if (actual === esquemaSesion) {
            const sugerido = sesiones > 0 && valorTarifa > 0 ? sesiones * valorTarifa : 0;
            const etiqueta = sesiones === 1 ? 'sesión' : 'sesiones';
            aplicarModoCalculado(
                sugerido,
                sesiones < 1
                    ? 'No hay sesiones registradas para realizar el cálculo.'
                    : valorTarifa <= 0
                        ? `Captura la tarifa por sesión. Se registraron ${sesiones} ${etiqueta}.`
                        : `${sesiones} ${etiqueta} × ${moneda.format(valorTarifa)}`
            );
        } else if (actual === esquemaHora) {
            const sugerido = horas > 0 && valorTarifa > 0 ? horas * valorTarifa : 0;
            aplicarModoCalculado(
                sugerido,
                horas <= 0
                    ? 'No hay horas académicas reportadas. Académica debe corregir la solicitud antes de valorar por hora.'
                    : valorTarifa <= 0
                        ? `Captura la tarifa por hora. Se reportaron ${horas.toFixed(2)} horas.`
                        : `${horas.toFixed(2)} horas × ${moneda.format(valorTarifa)}`
            );
        } else {
            monto.readOnly = true;
            monto.value = '';
            monto.dataset.calculado = '1';
            monto.classList.add('bg-slate-100', 'cursor-not-allowed');
            estadoCalculo.classList.add('hidden');
            ayuda.textContent = 'Selecciona un esquema de pago para calcular o capturar el monto.';
            ayuda.className = 'mt-1 min-h-5 text-xs text-slate-500';
        }

        esquemaAnterior = actual;
    }

    esquema.addEventListener('change', actualizar);
    tarifa.addEventListener('input', actualizar);
    monto.addEventListener('input', () => {
        if (esquema.value === esquemaFijo) {
            montoFijoTemporal = monto.value;
        }
    });

    actualizar();
});
</script>
@endpush
