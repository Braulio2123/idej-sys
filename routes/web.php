<?php

use App\Http\Controllers\AgendaOperativaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\BecaController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CargoMasivoController;
use App\Http\Controllers\CentroControlOperativoController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\ConceptoPagoController;
use App\Http\Controllers\ConfiguracionInstitucionalController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\CursoEducacionContinuaController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoAlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\NotificacionInternaController;
use App\Http\Controllers\HorarioAcademicoController;
use App\Http\Controllers\CalendarioAcademicoController;
use App\Http\Controllers\CalendarioMateriaController;
use App\Http\Controllers\CalendarioSesionController;
use App\Http\Controllers\DiaNoLaboralController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ParcialidadConvenioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteEjecutivoController;
use App\Http\Controllers\RequisitoDocumentalController;
use App\Http\Controllers\SolicitudPagoDocenteController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\SeguridadPermisoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Agenda operativa unificada para Académica, Sistemas, Recepción y Dirección.
    Route::get('agenda-operativa', [AgendaOperativaController::class, 'index'])
        ->middleware('rol:Admin,Sistemas,Academica,CAdmin,Direccion,Recepcion')
        ->name('agenda-operativa.index');

    Route::get('centro-control-operativo', [CentroControlOperativoController::class, 'index'])
        ->middleware('rol:Admin,Sistemas,Academica,CAdmin,Direccion,Recepcion')
        ->name('centro-control.index');


    // Notificaciones internas del panel administrativo.
    Route::get('notificaciones', [NotificacionInternaController::class, 'index'])
        ->name('notificaciones.index');
    Route::patch('notificaciones/leer-todas', [NotificacionInternaController::class, 'marcarTodasLeidas'])
        ->name('notificaciones.leer-todas');
    Route::patch('notificaciones/{notificacion}/leer', [NotificacionInternaController::class, 'marcarLeida'])
        ->name('notificaciones.leer');
    Route::patch('notificaciones/{notificacion}/no-leida', [NotificacionInternaController::class, 'marcarNoLeida'])
        ->name('notificaciones.no-leida');
    Route::delete('notificaciones/{notificacion}', [NotificacionInternaController::class, 'archivar'])
        ->name('notificaciones.archivar');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Solicitudes de pago docente: flujo Académica → Administración/Finanzas.
    Route::put('solicitudes_pago/{solicitud_pago}/aprobar', [SolicitudPagoDocenteController::class, 'aprobar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.aprobar');

    Route::get('solicitudes_pago/{solicitud_pago}/observar', [SolicitudPagoDocenteController::class, 'formObservar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.observar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/observar', [SolicitudPagoDocenteController::class, 'observar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.observar');

    Route::get('solicitudes_pago/{solicitud_pago}/pagar', [SolicitudPagoDocenteController::class, 'formPagar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.form_pagar');

    Route::put('solicitudes_pago/{solicitud_pago}/pagar', [SolicitudPagoDocenteController::class, 'pagar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.pagar');

    Route::get('solicitudes_pago/{solicitud_pago}/cancelar', [SolicitudPagoDocenteController::class, 'formCancelar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.cancelar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/cancelar', [SolicitudPagoDocenteController::class, 'cancelar'])
        ->middleware('rol:Admin,CAdmin,Finanzas')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.cancelar');

    Route::get('solicitudes_pago/{solicitud_pago}/comprobante', [SolicitudPagoDocenteController::class, 'descargarComprobante'])
        ->middleware('rol:Admin,CAdmin,Finanzas,Direccion')
        ->name('solicitudes_pago.comprobante');

    // Alumnos y operación de caja/recepción.
    Route::middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica,Direccion')->group(function () {
        Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
        Route::get('alumnos/create', [AlumnoController::class, 'create'])
            ->middleware('rol:Admin,Recepcion,CAdmin')
            ->name('alumnos.create');
        Route::post('alumnos', [AlumnoController::class, 'store'])
            ->middleware('rol:Admin,Recepcion,CAdmin')
            ->name('alumnos.store');
        Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->name('alumnos.show');
        Route::get('alumnos/{alumno}/edit', [AlumnoController::class, 'edit'])
            ->middleware('rol:Admin,Recepcion,CAdmin')
            ->name('alumnos.edit');
        Route::put('alumnos/{alumno}', [AlumnoController::class, 'update'])
            ->middleware('rol:Admin,Recepcion,CAdmin')
            ->name('alumnos.update');
        Route::delete('alumnos/{alumno}', [AlumnoController::class, 'destroy'])
            ->middleware('rol:Admin,Recepcion,CAdmin')
            ->name('alumnos.destroy');

        Route::resource('alumnos.cargos', CargoController::class)
            ->only(['create', 'store'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas');

        Route::resource('alumnos.pagos', PagoController::class)
            ->only(['create', 'store'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas');

        Route::get('alumnos/{alumno}/pagos/{pago}/recibo', [PagoController::class, 'recibo'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.pagos.recibo');

        Route::get('alumnos/{alumno}/pagos/{pago}/comprobante', [PagoController::class, 'descargarComprobante'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.pagos.comprobante');

        Route::get('alumnos/{alumno}/pagos/{pago}/cancelar', [PagoController::class, 'confirmarCancelacion'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.pagos.cancelar.confirmar');

        Route::put('alumnos/{alumno}/pagos/{pago}/cancelar', [PagoController::class, 'cancelar'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.pagos.cancelar');

        Route::get('alumnos/{alumno}/pagos/{pago}/ajuste-cancelacion', [PagoController::class, 'confirmarAjusteCancelacion'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.pagos.ajuste-cancelacion.confirmar');

        Route::put('alumnos/{alumno}/pagos/{pago}/ajuste-cancelacion', [PagoController::class, 'ajusteCancelacion'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.pagos.ajuste-cancelacion');

        Route::resource('alumnos.convenios', ConvenioController::class)
            ->only(['create', 'store', 'show', 'edit', 'update', 'destroy'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas');

        Route::get('/alumnos/{alumno}/cargos', [AlumnoController::class, 'cargosIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.cargos.index');

        Route::get('/alumnos/{alumno}/pagos', [AlumnoController::class, 'pagosIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.pagos.index');

        Route::get('/alumnos/{alumno}/convenios', [AlumnoController::class, 'conveniosIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.convenios.index');

        Route::get('/alumnos/{alumno}/becas', [BecaController::class, 'alumnoIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,Direccion')
            ->name('alumnos.becas.index');

        Route::get('/alumnos/{alumno}/becas/create', [BecaController::class, 'create'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->name('alumnos.becas.create');

        Route::post('/alumnos/{alumno}/becas', [BecaController::class, 'store'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->name('alumnos.becas.store');

        Route::get('/alumnos/{alumno}/becas/{beca}/cancelar', [BecaController::class, 'confirmarCancelacion'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.becas.cancelar.confirmar');

        Route::put('/alumnos/{alumno}/becas/{beca}/cancelar', [BecaController::class, 'cancelar'])
            ->middleware('rol:Admin,CAdmin,Finanzas')
            ->middleware('password.fresh:900')
            ->name('alumnos.becas.cancelar');

        Route::get('alumnos/{alumno}/documentos', [DocumentoAlumnoController::class, 'index'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica,Direccion')
            ->name('alumnos.documentos.index');

        Route::post('alumnos/{alumno}/documentos/generar-checklist', [DocumentoAlumnoController::class, 'generarChecklist'])
            ->middleware('rol:Admin,Recepcion,CAdmin,RRPP,Academica')
            ->name('alumnos.documentos.generar-checklist');

        Route::post('alumnos/{alumno}/documentos', [DocumentoAlumnoController::class, 'store'])
            ->middleware('rol:Admin,Recepcion,CAdmin,RRPP,Academica')
            ->name('alumnos.documentos.store');

        Route::put('alumnos/{alumno}/documentos/{documento}', [DocumentoAlumnoController::class, 'update'])
            ->middleware('rol:Admin,Recepcion,CAdmin,RRPP,Academica')
            ->name('alumnos.documentos.update');

        Route::get('alumnos/{alumno}/documentos/{documento}/descargar', [DocumentoAlumnoController::class, 'download'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica,Direccion')
            ->name('alumnos.documentos.download');

        Route::delete('alumnos/{alumno}/documentos/{documento}', [DocumentoAlumnoController::class, 'destroy'])
            ->middleware('rol:Admin,CAdmin')
            ->middleware('password.fresh:900')
            ->name('alumnos.documentos.destroy');

        Route::get('alumnos/{alumno}/seguimientos', [SeguimientoController::class, 'index'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica,Direccion')
            ->name('alumnos.seguimientos.index');

        Route::post('alumnos/{alumno}/seguimientos', [SeguimientoController::class, 'store'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica')
            ->name('alumnos.seguimientos.store');

        Route::put('alumnos/{alumno}/seguimientos/{seguimiento}', [SeguimientoController::class, 'update'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica')
            ->name('alumnos.seguimientos.update');

        Route::delete('alumnos/{alumno}/seguimientos/{seguimiento}', [SeguimientoController::class, 'destroy'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas,RRPP,Academica')
            ->name('alumnos.seguimientos.destroy');
    });

    // Prospectos y Relaciones Públicas.
    Route::middleware('rol:Admin,Recepcion,CAdmin,RRPP,Direccion')->group(function () {
        Route::get('prospectos', [ProspectoController::class, 'index'])->name('prospectos.index');
    });

    Route::middleware('rol:Admin,Recepcion,CAdmin,RRPP')->group(function () {
        Route::get('prospectos/create', [ProspectoController::class, 'create'])->name('prospectos.create');
        Route::post('prospectos', [ProspectoController::class, 'store'])->name('prospectos.store');
        Route::get('prospectos/{prospecto}/edit', [ProspectoController::class, 'edit'])->name('prospectos.edit');
        Route::put('prospectos/{prospecto}', [ProspectoController::class, 'update'])->name('prospectos.update');
        Route::delete('prospectos/{prospecto}', [ProspectoController::class, 'destroy'])->name('prospectos.destroy');

        Route::post('prospectos/{prospecto}/seguimientos', [ProspectoController::class, 'storeSeguimiento'])
            ->name('prospectos.seguimientos.store');

        Route::post('prospectos/{prospecto}/convertir', [ProspectoController::class, 'convertirAlumno'])
            ->name('prospectos.convertir');
    });

    Route::middleware('rol:Admin,Recepcion,CAdmin,RRPP,Direccion')->group(function () {
        Route::get('prospectos/{prospecto}', [ProspectoController::class, 'show'])->name('prospectos.show');
    });

    // Parcialidades de convenios.
    Route::prefix('convenios/{convenio}')
        ->middleware('rol:Admin,Recepcion,CAdmin,Finanzas')
        ->group(function () {
            Route::get('parcialidades', [ParcialidadConvenioController::class, 'index'])->name('parcialidades.index');
            Route::get('parcialidades/create', [ParcialidadConvenioController::class, 'create'])->name('parcialidades.create');
            Route::post('parcialidades', [ParcialidadConvenioController::class, 'store'])->name('parcialidades.store');
            Route::get('parcialidades/{parcialidad}/edit', [ParcialidadConvenioController::class, 'edit'])->name('parcialidades.edit');
            Route::put('parcialidades/{parcialidad}', [ParcialidadConvenioController::class, 'update'])->name('parcialidades.update');
            Route::delete('parcialidades/{parcialidad}', [ParcialidadConvenioController::class, 'destroy'])->name('parcialidades.destroy');
        });

    // Cargos masivos.
    Route::middleware('rol:Admin,CAdmin,Finanzas')->group(function () {
        Route::get('cargos/masivo', [CargoMasivoController::class, 'index'])->name('cargos.masivo.index');
        Route::post('cargos/masivo/filtrar', [CargoMasivoController::class, 'filtrarAlumnos'])->name('cargos.masivo.filtrar');
        Route::post('cargos/masivo', [CargoMasivoController::class, 'store'])->name('cargos.masivo.store');
        Route::get('cargos/masivo/{id}', [CargoMasivoController::class, 'show'])->name('cargos.masivo.show');
    });

    // Usuarios y configuración técnica.
    Route::middleware('rol:Admin,Sistemas')->group(function () {
        Route::patch('usuarios/{usuario}/reactivar', [UsuarioController::class, 'reactivar'])
            ->middleware('password.fresh:900')
            ->name('usuarios.reactivar');

        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])
            ->middleware('password.fresh:900')
            ->name('usuarios.update');

        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])
            ->middleware('password.fresh:900')
            ->name('usuarios.destroy');

        Route::resource('usuarios', UsuarioController::class)
            ->except(['update', 'destroy']);

        Route::get('seguridad/permisos', [SeguridadPermisoController::class, 'index'])
            ->name('seguridad.permisos.index');

        Route::get('configuracion/institucional', [ConfiguracionInstitucionalController::class, 'edit'])
            ->name('configuracion.institucional.edit');
        Route::put('configuracion/institucional', [ConfiguracionInstitucionalController::class, 'update'])
            ->middleware('password.fresh:900')
            ->name('configuracion.institucional.update');

        Route::get('sistema/mantenimiento', [MantenimientoController::class, 'index'])
            ->name('sistema.mantenimiento.index');
        Route::post('sistema/mantenimiento/limpiar-cache', [MantenimientoController::class, 'limpiarCache'])
            ->name('sistema.mantenimiento.limpiar-cache');
        Route::post('sistema/mantenimiento/storage-link', [MantenimientoController::class, 'crearStorageLink'])
            ->name('sistema.mantenimiento.storage-link');
        Route::post('sistema/mantenimiento/limpiar-logs', [MantenimientoController::class, 'limpiarLogs'])
            ->middleware('password.fresh:900')
            ->name('sistema.mantenimiento.limpiar-logs');
        Route::get('sistema/mantenimiento/backup-base-datos', [MantenimientoController::class, 'descargarBackupBaseDatos'])
            ->middleware('password.fresh:900')
            ->name('sistema.mantenimiento.backup-db');
        Route::get('sistema/mantenimiento/backup-archivos', [MantenimientoController::class, 'descargarBackupArchivos'])
            ->middleware('password.fresh:900')
            ->name('sistema.mantenimiento.backup-archivos');
    });

    // Finanzas / administración.
    Route::middleware('rol:Admin,CAdmin,Finanzas')->group(function () {
        Route::resource('conceptos', ConceptoPagoController::class);
        Route::post('becas/sincronizar', [BecaController::class, 'sincronizar'])->name('becas.sincronizar');
    });

    Route::get('becas', [BecaController::class, 'index'])
        ->middleware('rol:Admin,CAdmin,Finanzas,Direccion')
        ->name('becas.index');

    // Caja operativa: recepción y finanzas.
    Route::middleware('rol:Admin,Recepcion,CAdmin,Finanzas')->group(function () {
        Route::get('cortes-caja', [CorteCajaController::class, 'index'])->name('cortes-caja.index');
        Route::get('cortes-caja/create', [CorteCajaController::class, 'create'])->name('cortes-caja.create');
        Route::post('cortes-caja', [CorteCajaController::class, 'store'])->name('cortes-caja.store');
        Route::get('cortes-caja/{corteCaja}', [CorteCajaController::class, 'show'])->name('cortes-caja.show');
        Route::get('cortes-caja/{corteCaja}/cierre', [CorteCajaController::class, 'cierre'])->middleware('password.fresh:900')->name('cortes-caja.cierre');
        Route::put('cortes-caja/{corteCaja}/cerrar', [CorteCajaController::class, 'cerrar'])->middleware('password.fresh:900')->name('cortes-caja.cerrar');
    });

    Route::middleware('rol:Admin,CAdmin,Finanzas,Direccion')->group(function () {
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/export-excel', [ReporteController::class, 'exportExcel'])->name('reportes.export-excel');
        Route::get('reportes/export-pdf', [ReporteController::class, 'exportPdf'])->name('reportes.export-pdf');

        Route::get('reportes-ejecutivos', [ReporteEjecutivoController::class, 'index'])->name('reportes.ejecutivo');
        Route::get('reportes-ejecutivos/export-csv', [ReporteEjecutivoController::class, 'exportCsv'])->name('reportes.ejecutivo.export-csv');
    });

    // Académica / administración escolar.
    Route::middleware('rol:Admin,CAdmin,Academica,Sistemas')->group(function () {
        Route::resource('ciclos_escolares', CicloEscolarController::class)
            ->parameters(['ciclos_escolares' => 'ciclo_escolar']);

        Route::resource('grupos', GrupoController::class);

        Route::resource('materias', MateriaController::class)
            ->except(['show']);

        Route::resource('calendarios-academicos', CalendarioAcademicoController::class)
            ->names('calendarios_academicos')
            ->parameters(['calendarios-academicos' => 'calendarioAcademico']);

        Route::get('calendarios-academicos/{calendarioAcademico}/materias/create', [CalendarioMateriaController::class, 'create'])
            ->name('calendarios_academicos.materias.create');
        Route::post('calendarios-academicos/{calendarioAcademico}/materias', [CalendarioMateriaController::class, 'store'])
            ->name('calendarios_academicos.materias.store');
        Route::get('calendarios-academicos/{calendarioAcademico}/materias/{calendarioMateria}/edit', [CalendarioMateriaController::class, 'edit'])
            ->name('calendarios_academicos.materias.edit');
        Route::put('calendarios-academicos/{calendarioAcademico}/materias/{calendarioMateria}', [CalendarioMateriaController::class, 'update'])
            ->name('calendarios_academicos.materias.update');
        Route::delete('calendarios-academicos/{calendarioAcademico}/materias/{calendarioMateria}', [CalendarioMateriaController::class, 'destroy'])
            ->name('calendarios_academicos.materias.destroy');

        Route::get('calendarios-academicos/{calendarioAcademico}/sesiones/{calendarioSesion}/cancelar', [CalendarioSesionController::class, 'cancelar'])
            ->name('calendarios_academicos.sesiones.cancelar');
        Route::post('calendarios-academicos/{calendarioAcademico}/sesiones/{calendarioSesion}/cancelar', [CalendarioSesionController::class, 'cancelarStore'])
            ->name('calendarios_academicos.sesiones.cancelar.store');
        Route::get('calendarios-academicos/{calendarioAcademico}/sesiones/{calendarioSesion}/reprogramar', [CalendarioSesionController::class, 'reprogramar'])
            ->name('calendarios_academicos.sesiones.reprogramar');
        Route::post('calendarios-academicos/{calendarioAcademico}/sesiones/{calendarioSesion}/reprogramar', [CalendarioSesionController::class, 'reprogramarStore'])
            ->name('calendarios_academicos.sesiones.reprogramar.store');

        Route::resource('dias-no-laborales', DiaNoLaboralController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names('dias_no_laborales')
            ->parameters(['dias-no-laborales' => 'diaNoLaboral']);

        // El módulo antiguo de horarios semanales se conserva solo por compatibilidad técnica,
        // pero el flujo operativo del IDEJ usa calendarios académicos por fechas exactas.
        Route::resource('horarios_academicos', HorarioAcademicoController::class)
            ->parameters(['horarios_academicos' => 'horarioAcademico']);

        Route::resource('educacion-continua', CursoEducacionContinuaController::class)
            ->names('educacion_continua')
            ->parameters(['educacion-continua' => 'educacionContinua']);

        Route::post('educacion-continua/{educacionContinua}/sesiones', [CursoEducacionContinuaController::class, 'storeSesion'])
            ->name('educacion_continua.sesiones.store');
        Route::put('educacion-continua/{educacionContinua}/sesiones/{sesion}', [CursoEducacionContinuaController::class, 'updateSesion'])
            ->name('educacion_continua.sesiones.update');
        Route::delete('educacion-continua/{educacionContinua}/sesiones/{sesion}', [CursoEducacionContinuaController::class, 'destroySesion'])
            ->name('educacion_continua.sesiones.destroy');
        Route::get('educacion-continua/{educacionContinua}/sesiones/{sesion}/asistencia', [CursoEducacionContinuaController::class, 'asistencia'])
            ->name('educacion_continua.sesiones.asistencia');
        Route::post('educacion-continua/{educacionContinua}/sesiones/{sesion}/asistencia', [CursoEducacionContinuaController::class, 'guardarAsistencia'])
            ->name('educacion_continua.sesiones.asistencia.store');

        Route::post('educacion-continua/{educacionContinua}/inscritos', [CursoEducacionContinuaController::class, 'storeInscrito'])
            ->name('educacion_continua.inscritos.store');
        Route::put('educacion-continua/{educacionContinua}/inscritos/{inscrito}', [CursoEducacionContinuaController::class, 'updateInscrito'])
            ->name('educacion_continua.inscritos.update');
        Route::delete('educacion-continua/{educacionContinua}/inscritos/{inscrito}', [CursoEducacionContinuaController::class, 'destroyInscrito'])
            ->name('educacion_continua.inscritos.destroy');

        Route::resource('programas', ProgramaController::class)
            ->except(['show']);

        Route::resource('requisitos_documentales', RequisitoDocumentalController::class)
            ->except(['show'])
            ->parameters(['requisitos_documentales' => 'requisitoDocumental']);

        Route::resource('docentes', DocenteController::class);
    });

    // Solicitudes de pago docente.
    Route::middleware('rol:Admin,CAdmin,Academica,Finanzas,Direccion')->group(function () {
        Route::get('solicitudes_pago', [SolicitudPagoDocenteController::class, 'index'])->name('solicitudes_pago.index');
    });

    Route::middleware('rol:Admin,Academica')->group(function () {
        Route::get('solicitudes_pago/create', [SolicitudPagoDocenteController::class, 'create'])->name('solicitudes_pago.create');
        Route::post('solicitudes_pago', [SolicitudPagoDocenteController::class, 'store'])->name('solicitudes_pago.store');
    });

    Route::middleware('rol:Admin,Academica')->group(function () {
        Route::get('solicitudes_pago/{solicitud_pago}/edit', [SolicitudPagoDocenteController::class, 'edit'])->name('solicitudes_pago.edit');
        Route::put('solicitudes_pago/{solicitud_pago}', [SolicitudPagoDocenteController::class, 'update'])->name('solicitudes_pago.update');
    });

    Route::middleware('rol:Admin')->group(function () {
        Route::delete('solicitudes_pago/{solicitud_pago}', [SolicitudPagoDocenteController::class, 'destroy'])->middleware('password.fresh:900')->name('solicitudes_pago.destroy');
    });

    Route::middleware('rol:Admin,CAdmin,Academica,Finanzas,Direccion')->group(function () {
        Route::get('solicitudes_pago/{solicitud_pago}', [SolicitudPagoDocenteController::class, 'show'])->name('solicitudes_pago.show');
    });

    // Bitácora: lectura para Dirección/Sistemas; ocultar registros solo Admin con contraseña fresca.
    Route::middleware('rol:Admin,Sistemas,Direccion')->group(function () {
        Route::resource('bitacoras', BitacoraController::class)
            ->only(['index', 'show']);

        Route::delete('bitacoras/{bitacora}', [BitacoraController::class, 'destroy'])
            ->middleware('rol:Admin')
            ->middleware('password.fresh:900')
            ->name('bitacoras.destroy');

        Route::get('bitacoras/export/pdf', [BitacoraController::class, 'exportPdf'])
            ->name('bitacoras.export.pdf');
    });

    // Alias de consulta académica conservados por compatibilidad.
    Route::middleware('rol:Admin,Academica')->group(function () {
        Route::get('academica/grupos', [GrupoController::class, 'index'])->name('academica.grupos.index');
        Route::get('academica/grupos/{grupo}', [GrupoController::class, 'show'])->name('academica.grupos.show');
    });
});

/*
|--------------------------------------------------------------------------
| Portal Alumno PWA - Christian
|--------------------------------------------------------------------------
|
| Carga aislada de rutas del Portal Alumno.
|
| IMPORTANTE:
| - Este bloque NO pertenece al panel administrativo.
| - Las rutas reales del portal están en routes/portal_alumno.php.
| - Se mantiene separado para no mezclar el trabajo del área académica
|   administrativa con el módulo del alumno.
|
| URL base del portal:
| /portal-alumno
|
*/
require __DIR__.'/portal_alumno.php';

/*
|--------------------------------------------------------------------------
| Rutas de autenticación administrativa
|--------------------------------------------------------------------------
|
| Archivo original de autenticación del sistema administrativo.
| Se mantiene separado del Portal Alumno.
|
*/
require __DIR__.'/auth.php';
