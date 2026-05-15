@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Registrar Pago</h2>
            <p class="text-sm text-gray-500 mt-1">Alumno: <strong>{{ $alumno->nombre_completo }}</strong></p>
        </div>

        <a href="{{ route('alumnos.show', $alumno) }}" class="text-indigo-600 hover:underline font-medium">
            ← Regresar a ficha del alumno
        </a>
    </div>


    @if(! $corteCajaActiva)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6">
            <p class="font-semibold">No tienes una caja abierta.</p>
            <p class="text-sm mt-1">Para registrar pagos primero debes abrir una caja. Esto permite que el pago quede incluido en un corte diario.</p>
            <a href="{{ route('cortes-caja.create') }}" class="inline-flex mt-3 px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700">
                Abrir caja
            </a>
        </div>
    @else
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <p class="font-semibold">Caja activa #{{ $corteCajaActiva->id }}</p>
            <p class="text-sm mt-1">Apertura: {{ optional($corteCajaActiva->fecha_apertura)->format('d/m/Y H:i') }} · Saldo inicial: ${{ number_format($corteCajaActiva->saldo_inicial, 2) }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <p class="font-semibold mb-1">Revisa la información del pago:</p>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumnos.pagos.store', $alumno) }}" method="POST" enctype="multipart/form-data" id="form-pago">
        @csrf
        <input type="hidden" name="operacion_uuid" value="{{ old('operacion_uuid', (string) \Illuminate\Support\Str::uuid()) }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Cargos Regulares --}}
                <section class="border rounded-xl overflow-hidden">
                    <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Cargos Regulares</h3>
                        <span class="text-xs text-gray-500">Selecciona los adeudos a cubrir</span>
                    </div>

                    @if($cargosPendientes->isEmpty())
                        <p class="text-gray-500 p-4">No hay cargos pendientes.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-white border-b">
                                    <tr>
                                        <th class="p-3 text-center">Seleccionar</th>
                                        <th class="p-3 text-left">Descripción</th>
                                        <th class="p-3 text-right">Adeudo</th>
                                        <th class="p-3 text-center">Vencimiento</th>
                                        <th class="p-3 text-center">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cargosPendientes as $cargo)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 text-center">
                                                <input
                                                    type="checkbox"
                                                    name="cargos[]"
                                                    value="{{ $cargo->id }}"
                                                    data-adeudo="{{ $cargo->monto_adeudo }}"
                                                    class="seleccion-adeudo rounded border-gray-300"
                                                    @checked(in_array($cargo->id, old('cargos', [])))>
                                            </td>
                                            <td class="p-3">{{ $cargo->descripcion_cargo }}</td>
                                            <td class="p-3 text-right font-semibold">${{ number_format($cargo->monto_adeudo, 2) }}</td>
                                            <td class="p-3 text-center">{{ optional($cargo->fecha_vencimiento)->format('d/m/Y') }}</td>
                                            <td class="p-3 text-center">{{ $cargo->estatus }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- Parcialidades de Convenio --}}
                <section class="border rounded-xl overflow-hidden">
                    <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Parcialidades de Convenio</h3>
                        <span class="text-xs text-gray-500">Convenios activos o parcialmente pagados</span>
                    </div>

                    @if($parcialidadesPendientes->isEmpty())
                        <p class="text-gray-500 p-4">No hay parcialidades pendientes.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-white border-b">
                                    <tr>
                                        <th class="p-3 text-center">Seleccionar</th>
                                        <th class="p-3 text-left">Convenio</th>
                                        <th class="p-3 text-right">Adeudo</th>
                                        <th class="p-3 text-center">Vencimiento</th>
                                        <th class="p-3 text-center">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parcialidadesPendientes as $parcialidad)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 text-center">
                                                <input
                                                    type="checkbox"
                                                    name="parcialidades[]"
                                                    value="{{ $parcialidad->id }}"
                                                    data-adeudo="{{ $parcialidad->monto_adeudo }}"
                                                    class="seleccion-adeudo rounded border-gray-300"
                                                    @checked(in_array($parcialidad->id, old('parcialidades', [])))>
                                            </td>
                                            <td class="p-3">{{ $parcialidad->convenio->descripcion }}</td>
                                            <td class="p-3 text-right font-semibold">${{ number_format($parcialidad->monto_adeudo, 2) }}</td>
                                            <td class="p-3 text-center">{{ optional($parcialidad->fecha_vencimiento)->format('d/m/Y') }}</td>
                                            <td class="p-3 text-center">
                                                @if($parcialidad->estatus === 'Pagado')
                                                    <span class="text-green-700 font-semibold">Pagado</span>
                                                @elseif($parcialidad->estatus === 'Parcialmente Pagado')
                                                    <span class="text-yellow-700 font-semibold">Parcial</span>
                                                @else
                                                    <span class="text-red-700 font-semibold">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                {{-- Resumen --}}
                <section class="border rounded-xl p-4 bg-gray-50">
                    <h3 class="font-bold text-gray-800 mb-3">Resumen</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Adeudo seleccionado:</span>
                            <strong id="total-seleccionado">$0.00</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Saldo a favor actual:</span>
                            <strong>${{ number_format($alumno->saldo_a_favor ?? 0, 2) }}</strong>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Si el pago supera el adeudo seleccionado, el excedente se registrará como saldo a favor del alumno.
                    </p>
                </section>

                {{-- Datos del Pago --}}
                <section class="border rounded-xl p-4">
                    <h3 class="font-bold text-gray-800 mb-4">Datos del Pago</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Método de pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="w-full border rounded p-2" required>
                                <option value="">-- Selecciona --</option>
                                <option value="Efectivo" @selected(old('metodo_pago') === 'Efectivo')>Efectivo</option>
                                <option value="Transferencia" @selected(old('metodo_pago') === 'Transferencia')>Transferencia</option>
                                <option value="Tarjeta" @selected(old('metodo_pago') === 'Tarjeta')>Tarjeta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Monto total pagado</label>
                            <input type="number" step="0.01" min="0.01" name="monto_total_pagado" id="monto_total_pagado"
                                   class="w-full border rounded p-2" value="{{ old('monto_total_pagado') }}" required>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Fecha de pago</label>
                            <input type="date" name="fecha_pago" class="w-full border rounded p-2"
                                   value="{{ old('fecha_pago', now()->toDateString()) }}">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Folio del recibo</label>
                            <input type="text" name="folio_recibo" class="w-full border rounded p-2"
                                   value="{{ old('folio_recibo') }}" placeholder="Ej: 000123">
                        </div>
                    </div>
                </section>

                {{-- Transferencia --}}
                <section id="datos_transferencia" class="hidden border rounded-xl p-4 bg-blue-50">
                    <h4 class="font-bold text-gray-800 mb-3">Datos de Transferencia</h4>

                    <div class="space-y-3">
                        <input type="text" name="banco_emisor" value="{{ old('banco_emisor') }}" placeholder="Banco emisor" class="w-full border rounded p-2">
                        <input type="text" name="cuenta_origen" value="{{ old('cuenta_origen') }}" placeholder="Cuenta de origen" class="w-full border rounded p-2">
                        <input type="text" name="numero_autorizacion" value="{{ old('numero_autorizacion') }}" placeholder="Número de autorización" class="w-full border rounded p-2">
                        <input type="text" name="clave_rastreo" value="{{ old('clave_rastreo') }}" placeholder="Clave de rastreo" class="w-full border rounded p-2">
                        <input type="text" name="concepto_transferencia" value="{{ old('concepto_transferencia') }}" placeholder="Concepto" class="w-full border rounded p-2">
                        <input type="text" name="referencia_transferencia" value="{{ old('referencia_transferencia') }}" placeholder="Referencia bancaria" class="w-full border rounded p-2">
                        <input type="datetime-local" name="fecha_transferencia" value="{{ old('fecha_transferencia') }}" class="w-full border rounded p-2">
                        <input type="text" name="banco_destino" value="{{ old('banco_destino') }}" placeholder="Banco destino" class="w-full border rounded p-2">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comprobante de transferencia</label>
                            <input type="file" name="archivo_comprobante" accept=".pdf,image/*" class="block w-full text-sm">
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG o PNG. Máximo 4 MB.</p>
                        </div>
                    </div>
                </section>

                {{-- Tarjeta --}}
                <section id="campos_tarjeta" class="hidden border rounded-xl p-4 bg-purple-50">
                    <h4 class="font-bold text-gray-800 mb-3">Datos de Tarjeta</h4>

                    <div class="space-y-3">
                        <input type="text" name="tarjeta_banco_emisor" value="{{ old('tarjeta_banco_emisor') }}" placeholder="Banco emisor" class="w-full border rounded p-2">
                        <input type="text" name="tarjeta_numero_autorizacion" value="{{ old('tarjeta_numero_autorizacion') }}" placeholder="Número de autorización" class="w-full border rounded p-2">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ticket / comprobante</label>
                            <input type="file" name="comprobante_tarjeta" accept=".pdf,image/*" class="block w-full text-sm">
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG o PNG. Máximo 4 MB.</p>
                        </div>
                    </div>
                </section>

                <section class="border rounded-xl p-4">
                    <label class="block font-semibold text-gray-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="w-full border rounded p-2" placeholder="Notas internas del pago">{{ old('observaciones') }}</textarea>
                </section>

                <button type="submit" @disabled(! $corteCajaActiva) class="w-full bg-indigo-600 text-white px-4 py-3 rounded-lg hover:bg-indigo-700 font-semibold shadow disabled:opacity-50 disabled:cursor-not-allowed">
                    Registrar Pago
                </button>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const metodoPago = document.getElementById('metodo_pago');
        const transferencia = document.getElementById('datos_transferencia');
        const tarjeta = document.getElementById('campos_tarjeta');
        const checkboxes = document.querySelectorAll('.seleccion-adeudo');
        const totalSeleccionado = document.getElementById('total-seleccionado');
        const montoInput = document.getElementById('monto_total_pagado');

        function formatoMoneda(valor) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(valor);
        }

        function actualizarCamposPago() {
            const valor = metodoPago.value;

            transferencia.classList.toggle('hidden', valor !== 'Transferencia');
            tarjeta.classList.toggle('hidden', valor !== 'Tarjeta');
        }

        function actualizarTotalSeleccionado() {
            let total = 0;

            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    total += parseFloat(checkbox.dataset.adeudo || 0);
                }
            });

            total = Math.round(total * 100) / 100;
            totalSeleccionado.textContent = formatoMoneda(total);

            if (!montoInput.value || parseFloat(montoInput.value) === 0) {
                montoInput.value = total > 0 ? total.toFixed(2) : '';
            }
        }

        metodoPago.addEventListener('change', actualizarCamposPago);
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', actualizarTotalSeleccionado));

        actualizarCamposPago();
        actualizarTotalSeleccionado();
    });
</script>
@endpush
