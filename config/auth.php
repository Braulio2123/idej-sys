<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración predeterminada de autenticación
    |--------------------------------------------------------------------------
    |
    | Se mantiene el guard principal `web` para el sistema administrativo
    | existente del IDEJ. El Portal Alumno usa un guard separado llamado
    | `portal_alumno`, declarado más abajo.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'usuarios'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de autenticación
    |--------------------------------------------------------------------------
    |
    | `web`:
    |   Guard original del sistema administrativo/académico.
    |
    | `portal_alumno`:
    |   Guard exclusivo del Portal Alumno PWA trabajado por Christian.
    |   Se mantiene separado para no mezclar sesiones ni modelos del sistema
    |   administrativo.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'usuarios',
        ],

        /*
        |--------------------------------------------------------------------------
        | Portal Alumno PWA - Christian
        |--------------------------------------------------------------------------
        |
        | Guard exclusivo para alumnos.
        | No reemplaza ni modifica el acceso administrativo existente.
        |
        */
        'portal_alumno' => [
            'driver' => 'session',
            'provider' => 'portal_alumnos',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers de usuarios
    |--------------------------------------------------------------------------
    |
    | `usuarios`:
    |   Provider original del sistema administrativo.
    |
    | `portal_alumnos`:
    |   Provider exclusivo del Portal Alumno. Usa un modelo separado:
    |   App\Models\PortalAlumno\AlumnoPortal
    |
    | Importante:
    |   Este provider puede apuntar a la tabla `alumnos`, pero NO modifica
    |   ni reemplaza el modelo administrativo App\Models\Alumno.
    |
    */

    'providers' => [
        'usuarios' => [
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Portal Alumno PWA - Christian
        |--------------------------------------------------------------------------
        |
        | Provider exclusivo del portal del alumno.
        | Usa un modelo separado para proteger la lógica administrativa existente.
        |
        */
        'portal_alumnos' => [
            'driver' => 'eloquent',
            'model' => App\Models\PortalAlumno\AlumnoPortal::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de contraseñas
    |--------------------------------------------------------------------------
    |
    | Se conserva únicamente el broker original `usuarios` para el sistema
    | administrativo. Por ahora el Portal Alumno no tendrá recuperación de
    | contraseña independiente hasta que se implemente formalmente.
    |
    */

    'passwords' => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de confirmación de contraseña
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
