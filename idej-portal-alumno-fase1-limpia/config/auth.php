<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'usuarios'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'usuarios',
        ],

        /*
        |--------------------------------------------------------------------------
        | Guard exclusivo del Portal Alumno PWA - Christian
        |--------------------------------------------------------------------------
        |
        | Este guard se mantiene separado del acceso administrativo `web`.
        | Permite autenticar alumnos sin mezclar sesiones ni modelos internos
        | del personal administrativo del IDEJ.
        |
        */
        'portal_alumno' => [
            'driver' => 'session',
            'provider' => 'portal_alumnos',
        ],
    ],

    'providers' => [
        'usuarios' => [
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Provider exclusivo del Portal Alumno PWA - Christian
        |--------------------------------------------------------------------------
        |
        | Apunta al modelo App\Models\PortalAlumno\AlumnoPortal, que usa la
        | tabla `alumnos` sin reemplazar el modelo administrativo App\Models\Alumno.
        |
        */
        'portal_alumnos' => [
            'driver' => 'eloquent',
            'model' => App\Models\PortalAlumno\AlumnoPortal::class,
        ],
    ],

    'passwords' => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
