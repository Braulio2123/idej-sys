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
        Rol::FINANZAS,
    ],

    'roles_criticos' => [
        Rol::ADMIN,
        Rol::SISTEMAS,
        Rol::DIRECCION,
        Rol::CADMIN,
        Rol::FINANZAS,
    ],

    'permisos' => [
        'dashboard.ver' => [
            'modulo' => 'General',
            'nombre' => 'Ver dashboard',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION, Rol::RRPP, Rol::FINANZAS],
        ],



        'notificaciones.ver' => [
            'modulo' => 'General',
            'nombre' => 'Ver notificaciones internas',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION, Rol::RRPP, Rol::FINANZAS],
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
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
            'sensible' => true,
        ],
        'usuarios.editar' => [
            'modulo' => 'Administración',
            'nombre' => 'Editar usuarios internos',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
            'sensible' => true,
        ],
        'usuarios.desactivar' => [
            'modulo' => 'Administración',
            'nombre' => 'Desactivar/reactivar usuarios internos',
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
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
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
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
            'roles' => [Rol::ADMIN, Rol::SISTEMAS],
            'sensible' => true,
        ],

        'alumnos.ver' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Ver alumnos internos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'alumnos.gestionar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Crear/editar alumnos internos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN],
        ],
        'documentos.ver' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Ver expediente documental',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA, Rol::DIRECCION],
        ],
        'documentos.gestionar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Subir/revisar documentos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::ACADEMICA],
        ],
        'documentos.descargar' => [
            'modulo' => 'Alumnos y Recepción',
            'nombre' => 'Descargar documentos sensibles',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA, Rol::DIRECCION],
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
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA],
        ],
        'prospectos.ver' => [
            'modulo' => 'Relaciones Públicas',
            'nombre' => 'Ver prospectos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::DIRECCION],
        ],
        'prospectos.gestionar' => [
            'modulo' => 'Relaciones Públicas',
            'nombre' => 'Crear/editar prospectos y convertir a alumno',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::RRPP],
        ],

        'caja.ver' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Ver cortes de caja',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS],
        ],
        'caja.operar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Abrir/cerrar caja',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'pagos.registrar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Registrar pagos de alumnos',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'pagos.cancelar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Cancelar pagos o crear ajustes',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'pagos.comprobante' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Descargar comprobantes de pago',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION],
            'sensible' => true,
        ],
        'conceptos.gestionar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Gestionar conceptos de pago',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
        ],
        'becas.ver' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Ver becas',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION],
        ],
        'becas.gestionar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Crear/cancelar becas',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'convenios.gestionar' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Gestionar convenios y parcialidades',
            'roles' => [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS],
        ],
        'cargos.masivos' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Crear cargos masivos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'reportes.ver' => [
            'modulo' => 'Finanzas',
            'nombre' => 'Ver reportes financieros/operativos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION],
        ],
        'reportes.ejecutivos' => [
            'modulo' => 'Dirección',
            'nombre' => 'Ver reporte ejecutivo integral',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION],
        ],

        'academica.ver' => [
            'modulo' => 'Académica',
            'nombre' => 'Ver módulos académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS],
        ],
        'calendarios.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar calendarios académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS],
        ],
        'educacion_continua.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar Educación Continua',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS, Rol::DIRECCION],
        ],
        'catalogos_academicos.gestionar' => [
            'modulo' => 'Académica',
            'nombre' => 'Gestionar catálogos académicos',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS],
        ],

        'solicitudes_pago.ver' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Ver solicitudes de pago docente',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::FINANZAS, Rol::DIRECCION],
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
            'nombre' => 'Autorizar/observar solicitudes docentes',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'solicitudes_pago.pagar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Registrar pago a docentes',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
            'sensible' => true,
        ],
        'solicitudes_pago.cancelar' => [
            'modulo' => 'Solicitudes Docentes',
            'nombre' => 'Cancelar solicitudes docentes',
            'roles' => [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS],
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
        'es-finanzas' => [Rol::FINANZAS],
        'puede-ver-alumnos' => 'alumnos.ver',
        'puede-ver-academica' => 'academica.ver',
        'puede-ver-prospectos' => 'prospectos.ver',
        'puede-ver-finanzas' => 'reportes.ver',
        'puede-ver-reporte-ejecutivo' => 'reportes.ejecutivos',
        'puede-operar-caja' => 'caja.operar',
        'puede-administrar-usuarios' => 'usuarios.ver',
        'puede-mantenimiento-sistema' => 'mantenimiento.ver',
        'puede-ver-bitacora' => 'bitacoras.ver',
        'puede-ver-centro-control' => 'centro_control.ver',
        'puede-ver-notificaciones' => 'notificaciones.ver',
    ],
];
