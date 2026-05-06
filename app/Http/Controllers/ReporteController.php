<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Cargo;
use App\Models\Pago;
use App\Models\CicloEscolar;
use App\Models\Programa;
use App\Models\Grupo;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Dashboard financiero con filtros y gráficas.
     */
    public function index(Request $request)
    {
        // ==========================
        // 1. LEER FILTROS DEL FORM
        // ==========================
        $filtros = $request->validate([
            'ciclo_id'      => 'nullable|integer|exists:ciclos_escolares,id',
            'programa_id'   => 'nullable|integer|exists:programas,id',
            'grupo_id'      => 'nullable|integer|exists:grupos,id',
            'fecha_desde'   => 'nullable|date',
            'fecha_hasta'   => 'nullable|date',
            'estatus_cargo' => 'nullable|string|in:Todos,Pendiente,Pagado,En convenio',
        ]);

        $estatusCargo = $filtros['estatus_cargo'] ?? 'Todos';

        // Rango de fechas por defecto: últimos 6 meses
        $fechaDesde = $filtros['fecha_desde'] ?? Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $fechaHasta = $filtros['fecha_hasta'] ?? Carbon::now()->endOfMonth()->toDateString();

        // ==========================
        // 2. QUERIES BASE
        // ==========================
        $cargosQuery = Cargo::with(['alumno.grupo.programa'])
            ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        $pagosQuery = Pago::activos()->with(['alumno.grupo.programa'])
            ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta]);

        // Filtro por ciclo
        if (!empty($filtros['ciclo_id'])) {
            $cicloId = $filtros['ciclo_id'];
            $cargosQuery->whereHas('alumno.grupo.cicloEscolar', function ($q) use ($cicloId) {
                $q->where('id', $cicloId);
            });
            $pagosQuery->whereHas('alumno.grupo.cicloEscolar', function ($q) use ($cicloId) {
                $q->where('id', $cicloId);
            });
        }

        // Filtro por programa
        if (!empty($filtros['programa_id'])) {
            $programaId = $filtros['programa_id'];
            $cargosQuery->whereHas('alumno.grupo.programa', function ($q) use ($programaId) {
                $q->where('id', $programaId);
            });
            $pagosQuery->whereHas('alumno.grupo.programa', function ($q) use ($programaId) {
                $q->where('id', $programaId);
            });
        }

        // Filtro por grupo
        if (!empty($filtros['grupo_id'])) {
            $grupoId = $filtros['grupo_id'];
            $cargosQuery->whereHas('alumno.grupo', function ($q) use ($grupoId) {
                $q->where('id', $grupoId);
            });
            $pagosQuery->whereHas('alumno.grupo', function ($q) use ($grupoId) {
                $q->where('id', $grupoId);
            });
        }

        // Filtro por estatus de cargo
        if ($estatusCargo !== 'Todos') {
            $map = [
                'Pendiente'    => 'Pendiente',
                'Pagado'       => 'Pagado',
                'En convenio'  => 'En Convenio',
            ];
            if (isset($map[$estatusCargo])) {
                $cargosQuery->where('estatus', $map[$estatusCargo]);
            }
        }

        // ==========================
        // 3. OBTENER REGISTROS
        // ==========================
        $cargos = $cargosQuery->get();
        $pagos  = $pagosQuery->get();

        // ==========================
        // 4. TOTALES GENERALES
        // ==========================
        $totalCargos  = $cargos->sum('monto_original');
        $totalAdeudo  = $cargos->sum('monto_adeudo');
        $totalPagos   = $pagos->sum('monto_total_pagado');

        // ==========================
        // 5. AGRUPAR POR MES PARA GRÁFICAS
        // ==========================
        $ingresosPorMes = [];
        $adeudoPorMes   = [];

        $periodo = \Carbon\CarbonPeriod::create(
            Carbon::parse($fechaDesde)->startOfMonth(),
            '1 month',
            Carbon::parse($fechaHasta)->endOfMonth()
        );

        foreach ($periodo as $mes) {
            $key = $mes->format('Y-m');
            $ingresosPorMes[$key] = 0;
            $adeudoPorMes[$key]   = 0;
        }

        foreach ($pagos as $pago) {
            if (!$pago->fecha_pago) {
                continue;
            }
            $key = Carbon::parse($pago->fecha_pago)->format('Y-m');
            if (!isset($ingresosPorMes[$key])) {
                $ingresosPorMes[$key] = 0;
            }
            $ingresosPorMes[$key] += $pago->monto_total_pagado;
        }

        foreach ($cargos as $cargo) {
            $key = Carbon::parse($cargo->created_at)->format('Y-m');
            if (!isset($adeudoPorMes[$key])) {
                $adeudoPorMes[$key] = 0;
            }
            $adeudoPorMes[$key] += $cargo->monto_adeudo;
        }

        $labelsMeses   = [];
        $dataIngresos  = [];
        $dataAdeudos   = [];

        foreach ($ingresosPorMes as $ym => $valor) {
            $labelsMeses[]  = Carbon::createFromFormat('Y-m', $ym)->format('M Y');
            $dataIngresos[] = round($valor, 2);
            $dataAdeudos[]  = round($adeudoPorMes[$ym] ?? 0, 2);
        }

        // ==========================
        // 6. LISTAS PARA FILTROS
        // ==========================
        $ciclos    = CicloEscolar::orderBy('fecha_inicio', 'desc')->get();
        $programas = Programa::orderBy('nombre')->get();
        $grupos    = Grupo::with('programa')->orderBy('nombre')->get();

        // ==========================
        // 7. RETORNAR VISTA
        // ==========================
        return view('reportes.index', [
            'cargos'        => $cargos,
            'pagos'         => $pagos,
            'totalCargos'   => $totalCargos,
            'totalAdeudo'   => $totalAdeudo,
            'totalPagos'    => $totalPagos,
            'labelsMeses'   => $labelsMeses,
            'dataIngresos'  => $dataIngresos,
            'dataAdeudos'   => $dataAdeudos,
            'ciclos'        => $ciclos,
            'programas'     => $programas,
            'grupos'        => $grupos,
            'filtros'       => [
                'ciclo_id'      => $filtros['ciclo_id']      ?? null,
                'programa_id'   => $filtros['programa_id']   ?? null,
                'grupo_id'      => $filtros['grupo_id']      ?? null,
                'fecha_desde'   => $fechaDesde,
                'fecha_hasta'   => $fechaHasta,
                'estatus_cargo' => $estatusCargo,
            ],
        ]);
    }

    /**
     * Exportar a "Excel" (CSV) lo mismo que se ve en pantalla.
     */
    public function exportExcel(Request $request)
    {
        $filename = 'reporte_financiero_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($request) {
            $out = fopen('php://output', 'w');

            // Encabezados
            fputcsv($out, ['Tipo', 'Alumno', 'Programa', 'Grupo', 'Monto', 'Fecha']);

            // Cargos
            $cargos = $this->obtenerCargosFiltrados($request);
            foreach ($cargos as $cargo) {
                fputcsv($out, [
                    'Cargo',
                    optional($cargo->alumno)->nombre_completo,
                    optional(optional($cargo->alumno)->grupo->programa)->nombre,
                    optional($cargo->alumno->grupo)->nombre,
                    $cargo->monto_original,
                    optional($cargo->created_at)?->format('Y-m-d'),
                ]);
            }

            // Pagos
            $pagos = $this->obtenerPagosFiltrados($request);
            foreach ($pagos as $pago) {
                fputcsv($out, [
                    'Pago',
                    optional($pago->alumno)->nombre_completo,
                    optional(optional($pago->alumno)->grupo->programa)->nombre,
                    optional($pago->alumno->grupo)->nombre,
                    $pago->monto_total_pagado,
                    optional($pago->fecha_pago)?->format('Y-m-d'),
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Exportar PDF usando DomPDF, con los mismos filtros que el dashboard.
     */
    public function exportPdf(Request $request)
    {
        // Reutilizamos la misma lógica de filtros a nivel conceptual:
        $filtros = $request->validate([
            'ciclo_id'      => 'nullable|integer|exists:ciclos_escolares,id',
            'programa_id'   => 'nullable|integer|exists:programas,id',
            'grupo_id'      => 'nullable|integer|exists:grupos,id',
            'fecha_desde'   => 'nullable|date',
            'fecha_hasta'   => 'nullable|date',
            'estatus_cargo' => 'nullable|string|in:Todos,Pendiente,Pagado,En convenio',
        ]);

        $estatusCargo = $filtros['estatus_cargo'] ?? 'Todos';

        $fechaDesde = $filtros['fecha_desde'] ?? Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $fechaHasta = $filtros['fecha_hasta'] ?? Carbon::now()->endOfMonth()->toDateString();

        $cargosQuery = Cargo::with(['alumno.grupo.programa'])
            ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        $pagosQuery = Pago::activos()->with(['alumno.grupo.programa'])
            ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta]);

        if (!empty($filtros['ciclo_id'])) {
            $cicloId = $filtros['ciclo_id'];
            $cargosQuery->whereHas('alumno.grupo.cicloEscolar', function ($q) use ($cicloId) {
                $q->where('id', $cicloId);
            });
            $pagosQuery->whereHas('alumno.grupo.cicloEscolar', function ($q) use ($cicloId) {
                $q->where('id', $cicloId);
            });
        }

        if (!empty($filtros['programa_id'])) {
            $programaId = $filtros['programa_id'];
            $cargosQuery->whereHas('alumno.grupo.programa', function ($q) use ($programaId) {
                $q->where('id', $programaId);
            });
            $pagosQuery->whereHas('alumno.grupo.programa', function ($q) use ($programaId) {
                $q->where('id', $programaId);
            });
        }

        if (!empty($filtros['grupo_id'])) {
            $grupoId = $filtros['grupo_id'];
            $cargosQuery->whereHas('alumno.grupo', function ($q) use ($grupoId) {
                $q->where('id', $grupoId);
            });
            $pagosQuery->whereHas('alumno.grupo', function ($q) use ($grupoId) {
                $q->where('id', $grupoId);
            });
        }

        if ($estatusCargo !== 'Todos') {
            $map = [
                'Pendiente'    => 'Pendiente',
                'Pagado'       => 'Pagado',
                'En convenio'  => 'En Convenio',
            ];
            if (isset($map[$estatusCargo])) {
                $cargosQuery->where('estatus', $map[$estatusCargo]);
            }
        }

        $cargos = $cargosQuery->get();
        $pagos  = $pagosQuery->get();

        $totalCargos  = $cargos->sum('monto_original');
        $totalAdeudo  = $cargos->sum('monto_adeudo');
        $totalPagos   = $pagos->sum('monto_total_pagado');

        $data = [
            'cargos'      => $cargos,
            'pagos'       => $pagos,
            'totalCargos' => $totalCargos,
            'totalAdeudo' => $totalAdeudo,
            'totalPagos'  => $totalPagos,
            'fechaDesde'  => $fechaDesde,
            'fechaHasta'  => $fechaHasta,
        ];

        $pdf = Pdf::loadView('reportes.pdf', $data)
            ->setPaper('letter', 'portrait');

        $filename = 'reporte_financiero_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Helpers reutilizables para exportaciones CSV.
     */
    protected function obtenerCargosFiltrados(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(6)->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->endOfMonth()->toDateString());

        return Cargo::with(['alumno.grupo.programa'])
            ->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
            ->get();
    }

    protected function obtenerPagosFiltrados(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->subMonths(6)->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->endOfMonth()->toDateString());

        return Pago::activos()->with(['alumno.grupo.programa'])
            ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
            ->get();
    }
}
