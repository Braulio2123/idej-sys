<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Pago;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FinanzaController extends Controller
{
    public function index(): View
    {
        /** @var AlumnoPortal|null $alumno */
        $alumno = Auth::guard('portal_alumno')->user();

        abort_unless($alumno instanceof AlumnoPortal, 401);

        $alumno->load(['grupo.programa', 'grupo.cicloEscolar']);

        $cargosBase = Cargo::query()
            ->with('concepto')
            ->where('alumno_id', $alumno->id)
            ->whereNotIn('estatus', ['Pagado', 'Cancelado'])
            ->where('monto_adeudo', '>', 0);

        $totalAdeudo = (clone $cargosBase)->sum('monto_adeudo');

        $cargosVencidos = (clone $cargosBase)
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->count();

        $proximoVencimiento = (clone $cargosBase)
            ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
            ->orderBy('fecha_vencimiento')
            ->first();

        $cargos = (clone $cargosBase)
            ->orderByRaw("CASE WHEN fecha_vencimiento < CURDATE() THEN 0 ELSE 1 END")
            ->orderBy('fecha_vencimiento')
            ->orderByDesc('id')
            ->get();

        $pagos = Pago::query()
            ->with(['cargos.concepto'])
            ->where('alumno_id', $alumno->id)
            ->where('estatus', '!=', 'Cancelado')
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $totalPagado = Pago::query()
            ->where('alumno_id', $alumno->id)
            ->where('estatus', '!=', 'Cancelado')
            ->sum('monto_total_pagado');

        return view('portal_alumno.finanzas.index', compact(
            'alumno',
            'cargos',
            'pagos',
            'totalAdeudo',
            'totalPagado',
            'cargosVencidos',
            'proximoVencimiento'
        ));
    }
}
