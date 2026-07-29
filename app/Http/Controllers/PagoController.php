<?php

namespace App\Http\Controllers;

use App\Models\AjusteCaja;
use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\ConfiguracionInstitucional;
use App\Models\Convenio;
use App\Models\CorteCaja;
use App\Models\Pago;
use App\Models\ParcialidadConvenio;
use App\Models\NotificacionInterna;
use App\Models\Rol;
use App\Services\PrivateFileService;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagoController extends Controller
{
    use RegistraBitacora;

    public function __construct(private readonly PrivateFileService $privateFiles)
    {
    }

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
                $query->where('alumno_id', $alumno->id)
                    ->where('estatus', 'Activo');
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
            'monto_total_pagado' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'monto_recibido_efectivo' => ['nullable', 'required_if:metodo_pago,Efectivo', 'numeric', 'min:0.01', 'max:99999999.99'],
            'tratamiento_excedente' => ['nullable', 'required_if:metodo_pago,Efectivo', Rule::in(['cambio', 'saldo_favor'])],
            'es_pago_anticipado' => ['nullable', 'boolean'],
            'fecha_pago' => ['nullable', 'date', 'before_or_equal:today'],
            'operacion_uuid' => ['required', 'uuid'],

            'cargos' => ['nullable', 'array'],
            'cargos.*' => ['integer', 'distinct'],
            'parcialidades' => ['nullable', 'array'],
            'parcialidades.*' => ['integer', 'distinct'],

            'observaciones' => ['nullable', 'string', 'max:1000'],

            // Transferencia
            'banco_emisor' => ['nullable', 'string', 'max:150'],
            'cuenta_origen' => ['nullable', 'string', 'max:100'],
            'numero_autorizacion' => ['nullable', 'string', 'max:100'],
            'clave_rastreo' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $request->input('metodo_pago') === 'Transferencia'
                    && blank($request->input('referencia_transferencia'))
                    && blank($request->input('numero_autorizacion'))),
            ],
            'concepto_transferencia' => ['nullable', 'string', 'max:255'],
            'referencia_transferencia' => ['nullable', 'string', 'max:150'],
            'fecha_transferencia' => ['nullable', 'date', 'before_or_equal:now'],
            'banco_destino' => ['nullable', 'string', 'max:150'],
            'archivo_comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:4096'],

            // Tarjeta
            'tarjeta_banco_emisor' => ['nullable', 'string', 'max:150'],
            'tarjeta_numero_autorizacion' => ['nullable', 'required_if:metodo_pago,Tarjeta', 'string', 'max:100'],
            'comprobante_tarjeta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:4096'],
        ], [
            'monto_total_pagado.min' => 'El monto del pago debe ser mayor a cero.',
            'monto_total_pagado.max' => 'El monto del pago excede el límite permitido.',
            'monto_recibido_efectivo.required_if' => 'Captura el efectivo realmente recibido por caja.',
            'tratamiento_excedente.required_if' => 'Indica si la diferencia en efectivo se entrega como cambio o se conserva como saldo a favor.',
            'fecha_pago.before_or_equal' => 'La fecha del pago no puede ser posterior al día de hoy.',
            'fecha_transferencia.before_or_equal' => 'La fecha de la transferencia no puede estar en el futuro.',
            'clave_rastreo.required' => 'Captura una clave de rastreo, referencia o número de autorización para identificar la transferencia.',
            'tarjeta_numero_autorizacion.required_if' => 'Captura el número de autorización de la operación con tarjeta.',
            'archivo_comprobante.mimes' => 'El comprobante de transferencia debe ser PDF o imagen JPG/PNG.',
            'comprobante_tarjeta.mimes' => 'El comprobante de tarjeta debe ser PDF o imagen JPG/PNG.',
        ]);

        $cargoIds = collect($validated['cargos'] ?? [])->filter()->unique()->values();
        $parcialidadIds = collect($validated['parcialidades'] ?? [])->filter()->unique()->values();

        $esPagoAnticipado = (bool) ($validated['es_pago_anticipado'] ?? false);

        if ($cargoIds->isEmpty() && $parcialidadIds->isEmpty() && ! $esPagoAnticipado) {
            throw ValidationException::withMessages([
                'cargos' => 'Selecciona al menos un cargo o parcialidad, o marca el pago como anticipo/saldo a favor.',
            ]);
        }

        $pagoExistente = Pago::where('operacion_uuid', $validated['operacion_uuid'])->first();

        if ($pagoExistente && (int) $pagoExistente->alumno_id === (int) $alumno->id) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('info', "El pago #{$pagoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
        }

        if ($pagoExistente) {
            throw ValidationException::withMessages([
                'operacion_uuid' => 'La operación ya fue utilizada en otro registro. Recarga el formulario antes de volver a intentarlo.',
            ]);
        }

        $archivoComprobante = $this->guardarComprobante($request, $validated['metodo_pago']);

        try {
            $pago = DB::transaction(function () use ($validated, $alumno, $cargoIds, $parcialidadIds, $archivoComprobante) {
            $corteCaja = CorteCaja::abierta()
                ->deUsuario(Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $corteCaja) {
                throw ValidationException::withMessages([
                    'metodo_pago' => 'Antes de registrar pagos debes abrir una caja desde Administración financiera → Cortes de Caja.',
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

            $convenioIds = ParcialidadConvenio::whereIn('id', $parcialidadIds)
                ->pluck('convenio_id')
                ->unique()
                ->sort()
                ->values();

            $convenios = Convenio::whereIn('id', $convenioIds)
                ->where('alumno_id', $alumno->id)
                ->where('estatus', 'Activo')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($convenios->count() !== $convenioIds->count()) {
                throw ValidationException::withMessages([
                    'parcialidades' => 'Una o más parcialidades pertenecen a un convenio cancelado, finalizado o ajeno al alumno.',
                ]);
            }

            $parcialidades = ParcialidadConvenio::whereIn('id', $parcialidadIds)
                ->whereIn('convenio_id', $convenioIds)
                ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->whereHas('convenio', function ($query) use ($alumno) {
                    $query->where('alumno_id', $alumno->id)
                        ->where('estatus', 'Activo');
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

            $montoDisponible = round((float) $validated['monto_total_pagado'], 2);
            $montoRecibidoEfectivo = null;
            $cambioEntregado = 0.00;

            if ($validated['metodo_pago'] === 'Efectivo') {
                $montoRecibidoEfectivo = round((float) ($validated['monto_recibido_efectivo'] ?? $montoDisponible), 2);

                if ($montoRecibidoEfectivo + 0.0001 < $montoDisponible) {
                    throw ValidationException::withMessages([
                        'monto_recibido_efectivo' => 'El efectivo recibido no puede ser menor al monto que se está registrando como pago.',
                    ]);
                }

                if (($validated['tratamiento_excedente'] ?? null) === 'saldo_favor' && $montoRecibidoEfectivo > $montoDisponible) {
                    $montoDisponible = $montoRecibidoEfectivo;
                    $cambioEntregado = 0.00;
                } else {
                    $cambioEntregado = max(0, round($montoRecibidoEfectivo - $montoDisponible, 2));
                }
            }

            $pago = Pago::create([
                'alumno_id' => $alumno->id,
                'usuario_id' => Auth::id(),
                'corte_caja_id' => $corteCaja->id,
                'metodo_pago' => $validated['metodo_pago'],
                'monto_total_pagado' => $montoDisponible,
                'monto_recibido_efectivo' => $montoRecibidoEfectivo,
                'cambio_entregado' => $cambioEntregado,
                'tratamiento_excedente' => $validated['tratamiento_excedente'] ?? null,
                'es_pago_anticipado' => (bool) ($validated['es_pago_anticipado'] ?? false),
                'saldo_a_favor_generado' => 0,
                'estatus' => 'Activo',
                'fecha_pago' => $validated['fecha_pago'] ?? now()->toDateString(),
                'folio_recibo' => null,
                'recibo_uuid' => (string) Str::uuid(),
                'recibo_emitido_at' => now(),
                'recibo_version' => 1,
                'operacion_uuid' => $validated['operacion_uuid'],
                'referencia_bancaria' => $this->obtenerReferenciaBancaria($validated),
                'archivo_comprobante' => $archivoComprobante['path'] ?? null,
                'archivo_comprobante_original' => $archivoComprobante['original_name'] ?? null,
                'archivo_comprobante_mime' => $archivoComprobante['mime_type'] ?? null,
                'archivo_comprobante_tamano' => $archivoComprobante['size'] ?? null,
                'archivo_comprobante_sha256' => $archivoComprobante['sha256'] ?? null,
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
                $alumnoActual = Alumno::whereKey($alumno->id)->lockForUpdate()->firstOrFail();
                $alumnoActual->increment('saldo_a_favor', $saldoAFavorGenerado);

                $pago->forceFill([
                    'saldo_a_favor_generado' => $saldoAFavorGenerado,
                ])->save();
            }

            $this->actualizarConveniosDelAlumno($alumno);
            $this->actualizarEstadoFinanciero($alumno);
            $corteCaja->sincronizarTotalesSistema();

            $this->bitacora(
                'Registrar Pago',
                "Se registró un pago de $ {$pago->monto_total_pagado} para el alumno {$alumno->nombre_completo} (ID {$alumno->id}). Pago ID {$pago->id}. Corte de caja #{$corteCaja->id}." . ($pago->es_pago_anticipado ? ' Se registró como anticipo/saldo a favor.' : ''),
                'Pagos',
                $pago,
                $alumno->id
            );

            return $pago;
            }, 3);
        } catch (QueryException $e) {
            $this->eliminarComprobanteSiExiste($archivoComprobante['path'] ?? null);

            if ((string) $e->getCode() === '23000') {
                $pagoExistente = Pago::where('operacion_uuid', $validated['operacion_uuid'])->first();

                if ($pagoExistente && (int) $pagoExistente->alumno_id === (int) $alumno->id) {
                    return redirect()
                        ->route('alumnos.show', $alumno)
                        ->with('info', "El pago #{$pagoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
                }
            }

            // Una violación de integridad distinta al UUID puede indicar una llave
            // foránea, dato obligatorio o catálogo inconsistente. No debe ocultarse
            // como si fuera un doble envío.
            throw $e;
        } catch (\Throwable $e) {
            $this->eliminarComprobanteSiExiste($archivoComprobante['path'] ?? null);

            throw $e;
        }

        try {
            $this->notificarPagoAcademicoSiAplica($pago);
        } catch (\Throwable $e) {
            // El pago ya fue confirmado. Una notificación secundaria nunca debe
            // convertir una operación financiera exitosa en un error para el usuario.
            report($e);
        }

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
     * Descargar comprobante bancario/tarjeta desde disco privado.
     */
    public function descargarComprobante(Alumno $alumno, Pago $pago)
    {
        $this->validarPagoPerteneceAlumno($alumno, $pago);

        if (! $pago->archivo_comprobante) {
            abort(404);
        }

        $path = $this->privateFiles->ensurePrivate($pago->archivo_comprobante);

        if (! $path) {
            $this->bitacora(
                'Incidente Comprobante de Pago',
                "El comprobante del pago #{$pago->id} no existe en almacenamiento privado ni público heredado.",
                'Seguridad de Archivos',
                $pago,
                $alumno->id
            );

            return back()->with('error', 'El comprobante no está disponible. El incidente quedó registrado para revisión de Sistemas.');
        }

        $sha256 = $this->privateFiles->sha256($path);

        if ($pago->archivo_comprobante_sha256 && (! $sha256 || ! hash_equals($pago->archivo_comprobante_sha256, $sha256))) {
            $this->bitacora(
                'Incidente Integridad Comprobante',
                "El comprobante del pago #{$pago->id} no coincide con la huella de integridad registrada.",
                'Seguridad de Archivos',
                $pago,
                $alumno->id
            );

            return back()->with('error', 'El comprobante no superó la validación de integridad. Contacta al área de Sistemas.');
        }

        if (! $pago->archivo_comprobante_sha256 && $sha256) {
            $pago->forceFill(['archivo_comprobante_sha256' => $sha256])->saveQuietly();
        }

        $this->bitacora(
            'Descargar Comprobante de Pago',
            "Se descargó el comprobante del pago #{$pago->id} del alumno {$alumno->nombre_completo}.",
            'Pagos',
            $pago,
            $alumno->id
        );

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $nombre = $pago->archivo_comprobante_original ?: 'comprobante-pago-'.$pago->id.'.'.$extension;

        return $this->privateFiles->download($path, $nombre);
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

    private function notificarPagoAcademicoSiAplica(Pago $pago): void
    {
        $pago->loadMissing(['alumno', 'cargos.concepto']);

        $conceptosAcademicos = $pago->cargos
            ->pluck('concepto.nombre')
            ->filter(fn ($nombre) => filled($nombre))
            ->filter(fn ($nombre) => preg_match('/constancia|credencial|certificado|titulaci[oó]n/iu', $nombre))
            ->unique()
            ->values();

        if ($conceptosAcademicos->isEmpty()) {
            return;
        }

        NotificacionInterna::sincronizar([
            'rol_clave' => Rol::ACADEMICA,
            'tipo' => 'pago_tramite_academico',
            'modulo' => 'Pagos',
            'titulo' => 'Pago de trámite académico registrado',
            'mensaje' => sprintf(
                'Se registró el pago #%d de %s por: %s.',
                $pago->id,
                $pago->alumno?->nombre_completo ?? 'un alumno',
                $conceptosAcademicos->implode(', ')
            ),
            'url' => route('alumnos.show', $pago->alumno_id),
            'severidad' => NotificacionInterna::SEVERIDAD_MEDIA,
            'referencia_tipo' => Pago::class,
            'referencia_id' => $pago->id,
            'metadata' => [
                'alumno_id' => $pago->alumno_id,
                'conceptos' => $conceptosAcademicos->all(),
            ],
        ]);
    }

    private function generarFolioRecibo(Pago $pago): string
    {
        $fecha = optional($pago->fecha_pago)->format('Ym') ?: now()->format('Ym');
        $prefijo = ConfiguracionInstitucional::actual()->recibo_prefijo ?: 'IDEJ';

        return sprintf('%s-%s-%06d', strtoupper($prefijo), $fecha, $pago->id);
    }

    private function guardarComprobante(Request $request, string $metodoPago): ?array
    {
        if ($metodoPago === 'Transferencia' && $request->hasFile('archivo_comprobante')) {
            return $this->privateFiles->store(
                $request->file('archivo_comprobante'),
                'comprobantes/pagos',
                PrivateFileService::DOCUMENT_MIMES,
                'archivo_comprobante'
            );
        }

        if ($metodoPago === 'Tarjeta' && $request->hasFile('comprobante_tarjeta')) {
            return $this->privateFiles->store(
                $request->file('comprobante_tarjeta'),
                'comprobantes/pagos',
                PrivateFileService::DOCUMENT_MIMES,
                'comprobante_tarjeta'
            );
        }

        return null;
    }

    private function eliminarComprobanteSiExiste(?string $path): void
    {
        try {
            $this->privateFiles->delete($path);
        } catch (\Throwable $e) {
            report($e);
        }
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

        return null;
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
