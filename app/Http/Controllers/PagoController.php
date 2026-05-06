<?php

namespace App\Http\Controllers;

use App\Models\AjusteCaja;
use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\ConfiguracionInstitucional;
use App\Models\CorteCaja;
use App\Models\Pago;
use App\Models\ParcialidadConvenio;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagoController extends Controller
{
    use RegistraBitacora;

    /**
     * Formulario para registrar un pago del alumno.
     */
    public function create(Alumno $alumno)
    {
        $cargosPendientes = Cargo::where('alumno_id', $alumno->id)
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->orderBy('fecha_vencimiento')
            ->get();

        $parcialidadesPendientes = ParcialidadConvenio::whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->whereHas('convenio', function ($query) use ($alumno) {
                $query->where('alumno_id', $alumno->id);
            })
            ->with('convenio')
            ->orderBy('fecha_vencimiento')
            ->get();

        $corteCajaActiva = CorteCaja::abierta()->deUsuario(Auth::id())->first();

        return view('pagos.create', compact(
            'alumno',
            'cargosPendientes',
            'parcialidadesPendientes',
            'corteCajaActiva'
        ));
    }

    /**
     * Registrar el pago y aplicarlo a cargos/parcialidades seleccionadas.
     */
    public function store(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'metodo_pago' => ['required', Rule::in(['Efectivo', 'Transferencia', 'Tarjeta'])],
            'monto_total_pagado' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['nullable', 'date'],

            'cargos' => ['nullable', 'array'],
            'cargos.*' => ['integer', 'distinct'],
            'parcialidades' => ['nullable', 'array'],
            'parcialidades.*' => ['integer', 'distinct'],

            'folio_recibo' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:1000'],

            // Transferencia
            'banco_emisor' => ['nullable', 'string', 'max:150'],
            'cuenta_origen' => ['nullable', 'string', 'max:100'],
            'numero_autorizacion' => ['nullable', 'string', 'max:100'],
            'clave_rastreo' => ['nullable', 'string', 'max:100'],
            'concepto_transferencia' => ['nullable', 'string', 'max:255'],
            'referencia_transferencia' => ['nullable', 'string', 'max:150'],
            'fecha_transferencia' => ['nullable', 'date'],
            'banco_destino' => ['nullable', 'string', 'max:150'],
            'archivo_comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],

            // Tarjeta
            'tarjeta_banco_emisor' => ['nullable', 'string', 'max:150'],
            'tarjeta_numero_autorizacion' => ['nullable', 'string', 'max:100'],
            'comprobante_tarjeta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'monto_total_pagado.min' => 'El monto del pago debe ser mayor a cero.',
            'archivo_comprobante.mimes' => 'El comprobante de transferencia debe ser PDF o imagen JPG/PNG.',
            'comprobante_tarjeta.mimes' => 'El comprobante de tarjeta debe ser PDF o imagen JPG/PNG.',
        ]);

        $cargoIds = collect($validated['cargos'] ?? [])->filter()->unique()->values();
        $parcialidadIds = collect($validated['parcialidades'] ?? [])->filter()->unique()->values();

        if ($cargoIds->isEmpty() && $parcialidadIds->isEmpty()) {
            throw ValidationException::withMessages([
                'cargos' => 'Selecciona al menos un cargo o una parcialidad para aplicar el pago.',
            ]);
        }

        $pago = DB::transaction(function () use ($request, $validated, $alumno, $cargoIds, $parcialidadIds) {
            $corteCaja = CorteCaja::abierta()
                ->deUsuario(Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $corteCaja) {
                throw ValidationException::withMessages([
                    'metodo_pago' => 'Antes de registrar pagos debes abrir una caja desde Finanzas → Cortes de Caja.',
                ]);
            }

            $cargos = Cargo::where('alumno_id', $alumno->id)
                ->whereIn('id', $cargoIds)
                ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->orderBy('fecha_vencimiento')
                ->lockForUpdate()
                ->get();

            if ($cargos->count() !== $cargoIds->count()) {
                throw ValidationException::withMessages([
                    'cargos' => 'Uno o más cargos seleccionados no pertenecen al alumno o ya no están pendientes.',
                ]);
            }

            $parcialidades = ParcialidadConvenio::whereIn('id', $parcialidadIds)
                ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->whereHas('convenio', function ($query) use ($alumno) {
                    $query->where('alumno_id', $alumno->id);
                })
                ->with('convenio')
                ->orderBy('fecha_vencimiento')
                ->lockForUpdate()
                ->get();

            if ($parcialidades->count() !== $parcialidadIds->count()) {
                throw ValidationException::withMessages([
                    'parcialidades' => 'Una o más parcialidades seleccionadas no pertenecen al alumno o ya no están pendientes.',
                ]);
            }

            $archivoComprobante = $this->guardarComprobante($request, $validated['metodo_pago']);
            $montoDisponible = round((float) $validated['monto_total_pagado'], 2);

            $pago = Pago::create([
                'alumno_id' => $alumno->id,
                'usuario_id' => Auth::id(),
                'corte_caja_id' => $corteCaja->id,
                'metodo_pago' => $validated['metodo_pago'],
                'monto_total_pagado' => $montoDisponible,
                'saldo_a_favor_generado' => 0,
                'estatus' => 'Activo',
                'fecha_pago' => $validated['fecha_pago'] ?? now()->toDateString(),
                'folio_recibo' => $validated['folio_recibo'] ?? null,
                'recibo_uuid' => (string) Str::uuid(),
                'recibo_emitido_at' => now(),
                'recibo_version' => 1,
                'referencia_bancaria' => $this->obtenerReferenciaBancaria($validated),
                'archivo_comprobante' => $archivoComprobante,
                'banco_emisor' => $this->obtenerBancoEmisor($validated),
                'cuenta_origen' => $validated['cuenta_origen'] ?? null,
                'numero_autorizacion' => $this->obtenerNumeroAutorizacion($validated),
                'clave_rastreo' => $validated['clave_rastreo'] ?? null,
                'concepto_transferencia' => $validated['concepto_transferencia'] ?? null,
                'fecha_transferencia' => $validated['fecha_transferencia'] ?? null,
                'banco_destino' => $validated['banco_destino'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            if (blank($pago->folio_recibo)) {
                $pago->forceFill([
                    'folio_recibo' => $this->generarFolioRecibo($pago),
                ])->save();
            }

            foreach ($cargos as $cargo) {
                if ($montoDisponible <= 0) {
                    break;
                }

                $montoAplicar = min($montoDisponible, (float) $cargo->monto_adeudo);
                $nuevoAdeudo = round((float) $cargo->monto_adeudo - $montoAplicar, 2);

                $cargo->update([
                    'monto_adeudo' => max(0, $nuevoAdeudo),
                    'estatus' => $nuevoAdeudo <= 0 ? 'Pagado' : 'Parcialmente Pagado',
                ]);

                $pago->cargos()->attach($cargo->id, [
                    'monto_aplicado' => $montoAplicar,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $montoDisponible = round($montoDisponible - $montoAplicar, 2);
            }

            foreach ($parcialidades as $parcialidad) {
                if ($montoDisponible <= 0) {
                    break;
                }

                $montoAplicar = min($montoDisponible, (float) $parcialidad->monto_adeudo);
                $nuevoAdeudo = round((float) $parcialidad->monto_adeudo - $montoAplicar, 2);

                $parcialidad->update([
                    'monto_adeudo' => max(0, $nuevoAdeudo),
                    'estatus' => $nuevoAdeudo <= 0 ? 'Pagado' : 'Parcialmente Pagado',
                ]);

                $pago->parcialidades()->attach($parcialidad->id, [
                    'monto_aplicado' => $montoAplicar,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $montoDisponible = round($montoDisponible - $montoAplicar, 2);
            }

            $saldoAFavorGenerado = max(0, round($montoDisponible, 2));

            if ($saldoAFavorGenerado > 0) {
                $alumno->increment('saldo_a_favor', $saldoAFavorGenerado);

                $pago->forceFill([
                    'saldo_a_favor_generado' => $saldoAFavorGenerado,
                ])->save();
            }

            $this->actualizarConveniosDelAlumno($alumno);
            $this->actualizarEstadoFinanciero($alumno);
            $corteCaja->sincronizarTotalesSistema();

            $this->bitacora(
                'Registrar Pago',
                "Se registró un pago de $ {$validated['monto_total_pagado']} para el alumno {$alumno->nombre_completo} (ID {$alumno->id}). Pago ID {$pago->id}. Corte de caja #{$corteCaja->id}.",
                'Pagos',
                $pago,
                $alumno->id
            );

            return $pago;
        }, 3);

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', "Pago #{$pago->id} registrado correctamente.");
    }

    /**
     * Formulario de confirmación para cancelar un pago.
     */
    public function confirmarCancelacion(Alumno $alumno, Pago $pago)
    {
        $this->validarPagoPerteneceAlumno($alumno, $pago);

        $pago->loadMissing([
            'alumno',
            'usuario',
            'canceladoPor',
            'corteCaja.usuario',
            'cargos.concepto',
            'parcialidades.convenio',
        ]);

        $totalAplicado = $this->calcularTotalAplicado($pago);
        $saldoAFavorGenerado = $this->obtenerSaldoAFavorGenerado($pago);

        return view('pagos.cancelar', compact('alumno', 'pago', 'totalAplicado', 'saldoAFavorGenerado'));
    }

    /**
     * Cancelar pago sin eliminarlo y revertir sus efectos financieros.
     */
    public function cancelar(Request $request, Alumno $alumno, Pago $pago)
    {
        $validated = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'motivo_cancelacion.required' => 'Captura el motivo de cancelación.',
            'motivo_cancelacion.min' => 'El motivo debe explicar claramente la causa de cancelación.',
        ]);

        DB::transaction(function () use ($alumno, $pago, $validated) {
            $pago = Pago::whereKey($pago->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validarPagoPerteneceAlumno($alumno, $pago);

            if ($pago->estaCancelado()) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'Este pago ya fue cancelado anteriormente.',
                ]);
            }

            $corteCaja = $pago->corte_caja_id
                ? CorteCaja::whereKey($pago->corte_caja_id)->lockForUpdate()->first()
                : null;

            if (! $corteCaja) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'No se puede cancelar un pago sin corte de caja asociado.',
                ]);
            }

            if (! $corteCaja->estaAbierta()) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'No se puede cancelar el pago porque su caja ya está cerrada. Para cajas cerradas se requiere un flujo de ajuste administrativo.',
                ]);
            }

            $this->revertirEfectosFinancierosDelPago($pago, $alumno);

            $pago->forceFill([
                'estatus' => 'Cancelado',
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ])->save();

            $alumno->refresh();
            $this->actualizarConveniosDelAlumno($alumno);
            $this->actualizarEstadoFinanciero($alumno);
            $corteCaja->sincronizarTotalesSistema();

            $this->bitacora(
                'Cancelar Pago',
                "Se canceló el pago #{$pago->id} del alumno {$alumno->nombre_completo} (ID {$alumno->id}). Motivo: {$validated['motivo_cancelacion']}",
                'Pagos',
                $pago,
                $alumno->id
            );
        }, 3);

        return redirect()
            ->route('alumnos.pagos.index', $alumno)
            ->with('success', "Pago #{$pago->id} cancelado correctamente. Los adeudos y el corte de caja fueron recalculados.");
    }

    /**
     * Formulario de ajuste para cancelar un pago perteneciente a una caja cerrada.
     */
    public function confirmarAjusteCancelacion(Alumno $alumno, Pago $pago)
    {
        $this->validarPagoPerteneceAlumno($alumno, $pago);

        $pago->loadMissing([
            'alumno',
            'usuario',
            'canceladoPor',
            'corteCaja.usuario',
            'cargos.concepto',
            'parcialidades.convenio',
        ]);

        if ($pago->estaCancelado()) {
            return redirect()
                ->route('alumnos.pagos.index', $alumno)
                ->with('info', 'Este pago ya fue cancelado anteriormente.');
        }

        if (! $pago->corteCaja || ! $pago->corteCaja->estaCerrada()) {
            return redirect()
                ->route('alumnos.pagos.cancelar.confirmar', [$alumno, $pago])
                ->with('info', 'Este pago todavía pertenece a una caja abierta. Usa la cancelación normal.');
        }

        $totalAplicado = $this->calcularTotalAplicado($pago);
        $saldoAFavorGenerado = $this->obtenerSaldoAFavorGenerado($pago);

        return view('pagos.ajuste_cancelacion', compact('alumno', 'pago', 'totalAplicado', 'saldoAFavorGenerado'));
    }

    /**
     * Cancelar un pago de caja cerrada mediante ajuste administrativo.
     */
    public function ajusteCancelacion(Request $request, Alumno $alumno, Pago $pago)
    {
        $validated = $request->validate([
            'motivo_ajuste' => ['required', 'string', 'min:15', 'max:1200'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ], [
            'motivo_ajuste.required' => 'Captura el motivo del ajuste administrativo.',
            'motivo_ajuste.min' => 'El motivo debe explicar claramente por qué se cancela un pago de una caja ya cerrada.',
        ]);

        $ajuste = DB::transaction(function () use ($alumno, $pago, $validated) {
            $pago = Pago::whereKey($pago->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validarPagoPerteneceAlumno($alumno, $pago);

            if ($pago->estaCancelado()) {
                throw ValidationException::withMessages([
                    'motivo_ajuste' => 'Este pago ya fue cancelado anteriormente.',
                ]);
            }

            $corteCaja = $pago->corte_caja_id
                ? CorteCaja::whereKey($pago->corte_caja_id)->lockForUpdate()->first()
                : null;

            if (! $corteCaja) {
                throw ValidationException::withMessages([
                    'motivo_ajuste' => 'No se puede aplicar ajuste a un pago sin corte de caja asociado.',
                ]);
            }

            if (! $corteCaja->estaCerrada()) {
                throw ValidationException::withMessages([
                    'motivo_ajuste' => 'Este pago pertenece a una caja abierta. Usa la cancelación normal para que el corte se recalcule automáticamente.',
                ]);
            }

            $this->revertirEfectosFinancierosDelPago($pago, $alumno, 'motivo_ajuste');

            $ajuste = AjusteCaja::create([
                'corte_caja_id' => $corteCaja->id,
                'pago_id' => $pago->id,
                'alumno_id' => $alumno->id,
                'usuario_id' => Auth::id(),
                'tipo' => AjusteCaja::TIPO_CANCELACION_PAGO_CERRADO,
                'metodo_pago' => $pago->metodo_pago,
                'monto_ajuste' => -1 * abs(round((float) $pago->monto_total_pagado, 2)),
                'estatus' => AjusteCaja::ESTATUS_APLICADO,
                'motivo' => $validated['motivo_ajuste'],
                'observaciones' => $validated['observaciones'] ?? null,
                'fecha_aplicacion' => now(),
            ]);

            $pago->forceFill([
                'estatus' => 'Cancelado',
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => 'Ajuste administrativo en caja cerrada #' . $ajuste->id . ': ' . $validated['motivo_ajuste'],
            ])->save();

            $alumno->refresh();
            $this->actualizarConveniosDelAlumno($alumno);
            $this->actualizarEstadoFinanciero($alumno);

            $this->bitacora(
                'Ajuste de Caja Cerrada',
                "Se canceló mediante ajuste administrativo el pago #{$pago->id} del alumno {$alumno->nombre_completo} (ID {$alumno->id}) dentro del corte cerrado #{$corteCaja->id}. Ajuste #{$ajuste->id}. Motivo: {$validated['motivo_ajuste']}",
                'Cortes de Caja',
                $ajuste,
                $alumno->id
            );

            return $ajuste;
        }, 3);

        return redirect()
            ->route('cortes-caja.show', $ajuste->corte_caja_id)
            ->with('success', "Ajuste administrativo #{$ajuste->id} aplicado correctamente. El pago fue cancelado y el adeudo del alumno fue revertido sin modificar el cierre original de caja.");
    }

    /**
     * Generar y descargar el recibo formal del pago en PDF.
     */
    public function recibo(Alumno $alumno, Pago $pago)
    {
        if ((int) $pago->alumno_id !== (int) $alumno->id) {
            abort(404);
        }

        $pago->loadMissing([
            'alumno.grupo.programa',
            'usuario',
            'canceladoPor',
            'corteCaja.usuario',
            'cargos.concepto',
            'parcialidades.convenio',
        ]);

        $this->asegurarDatosRecibo($pago);

        $totalAplicadoCargos = (float) $pago->cargos->sum(fn ($cargo) => (float) ($cargo->pivot->monto_aplicado ?? 0));
        $totalAplicadoParcialidades = (float) $pago->parcialidades->sum(fn ($parcialidad) => (float) ($parcialidad->pivot->monto_aplicado ?? 0));
        $totalAplicado = round($totalAplicadoCargos + $totalAplicadoParcialidades, 2);
        $saldoAFavorGenerado = $this->obtenerSaldoAFavorGenerado($pago);

        $pdf = Pdf::loadView('pagos.recibo_pdf', [
            'pago' => $pago,
            'alumno' => $alumno,
            'totalAplicadoCargos' => $totalAplicadoCargos,
            'totalAplicadoParcialidades' => $totalAplicadoParcialidades,
            'totalAplicado' => $totalAplicado,
            'saldoAFavorGenerado' => $saldoAFavorGenerado,
        ])->setPaper('letter', 'portrait');

        $filename = 'recibo_' . str_replace(['/', '\\', ' '], '_', $pago->folio_recibo ?: $pago->id) . '.pdf';

        return $pdf->stream($filename);
    }

    private function asegurarDatosRecibo(Pago $pago): void
    {
        $datos = [];

        if (blank($pago->recibo_uuid)) {
            $datos['recibo_uuid'] = (string) Str::uuid();
        }

        if (blank($pago->recibo_emitido_at)) {
            $datos['recibo_emitido_at'] = now();
        }

        if (blank($pago->recibo_version)) {
            $datos['recibo_version'] = 1;
        }

        if (blank($pago->folio_recibo)) {
            $datos['folio_recibo'] = $this->generarFolioRecibo($pago);
        }

        if (! empty($datos)) {
            $pago->forceFill($datos)->save();
            $pago->refresh();
        }
    }

    private function generarFolioRecibo(Pago $pago): string
    {
        $fecha = optional($pago->fecha_pago)->format('Ym') ?: now()->format('Ym');
        $prefijo = ConfiguracionInstitucional::actual()->recibo_prefijo ?: 'IDEJ';

        return sprintf('%s-%s-%06d', strtoupper($prefijo), $fecha, $pago->id);
    }

    private function guardarComprobante(Request $request, string $metodoPago): ?string
    {
        if ($metodoPago === 'Transferencia' && $request->hasFile('archivo_comprobante')) {
            return $request->file('archivo_comprobante')->store('comprobantes/pagos', 'public');
        }

        if ($metodoPago === 'Tarjeta' && $request->hasFile('comprobante_tarjeta')) {
            return $request->file('comprobante_tarjeta')->store('comprobantes/pagos', 'public');
        }

        return null;
    }

    private function obtenerReferenciaBancaria(array $validated): ?string
    {
        if (($validated['metodo_pago'] ?? null) === 'Transferencia') {
            return $validated['referencia_transferencia']
                ?? $validated['clave_rastreo']
                ?? $validated['numero_autorizacion']
                ?? null;
        }

        if (($validated['metodo_pago'] ?? null) === 'Tarjeta') {
            return $validated['tarjeta_numero_autorizacion'] ?? null;
        }

        return $validated['folio_recibo'] ?? null;
    }

    private function obtenerBancoEmisor(array $validated): ?string
    {
        if (($validated['metodo_pago'] ?? null) === 'Tarjeta') {
            return $validated['tarjeta_banco_emisor'] ?? null;
        }

        return $validated['banco_emisor'] ?? null;
    }

    private function obtenerNumeroAutorizacion(array $validated): ?string
    {
        if (($validated['metodo_pago'] ?? null) === 'Tarjeta') {
            return $validated['tarjeta_numero_autorizacion'] ?? null;
        }

        return $validated['numero_autorizacion'] ?? null;
    }

    private function revertirEfectosFinancierosDelPago(Pago $pago, Alumno $alumno, string $campoError = 'motivo_cancelacion'): void
    {
        $pago->loadMissing([
            'cargos',
            'parcialidades.convenio',
        ]);

        foreach ($pago->cargos as $cargoRelacionado) {
            $montoAplicado = round((float) ($cargoRelacionado->pivot->monto_aplicado ?? 0), 2);

            if ($montoAplicado <= 0) {
                continue;
            }

            $cargo = Cargo::whereKey($cargoRelacionado->id)->lockForUpdate()->firstOrFail();
            $nuevoAdeudo = min(
                round((float) $cargo->monto_original, 2),
                round((float) $cargo->monto_adeudo + $montoAplicado, 2)
            );

            $cargo->update([
                'monto_adeudo' => $nuevoAdeudo,
                'estatus' => $this->determinarEstatusCargo($cargo, $nuevoAdeudo),
            ]);
        }

        foreach ($pago->parcialidades as $parcialidadRelacionada) {
            $montoAplicado = round((float) ($parcialidadRelacionada->pivot->monto_aplicado ?? 0), 2);

            if ($montoAplicado <= 0) {
                continue;
            }

            $parcialidad = ParcialidadConvenio::whereKey($parcialidadRelacionada->id)->lockForUpdate()->firstOrFail();
            $nuevoAdeudo = min(
                round((float) $parcialidad->monto_parcialidad, 2),
                round((float) $parcialidad->monto_adeudo + $montoAplicado, 2)
            );

            $parcialidad->update([
                'monto_adeudo' => $nuevoAdeudo,
                'estatus' => $this->determinarEstatusParcialidad($parcialidad, $nuevoAdeudo),
            ]);
        }

        $saldoAFavorGenerado = $this->obtenerSaldoAFavorGenerado($pago);

        if ($saldoAFavorGenerado > 0) {
            $alumnoActual = Alumno::whereKey($alumno->id)->lockForUpdate()->firstOrFail();
            $saldoActual = round((float) $alumnoActual->saldo_a_favor, 2);

            if ($saldoActual + 0.0001 < $saldoAFavorGenerado) {
                throw ValidationException::withMessages([
                    $campoError => 'No se puede cancelar este pago porque el saldo a favor generado ya fue usado total o parcialmente. Primero debe revisarse el movimiento administrativo relacionado.',
                ]);
            }

            $alumnoActual->forceFill([
                'saldo_a_favor' => round($saldoActual - $saldoAFavorGenerado, 2),
            ])->save();
        }
    }

    private function validarPagoPerteneceAlumno(Alumno $alumno, Pago $pago): void
    {
        if ((int) $pago->alumno_id !== (int) $alumno->id) {
            abort(404);
        }
    }

    private function calcularTotalAplicado(Pago $pago): float
    {
        $pago->loadMissing(['cargos', 'parcialidades']);

        $totalCargos = (float) $pago->cargos->sum(fn ($cargo) => (float) ($cargo->pivot->monto_aplicado ?? 0));
        $totalParcialidades = (float) $pago->parcialidades->sum(fn ($parcialidad) => (float) ($parcialidad->pivot->monto_aplicado ?? 0));

        return round($totalCargos + $totalParcialidades, 2);
    }

    private function obtenerSaldoAFavorGenerado(Pago $pago): float
    {
        $saldoRegistrado = round((float) ($pago->saldo_a_favor_generado ?? 0), 2);

        if ($saldoRegistrado > 0) {
            return $saldoRegistrado;
        }

        // Compatibilidad con pagos previos a esta fase: si el campo nuevo aún no tenía
        // valor histórico, se calcula con base en lo recibido menos lo aplicado.
        return max(0, round((float) $pago->monto_total_pagado - $this->calcularTotalAplicado($pago), 2));
    }

    private function determinarEstatusCargo(Cargo $cargo, float $nuevoAdeudo): string
    {
        $montoOriginal = round((float) $cargo->monto_original, 2);

        if ($nuevoAdeudo <= 0) {
            return 'Pagado';
        }

        if ($nuevoAdeudo >= $montoOriginal) {
            return 'Pendiente';
        }

        return 'Parcialmente Pagado';
    }

    private function determinarEstatusParcialidad(ParcialidadConvenio $parcialidad, float $nuevoAdeudo): string
    {
        $montoParcialidad = round((float) $parcialidad->monto_parcialidad, 2);

        if ($nuevoAdeudo <= 0) {
            return 'Pagado';
        }

        if ($nuevoAdeudo >= $montoParcialidad) {
            return 'Pendiente';
        }

        return 'Parcialmente Pagado';
    }

    private function actualizarConveniosDelAlumno(Alumno $alumno): void
    {
        $alumno->convenios()->with('parcialidades')->get()->each(function ($convenio) {
            $pendientes = $convenio->parcialidades()
                ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->count();

            $convenio->update([
                'estatus' => $pendientes === 0 ? 'Finalizado' : 'Activo',
            ]);
        });
    }

    private function actualizarEstadoFinanciero(Alumno $alumno): void
    {
        $adeudosCargos = Cargo::where('alumno_id', $alumno->id)
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->exists();

        $adeudosParcialidades = ParcialidadConvenio::whereHas('convenio', function ($query) use ($alumno) {
                $query->where('alumno_id', $alumno->id);
            })
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->exists();

        if ($adeudosParcialidades) {
            $estatusFinanciero = 'En Convenio';
        } elseif ($adeudosCargos) {
            $estatusFinanciero = 'Con Adeudo';
        } elseif ((int) $alumno->beca_porcentaje > 0) {
            $estatusFinanciero = 'Becado';
        } else {
            $estatusFinanciero = 'Al Corriente';
        }

        $alumno->forceFill([
            'estatus_financiero' => $estatusFinanciero,
        ])->save();
    }
}
