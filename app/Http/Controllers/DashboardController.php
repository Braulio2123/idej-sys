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

        $pagosPendientes    = SolicitudPagoDocente::where('estatus', 'Pendiente')->count();
        $pagosAprobados     = SolicitudPagoDocente::where('estatus', 'Aprobada')->count();
        $pagosPagados       = SolicitudPagoDocente::where('estatus', 'Pagada')->count();

        $montoPagadoMes     = SolicitudPagoDocente::where('estatus', 'Pagada')
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

        // CAJA / FINANZAS
        $cajaAbierta = CorteCaja::abierta()->deUsuario($usuario->id)->first();
        $ingresosCajaAbierta = $cajaAbierta ? $cajaAbierta->calcularTotalesSistema() : null;
        $cortesAbiertos = CorteCaja::abierta()->count();
        $cortesCerradosHoy = CorteCaja::cerrada()->whereDate('fecha_cierre', $hoy)->count();
        $ingresosWebHoy = Pago::activos()->whereDate('fecha_pago', $hoy)->sum('monto_total_pagado');
        $pagosWebHoy = Pago::activos()->whereDate('fecha_pago', $hoy)->count();

        // ACADÉMICA
        $solicitudesPendientes = SolicitudPagoDocente::where('estatus', 'Pendiente')->count();
        $docentesActivos       = Docente::where('estatus', 'Activo')->count();
        $gruposActivos         = Grupo::count();
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

        return view('dashboard', compact(
            'rol',

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
}
