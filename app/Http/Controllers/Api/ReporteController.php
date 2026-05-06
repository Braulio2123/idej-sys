<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cargo;
use App\Models\Pago;

class ReporteController extends Controller
{
    //
    public function ApiReporte(Request $request)
    {
        $filtros = $request->validate([
            'ciclo_id' => 'nullable|integer|exists:ciclos_escolares,id',
            'programa_id' => 'nullable|integer|exists:programas,id',
            'grupo_id' => 'nullable|integer|exists:grupos,id',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
            'estatus_cargo' => 'nullable|string|in:Todos,Pendiente,Pagado,En convenio',
        ]);

        $estatusCargo = $filtros['estatus_cargo'] ?? 'Todos';

        $fechaDesde = $filtros['fecha_desde'] ?? now()->subMonths(6)->startOfMonth()->toDateString();
        $fechaHasta = $filtros['fecha_hasta'] ?? now()->endOfMonth()->toDateString();

        // ==========================
        // QUERIES (IGUAL QUE EN INDEX)
        // ==========================
        $cargosQuery = Cargo::with(['alumno.grupo.programa'])
            ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        $pagosQuery = Pago::activos()->with(['alumno.grupo.programa'])
            ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta]);

        if (!empty($filtros['ciclo_id'])) {
            $cicloId = $filtros['ciclo_id'];
            $cargosQuery->whereHas('alumno.grupo.cicloEscolar', fn($q) => $q->where('id', $cicloId));
            $pagosQuery->whereHas('alumno.grupo.cicloEscolar', fn($q) => $q->where('id', $cicloId));
        }

        if (!empty($filtros['programa_id'])) {
            $programaId = $filtros['programa_id'];
            $cargosQuery->whereHas('alumno.grupo.programa', fn($q) => $q->where('id', $programaId));
            $pagosQuery->whereHas('alumno.grupo.programa', fn($q) => $q->where('id', $programaId));
        }

        if (!empty($filtros['grupo_id'])) {
            $grupoId = $filtros['grupo_id'];
            $cargosQuery->whereHas('alumno.grupo', fn($q) => $q->where('id', $grupoId));
            $pagosQuery->whereHas('alumno.grupo', fn($q) => $q->where('id', $grupoId));
        }

        if ($estatusCargo !== 'Todos') {
            $map = [
                'Pendiente' => 'Pendiente',
                'Pagado' => 'Pagado',
                'En convenio' => 'En Convenio',
            ];
            if (isset($map[$estatusCargo])) {
                $cargosQuery->where('estatus', $map[$estatusCargo]);
            }
        }

        $cargos = $cargosQuery->get();
        $pagos = $pagosQuery->get();

        // ==========================
        // MAPEAR AL FORMATO QUE PIDE ANDROID
        // ==========================
        $cargosMapped = $cargos->map(function ($c) {
            return [
                'alumno' => $c->alumno?->nombre_completo,
                'programa' => $c->alumno?->grupo?->programa?->nombre,
                'grupo' => $c->alumno?->grupo?->nombre,
                'monto' => $c->monto_original,
                'fecha' => $c->created_at?->format('Y-m-d'),
            ];
        });

        $pagosMapped = $pagos->map(function ($p) {
            return [
                'alumno' => $p->alumno?->nombre_completo,
                'programa' => $p->alumno?->grupo?->programa?->nombre,
                'grupo' => $p->alumno?->grupo?->nombre,
                'monto' => $p->monto_total_pagado,
                'fecha' => $p->fecha_pago?->format('Y-m-d'),
            ];
        });

        return response()->json([
            'cargos' => $cargosMapped,
            'pagos' => $pagosMapped,
            'totalCargos' => $cargos->sum('monto_original'),
            'totalAdeudo' => $cargos->sum('monto_adeudo'),
            'totalPagos' => $pagos->sum('monto_total_pagado'),
        ]);
    }

}
