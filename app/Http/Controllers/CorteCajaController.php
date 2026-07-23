<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\Usuario;
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

    public function index(Request $request)
    {
        $query = CorteCaja::with('usuario')
            ->withCount(['pagos' => fn ($query) => $query->activos()])
            ->latest('fecha_apertura');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        $cortes = $query->paginate(15)->withQueryString();
        $usuarios = Usuario::orderBy('nombre')->get();
        $cajaAbierta = CorteCaja::abierta()->deUsuario(Auth::id())->first();

        return view('cortes_caja.index', compact('cortes', 'usuarios', 'cajaAbierta'));
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
        if ($corteCaja->estaCerrada()) {
            return redirect()
                ->route('cortes-caja.show', $corteCaja)
                ->with('info', 'Esta caja ya fue cerrada.');
        }

        $validated = $request->validate([
            'efectivo_reportado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'transferencia_reportado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tarjeta_reportado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'observaciones_cierre' => ['nullable', 'string', 'max:1500'],
        ]);

        DB::transaction(function () use ($validated, $corteCaja) {
            $corte = CorteCaja::whereKey($corteCaja->id)->lockForUpdate()->firstOrFail();

            if ($corte->estaCerrada()) {
                throw ValidationException::withMessages([
                    'efectivo_reportado' => 'Esta caja ya fue cerrada por otro proceso.',
                ]);
            }

            $totales = $corte->calcularTotalesSistema();

            $efectivoReportado = round((float) $validated['efectivo_reportado'], 2);
            $transferenciaReportada = round((float) $validated['transferencia_reportado'], 2);
            $tarjetaReportada = round((float) $validated['tarjeta_reportado'], 2);
            $totalReportado = round($efectivoReportado + $transferenciaReportada + $tarjetaReportada, 2);

            $resumenMovimientos = $corte->resumenMovimientos();
            $efectivoEsperado = round((float) $corte->saldo_inicial + (float) $totales['efectivo_sistema'] + (float) $resumenMovimientos['neto_efectivo'], 2);
            $totalEsperado = round((float) $corte->saldo_inicial + (float) $totales['total_sistema'] + (float) $resumenMovimientos['neto_total'], 2);

            $corte->update([
                'fecha_cierre' => now(),
                'efectivo_sistema' => $totales['efectivo_sistema'],
                'transferencia_sistema' => $totales['transferencia_sistema'],
                'tarjeta_sistema' => $totales['tarjeta_sistema'],
                'total_sistema' => $totales['total_sistema'],
                'cantidad_pagos' => $totales['cantidad_pagos'],
                'efectivo_reportado' => $efectivoReportado,
                'transferencia_reportado' => $transferenciaReportada,
                'tarjeta_reportado' => $tarjetaReportada,
                'total_reportado' => $totalReportado,
                'diferencia_efectivo' => round($efectivoReportado - $efectivoEsperado, 2),
                'diferencia_total' => round($totalReportado - $totalEsperado, 2),
                'estatus' => CorteCaja::ESTATUS_CERRADA,
                'usuario_caja_abierta_id' => null,
                'observaciones_cierre' => $validated['observaciones_cierre'] ?? null,
            ]);

            $this->bitacora(
                'Cerrar Caja',
                "Se cerró el corte de caja #{$corte->id}. Total sistema: $ " . number_format((float) $totales['total_sistema'], 2) .
                ". Total reportado: $ " . number_format($totalReportado, 2) .
                ". Diferencia total: $ " . number_format((float) $corte->diferencia_total, 2)
            );
        });

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with('success', 'Caja cerrada correctamente.');
    }


    public function registrarMovimiento(Request $request, CorteCaja $corteCaja)
    {
        if ($corteCaja->estaCerrada()) {
            return back()->with('error', 'No se pueden registrar movimientos en una caja cerrada. Usa un ajuste administrativo si necesitas documentar una corrección posterior.');
        }

        if ((int) $corteCaja->usuario_id !== (int) Auth::id() && ! Auth::user()?->tieneRol('Admin', 'CAdmin', 'Finanzas')) {
            abort(403);
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:Entrada,Salida'],
            'concepto' => ['required', 'string', 'max:120'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
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

        DB::transaction(function () use ($validated, $request, $corteCaja) {
            $corte = CorteCaja::whereKey($corteCaja->id)->lockForUpdate()->firstOrFail();

            if ($corte->estaCerrada()) {
                throw ValidationException::withMessages([
                    'monto' => 'La caja fue cerrada por otro proceso. No se registró el movimiento.',
                ]);
            }

            $path = null;
            $original = null;

            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('comprobantes/movimientos_caja', 'local');
                $original = $request->file('comprobante')->getClientOriginalName();
            }

            $movimiento = MovimientoCaja::create([
                'corte_caja_id' => $corte->id,
                'usuario_id' => Auth::id(),
                'tipo' => $validated['tipo'],
                'concepto' => $validated['concepto'],
                'monto' => round((float) $validated['monto'], 2),
                'metodo_pago' => $validated['metodo_pago'],
                'referencia' => $validated['referencia'] ?? null,
                'comprobante_path' => $path,
                'comprobante_original' => $original,
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
        }, 3);

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with('success', 'Movimiento de caja registrado correctamente.');
    }

    public function cancelarMovimiento(Request $request, CorteCaja $corteCaja, MovimientoCaja $movimientoCaja)
    {
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

        DB::transaction(function () use ($validated, $movimientoCaja) {
            $movimiento = MovimientoCaja::whereKey($movimientoCaja->id)->lockForUpdate()->firstOrFail();

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

    public function pdf(CorteCaja $corteCaja)
    {
        if (! Auth::user()?->tieneRol('Admin', 'CAdmin', 'Finanzas', 'Direccion')) {
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
