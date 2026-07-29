<?php

use App\Http\Controllers\AgendaOperativaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\BecaController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\CargoMasivoController;
use App\Http\Controllers\CobranzaEmailController;
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
use App\Http\Controllers\PlanCargoRecurrenteController;
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

Route::middleware(['auth', 'idempotent'])->group(function () {
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
    Route::get('notificaciones/resumen-json', [NotificacionInternaController::class, 'resumen'])
        ->name('notificaciones.resumen-json');
    Route::post('notificaciones/probar', [NotificacionInternaController::class, 'probar'])
        ->name('notificaciones.probar');
    Route::post('notificaciones/sincronizar-operativas', [NotificacionInternaController::class, 'sincronizarOperativas'])
        ->middleware('rol:Admin,Sistemas')
        ->name('notificaciones.sincronizar-operativas');
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
    Route::delete('/profile/sessions/others', [ProfileController::class, 'destroyOtherSessions'])
        ->middleware('password.fresh:900')
        ->name('profile.sessions.destroy-others');

    // Solicitudes de pago docente: Académica registra actividad; CAdmin valora, programa y paga o rechaza.
    Route::get('solicitudes_pago/{solicitud_pago}/valorar', [SolicitudPagoDocenteController::class, 'formValorar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.valorar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/valorar', [SolicitudPagoDocenteController::class, 'valorar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.valorar');

    // Compatibilidad temporal con formularios previos.
    Route::put('solicitudes_pago/{solicitud_pago}/aprobar', [SolicitudPagoDocenteController::class, 'aprobar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.aprobar');

    Route::put('solicitudes_pago/{solicitud_pago}/tentativa', [SolicitudPagoDocenteController::class, 'actualizarTentativa'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.tentativa');

    Route::get('solicitudes_pago/{solicitud_pago}/observar', [SolicitudPagoDocenteController::class, 'formObservar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.observar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/observar', [SolicitudPagoDocenteController::class, 'observar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.observar');

    Route::get('solicitudes_pago/{solicitud_pago}/rechazar', [SolicitudPagoDocenteController::class, 'formRechazar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.rechazar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/rechazar', [SolicitudPagoDocenteController::class, 'rechazar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.rechazar');

    Route::get('solicitudes_pago/{solicitud_pago}/pagar', [SolicitudPagoDocenteController::class, 'formPagar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.form_pagar');

    Route::put('solicitudes_pago/{solicitud_pago}/pagar', [SolicitudPagoDocenteController::class, 'pagar'])
        ->middleware('rol:Admin,CAdmin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.pagar');

    Route::get('solicitudes_pago/{solicitud_pago}/cancelar', [SolicitudPagoDocenteController::class, 'formCancelar'])
        ->middleware('rol:Admin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.cancelar.form');

    Route::put('solicitudes_pago/{solicitud_pago}/cancelar', [SolicitudPagoDocenteController::class, 'cancelar'])
        ->middleware('rol:Admin')
        ->middleware('password.fresh:900')
        ->name('solicitudes_pago.cancelar');

    Route::get('solicitudes_pago/{solicitud_pago}/comprobante', [SolicitudPagoDocenteController::class, 'descargarComprobante'])
        ->middleware('rol:Admin,CAdmin')
        ->name('solicitudes_pago.comprobante');

    Route::get('solicitudes_pago/{solicitud_pago}/acuse-pago', [SolicitudPagoDocenteController::class, 'acusePago'])
        ->middleware('rol:Admin,CAdmin')
        ->name('solicitudes_pago.acuse_pago');

    // Alumnos: consulta amplia institucional; edición administrativa limitada.
    // RRPP trabaja exclusivamente con prospectos y no accede al expediente completo.
    Route::middleware('permiso:alumnos.ver')->group(function () {
        Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
        Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->name('alumnos.show');

        Route::get('/alumnos/{alumno}/cargos', [AlumnoController::class, 'cargosIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Direccion')
            ->name('alumnos.cargos.index');
        Route::get('/alumnos/{alumno}/pagos', [AlumnoController::class, 'pagosIndex'])
            ->middleware('rol:Admin,Recepcion,CAdmin,Direccion')
            ->name('alumnos.pagos.index');
        Route::get('/alumnos/{alumno}/convenios', [AlumnoController::class, 'conveniosIndex'])
            ->middleware('permiso:convenios.ver')
            ->name('alumnos.convenios.index');
        Route::get('/alumnos/{alumno}/becas', [BecaController::class, 'alumnoIndex'])
            ->middleware('permiso:becas.ver')
            ->name('alumnos.becas.index');
    });

    Route::middleware('permiso:alumnos.gestionar')->group(function () {
        Route::get('alumnos/create', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::get('alumnos/{alumno}/edit', [AlumnoController::class, 'edit'])->name('alumnos.edit');
        Route::put('alumnos/{alumno}', [AlumnoController::class, 'update'])->name('alumnos.update');
    });

    Route::delete('alumnos/{alumno}', [AlumnoController::class, 'destroy'])
        ->middleware(['rol:Admin', 'password.fresh:900'])
        ->name('alumnos.destroy');

    // CAdmin crea cargos; Recepción únicamente registra cobros autorizados.
    Route::resource('alumnos.cargos', CargoController::class)
        ->only(['create', 'store'])
        ->middleware('rol:Admin,CAdmin');

    Route::resource('alumnos.pagos', PagoController::class)
        ->only(['create', 'store'])
        ->middleware('permiso:pagos.registrar');

    Route::get('alumnos/{alumno}/pagos/{pago}/recibo', [PagoController::class, 'recibo'])
        ->middleware('permiso:pagos.comprobante')
        ->name('alumnos.pagos.recibo');
    Route::get('alumnos/{alumno}/pagos/{pago}/comprobante', [PagoController::class, 'descargarComprobante'])
        ->middleware('permiso:pagos.comprobante')
        ->name('alumnos.pagos.comprobante');

    Route::middleware(['permiso:pagos.cancelar', 'password.fresh:900'])->group(function () {
        Route::get('alumnos/{alumno}/pagos/{pago}/cancelar', [PagoController::class, 'confirmarCancelacion'])
            ->name('alumnos.pagos.cancelar.confirmar');
        Route::put('alumnos/{alumno}/pagos/{pago}/cancelar', [PagoController::class, 'cancelar'])
            ->name('alumnos.pagos.cancelar');
        Route::get('alumnos/{alumno}/pagos/{pago}/ajuste-cancelacion', [PagoController::class, 'confirmarAjusteCancelacion'])
            ->name('alumnos.pagos.ajuste-cancelacion.confirmar');
        Route::post('alumnos/{alumno}/pagos/{pago}/ajuste-cancelacion', [PagoController::class, 'ajusteCancelacion'])
            ->name('alumnos.pagos.ajuste-cancelacion');
    });

    // Convenios: Recepción y Dirección consultan; solo CAdmin/Admin modifican.
    Route::middleware('permiso:convenios.ver')->group(function () {
        Route::get('alumnos/{alumno}/convenios/{convenio}', [ConvenioController::class, 'show'])
            ->name('alumnos.convenios.show');
        Route::get('alumnos/{alumno}/convenios/{convenio}/pdf', [ConvenioController::class, 'pdf'])
            ->name('alumnos.convenios.pdf');
        Route::get('convenios/{convenio}/parcialidades', [ParcialidadConvenioController::class, 'index'])
            ->name('parcialidades.index');
    });

    Route::middleware('permiso:convenios.gestionar')->group(function () {
        Route::get('alumnos/{alumno}/convenios/create', [ConvenioController::class, 'create'])
            ->name('alumnos.convenios.create');
        Route::post('alumnos/{alumno}/convenios', [ConvenioController::class, 'store'])
            ->name('alumnos.convenios.store');
        Route::get('alumnos/{alumno}/convenios/{convenio}/edit', [ConvenioController::class, 'edit'])
            ->name('alumnos.convenios.edit');
        Route::put('alumnos/{alumno}/convenios/{convenio}', [ConvenioController::class, 'update'])
            ->name('alumnos.convenios.update');
        Route::delete('alumnos/{alumno}/convenios/{convenio}', [ConvenioController::class, 'destroy'])
            ->middleware('password.fresh:900')
            ->name('alumnos.convenios.destroy');

        Route::get('convenios/{convenio}/parcialidades/create', [ParcialidadConvenioController::class, 'create'])
            ->name('parcialidades.create');
        Route::post('convenios/{convenio}/parcialidades', [ParcialidadConvenioController::class, 'store'])
            ->name('parcialidades.store');
        Route::get('convenios/{convenio}/parcialidades/{parcialidad}/edit', [ParcialidadConvenioController::class, 'edit'])
            ->name('parcialidades.edit');
        Route::put('convenios/{convenio}/parcialidades/{parcialidad}', [ParcialidadConvenioController::class, 'update'])
            ->name('parcialidades.update');
        Route::delete('convenios/{convenio}/parcialidades/{parcialidad}', [ParcialidadConvenioController::class, 'destroy'])
            ->middleware('password.fresh:900')
            ->name('parcialidades.destroy');
    });

    Route::middleware('permiso:becas.gestionar')->group(function () {
        Route::get('/alumnos/{alumno}/becas/create', [BecaController::class, 'create'])->name('alumnos.becas.create');
        Route::post('/alumnos/{alumno}/becas', [BecaController::class, 'store'])->name('alumnos.becas.store');
        Route::get('/alumnos/{alumno}/becas/{beca}/cancelar', [BecaController::class, 'confirmarCancelacion'])
            ->middleware('password.fresh:900')
            ->name('alumnos.becas.cancelar.confirmar');
        Route::put('/alumnos/{alumno}/becas/{beca}/cancelar', [BecaController::class, 'cancelar'])
            ->middleware('password.fresh:900')
            ->name('alumnos.becas.cancelar');
    });

    // Expediente privado: Dirección consulta estatus, pero no descarga archivos.
    Route::get('alumnos/{alumno}/documentos', [DocumentoAlumnoController::class, 'index'])
        ->middleware('permiso:documentos.ver')
        ->name('alumnos.documentos.index');
    Route::post('alumnos/{alumno}/documentos/generar-checklist', [DocumentoAlumnoController::class, 'generarChecklist'])
        ->middleware('permiso:documentos.gestionar')
        ->name('alumnos.documentos.generar-checklist');
    Route::post('alumnos/{alumno}/documentos', [DocumentoAlumnoController::class, 'store'])
        ->middleware('permiso:documentos.gestionar')
        ->name('alumnos.documentos.store');
    Route::put('alumnos/{alumno}/documentos/{documento}', [DocumentoAlumnoController::class, 'update'])
        ->middleware('permiso:documentos.gestionar')
        ->name('alumnos.documentos.update');
    Route::get('alumnos/{alumno}/documentos/{documento}/descargar', [DocumentoAlumnoController::class, 'download'])
        ->withTrashed()
        ->middleware('permiso:documentos.descargar')
        ->name('alumnos.documentos.download');
    Route::delete('alumnos/{alumno}/documentos/{documento}', [DocumentoAlumnoController::class, 'destroy'])
        ->middleware(['permiso:documentos.eliminar', 'password.fresh:900'])
        ->name('alumnos.documentos.destroy');

    Route::middleware('permiso:alumnos.ver')->group(function () {
        Route::get('alumnos/{alumno}/seguimientos', [SeguimientoController::class, 'index'])
            ->name('alumnos.seguimientos.index');
    });
    Route::middleware('permiso:seguimientos.gestionar')->group(function () {
        Route::post('alumnos/{alumno}/seguimientos', [SeguimientoController::class, 'store'])
            ->name('alumnos.seguimientos.store');
        Route::put('alumnos/{alumno}/seguimientos/{seguimiento}', [SeguimientoController::class, 'update'])
            ->name('alumnos.seguimientos.update');
        Route::delete('alumnos/{alumno}/seguimientos/{seguimiento}', [SeguimientoController::class, 'destroy'])
            ->name('alumnos.seguimientos.destroy');
    });

    // Prospectos y Relaciones Públicas. RRPP no accede al expediente del alumno.
    Route::middleware('permiso:prospectos.ver')->group(function () {
        Route::get('prospectos', [ProspectoController::class, 'index'])->name('prospectos.index');
        Route::get('prospectos/{prospecto}', [ProspectoController::class, 'show'])->name('prospectos.show');
    });
    Route::middleware('permiso:prospectos.gestionar')->group(function () {
        Route::get('prospectos/create', [ProspectoController::class, 'create'])->name('prospectos.create');
        Route::post('prospectos', [ProspectoController::class, 'store'])->name('prospectos.store');
        Route::get('prospectos/{prospecto}/edit', [ProspectoController::class, 'edit'])->name('prospectos.edit');
        Route::put('prospectos/{prospecto}', [ProspectoController::class, 'update'])->name('prospectos.update');
        Route::delete('prospectos/{prospecto}', [ProspectoController::class, 'destroy'])->name('prospectos.destroy');
        Route::post('prospectos/{prospecto}/seguimientos', [ProspectoController::class, 'storeSeguimiento'])
            ->name('prospectos.seguimientos.store');
        Route::post('prospectos/{prospecto}/convertir', [ProspectoController::class, 'convertirAlumno'])
            ->middleware('permiso:prospectos.convertir')
            ->name('prospectos.convertir');
    });

    // Cargos masivos, recurrentes y cobranza por correo.
    Route::middleware('rol:Admin,CAdmin')->group(function () {
        Route::get('cargos/masivo', [CargoMasivoController::class, 'index'])->name('cargos.masivo.index');
        Route::post('cargos/masivo/filtrar', [CargoMasivoController::class, 'filtrarAlumnos'])
            ->withoutMiddleware('idempotent')
            ->name('cargos.masivo.filtrar');
        Route::post('cargos/masivo', [CargoMasivoController::class, 'store'])->name('cargos.masivo.store');
        Route::get('cargos/masivo/{id}', [CargoMasivoController::class, 'show'])->name('cargos.masivo.show');

        Route::resource('cargos/recurrentes', PlanCargoRecurrenteController::class)
            ->parameters(['recurrentes' => 'recurrente'])
            ->names('cargos.recurrentes')
            ->middleware('password.fresh:900')
            ->except(['show']);
        Route::post('cargos/recurrentes/{recurrente}/ejecutar', [PlanCargoRecurrenteController::class, 'ejecutar'])
            ->middleware('password.fresh:900')
            ->name('cargos.recurrentes.ejecutar');

        Route::get('cobranza/correos', [CobranzaEmailController::class, 'index'])->name('cobranza.correos.index');
        Route::post('cobranza/correos/enviar', [CobranzaEmailController::class, 'enviar'])
            ->middleware('password.fresh:900')
            ->name('cobranza.correos.enviar');
    });

    // Usuarios internos: Sistemas conserva consulta y soporte de credenciales
    // operativas; creación, roles, activación y desactivación quedan solo en Admin.
    Route::get('usuarios', [UsuarioController::class, 'index'])
        ->middleware('permiso:usuarios.ver')
        ->name('usuarios.index');

    Route::patch('usuarios/{usuario}/password-temporal', [UsuarioController::class, 'generarPasswordTemporal'])
        ->middleware(['permiso:usuarios.credenciales', 'password.fresh:900'])
        ->name('usuarios.password-temporal');

    Route::middleware('rol:Admin')->group(function () {
        Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])
            ->middleware('password.fresh:900')
            ->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])
            ->middleware('password.fresh:900')
            ->name('usuarios.destroy');
        Route::patch('usuarios/{usuario}/reactivar', [UsuarioController::class, 'reactivar'])
            ->middleware('password.fresh:900')
            ->name('usuarios.reactivar');
    });

    Route::get('seguridad/permisos', [SeguridadPermisoController::class, 'index'])
        ->middleware('permiso:seguridad.permisos.ver')
        ->name('seguridad.permisos.index');

    // Configuración institucional: únicamente Admin puede modificar datos
    // oficiales, folios, moratorios y parámetros financieros.
    Route::middleware(['rol:Admin', 'permiso:configuracion.editar'])->group(function () {
        Route::get('configuracion/institucional', [ConfiguracionInstitucionalController::class, 'edit'])
            ->name('configuracion.institucional.edit');
        Route::put('configuracion/institucional', [ConfiguracionInstitucionalController::class, 'update'])
            ->middleware('password.fresh:900')
            ->name('configuracion.institucional.update');
    });

    // Sistemas conserva tareas técnicas no destructivas. Los respaldos y el
    // vaciado de logs quedan reservados a Admin por contener evidencia y datos.
    Route::middleware(['rol:Admin,Sistemas', 'permiso:mantenimiento.ver'])->group(function () {
        Route::get('sistema/mantenimiento', [MantenimientoController::class, 'index'])
            ->name('sistema.mantenimiento.index');
        Route::post('sistema/mantenimiento/limpiar-cache', [MantenimientoController::class, 'limpiarCache'])
            ->middleware('permiso:mantenimiento.ejecutar')
            ->name('sistema.mantenimiento.limpiar-cache');
        Route::post('sistema/mantenimiento/storage-link', [MantenimientoController::class, 'crearStorageLink'])
            ->middleware('permiso:mantenimiento.ejecutar')
            ->name('sistema.mantenimiento.storage-link');
    });

    Route::middleware(['rol:Admin', 'password.fresh:900'])->group(function () {
        Route::post('sistema/mantenimiento/limpiar-logs', [MantenimientoController::class, 'limpiarLogs'])
            ->middleware('permiso:mantenimiento.logs')
            ->name('sistema.mantenimiento.limpiar-logs');
        Route::post('sistema/mantenimiento/backup-base-datos', [MantenimientoController::class, 'descargarBackupBaseDatos'])
            ->middleware('permiso:mantenimiento.backups')
            ->name('sistema.mantenimiento.backup-db');
        Route::post('sistema/mantenimiento/backup-archivos', [MantenimientoController::class, 'descargarBackupArchivos'])
            ->middleware('permiso:mantenimiento.backups')
            ->name('sistema.mantenimiento.backup-archivos');
    });

    // Coordinación Administrativa.
    Route::middleware('rol:Admin,CAdmin')->group(function () {
        Route::resource('conceptos', ConceptoPagoController::class)->except(['show']);
        Route::post('becas/sincronizar', [BecaController::class, 'sincronizar'])->name('becas.sincronizar');
    });

    Route::get('becas', [BecaController::class, 'index'])
        ->middleware('rol:Admin,CAdmin,Direccion')
        ->name('becas.index');

    // Caja operativa: Recepción solo puede consultar y operar su propia caja.
    // La ruta estática /create debe declararse antes de /{corteCaja}.
    Route::get('cortes-caja/create', [CorteCajaController::class, 'create'])
        ->middleware('permiso:caja.operar')
        ->name('cortes-caja.create');

    Route::middleware('permiso:caja.ver')->group(function () {
        Route::get('cortes-caja', [CorteCajaController::class, 'index'])->name('cortes-caja.index');
        Route::get('cortes-caja/{corteCaja}', [CorteCajaController::class, 'show'])->name('cortes-caja.show');
    });

    Route::get('cortes-caja/{corteCaja}/movimientos/{movimientoCaja}/comprobante', [CorteCajaController::class, 'descargarComprobanteMovimiento'])
        ->middleware('permiso:caja.comprobante')
        ->name('cortes-caja.movimientos.comprobante');

    Route::middleware('permiso:caja.operar')->group(function () {
        Route::post('cortes-caja', [CorteCajaController::class, 'store'])->name('cortes-caja.store');
        Route::post('cortes-caja/{corteCaja}/movimientos', [CorteCajaController::class, 'registrarMovimiento'])->name('cortes-caja.movimientos.store');
        Route::put('cortes-caja/{corteCaja}/movimientos/{movimientoCaja}/cancelar', [CorteCajaController::class, 'cancelarMovimiento'])
            ->middleware('password.fresh:900')
            ->name('cortes-caja.movimientos.cancelar');
        Route::get('cortes-caja/{corteCaja}/cierre', [CorteCajaController::class, 'cierre'])
            ->middleware('password.fresh:900')
            ->name('cortes-caja.cierre');
        Route::put('cortes-caja/{corteCaja}/cerrar', [CorteCajaController::class, 'cerrar'])
            ->middleware('password.fresh:900')
            ->name('cortes-caja.cerrar');
    });

    Route::get('cortes-caja/{corteCaja}/pdf', [CorteCajaController::class, 'pdf'])
        ->middleware('permiso:caja.pdf')
        ->name('cortes-caja.pdf');

    Route::middleware('rol:Admin,CAdmin,Direccion')->group(function () {
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/export-excel', [ReporteController::class, 'exportExcel'])->name('reportes.export-excel');
        Route::get('reportes/export-pdf', [ReporteController::class, 'exportPdf'])->name('reportes.export-pdf');

        Route::get('reportes-ejecutivos', [ReporteEjecutivoController::class, 'index'])->name('reportes.ejecutivo');
        Route::get('reportes-ejecutivos/export-csv', [ReporteEjecutivoController::class, 'exportCsv'])->name('reportes.ejecutivo.export-csv');
    });

    // Académica / administración escolar.
    Route::middleware('permiso:educacion_continua.ver')->group(function () {
        Route::get('educacion-continua', [CursoEducacionContinuaController::class, 'index'])
            ->name('educacion_continua.index');
        Route::get('educacion-continua/{educacionContinua}', [CursoEducacionContinuaController::class, 'show'])
            ->name('educacion_continua.show');
    });

    Route::put('educacion-continua/{educacionContinua}/sesiones/{sesion}', [CursoEducacionContinuaController::class, 'updateSesion'])
        ->middleware('permiso:educacion_continua.sesiones.actualizar')
        ->name('educacion_continua.sesiones.update');

    // Oferta académica general: Recepción y RRPP solo consultan catálogos necesarios
    // para atención y captación; no ven expedientes académicos detallados.
    Route::middleware('permiso:oferta_academica.ver')->group(function () {
        Route::get('ciclos_escolares', [CicloEscolarController::class, 'index'])->name('ciclos_escolares.index');
        Route::get('grupos', [GrupoController::class, 'index'])->name('grupos.index');
        Route::get('programas', [ProgramaController::class, 'index'])->name('programas.index');
    });

    // Consulta académica detallada para Académica, CAdmin y Dirección.
    Route::middleware('permiso:academica.ver')->group(function () {
        Route::get('grupos/{grupo}', [GrupoController::class, 'show'])->name('grupos.show');
        Route::get('materias', [MateriaController::class, 'index'])->name('materias.index');
        Route::get('dias-no-laborales', [DiaNoLaboralController::class, 'index'])
            ->name('dias_no_laborales.index');
        Route::get('docentes', [DocenteController::class, 'index'])->name('docentes.index');
        Route::get('docentes/{docente}', [DocenteController::class, 'show'])->name('docentes.show');
    });

    Route::middleware('permiso:calendarios.ver')->group(function () {
        Route::get('calendarios-academicos', [CalendarioAcademicoController::class, 'index'])
            ->name('calendarios_academicos.index');
        Route::get('calendarios-academicos/{calendarioAcademico}', [CalendarioAcademicoController::class, 'show'])
            ->name('calendarios_academicos.show');
    });

    Route::middleware('permiso:horarios.ver')->group(function () {
        Route::get('horarios_academicos', [HorarioAcademicoController::class, 'index'])
            ->name('horarios_academicos.index');
        Route::get('horarios_academicos/{horarioAcademico}', [HorarioAcademicoController::class, 'show'])
            ->name('horarios_academicos.show');
    });

    // Catálogos académicos: solo Admin y Coordinación Académica modifican.
    Route::middleware('permiso:catalogos_academicos.gestionar')->group(function () {
        Route::get('ciclos_escolares/create', [CicloEscolarController::class, 'create'])->name('ciclos_escolares.create');
        Route::post('ciclos_escolares', [CicloEscolarController::class, 'store'])->name('ciclos_escolares.store');
        Route::get('ciclos_escolares/{ciclo_escolar}/edit', [CicloEscolarController::class, 'edit'])->name('ciclos_escolares.edit');
        Route::put('ciclos_escolares/{ciclo_escolar}', [CicloEscolarController::class, 'update'])->name('ciclos_escolares.update');
        Route::delete('ciclos_escolares/{ciclo_escolar}', [CicloEscolarController::class, 'destroy'])
            ->middleware('password.fresh:900')->name('ciclos_escolares.destroy');

        Route::get('grupos/create', [GrupoController::class, 'create'])->name('grupos.create');
        Route::post('grupos', [GrupoController::class, 'store'])->name('grupos.store');
        Route::get('grupos/{grupo}/edit', [GrupoController::class, 'edit'])->name('grupos.edit');
        Route::put('grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');
        Route::delete('grupos/{grupo}', [GrupoController::class, 'destroy'])
            ->middleware('password.fresh:900')->name('grupos.destroy');

        Route::get('materias/create', [MateriaController::class, 'create'])->name('materias.create');
        Route::post('materias', [MateriaController::class, 'store'])->name('materias.store');
        Route::get('materias/{materia}/edit', [MateriaController::class, 'edit'])->name('materias.edit');
        Route::put('materias/{materia}', [MateriaController::class, 'update'])->name('materias.update');
        Route::delete('materias/{materia}', [MateriaController::class, 'destroy'])->name('materias.destroy');

        Route::get('programas/create', [ProgramaController::class, 'create'])->name('programas.create');
        Route::post('programas', [ProgramaController::class, 'store'])->name('programas.store');
        Route::get('programas/{programa}/edit', [ProgramaController::class, 'edit'])->name('programas.edit');
        Route::put('programas/{programa}', [ProgramaController::class, 'update'])->name('programas.update');
        Route::delete('programas/{programa}', [ProgramaController::class, 'destroy'])->name('programas.destroy');

        // Módulo antiguo conservado por compatibilidad; la operación principal usa calendarios exactos.
        Route::get('horarios_academicos/create', [HorarioAcademicoController::class, 'create'])->name('horarios_academicos.create');
        Route::post('horarios_academicos', [HorarioAcademicoController::class, 'store'])->name('horarios_academicos.store');
        Route::get('horarios_academicos/{horarioAcademico}/edit', [HorarioAcademicoController::class, 'edit'])->name('horarios_academicos.edit');
        Route::put('horarios_academicos/{horarioAcademico}', [HorarioAcademicoController::class, 'update'])->name('horarios_academicos.update');
        Route::delete('horarios_academicos/{horarioAcademico}', [HorarioAcademicoController::class, 'destroy'])->name('horarios_academicos.destroy');
    });

    // Calendarios, sesiones y días no laborables: solo Académica/Admin.
    Route::middleware('permiso:calendarios.gestionar')->group(function () {
        Route::get('calendarios-academicos/create', [CalendarioAcademicoController::class, 'create'])->name('calendarios_academicos.create');
        Route::post('calendarios-academicos', [CalendarioAcademicoController::class, 'store'])->name('calendarios_academicos.store');
        Route::get('calendarios-academicos/{calendarioAcademico}/edit', [CalendarioAcademicoController::class, 'edit'])->name('calendarios_academicos.edit');
        Route::put('calendarios-academicos/{calendarioAcademico}', [CalendarioAcademicoController::class, 'update'])->name('calendarios_academicos.update');
        Route::delete('calendarios-academicos/{calendarioAcademico}', [CalendarioAcademicoController::class, 'destroy'])
            ->middleware('password.fresh:900')->name('calendarios_academicos.destroy');

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

        Route::post('dias-no-laborales/cargar-oficiales', [DiaNoLaboralController::class, 'cargarOficiales'])
            ->name('dias_no_laborales.cargar-oficiales');
        Route::post('dias-no-laborales', [DiaNoLaboralController::class, 'store'])->name('dias_no_laborales.store');
        Route::put('dias-no-laborales/{diaNoLaboral}', [DiaNoLaboralController::class, 'update'])->name('dias_no_laborales.update');
        Route::delete('dias-no-laborales/{diaNoLaboral}', [DiaNoLaboralController::class, 'destroy'])->name('dias_no_laborales.destroy');
    });

    // Educación Continua sigue bajo CAdmin/Académica; Sistemas solo actualiza datos técnicos de sesión.
    Route::middleware('permiso:educacion_continua.gestionar')->group(function () {
        Route::get('educacion-continua/create', [CursoEducacionContinuaController::class, 'create'])->name('educacion_continua.create');
        Route::post('educacion-continua', [CursoEducacionContinuaController::class, 'store'])->name('educacion_continua.store');
        Route::get('educacion-continua/{educacionContinua}/edit', [CursoEducacionContinuaController::class, 'edit'])->name('educacion_continua.edit');
        Route::put('educacion-continua/{educacionContinua}', [CursoEducacionContinuaController::class, 'update'])->name('educacion_continua.update');
        Route::delete('educacion-continua/{educacionContinua}', [CursoEducacionContinuaController::class, 'destroy'])
            ->middleware('password.fresh:900')->name('educacion_continua.destroy');
        Route::post('educacion-continua/{educacionContinua}/sesiones', [CursoEducacionContinuaController::class, 'storeSesion'])
            ->name('educacion_continua.sesiones.store');
        Route::delete('educacion-continua/{educacionContinua}/sesiones/{sesion}', [CursoEducacionContinuaController::class, 'destroySesion'])
            ->middleware('password.fresh:900')->name('educacion_continua.sesiones.destroy');
        Route::get('educacion-continua/{educacionContinua}/sesiones/{sesion}/asistencia', [CursoEducacionContinuaController::class, 'asistencia'])
            ->name('educacion_continua.sesiones.asistencia');
        Route::post('educacion-continua/{educacionContinua}/sesiones/{sesion}/asistencia', [CursoEducacionContinuaController::class, 'guardarAsistencia'])
            ->name('educacion_continua.sesiones.asistencia.store');
        Route::post('educacion-continua/{educacionContinua}/inscritos', [CursoEducacionContinuaController::class, 'storeInscrito'])
            ->name('educacion_continua.inscritos.store');
        Route::put('educacion-continua/{educacionContinua}/inscritos/{inscrito}', [CursoEducacionContinuaController::class, 'updateInscrito'])
            ->name('educacion_continua.inscritos.update');
        Route::delete('educacion-continua/{educacionContinua}/inscritos/{inscrito}', [CursoEducacionContinuaController::class, 'destroyInscrito'])
            ->middleware('password.fresh:900')->name('educacion_continua.inscritos.destroy');
    });

    Route::resource('requisitos_documentales', RequisitoDocumentalController::class)
        ->except(['show'])
        ->middleware('permiso:requisitos_documentales.gestionar')
        ->parameters(['requisitos_documentales' => 'requisitoDocumental']);

    // Académica administra docentes; CAdmin solo actualiza datos fiscales/bancarios.
    Route::middleware('permiso:docentes.gestionar')->group(function () {
        Route::get('docentes/create', [DocenteController::class, 'create'])->name('docentes.create');
        Route::post('docentes', [DocenteController::class, 'store'])->name('docentes.store');
        Route::get('docentes/{docente}/edit', [DocenteController::class, 'edit'])->name('docentes.edit');
        Route::put('docentes/{docente}', [DocenteController::class, 'update'])->name('docentes.update');
        Route::delete('docentes/{docente}', [DocenteController::class, 'destroy'])
            ->middleware('password.fresh:900')->name('docentes.destroy');
    });
    Route::get('docentes/{docente}/datos-financieros/edit', [DocenteController::class, 'editFinanciero'])
        ->middleware('rol:Admin,CAdmin')->name('docentes.financieros.edit');
    Route::put('docentes/{docente}/datos-financieros', [DocenteController::class, 'updateFinanciero'])
        ->middleware('rol:Admin,CAdmin')->name('docentes.financieros.update');

    Route::get('docentes/{docente}/documentos/{tipo}', [DocenteController::class, 'descargarDocumento'])
        ->middleware('rol:Admin,CAdmin,Academica')
        ->whereIn('tipo', ['curriculum', 'titulo_cedula', 'constancia_fiscal'])
        ->name('docentes.documentos.download');

    // Solicitudes de pago docente.
    Route::middleware('rol:Admin,CAdmin,Academica,Direccion')->group(function () {
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

    Route::middleware('rol:Admin,CAdmin,Academica,Direccion')->group(function () {
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
    Route::middleware('permiso:academica.ver')->group(function () {
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
