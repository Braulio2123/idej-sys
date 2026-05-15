<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\Pago;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
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
                "Se abrió la caja #{$corte->id} con saldo inicial de $ " . number_format((float) $corte->saldo_inicial, 2)
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

        return view('cortes_caja.show', compact('corteCaja', 'totalesActuales', 'resumenAjustes'));
    }

    public function cierre(CorteCaja $corteCaja)
    {
        if ($corteCaja->estaCerrada()) {
            return redirect()
                ->route('cortes-caja.show', $corteCaja)
                ->with('info', 'Esta caja ya fue cerrada.');
        }

        $corteCaja->load(['usuario', 'pagos.alumno']);
        $totalesActuales = $corteCaja->calcularTotalesSistema();

        return view('cortes_caja.cierre', compact('corteCaja', 'totalesActuales'));
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

            $efectivoEsperado = round((float) $corte->saldo_inicial + (float) $totales['efectivo_sistema'], 2);

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
                'diferencia_total' => round($totalReportado - ((float) $corte->saldo_inicial + (float) $totales['total_sistema']), 2),
                'estatus' => CorteCaja::ESTATUS_CERRADA,
                'usuario_caja_abierta_id' => null,
                'observaciones_cierre' => $validated['observaciones_cierre'] ?? null,
            ]);

            $this->bitacora(
                'Cerrar Caja',
                "Se cerró la caja #{$corte->id}. Total sistema: $ " . number_format((float) $totales['total_sistema'], 2) .
                ". Total reportado: $ " . number_format($totalReportado, 2) .
                ". Diferencia total: $ " . number_format((float) $corte->diferencia_total, 2)
            );
        });

        return redirect()
            ->route('cortes-caja.show', $corteCaja)
            ->with('success', 'Caja cerrada correctamente.');
    }
}
