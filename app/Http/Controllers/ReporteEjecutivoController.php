<?php

namespace App\Http\Controllers;

use App\Models\Beca;
use App\Models\CalendarioSesion;
use App\Models\Cargo;
use App\Models\Convenio;
use App\Models\CorteCaja;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;
use App\Models\Pago;
use App\Models\ParcialidadConvenio;
use App\Models\Prospecto;
use App\Models\SolicitudPagoDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteEjecutivoController extends Controller
{
    public function index(Request $request)
    {
        [$fechaDesde, $fechaHasta, $rango] = $this->resolverRango($request);
        [$inicio, $fin] = $this->fechasParaQuery($fechaDesde, $fechaHasta);
        $hoy = Carbon::today();
        $proximos30 = $hoy->copy()->addDays(30);

        $finanzas = $this->resumenFinanciero($inicio, $fin, $hoy);
        $alumnos = $this->resumenAlumnos($inicio, $fin);
        $prospectos = $this->resumenProspectos($inicio, $fin);
        $becasConvenios = $this->resumenBecasConvenios($hoy);
        $docentes = $this->resumenSolicitudesDocentes($inicio, $fin, $hoy);
        $operacion = $this->resumenOperacionAcademica($hoy, $proximos30);
        $alertas = $this->alertasEjecutivas($finanzas, $prospectos, $becasConvenios, $docentes, $operacion);
        $graficas = $this->seriesMensuales($fechaDesde, $fechaHasta);

        return view('reportes.ejecutivo', [
            'rango' => $rango,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'finanzas' => $finanzas,
            'alumnos' => $alumnos,
            'prospectos' => $prospectos,
            'becasConvenios' => $becasConvenios,
            'docentes' => $docentes,
            'operacion' => $operacion,
            'alertas' => $alertas,
            'graficas' => $graficas,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$fechaDesde, $fechaHasta] = $this->resolverRango($request);
        [$inicio, $fin] = $this->fechasParaQuery($fechaDesde, $fechaHasta);
        $hoy = Carbon::today();
        $proximos30 = $hoy->copy()->addDays(30);

        $finanzas = $this->resumenFinanciero($inicio, $fin, $hoy);
        $alumnos = $this->resumenAlumnos($inicio, $fin);
        $prospectos = $this->resumenProspectos($inicio, $fin);
        $becasConvenios = $this->resumenBecasConvenios($hoy);
        $docentes = $this->resumenSolicitudesDocentes($inicio, $fin, $hoy);
        $operacion = $this->resumenOperacionAcademica($hoy, $proximos30);
        $alertas = $this->alertasEjecutivas($finanzas, $prospectos, $becasConvenios, $docentes, $operacion);

        $filename = 'reporte_ejecutivo_idej_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($fechaDesde, $fechaHasta, $finanzas, $alumnos, $prospectos, $becasConvenios, $docentes, $operacion, $alertas) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Reporte Ejecutivo IDEJ-SYS']);
            fputcsv($out, ['Periodo', $fechaDesde->format('Y-m-d') . ' a ' . $fechaHasta->format('Y-m-d')]);
            fputcsv($out, []);

            fputcsv($out, ['Sección', 'Indicador', 'Valor']);
            foreach ($this->csvFilas($finanzas, 'Finanzas') as $fila) {
                fputcsv($out, $fila);
            }
            foreach ($this->csvFilas($alumnos, 'Alumnos') as $fila) {
                fputcsv($out, $fila);
            }
            foreach ($this->csvFilas($prospectos, 'Prospectos') as $fila) {
                fputcsv($out, $fila);
            }
            foreach ($this->csvFilas($becasConvenios, 'Becas y convenios') as $fila) {
                fputcsv($out, $fila);
            }
            foreach ($this->csvFilas($docentes, 'Solicitudes docentes') as $fila) {
                fputcsv($out, $fila);
            }
            foreach ($this->csvFilas($operacion, 'Operación académica') as $fila) {
                fputcsv($out, $fila);
            }

            fputcsv($out, []);
            fputcsv($out, ['Alertas ejecutivas']);
            fputcsv($out, ['Severidad', 'Título', 'Detalle']);
            foreach ($alertas as $alerta) {
                fputcsv($out, [$alerta['severidad'], $alerta['titulo'], $alerta['detalle']]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolverRango(Request $request): array
    {
        $data = $request->validate([
            'rango' => 'nullable|string|in:hoy,semana,mes,trimestre,anio,personalizado',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $rango = $data['rango'] ?? 'mes';
        $hoy = Carbon::today();

        if ($rango === 'personalizado' && ! empty($data['fecha_desde']) && ! empty($data['fecha_hasta'])) {
            $desde = Carbon::parse($data['fecha_desde'])->startOfDay();
            $hasta = Carbon::parse($data['fecha_hasta'])->endOfDay();
        } else {
            [$desde, $hasta] = match ($rango) {
                'hoy' => [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()],
                'semana' => [$hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek()],
                'trimestre' => [$hoy->copy()->subMonths(2)->startOfMonth(), $hoy->copy()->endOfMonth()],
                'anio' => [$hoy->copy()->startOfYear(), $hoy->copy()->endOfYear()],
                default => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()],
            };
        }

        if ($desde->greaterThan($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return [$desde, $hasta, $rango];
    }

    private function fechasParaQuery(Carbon $desde, Carbon $hasta): array
    {
        return [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()];
    }

    private function resumenFinanciero(Carbon $inicio, Carbon $fin, Carbon $hoy): array
    {
        $pagosActivosPeriodo = Pago::activos()
            ->whereBetween('fecha_pago', [$inicio->toDateString(), $fin->toDateString()]);

        $pagosCanceladosPeriodo = Pago::cancelados()
            ->whereBetween('fecha_cancelacion', [$inicio, $fin]);

        $cargosPeriodo = Cargo::query()
            ->whereBetween('created_at', [$inicio, $fin]);

        $adeudosVigentes = Cargo::query()
            ->where('monto_adeudo', '>', 0)
            ->whereNotIn('estatus', ['Pagado', 'Cancelado']);

        $adeudosVencidos = (clone $adeudosVigentes)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy->toDateString());

        $cortesAbiertos = CorteCaja::abierta()->count();
        $cortesConDiferencia = CorteCaja::cerrada()
            ->whereBetween('fecha_cierre', [$inicio, $fin])
            ->where(function ($query) {
                $query->where('diferencia_total', '!=', 0)
                    ->orWhere('diferencia_efectivo', '!=', 0);
            });

        $ingresos = (float) (clone $pagosActivosPeriodo)->sum('monto_total_pagado');
        $cargosGenerados = (float) (clone $cargosPeriodo)->sum('monto_original');
        $adeudoTotal = (float) (clone $adeudosVigentes)->sum('monto_adeudo');
        $adeudoVencido = (float) (clone $adeudosVencidos)->sum('monto_adeudo');
        $pagosCanceladosMonto = (float) (clone $pagosCanceladosPeriodo)->sum('monto_total_pagado');

        return [
            'ingresos_periodo' => round($ingresos, 2),
            'cargos_generados_periodo' => round($cargosGenerados, 2),
            'adeudo_total' => round($adeudoTotal, 2),
            'adeudo_vencido' => round($adeudoVencido, 2),
            'pagos_activos_count' => (int) (clone $pagosActivosPeriodo)->count(),
            'pagos_cancelados_count' => (int) (clone $pagosCanceladosPeriodo)->count(),
            'pagos_cancelados_monto' => round($pagosCanceladosMonto, 2),
            'cortes_abiertos' => $cortesAbiertos,
            'cortes_con_diferencia' => (int) (clone $cortesConDiferencia)->count(),
            'diferencia_total_periodo' => round(abs((float) (clone $cortesConDiferencia)->sum('diferencia_total')), 2),
            'porcentaje_recuperacion' => $cargosGenerados > 0 ? round(($ingresos / $cargosGenerados) * 100, 2) : 0,
            'por_metodo' => Pago::activos()
                ->select('metodo_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(monto_total_pagado) as monto'))
                ->whereBetween('fecha_pago', [$inicio->toDateString(), $fin->toDateString()])
                ->groupBy('metodo_pago')
                ->orderByDesc('monto')
                ->get(),
            'adeudo_por_programa' => $this->adeudoPorPrograma(5),
        ];
    }

    private function resumenAlumnos(Carbon $inicio, Carbon $fin): array
    {
        $estatusAcademico = DB::table('alumnos')
            ->select('estatus_academico', DB::raw('COUNT(*) as total'))
            ->groupBy('estatus_academico')
            ->orderByDesc('total')
            ->get();

        $estatusFinanciero = DB::table('alumnos')
            ->select('estatus_financiero', DB::raw('COUNT(*) as total'))
            ->groupBy('estatus_financiero')
            ->orderByDesc('total')
            ->get();

        return [
            'total' => DB::table('alumnos')->count(),
            'nuevos_periodo' => DB::table('alumnos')->whereBetween('created_at', [$inicio, $fin])->count(),
            'con_adeudo' => DB::table('alumnos')->where('estatus_financiero', 'Con adeudo')->count(),
            'al_corriente' => DB::table('alumnos')->where('estatus_financiero', 'Al corriente')->count(),
            'estatus_academico' => $estatusAcademico,
            'estatus_financiero' => $estatusFinanciero,
        ];
    }

    private function resumenProspectos(Carbon $inicio, Carbon $fin): array
    {
        $activos = Prospecto::activos()->count();
        $nuevos = Prospecto::query()->whereBetween('created_at', [$inicio, $fin])->count();
        $convertidos = Prospecto::query()
            ->where(function ($query) {
                $query->where('estatus', Prospecto::ESTATUS_INSCRITO)
                    ->orWhereNotNull('alumno_id');
            })
            ->whereBetween('updated_at', [$inicio, $fin])
            ->count();

        return [
            'activos' => $activos,
            'nuevos_periodo' => $nuevos,
            'convertidos_periodo' => $convertidos,
            'vencidos' => Prospecto::vencidos()->count(),
            'proximos_7_dias' => Prospecto::proximos()->count(),
            'conversion_periodo' => $nuevos > 0 ? round(($convertidos / $nuevos) * 100, 2) : 0,
            'por_estatus' => DB::table('prospectos')
                ->select('estatus', DB::raw('COUNT(*) as total'))
                ->groupBy('estatus')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    private function resumenBecasConvenios(Carbon $hoy): array
    {
        $becasActivas = Beca::activas();
        $conveniosActivos = Convenio::query()->whereIn('estatus', ['Activo', 'Vigente', 'En curso']);
        $parcialidadesPendientes = ParcialidadConvenio::query()
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado']);

        $montoConveniosPendiente = (float) (clone $parcialidadesPendientes)->sum('monto_adeudo');
        $parcialidadesVencidas = (clone $parcialidadesPendientes)
            ->whereDate('fecha_vencimiento', '<', $hoy->toDateString());

        return [
            'becas_activas' => (int) (clone $becasActivas)->count(),
            'becas_por_vencer_30' => Beca::activas()
                ->whereNotNull('fecha_fin')
                ->whereBetween('fecha_fin', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()])
                ->count(),
            'promedio_beca' => round((float) Beca::activas()->avg('porcentaje'), 2),
            'monto_becado_en_cargos' => round((float) Cargo::query()->whereNotNull('beca_id')->sum('beca_monto_aplicado'), 2),
            'convenios_activos' => (int) (clone $conveniosActivos)->count(),
            'monto_convenios_pendiente' => round($montoConveniosPendiente, 2),
            'parcialidades_vencidas' => (int) (clone $parcialidadesVencidas)->count(),
            'monto_parcialidades_vencidas' => round((float) (clone $parcialidadesVencidas)->sum('monto_adeudo'), 2),
        ];
    }

    private function resumenSolicitudesDocentes(Carbon $inicio, Carbon $fin, Carbon $hoy): array
    {
        $pendientes = SolicitudPagoDocente::query()->where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE);
        $observadas = SolicitudPagoDocente::query()->where('estatus', SolicitudPagoDocente::ESTATUS_OBSERVADA);
        $autorizadas = SolicitudPagoDocente::query()->where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA);
        $pagadasPeriodo = SolicitudPagoDocente::query()
            ->where('estatus', SolicitudPagoDocente::ESTATUS_PAGADA)
            ->whereBetween('fecha_pago', [$inicio->toDateString(), $fin->toDateString()]);
        $vencidasSinPago = SolicitudPagoDocente::query()
            ->where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA)
            ->whereNotNull('fecha_limite_pago')
            ->whereDate('fecha_limite_pago', '<', $hoy->toDateString());

        return [
            'pendientes' => (int) (clone $pendientes)->count(),
            'pendientes_monto' => round((float) (clone $pendientes)->sum('monto'), 2),
            'observadas' => (int) (clone $observadas)->count(),
            'autorizadas' => (int) (clone $autorizadas)->count(),
            'autorizadas_monto' => round((float) (clone $autorizadas)->sum('monto'), 2),
            'pagadas_periodo' => (int) (clone $pagadasPeriodo)->count(),
            'pagadas_monto_periodo' => round((float) (clone $pagadasPeriodo)->sum('monto'), 2),
            'vencidas_sin_pago' => (int) (clone $vencidasSinPago)->count(),
            'vencidas_sin_pago_monto' => round((float) (clone $vencidasSinPago)->sum('monto'), 2),
            'por_estatus' => DB::table('solicitudes_pago_docentes')
                ->select('estatus', DB::raw('COUNT(*) as total'), DB::raw('SUM(monto) as monto'))
                ->groupBy('estatus')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    private function resumenOperacionAcademica(Carbon $hoy, Carbon $proximos30): array
    {
        $sesionesPrincipales = CalendarioSesion::query()
            ->whereBetween('fecha', [$hoy->toDateString(), $proximos30->toDateString()])
            ->whereNotIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA]);

        $sesionesEc = CursoSesion::query()
            ->whereBetween('fecha', [$hoy->toDateString(), $proximos30->toDateString()])
            ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA]);

        $sesionesPrincipalesIncompletas = CalendarioSesion::query()
            ->whereBetween('fecha', [$hoy->toDateString(), $proximos30->toDateString()])
            ->whereNotIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA])
            ->where(function ($query) {
                $query->whereNull('hora_inicio')
                    ->orWhereNull('hora_fin')
                    ->orWhereNull('aula')
                    ->orWhere('aula', '');
            });

        $sesionesEcIncompletas = CursoSesion::query()
            ->whereBetween('fecha', [$hoy->toDateString(), $proximos30->toDateString()])
            ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])
            ->where(function ($query) {
                $query->whereNull('hora_inicio')
                    ->orWhereNull('hora_fin')
                    ->orWhere(function ($q) {
                        $q->whereNull('docente_id')->whereNull('expositor_nombre');
                    })
                    ->orWhereNull('aula_liga')
                    ->orWhere('aula_liga', '');
            });

        $canceladasSinReposicion = CalendarioSesion::query()
            ->whereIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA])
            ->whereDoesntHave('reposiciones')
            ->count();

        return [
            'sesiones_principales_30' => (int) (clone $sesionesPrincipales)->count(),
            'sesiones_educacion_continua_30' => (int) (clone $sesionesEc)->count(),
            'sesiones_hoy' => CalendarioSesion::query()->whereDate('fecha', $hoy->toDateString())->count()
                + CursoSesion::query()->whereDate('fecha', $hoy->toDateString())->count(),
            'sesiones_incompletas' => (int) (clone $sesionesPrincipalesIncompletas)->count() + (int) (clone $sesionesEcIncompletas)->count(),
            'canceladas_sin_reposicion' => $canceladasSinReposicion,
            'cursos_activos' => CursoEducacionContinua::operativos()->count(),
            'cursos_sin_sesiones' => CursoEducacionContinua::operativos()->doesntHave('sesiones')->count(),
            'calendarios_activos' => DB::table('calendarios_academicos')
                ->whereIn('estatus', ['Planeado', 'Aprobado', 'En curso'])
                ->count(),
        ];
    }

    private function adeudoPorPrograma(int $limite = 5)
    {
        return DB::table('cargos')
            ->join('alumnos', 'alumnos.id', '=', 'cargos.alumno_id')
            ->leftJoin('grupos', 'grupos.id', '=', 'alumnos.grupo_id')
            ->leftJoin('programas', 'programas.id', '=', 'grupos.programa_id')
            ->select(DB::raw("COALESCE(programas.nombre, 'Sin programa') as programa"), DB::raw('SUM(cargos.monto_adeudo) as adeudo'))
            ->where('cargos.monto_adeudo', '>', 0)
            ->whereNotIn('cargos.estatus', ['Pagado', 'Cancelado'])
            ->groupBy('programa')
            ->orderByDesc('adeudo')
            ->limit($limite)
            ->get();
    }

    private function seriesMensuales(Carbon $fechaDesde, Carbon $fechaHasta): array
    {
        $labels = [];
        $ingresos = [];
        $cargos = [];

        $cursor = $fechaDesde->copy()->startOfMonth();
        $fin = $fechaHasta->copy()->endOfMonth();

        while ($cursor->lessThanOrEqualTo($fin)) {
            $inicioMes = $cursor->copy()->startOfMonth();
            $finMes = $cursor->copy()->endOfMonth();

            $labels[] = ucfirst($cursor->translatedFormat('M Y'));
            $ingresos[] = round((float) Pago::activos()
                ->whereBetween('fecha_pago', [$inicioMes->toDateString(), $finMes->toDateString()])
                ->sum('monto_total_pagado'), 2);
            $cargos[] = round((float) Cargo::query()
                ->whereBetween('created_at', [$inicioMes, $finMes])
                ->sum('monto_original'), 2);

            $cursor->addMonth();
        }

        return compact('labels', 'ingresos', 'cargos');
    }

    private function alertasEjecutivas(array $finanzas, array $prospectos, array $becasConvenios, array $docentes, array $operacion): array
    {
        $alertas = [];

        if ($finanzas['cortes_abiertos'] > 0) {
            $alertas[] = [
                'severidad' => 'alta',
                'titulo' => 'Cajas abiertas',
                'detalle' => "Hay {$finanzas['cortes_abiertos']} caja(s) abierta(s). Conviene cerrarlas al finalizar operación para evitar diferencias.",
                'ruta' => route('cortes-caja.index'),
            ];
        }

        if ($finanzas['adeudo_vencido'] > 0) {
            $alertas[] = [
                'severidad' => 'alta',
                'titulo' => 'Cartera vencida',
                'detalle' => 'Adeudo vencido actual: $' . number_format($finanzas['adeudo_vencido'], 2),
                'ruta' => route('reportes.index'),
            ];
        }

        if ($docentes['vencidas_sin_pago'] > 0) {
            $alertas[] = [
                'severidad' => 'critica',
                'titulo' => 'Pagos docentes vencidos',
                'detalle' => "Hay {$docentes['vencidas_sin_pago']} solicitud(es) autorizada(s) vencida(s) sin pago.",
                'ruta' => route('solicitudes_pago.index'),
            ];
        }

        if ($docentes['observadas'] > 0) {
            $alertas[] = [
                'severidad' => 'media',
                'titulo' => 'Solicitudes docentes observadas',
                'detalle' => "Hay {$docentes['observadas']} solicitud(es) observada(s) que requieren corrección o seguimiento.",
                'ruta' => route('solicitudes_pago.index'),
            ];
        }

        if ($operacion['sesiones_incompletas'] > 0) {
            $alertas[] = [
                'severidad' => 'alta',
                'titulo' => 'Sesiones incompletas',
                'detalle' => "Hay {$operacion['sesiones_incompletas']} sesión(es) próximas con datos operativos incompletos.",
                'ruta' => route('centro-control.index'),
            ];
        }

        if ($operacion['canceladas_sin_reposicion'] > 0) {
            $alertas[] = [
                'severidad' => 'media',
                'titulo' => 'Cancelaciones sin reposición',
                'detalle' => "Hay {$operacion['canceladas_sin_reposicion']} clase(s) cancelada(s) o suspendida(s) sin reposición vinculada.",
                'ruta' => route('centro-control.index'),
            ];
        }

        if ($prospectos['vencidos'] > 0) {
            $alertas[] = [
                'severidad' => 'media',
                'titulo' => 'Prospectos vencidos',
                'detalle' => "Hay {$prospectos['vencidos']} prospecto(s) con próximo contacto vencido.",
                'ruta' => route('prospectos.index'),
            ];
        }

        if ($becasConvenios['parcialidades_vencidas'] > 0) {
            $alertas[] = [
                'severidad' => 'alta',
                'titulo' => 'Convenios vencidos',
                'detalle' => "Hay {$becasConvenios['parcialidades_vencidas']} parcialidad(es) de convenio vencida(s).",
                'ruta' => route('reportes.index'),
            ];
        }

        return $alertas;
    }

    private function csvFilas(array $datos, string $seccion): array
    {
        $filas = [];

        foreach ($datos as $clave => $valor) {
            if (is_iterable($valor) && ! is_string($valor)) {
                continue;
            }

            $filas[] = [$seccion, str_replace('_', ' ', $clave), $valor];
        }

        return $filas;
    }
}
