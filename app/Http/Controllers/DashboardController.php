<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Alumno;
use App\Models\Docente;
use App\Models\SolicitudPagoDocente;
use App\Models\Bitacora;
use App\Models\Seguimiento;
use App\Models\DocumentoAlumno;
use App\Models\Prospecto;
use App\Models\CorteCaja;
use App\Models\Pago;
use App\Models\Beca;
use App\Models\Cargo;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioSesion;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;
use App\Models\NotificacionInterna;
use App\Models\Rol;
use App\Models\Usuario;

class DashboardController extends Controller
{
    /**
     * MOSTRAR PANEL DE CONTROL
     * NO APLICA BITÁCORA (solo es lectura de datos)
     */
    public function index()
    {
        $usuario = Auth::user();
        $rol = $usuario->rol->nombre ?? 'Sin rol';

        // Fechas
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        // ADMIN – Datos generales
        $alumnosCount       = Alumno::count();
        $alumnosNuevosMes   = Alumno::whereDate('created_at', '>=', $inicioMes)->count();
        $ultimosAlumnos     = Alumno::latest()->take(5)->get();

        $docentesCount      = Docente::count();
        $docentesNuevosMes  = Docente::whereDate('created_at', '>=', $inicioMes)->count();

        $pagosPendientes    = SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE)->count();
        $pagosAprobados     = SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA)->count();
        $pagosPagados       = SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PAGADA)->count();

        $montoPagadoMes     = SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PAGADA)
                                ->whereDate('fecha_pago', '>=', $inicioMes)
                                ->sum('monto');

        $ultimasSolicitudes = SolicitudPagoDocente::with('docente')
                                ->latest()->take(5)->get();

        // RECEPCIÓN / SEGUIMIENTOS
        $alumnosConAdeudo   = Alumno::where('estatus_financiero', 'Con Adeudo')->count();
        $bitacorasHoy       = Bitacora::whereDate('created_at', $hoy)->count();
        $seguimientosAbiertos = Seguimiento::abiertos()->count();
        $seguimientosVencidos = Seguimiento::vencidos()->count();
        $seguimientosProximos = Seguimiento::proximos()->with('alumno', 'usuario')->orderBy('fecha_proximo_contacto')->take(5)->get();
        $documentosPendientes = DocumentoAlumno::pendientes()->count();
        $documentosRevision = DocumentoAlumno::where('estatus', DocumentoAlumno::ESTATUS_EN_REVISION)->count();
        $documentosRecientes = DocumentoAlumno::with(['alumno', 'usuarioSubio'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        // RELACIONES PÚBLICAS / PROSPECTOS
        $prospectosActivos = Prospecto::activos()->count();
        $prospectosVencidos = Prospecto::vencidos()->count();
        $prospectosInscritosMes = Prospecto::where('estatus', Prospecto::ESTATUS_INSCRITO)
            ->whereDate('fecha_conversion', '>=', $inicioMes)
            ->count();
        $prospectosProximos = Prospecto::proximos()
            ->with(['programa', 'asesor'])
            ->orderBy('fecha_proximo_contacto')
            ->take(5)
            ->get();

        // BECAS INSTITUCIONALES
        $becasActivas = Beca::vigentes()->count();
        $becasProgramadas = Beca::programadas()->count();
        $descuentoBecasMes = Cargo::whereDate('created_at', '>=', $inicioMes)
            ->sum('beca_monto_aplicado');
        $becasRecientes = Beca::with(['alumno', 'autorizadoPor'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // CAJA / ADMINISTRACIÓN FINANCIERA
        $cajaAbierta = CorteCaja::abierta()->deUsuario($usuario->id)->first();
        $ingresosCajaAbierta = $cajaAbierta ? $cajaAbierta->calcularTotalesSistema() : null;
        $cortesAbiertos = CorteCaja::abierta()->count();
        $cortesCerradosHoy = CorteCaja::cerrada()->whereDate('fecha_cierre', $hoy)->count();
        $ingresosWebHoy = Pago::activos()->whereDate('fecha_pago', $hoy)->sum('monto_total_pagado');
        $pagosWebHoy = Pago::activos()->whereDate('fecha_pago', $hoy)->count();

        // ACADÉMICA
        $solicitudesPendientes = SolicitudPagoDocente::where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE)->count();
        $docentesActivos       = Docente::where('estatus', 'Activo')->count();
        $gruposActivos         = Grupo::activos()->count();
        $materiasActivas       = Materia::activas()->count();
        $calendariosActivos    = CalendarioAcademico::operativos()->count();
        $sesionesProgramadas   = CalendarioSesion::whereDate('fecha', '>=', $hoy)->whereNotIn('estatus', ['Cancelada', 'Suspendida'])->count();
        $sesionesHoy           = CalendarioSesion::activos()->whereDate('fecha', $hoy)->count();
        $sesionesHoyLista      = CalendarioSesion::activos()
            ->with(['calendarioMateria.calendario.grupo.programa', 'calendarioMateria.materia', 'calendarioMateria.docente'])
            ->whereDate('fecha', $hoy)
            ->orderBy('hora_inicio')
            ->take(6)
            ->get();

        $sesionesProximasLista = CalendarioSesion::activos()
            ->with(['calendarioMateria.calendario.grupo.programa', 'calendarioMateria.materia', 'calendarioMateria.docente'])
            ->whereDate('fecha', '>=', $hoy)
            ->whereDate('fecha', '<=', now()->copy()->addDays(14)->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->take(12)
            ->get();

        // EDUCACIÓN CONTINUA / CURSOS ESPECIALES
        $cursosEducacionActivos = CursoEducacionContinua::operativos()->count();
        $sesionesEducacionProximas = CursoSesion::activas()
            ->with(['curso', 'docente'])
            ->whereDate('fecha', '>=', $hoy)
            ->whereDate('fecha', '<=', now()->copy()->addDays(14)->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->take(12)
            ->get();

        $panelRol = $this->construirPanelPorRol($usuario, [
            'alumnosCount' => $alumnosCount,
            'alumnosNuevosMes' => $alumnosNuevosMes,
            'docentesCount' => $docentesCount,
            'pagosPendientes' => $pagosPendientes,
            'pagosAprobados' => $pagosAprobados,
            'pagosPagados' => $pagosPagados,
            'montoPagadoMes' => $montoPagadoMes,
            'alumnosConAdeudo' => $alumnosConAdeudo,
            'seguimientosAbiertos' => $seguimientosAbiertos,
            'seguimientosVencidos' => $seguimientosVencidos,
            'documentosPendientes' => $documentosPendientes,
            'documentosRevision' => $documentosRevision,
            'prospectosActivos' => $prospectosActivos,
            'prospectosVencidos' => $prospectosVencidos,
            'prospectosInscritosMes' => $prospectosInscritosMes,
            'becasActivas' => $becasActivas,
            'cortesAbiertos' => $cortesAbiertos,
            'ingresosWebHoy' => $ingresosWebHoy,
            'pagosWebHoy' => $pagosWebHoy,
            'solicitudesPendientes' => $solicitudesPendientes,
            'docentesActivos' => $docentesActivos,
            'gruposActivos' => $gruposActivos,
            'calendariosActivos' => $calendariosActivos,
            'sesionesProgramadas' => $sesionesProgramadas,
            'sesionesHoy' => $sesionesHoy,
            'cursosEducacionActivos' => $cursosEducacionActivos,
            'bitacorasHoy' => $bitacorasHoy,
        ]);

        return view('dashboard', compact(
            'rol',
            'panelRol',

            // Admin
            'alumnosCount',
            'alumnosNuevosMes',
            'ultimosAlumnos',
            'docentesCount',
            'docentesNuevosMes',
            'pagosPendientes',
            'pagosAprobados',
            'pagosPagados',
            'montoPagadoMes',
            'ultimasSolicitudes',

            // Recepción / seguimientos
            'alumnosConAdeudo',
            'bitacorasHoy',
            'seguimientosAbiertos',
            'seguimientosVencidos',
            'seguimientosProximos',
            'documentosPendientes',
            'documentosRevision',
            'documentosRecientes',
            'prospectosActivos',
            'prospectosVencidos',
            'prospectosInscritosMes',
            'prospectosProximos',
            'becasActivas',
            'becasProgramadas',
            'descuentoBecasMes',
            'becasRecientes',
            'cajaAbierta',
            'ingresosCajaAbierta',
            'cortesAbiertos',
            'cortesCerradosHoy',
            'ingresosWebHoy',
            'pagosWebHoy',

            // Académica
            'solicitudesPendientes',
            'docentesActivos',
            'gruposActivos',
            'materiasActivas',
            'calendariosActivos',
            'sesionesProgramadas',
            'sesionesHoy',
            'sesionesHoyLista',
            'sesionesProximasLista',
            'cursosEducacionActivos',
            'sesionesEducacionProximas'
        ));
    }

    private function construirPanelPorRol(Usuario $usuario, array $m): array
    {
        $rol = $usuario->rolClave();
        $notificacionesPendientes = NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->noLeidas()
            ->count();

        $usuariosActivos = Usuario::where('activo', true)->count();

        $base = [
            'titulo' => 'Resumen operativo de tu área',
            'descripcion' => 'Indicadores rápidos y acciones frecuentes según tu rol.',
            'cards' => [],
            'acciones' => [],
            'alertas' => [],
        ];

        $card = fn (string $label, mixed $value, string $hint = '', ?string $route = null) => [
            'label' => $label,
            'value' => $value,
            'hint' => $hint,
            'route' => $route,
        ];

        $accion = fn (string $label, string $route, string $hint = '') => [
            'label' => $label,
            'route' => $route,
            'hint' => $hint,
        ];

        $base['alertas'][] = $notificacionesPendientes > 0
            ? "Tienes {$notificacionesPendientes} notificación(es) interna(s) pendiente(s)."
            : 'No tienes notificaciones internas pendientes.';

        switch ($rol) {
            case Rol::SISTEMAS:
                $base['titulo'] = 'Panel de Sistemas';
                $base['descripcion'] = 'Estado operativo, usuarios, bitácora y alertas técnicas del sistema interno.';
                $base['cards'] = [
                    $card('Usuarios activos', $usuariosActivos, 'Cuentas internas habilitadas', route('usuarios.index')),
                    $card('Eventos hoy', $m['bitacorasHoy'], 'Actividad registrada en bitácora', route('bitacoras.index')),
                    $card('Notificaciones', $notificacionesPendientes, 'Alertas visibles para tu cuenta', route('notificaciones.index')),
                    $card('Sesiones próximas', $m['sesionesProgramadas'], 'Calendario y operación técnica', route('agenda-operativa.index')),
                ];
                $base['acciones'] = [
                    $accion('Revisar mantenimiento', route('sistema.mantenimiento.index'), 'Cache, respaldo y estado de carpetas'),
                    $accion('Ver Centro de Control', route('centro-control.index'), 'Conflictos de aula, docente, liga y agenda'),
                    $accion('Auditar bitácora', route('bitacoras.index'), 'Rastreo institucional de actividad'),
                ];
                break;

            case Rol::DIRECCION:
                $base['titulo'] = 'Panel de Dirección';
                $base['descripcion'] = 'Lectura ejecutiva de ingresos, adeudos, prospectos, solicitudes y operación académica.';
                $base['cards'] = [
                    $card('Ingresos hoy', '$'.number_format((float) $m['ingresosWebHoy'], 2), $m['pagosWebHoy'].' pago(s)', route('reportes.ejecutivo')),
                    $card('Alumnos con adeudo', $m['alumnosConAdeudo'], 'Seguimiento financiero', route('alumnos.index')),
                    $card('Prospectos activos', $m['prospectosActivos'], 'Relaciones Públicas', route('prospectos.index')),
                    $card('Solicitudes pendientes', $m['solicitudesPendientes'], 'Solicitudes docentes por valorar', route('solicitudes_pago.index')),
                ];
                $base['acciones'] = [
                    $accion('Ver reporte ejecutivo', route('reportes.ejecutivo'), 'Indicadores generales del periodo'),
                    $accion('Consultar agenda operativa', route('agenda-operativa.index'), 'Clases y actividades próximas'),
                ];
                break;

            case Rol::CADMIN:
                $base['titulo'] = 'Panel de Coordinación Administrativa';
                $base['descripcion'] = 'Gestión administrativa y financiera: caja, alumnos, becas, cargos, convenios y pagos docentes.';
                $base['cards'] = [
                    $card('Cajas abiertas', $m['cortesAbiertos'], 'Supervisión de cortes', route('cortes-caja.index')),
                    $card('Solicitudes por revisar', $m['pagosPendientes'], 'Pendientes de valoración', route('solicitudes_pago.index')),
                    $card('Becas activas', $m['becasActivas'], 'Apoyos vigentes', route('becas.index')),
                    $card('Ingresos hoy', '$'.number_format((float) $m['ingresosWebHoy'], 2), $m['pagosWebHoy'].' pago(s)', route('reportes.index')),
                ];
                $base['acciones'] = [
                    $accion('Abrir / revisar caja', route('cortes-caja.index'), 'Operación diaria de caja'),
                    $accion('Cargos masivos', route('cargos.masivo.index'), 'Asignación de cargos por grupo'),
                    $accion('Solicitudes docentes', route('solicitudes_pago.index'), 'Valorar, programar, observar, rechazar o pagar'),
                ];
                break;

            case Rol::ACADEMICA:
                $base['titulo'] = 'Panel de Coordinación Académica';
                $base['descripcion'] = 'Calendarios, grupos, sesiones, docentes y solicitudes docentes generadas por el área académica.';
                $base['cards'] = [
                    $card('Calendarios operativos', $m['calendariosActivos'], 'Planeación activa', route('calendarios_academicos.index')),
                    $card('Sesiones hoy', $m['sesionesHoy'], 'Clases programadas hoy', route('agenda-operativa.index')),
                    $card('Solicitudes pendientes', $m['solicitudesPendientes'], 'Seguimiento de pagos docentes', route('solicitudes_pago.index')),
                    $card('Cursos especiales', $m['cursosEducacionActivos'], 'Educación Continua activa', route('educacion_continua.index')),
                ];
                $base['acciones'] = [
                    $accion('Crear solicitud docente', route('solicitudes_pago.create'), 'Registrar servicio académico a pagar'),
                    $accion('Revisar calendarios', route('calendarios_academicos.index'), 'Planeación por fecha exacta'),
                    $accion('Centro de Control', route('centro-control.index'), 'Detectar choques antes de operar'),
                ];
                break;

            case Rol::RECEPCION:
                $base['titulo'] = 'Panel de Recepción';
                $base['descripcion'] = 'Atención diaria, caja, expedientes, documentos y seguimiento de alumnos.';
                $base['cards'] = [
                    $card('Seguimientos abiertos', $m['seguimientosAbiertos'], 'Pendientes o en proceso', route('alumnos.index')),
                    $card('Alumnos con adeudo', $m['alumnosConAdeudo'], 'Canalizar a Coordinación Administrativa', route('alumnos.index')),
                    $card('Documentos en revisión', $m['documentosRevision'], 'Expedientes por validar', route('alumnos.index')),
                    $card('Caja abierta', $m['cortesAbiertos'], 'Cortes activos del día', route('cortes-caja.index')),
                ];
                $base['acciones'] = [
                    $accion('Registrar alumno', route('alumnos.create'), 'Captura inicial de expediente'),
                    $accion('Operar caja', route('cortes-caja.index'), 'Pagos y cortes'),
                    $accion('Agenda operativa', route('agenda-operativa.index'), 'Orientación del día'),
                ];
                break;

            case Rol::RRPP:
                $base['titulo'] = 'Panel de Relaciones Públicas';
                $base['descripcion'] = 'Prospectos, seguimiento comercial y conversión a alumno.';
                $base['cards'] = [
                    $card('Prospectos activos', $m['prospectosActivos'], 'En contacto o seguimiento', route('prospectos.index')),
                    $card('Prospectos vencidos', $m['prospectosVencidos'], 'Requieren contacto inmediato', route('prospectos.index')),
                    $card('Convertidos este mes', $m['prospectosInscritosMes'], 'Prospectos inscritos', route('prospectos.index')),
                    $card('Grupos disponibles', $m['gruposActivos'], 'Oferta académica vigente', route('grupos.index')),
                ];
                $base['acciones'] = [
                    $accion('Nuevo prospecto', route('prospectos.create'), 'Capturar contacto nuevo'),
                    $accion('Ver prospectos', route('prospectos.index'), 'Filtrar por asesor, medio o programa'),
                    $accion('Consultar oferta académica', route('programas.index'), 'Programas, ciclos y grupos disponibles'),
                ];
                break;


            default:
                $base['titulo'] = 'Panel de Administración General';
                $base['descripcion'] = 'Resumen institucional completo para supervisión del sistema interno.';
                $base['cards'] = [
                    $card('Alumnos', $m['alumnosCount'], '+'.$m['alumnosNuevosMes'].' este mes', route('alumnos.index')),
                    $card('Ingresos hoy', '$'.number_format((float) $m['ingresosWebHoy'], 2), $m['pagosWebHoy'].' pago(s)', route('reportes.ejecutivo')),
                    $card('Solicitudes pendientes', $m['pagosPendientes'], 'Docentes por revisar', route('solicitudes_pago.index')),
                    $card('Alertas pendientes', $notificacionesPendientes, 'Notificaciones internas', route('notificaciones.index')),
                ];
                $base['acciones'] = [
                    $accion('Centro de Control', route('centro-control.index'), 'Alertas operativas'),
                    $accion('Usuarios', route('usuarios.index'), 'Administrar accesos internos'),
                    $accion('Reporte ejecutivo', route('reportes.ejecutivo'), 'Indicadores integrales'),
                ];
                break;
        }

        return $base;
    }

}
