<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\NotificacionInterna;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PrivateFileService;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CorteCajaController extends Controller
{
    use RegistraBitacora;

    public function __construct(private readonly PrivateFileService $privateFiles)
    {
    }

    public function index(Request $request)
    {
        $usuarioActual = Auth::user();
        $puedeSupervisar = $usuarioActual?->puedeSupervisarCajas() ?? false;
        $puedeConsultarTodas = $puedeSupervisar
            || $usuarioActual?->rolClave() === Rol::DIRECCION;

        $query = CorteCaja::with('usuario')
            ->withCount(['pagos' => fn ($query) => $query->activos()])
            ->latest('fecha_apertura');

        if (! $puedeConsultarTodas) {
            $query->where('usuario_id', Auth::id());
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($puedeConsultarTodas && $request->filled('usuario_id')) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        $cortes = $query->paginate(15)->withQueryString();
        $usuarios = $puedeConsultarTodas
            ? Usuario::orderBy('nombre')->get()
            : collect([$usuarioActual])->filter();
        $cajaAbierta = $usuarioActual?->tienePermiso('caja.operar')
            ? CorteCaja::abierta()->deUsuario(Auth::id())->first()
            : null;

        return view('cortes_caja.index', compact(
            'cortes',
            'usuarios',
            'cajaAbierta',
            'puedeSupervisar',
            'puedeConsultarTodas'
        ));
    }

    public function create()
    {
        $cajaAbierta = CorteCaja::abierta()->deUsuario(Auth::id())->first();

        if ($cajaAbierta) {
            return redirect()
                ->route('cortes-caja.show', $cajaAbierta)
                ->with('info', 'Ya tienes una caja abierta. Debes cerrarla antes de abrir otra.');
        }

        return view('cortes_caja.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'saldo_inicial' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'observaciones_apertura' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $corte = DB::transaction(function () use ($validated) {
            $existente = CorteCaja::abierta()
                ->deUsuario(Auth::id())
                ->lockForUpdate()
                ->first();

            if ($existente) {
                throw ValidationException::withMessages([
                    'saldo_inicial' => 'Ya tienes una caja abierta. Cierra la caja actual antes de abrir otra.',
                ]);
            }

            $corte = CorteCaja::create([
                'usuario_id' => Auth::id(),
                'usuario_caja_abierta_id' => Auth::id(),
                'fecha_apertura' => now(),
                'saldo_inicial' => $validated['saldo_inicial'],
                'estatus' => CorteCaja::ESTATUS_ABIERTA,
                'observaciones_apertura' => $validated['observaciones_apertura'] ?? null,
            ]);

            $this->bitacora(
                'Abrir Caja',
                "Se abrió el corte de caja #{$corte->id} con saldo inicial de $ " . number_format((float) $corte->saldo_inicial, 2)
            );

            return $corte;
            }, 3);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'saldo_inicial' => 'Ya existe una caja abierta para tu usuario. Se evitó abrir una caja duplicada.',
                ]);
            }

            throw $e;
        }

        return redirect()
            ->route('cortes-caja.show', $corte)
            ->with('success', 'Caja abierta correctamente. Ya puedes registrar pagos.');
    }

    public function show(CorteCaja $corteCaja)
    {
        $this->autorizarConsultaCaja($corteCaja);

        $corteCaja->load([
            'usuario',
            'pagos.alumno',
            'pagos.usuario',
            'pagos.canceladoPor',
            'ajustes.usuario',
            'ajustes.alumno',
            'ajustes.pago',
            'movimientos.usuario',
            'movimientos.canceladoPor',
        ]);

        // En cajas abiertas se muestran totales vivos. En cajas cerradas se conservan
        // los importes capturados al cierre para no alterar el corte histórico.
        $totalesActuales = $corteCaja->estaCerrada()
            ? [
                'efectivo_sistema' => (float) $corteCaja->efectivo_sistema,
                'transferencia_sistema' => (float) $corteCaja->transferencia_sistema,
                'tarjeta_sistema' => (float) $corteCaja->tarjeta_sistema,
                'total_sistema' => (float) $corteCaja->total_sistema,
                'cantidad_pagos' => (int) $corteCaja->cantidad_pagos,
            ]
            : $corteCaja->calcularTotalesSistema();

        $resumenAjustes = $corteCaja->resumenAjustes();
        $resumenMovimientos = $corteCaja->resumenMovimientos();

        return view('cortes_caja.show', compact('corteCaja', 'totalesActuales', 'resumenAjustes', 'resumenMovimientos'));
    }

    public function cierre(CorteCaja $corteCaja)
    {
        $this->autorizarOperacionCaja($corteCaja);

        if ($corteCaja->estaCerrada()) {
            return redirect()
                ->route('cortes-caja.show', $corteCaja)
                ->with('info', 'Esta caja ya fue cerrada.');
        }

        $corteCaja->load(['usuario', 'pagos.alumno', 'movimientos.usuario']);
        $totalesActuales = $corteCaja->calcularTotalesSistema();
        $resumenMovimientos = $corteCaja->resumenMovimientos();

        return view('cortes_caja.cierre', compact('corteCaja', 'totalesActuales', 'resumenMovimientos'));
    }

    public function cerrar(Request $request, CorteCaja $corteCaja)
    {
        $this->autorizarOperacionCaja($corteCaja);

        if ($corteCaja->estaCerrada()) {
            return redirect()
                ->route('cortes-caja.show', $corteCaja)
                ->with('info', 'Esta caja ya fue cerrada.');
        }

        $validated = $request->validate([
            'efectivo_reportado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'transferencia_reportado' => ['required', 'numeric', 'min:-99999999.99', 'max:99999999.99'],
            'tarjeta_reportado' => ['required', 'numeric', 'min:-99999999.99', 'max:99999999.99'],
            'otro_reportado' => ['required', 'numeric', 'min:-99999999.99', 'max:99999999.99'],
            'observaciones_cierre' => ['nullable', 'string', 'max:1500'],
            'confirmar_diferencia' => ['nullable', 'accepted'],
        ]);

        $corteCerrado = DB::transaction(function () use ($validated, $corteCaja) {
            $corte = CorteCaja::whereKey($corteCaja->id)->lockForUpdate()->firstOrFail();
            $this->autorizarOperacionCaja($corte);

            if ($corte->estaCerrada()) {
                throw ValidationException::withMessages([
                    'efectivo_reportado' => 'Esta caja ya fue cerrada por otro proceso.',
                ]);
            }

            $totales = $corte->calcularTotalesSistema();
            $movimientos = $corte->resumenMovimientos();

            $esperados = [
                'efectivo' => round((float) $corte->saldo_inicial + (float) $totales['efectivo_sistema'] + (float) $movimientos['neto_efectivo'], 2),
                'transferencia' => round((float) $totales['transferencia_sistema'] + (float) $movimientos['neto_transferencia'], 2),
                'tarjeta' => round((float) $totales['tarjeta_sistema'] + (float) $movimientos['neto_tarjeta'], 2),
                'otro' => round((float) $movimientos['neto_otro'], 2),
            ];

            $reportados = [
                'efectivo' => round((float) $validated['efectivo_reportado'], 2),
                'transferencia' => round((float) $validated['transferencia_reportado'], 2),
                'tarjeta' => round((float) $validated['tarjeta_reportado'], 2),
                'otro' => round((float) $validated['otro_reportado'], 2),
            ];

            $diferencias = [
                'efectivo' => round($reportados['efectivo'] - $esperados['efectivo'], 2),
                'transferencia' => round($reportados['transferencia'] - $esperados['transferencia'], 2),
                'tarjeta' => round($reportados['tarjeta'] - $esperados['tarjeta'], 2),
                'otro' => round($reportados['otro'] - $esperados['otro'], 2),
            ];

            $totalEsperado = round(array_sum($esperados), 2);
            $totalReportado = round(array_sum($reportados), 2);
            $diferenciaTotal = round($totalReportado - $totalEsperado, 2);

            $diferenciasParaValidar = array_values($diferencias);
            $diferenciasParaValidar[] = $diferenciaTotal;

            if (CorteCaja::tieneDiferencia(...$diferenciasParaValidar)) {
                $observaciones = trim((string) ($validated['observaciones_cierre'] ?? ''));

                if (strlen($observaciones) < 10) {
                    throw ValidationException::withMessages([
                        'observaciones_cierre' => 'Cuando existe una diferencia debes explicar la causa con al menos 10 caracteres.',
                    ]);
                }

                if (! ($validated['confirmar_diferencia'] ?? false)) {
                    throw ValidationException::withMessages([
                        'confirmar_diferencia' => 'Confirma expresamente que revisaste y aceptas cerrar la caja con diferencia.',
                    ]);
                }
            }

            $corte->update([
                'fecha_cierre' => now(),
                'efectivo_sistema' => $totales['efectivo_sistema'],
                'transferencia_sistema' => $totales['transferencia_sistema'],
                'tarjeta_sistema' => $totales['tarjeta_sistema'],
                'total_sistema' => $totales['total_sistema'],
                'cantidad_pagos' => $totales['cantidad_pagos'],
                'efectivo_reportado' => $reportados['efectivo'],
                'transferencia_reportado' => $reportados['transferencia'],
                'tarjeta_reportado' => $reportados['tarjeta'],
                'otro_reportado' => $reportados['otro'],
                'total_reportado' => $totalReportado,
                'diferencia_efectivo' => $diferencias['efectivo'],
                'diferencia_transferencia' => $diferencias['transferencia'],
                'diferencia_tarjeta' => $diferencias['tarjeta'],
                'diferencia_otro' => $diferencias['otro'],
                'diferencia_total' => $diferenciaTotal,
                'estatus' => CorteCaja::ESTATUS_CERRADA,
                'usuario_caja_abierta_id' => null,
                'observaciones_cierre' => $validated['observaciones_cierre'] ?? null,
            ]);

            $this->bitacora(
                'Cerrar Caja',
                "Se cerró el corte de caja #{$corte->id}. Total esperado: $ " . number_format($totalEsperado, 2) .
                ". Total reportado: $ " . number_format($totalReportado, 2) .
                ". Diferencia total: $ " . number_format($diferenciaTotal, 2)
            );

            return $corte->fresh();
        }, 3);

        $tieneDiferencias = $this->corteTieneDiferencias($corteCerrado);

        if ($tieneDiferencias) {
            try {
                $this->notificarCierreConDiferencia($corteCerrado);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with(
                'success',
                $tieneDiferencias
                    ? 'Caja cerrada con diferencia documentada. El caso quedó notificado para revisión administrativa.'
                    : 'Caja cerrada correctamente sin diferencias.'
            );
    }


    public function registrarMovimiento(Request $request, CorteCaja $corteCaja)
    {
        $this->autorizarOperacionCaja($corteCaja);

        if ($corteCaja->estaCerrada()) {
            return back()->with('error', 'No se pueden registrar movimientos en una caja cerrada. Usa un ajuste administrativo si necesitas documentar una corrección posterior.');
        }

        $validated = $request->validate([
            'operacion_uuid' => ['required', 'uuid'],
            'tipo' => ['required', 'in:Entrada,Salida'],
            'concepto' => ['required', 'string', 'max:120'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'metodo_pago' => ['required', 'in:Efectivo,Transferencia,Tarjeta,Otro'],
            'referencia' => ['nullable', 'string', 'max:200'],
            'comprobante' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:4096'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'tipo.required' => 'Selecciona si el movimiento es entrada o salida.',
            'concepto.required' => 'Indica el concepto del movimiento.',
            'monto.required' => 'Captura el monto del movimiento.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'comprobante.mimetypes' => 'El comprobante debe ser PDF o imagen válida.',
            'comprobante.max' => 'El comprobante no debe pesar más de 4 MB.',
        ]);

        $movimientoExistente = MovimientoCaja::where('operacion_uuid', $validated['operacion_uuid'])->first();

        if ($movimientoExistente && (int) $movimientoExistente->corte_caja_id === (int) $corteCaja->id) {
            return redirect()
                ->route('cortes-caja.show', $corteCaja)
                ->with('info', "El movimiento #{$movimientoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
        }

        if ($movimientoExistente) {
            throw ValidationException::withMessages([
                'operacion_uuid' => 'La operación ya fue utilizada. Recarga la página antes de volver a intentarlo.',
            ]);
        }

        $archivoGuardado = $this->guardarComprobanteMovimiento($request);

        try {
            $movimiento = DB::transaction(function () use ($validated, $corteCaja, $archivoGuardado) {
                $corte = CorteCaja::whereKey($corteCaja->id)->lockForUpdate()->firstOrFail();
                $this->autorizarOperacionCaja($corte);

                if ($corte->estaCerrada()) {
                    throw ValidationException::withMessages([
                        'monto' => 'La caja fue cerrada por otro proceso. No se registró el movimiento.',
                    ]);
                }

                $monto = round((float) $validated['monto'], 2);

                if ($validated['tipo'] === MovimientoCaja::TIPO_SALIDA && $validated['metodo_pago'] === 'Efectivo') {
                    $efectivoDisponible = $corte->efectivoDisponible();

                    if ($monto - $efectivoDisponible > CorteCaja::TOLERANCIA_DIFERENCIA) {
                        throw ValidationException::withMessages([
                            'monto' => 'La salida excede el efectivo disponible en caja ($'.number_format($efectivoDisponible, 2).').',
                        ]);
                    }
                }

                $movimiento = MovimientoCaja::create([
                    'operacion_uuid' => $validated['operacion_uuid'],
                    'corte_caja_id' => $corte->id,
                    'usuario_id' => Auth::id(),
                    'tipo' => $validated['tipo'],
                    'concepto' => $validated['concepto'],
                    'monto' => $monto,
                    'metodo_pago' => $validated['metodo_pago'],
                    'referencia' => $validated['referencia'] ?? null,
                    'comprobante_path' => $archivoGuardado['path'] ?? null,
                    'comprobante_original' => $archivoGuardado['original_name'] ?? null,
                    'comprobante_mime' => $archivoGuardado['mime_type'] ?? null,
                    'comprobante_tamano' => $archivoGuardado['size'] ?? null,
                    'comprobante_sha256' => $archivoGuardado['sha256'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? null,
                    'fecha_movimiento' => now(),
                    'estatus' => MovimientoCaja::ESTATUS_APLICADO,
                ]);

                $this->bitacora(
                    'Registrar Movimiento de Caja',
                    "Se registró un movimiento operativo ({$movimiento->tipo}) en el corte de caja #{$corte->id} por $" . number_format((float) $movimiento->monto, 2) . ". Concepto: {$movimiento->concepto}.",
                    'Caja y Cortes',
                    $movimiento
                );

                return $movimiento;
            }, 3);
        } catch (QueryException $e) {
            $this->eliminarComprobanteMovimiento($archivoGuardado['path'] ?? null);

            if ((string) $e->getCode() === '23000') {
                $movimientoExistente = MovimientoCaja::where('operacion_uuid', $validated['operacion_uuid'])->first();

                if ($movimientoExistente && (int) $movimientoExistente->corte_caja_id === (int) $corteCaja->id) {
                    return redirect()
                        ->route('cortes-caja.show', $corteCaja)
                        ->with('info', "El movimiento #{$movimientoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
                }
            }

            throw $e;
        } catch (\Throwable $e) {
            $this->eliminarComprobanteMovimiento($archivoGuardado['path'] ?? null);

            throw $e;
        }

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with('success', "Movimiento de caja #{$movimiento->id} registrado correctamente.");
    }

    public function descargarComprobanteMovimiento(Request $request, CorteCaja $corteCaja, MovimientoCaja $movimientoCaja)
    {
        $this->autorizarOperacionCaja($corteCaja);
        abort_if((int) $movimientoCaja->corte_caja_id !== (int) $corteCaja->id, 404);

        if (! $movimientoCaja->comprobante_path) {
            abort(404);
        }

        $path = $this->privateFiles->ensurePrivate($movimientoCaja->comprobante_path);

        if (! $path) {
            $this->bitacora(
                'Incidente Comprobante Movimiento Caja',
                "El comprobante del movimiento #{$movimientoCaja->id} del corte #{$corteCaja->id} no existe en almacenamiento.",
                'Seguridad de Archivos',
                $movimientoCaja
            );

            return back()->with('error', 'El comprobante no está disponible. El incidente quedó registrado para revisión de Sistemas.');
        }

        $sha256 = $this->privateFiles->sha256($path);

        if ($movimientoCaja->comprobante_sha256 && (! $sha256 || ! hash_equals($movimientoCaja->comprobante_sha256, $sha256))) {
            $this->bitacora(
                'Incidente Integridad Movimiento Caja',
                "El comprobante del movimiento #{$movimientoCaja->id} no coincide con su huella registrada.",
                'Seguridad de Archivos',
                $movimientoCaja
            );

            return back()->with('error', 'El comprobante no superó la validación de integridad. Contacta al área de Sistemas.');
        }

        if (! $movimientoCaja->comprobante_sha256 && $sha256) {
            $movimientoCaja->forceFill(['comprobante_sha256' => $sha256])->saveQuietly();
        }

        $this->bitacora(
            'Descargar Comprobante Movimiento Caja',
            "Se descargó el comprobante del movimiento #{$movimientoCaja->id} del corte #{$corteCaja->id}.",
            'Caja y Cortes',
            $movimientoCaja
        );

        return $this->privateFiles->download(
            $path,
            $movimientoCaja->comprobante_original ?: 'comprobante-movimiento-'.$movimientoCaja->id.'.pdf'
        );
    }

    public function cancelarMovimiento(Request $request, CorteCaja $corteCaja, MovimientoCaja $movimientoCaja)
    {
        $this->autorizarOperacionCaja($corteCaja);

        if ((int) $movimientoCaja->corte_caja_id !== (int) $corteCaja->id) {
            abort(404);
        }

        if ($corteCaja->estaCerrada()) {
            return back()->with('error', 'No se puede cancelar un movimiento de una caja cerrada. Registra un ajuste administrativo para conservar el cierre original.');
        }

        $validated = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:8', 'max:1000'],
        ], [
            'motivo_cancelacion.required' => 'Indica el motivo de cancelación del movimiento.',
            'motivo_cancelacion.min' => 'El motivo debe explicar claramente por qué se cancela el movimiento.',
        ]);

        DB::transaction(function () use ($validated, $movimientoCaja, $corteCaja) {
            $corte = CorteCaja::whereKey($corteCaja->id)->lockForUpdate()->firstOrFail();
            $this->autorizarOperacionCaja($corte);

            if ($corte->estaCerrada()) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'La caja fue cerrada por otro proceso. No se canceló el movimiento.',
                ]);
            }

            $movimiento = MovimientoCaja::whereKey($movimientoCaja->id)->lockForUpdate()->firstOrFail();

            if ((int) $movimiento->corte_caja_id !== (int) $corte->id) {
                abort(404);
            }

            if ($movimiento->estaCancelado()) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'Este movimiento ya fue cancelado anteriormente.',
                ]);
            }

            $movimiento->update([
                'estatus' => MovimientoCaja::ESTATUS_CANCELADO,
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ]);

            $this->bitacora(
                'Cancelar Movimiento de Caja',
                "Se canceló un movimiento operativo del corte de caja #{$movimiento->corte_caja_id}. Folio interno del movimiento: #{$movimiento->id}. Motivo: {$validated['motivo_cancelacion']}.",
                'Caja y Cortes',
                $movimiento
            );
        }, 3);

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with('success', 'Movimiento de caja cancelado correctamente.');
    }

    private function guardarComprobanteMovimiento(Request $request): ?array
    {
        if (! $request->hasFile('comprobante')) {
            return null;
        }

        return $this->privateFiles->store(
            $request->file('comprobante'),
            'comprobantes/movimientos_caja',
            PrivateFileService::DOCUMENT_MIMES,
            'comprobante'
        );
    }

    private function eliminarComprobanteMovimiento(?string $path): void
    {
        try {
            $this->privateFiles->delete($path);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function corteTieneDiferencias(CorteCaja $corteCaja): bool
    {
        return CorteCaja::tieneDiferencia(
            (float) $corteCaja->diferencia_efectivo,
            (float) $corteCaja->diferencia_transferencia,
            (float) $corteCaja->diferencia_tarjeta,
            (float) $corteCaja->diferencia_otro,
            (float) $corteCaja->diferencia_total,
        );
    }

    private function notificarCierreConDiferencia(CorteCaja $corteCaja): void
    {
        $corteCaja->loadMissing('usuario');

        foreach ([Rol::ADMIN, Rol::CADMIN] as $rolClave) {
            NotificacionInterna::sincronizar([
                'rol_clave' => $rolClave,
                'tipo' => 'cierre_caja_con_diferencia',
                'modulo' => 'Caja y Cortes',
                'titulo' => 'Corte de caja con diferencia',
                'mensaje' => sprintf(
                    'La caja #%d de %s cerró con una diferencia total de $%s. Revisa las observaciones del cierre.',
                    $corteCaja->id,
                    $corteCaja->usuario?->nombre ?? 'un usuario',
                    number_format((float) $corteCaja->diferencia_total, 2)
                ),
                'url' => route('cortes-caja.show', $corteCaja),
                'severidad' => NotificacionInterna::SEVERIDAD_ALTA,
                'referencia_tipo' => CorteCaja::class,
                'referencia_id' => $corteCaja->id,
                'metadata' => [
                    'diferencia_efectivo' => (float) $corteCaja->diferencia_efectivo,
                    'diferencia_transferencia' => (float) $corteCaja->diferencia_transferencia,
                    'diferencia_tarjeta' => (float) $corteCaja->diferencia_tarjeta,
                    'diferencia_otro' => (float) $corteCaja->diferencia_otro,
                    'diferencia_total' => (float) $corteCaja->diferencia_total,
                ],
            ]);
        }
    }

    private function autorizarConsultaCaja(CorteCaja $corteCaja): void
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario) {
            abort(403);
        }

        if ($usuario->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION)) {
            return;
        }

        if ($usuario->rolClave() === Rol::RECEPCION
            && (int) $corteCaja->usuario_id === (int) $usuario->id) {
            return;
        }

        abort(403, 'No tienes permiso para consultar esta caja.');
    }

    private function autorizarOperacionCaja(CorteCaja $corteCaja): void
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario || ! $usuario->puedeOperarCaja($corteCaja)) {
            abort(403, 'No tienes permiso para operar la caja de otro usuario.');
        }
    }

    public function pdf(CorteCaja $corteCaja)
    {
        if (! Auth::user()?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION)) {
            abort(403);
        }

        if ($corteCaja->estaAbierta()) {
            return back()->with('error', 'El PDF oficial del corte se genera cuando la caja ya está cerrada.');
        }

        $corteCaja->load([
            'usuario',
            'pagos.alumno',
            'pagos.usuario',
            'pagos.canceladoPor',
            'ajustes.usuario',
            'ajustes.alumno',
            'ajustes.pago',
            'movimientos.usuario',
            'movimientos.canceladoPor',
        ]);

        $totalesActuales = [
            'efectivo_sistema' => (float) $corteCaja->efectivo_sistema,
            'transferencia_sistema' => (float) $corteCaja->transferencia_sistema,
            'tarjeta_sistema' => (float) $corteCaja->tarjeta_sistema,
            'total_sistema' => (float) $corteCaja->total_sistema,
            'cantidad_pagos' => (int) $corteCaja->cantidad_pagos,
        ];

        $pdf = Pdf::loadView('cortes_caja.pdf', [
            'corteCaja' => $corteCaja,
            'totalesActuales' => $totalesActuales,
            'resumenMovimientos' => $corteCaja->resumenMovimientos(),
            'resumenAjustes' => $corteCaja->resumenAjustes(),
        ])->setPaper('letter', 'portrait');

        $this->bitacora(
            'Generar PDF Corte de Caja',
            "Se generó PDF oficial del corte de caja #{$corteCaja->id}.",
            'Caja y Cortes',
            $corteCaja
        );

        return $pdf->stream('corte_caja_'.$corteCaja->id.'.pdf');
    }

}
