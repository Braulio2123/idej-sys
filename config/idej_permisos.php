<?php

use App\Models\Rol;

return [
    /*
    |--------------------------------------------------------------------------
    | Matriz central de permisos internos IDEJ-SYS
    |--------------------------------------------------------------------------
    |
    | Esta matriz documenta y centraliza los permisos funcionales del panel
    | administrativo. Admin conserva acceso total por regla del modelo Usuario.
    | El Portal Alumno no forma parte de esta matriz.
    |
    */

    'roles' => [
        Rol::ADMIN,
        Rol::SISTEMAS,
        Rol::DIRECCION,
        Rol::CADMIN,
        Rol::ACADEMICA,
        Rol::RECEPCION,
        Rol::RRPP,
    ],

    'roles_criticos' => [
        Rol::ADMIN,
        Rol::SISTEMAS,
        Rol::DIRECCION,
        Rol::CADMIN,
    ],

    'permisos' => [
        'dashboard.ver' => [
            'modulo' => 'General',
            'nombre' => 'Ver dashboard',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION, Rol::RRPP],
        ],



        'notificaciones.ver' => [
            'modulo' => 'General',
            'nombre' => 'Ver notificaciones internas',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION, Rol::RRPP],
        ],

        'agenda_operativa.ver' => [
            'modulo' => 'Operación',
            'nombre' => 'Ver agenda operativa',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::ACADEMICA, Rol::CADMIN, Rol::DIRECCION, Rol::RECEPCION],
        ],

        'centro_control.ver' => [
            'modulo' => 'Operación',
            'nombre' => 'Ver centro de control operativo',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::ACADEMICA, Rol::CADMIN, Rol::DIRECCION, Rol::RECEPCION],
        ],

        'usuarios.ver' => [
            'modulo' => 'Administración',
            'nombre' => 'Ver usuarios internos',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
        ],
        'usuarios.crear' => [
            'modulo' => 'Administración',
            'nombre' => 'Crear usuarios internos',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'usuarios.editar' => [
            'modulo' => 'Administración',
            'nombre' => 'Editar usuarios internos',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'usuarios.desactivar' => [
            'modulo' => 'Administración',
            'nombre' => 'Desactivar/reactivar usuarios internos',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'usuarios.credenciales' => [
            'modulo' => 'Administración',
            'nombre' => 'Generar contraseña temporal',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'seguridad.permisos.ver' => [
            'modulo' => 'Administración',
            'nombre' => 'Consultar matriz de permisos',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION],
        ],
        'configuracion.editar' => [
            'modulo' => 'Administración',
            'nombre' => 'Editar configuración institucional',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'mantenimiento.ver' => [
            'modulo' => 'Administración',
            'nombre' => 'Ver mantenimiento del sistema',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
        ],
        'mantenimiento.ejecutar' => [
            'modulo' => 'Administración',
            'nombre' => 'Ejecutar acciones de mantenimiento',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
            'sensible' => true,
        ],
        'mantenimiento.backups' => [
            'modulo' => 'Administración',
            'nombre' => 'Descargar respaldos',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
        'mantenimiento.logs' => [
            'modulo' => 'Administración',
            'nombre' => 'Vaciar registros técnicos',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],

        'alumnos.ver' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Ver alumnos internos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'alumnos.gestionar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Crear/editar alumnos internos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN],
        ],
        'documentos.ver' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Ver expediente documental',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'documentos.gestionar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Subir/revisar documentos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::ACADEMICA],
        ],
        'documentos.descargar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Descargar documentos sensibles',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::ACADEMICA],
            'sensible' => true,
        ],
        'documentos.eliminar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Eliminar documentos del expediente',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'seguimientos.gestionar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Registrar seguimientos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::ACADEMICA],
        ],
        'prospectos.ver' => [
            'modulo' => 'Relaciones Públicas',
            'nombre' => 'Ver prospectos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::DIRECCION],
        ],
        'prospectos.gestionar' => [
            'modulo' => 'Relaciones Públicas',
            'nombre' => 'Crear/editar prospectos y registrar seguimientos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::RRPP],
        ],
        'prospectos.convertir' => [
            'modulo' => 'Relaciones Públicas',
            'nombre' => 'Validar y convertir prospectos a alumnos',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],

        'caja.ver' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Ver cortes de caja',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::DIRECCION],
        ],
        'caja.operar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Abrir/cerrar caja',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN],
            'sensible' => true,
        ],
        'caja.pdf' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Generar PDF oficial de cortes cerrados',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION],
            'sensible' => true,
        ],
        'caja.comprobante' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Descargar comprobantes de movimientos de caja',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::RECEPCION],
            'sensible' => true,
        ],
        'pagos.registrar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Registrar pagos de alumnos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN],
            'sensible' => true,
        ],
        'pagos.cancelar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Cancelar pagos o crear ajustes',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'pagos.comprobante' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Descargar comprobantes de pago',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN],
            'sensible' => true,
        ],
        'conceptos.gestionar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Gestionar conceptos de pago',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
        ],
        'becas.ver' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Ver becas',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::DIRECCION],
        ],
        'becas.gestionar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Crear/cancelar becas',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'convenios.ver' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Consultar convenios y parcialidades',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::DIRECCION],
        ],
        'convenios.gestionar' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Gestionar convenios y parcialidades',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'cargos.masivos' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Crear cargos masivos',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'reportes.ver' => [
            'modulo' => 'Administración financiera',
            'nombre' => 'Ver reportes financieros/operativos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION],
        ],
        'reportes.ejecutivos' => [
            'modulo' => 'Dirección',
            'nombre' => 'Ver reporte ejecutivo integral',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION],
        ],

        'oferta_academica.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Consultar oferta académica, ciclos y grupos disponibles',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION, Rol::RECEPCION, Rol::RRPP],
        ],
        'academica.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Consultar operación académica detallada',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'calendarios.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Consultar calendarios académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION, Rol::RECEPCION],
        ],
        'horarios.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Consultar horarios académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION, Rol::RECEPCION],
        ],
        'calendarios.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar calendarios, sesiones y días no laborables',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
            'sensible' => true,
        ],
        'educacion_continua.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Ver Educación Continua',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS, Rol::DIRECCION, Rol::RRPP],
        ],
        'educacion_continua.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar cursos, sesiones e inscritos de Educación Continua',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA],
        ],
        'educacion_continua.sesiones.actualizar' => [
            'modulo' => 'Académica',
            'nombre' => 'Actualizar sesiones o asignar aula y equipo',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS],
        ],
        'catalogos_academicos.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar catálogos académicos',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
            'sensible' => true,
        ],
        'requisitos_documentales.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar requisitos documentales administrativos y académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA],
        ],
        'docentes.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar docentes y documentación académica',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
            'sensible' => true,
        ],

        'solicitudes_pago.ver' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Ver solicitudes de pago docente',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'solicitudes_pago.crear' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Crear solicitudes de pago docente',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
        ],
        'solicitudes_pago.editar_academica' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Editar solicitudes desde Académica',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
        ],
        'solicitudes_pago.autorizar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Valorar, programar, observar o rechazar solicitudes docentes',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'solicitudes_pago.pagar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Registrar pago a docentes',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'solicitudes_pago.cancelar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Rechazar solicitudes docentes o cancelar excepcionalmente como Admin',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
            'sensible' => true,
        ],
        'solicitudes_pago.eliminar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Eliminar solicitudes docentes no pagadas',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],

        'bitacoras.ver' => [
            'modulo' => 'Auditoría',
            'nombre' => 'Ver bitácora del sistema',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION],
            'sensible' => true,
        ],
        'bitacoras.ocultar' => [
            'modulo' => 'Auditoría',
            'nombre' => 'Ocultar registros de bitácora',
            'roles' => [Rol::ADMIN],
            'sensible' => true,
        ],
    ],

    'gates' => [
        'es-admin' => [Rol::ADMIN],
        'es-sistemas' => [Rol::SISTEMAS],
        'es-direccion' => [Rol::DIRECCION],
        'es-cadmin' => [Rol::CADMIN],
        'es-recepcion' => [Rol::RECEPCION],
        'es-academica' => [Rol::ACADEMICA],
        'puede-ver-alumnos' => 'alumnos.ver',
        'puede-ver-oferta-academica' => 'oferta_academica.ver',
        'puede-ver-academica' => 'academica.ver',
        'puede-ver-prospectos' => 'prospectos.ver',
        'puede-ver-administracion-financiera' => 'reportes.ver',
        'puede-ver-reporte-ejecutivo' => 'reportes.ejecutivos',
        'puede-operar-caja' => 'caja.operar',
        'puede-administrar-usuarios' => 'usuarios.ver',
        'puede-mantenimiento-sistema' => 'mantenimiento.ver',
        'puede-ver-bitacora' => 'bitacoras.ver',
        'puede-ver-centro-control' => 'centro_control.ver',
        'puede-ver-notificaciones' => 'notificaciones.ver',
    ],
];
