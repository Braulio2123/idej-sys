<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Recordatorios operativos IDEJ-SYS
    |--------------------------------------------------------------------------
    |
    | Fase 35: el canal institucional activo es únicamente correo electrónico.
    | SMS y WhatsApp quedan fuera del alcance para evitar costos, proveedores
    | externos y complejidad de consentimiento mientras se estabiliza cobranza.
    |
    */

    'alumnos_adeudo' => [
        'activo' => env('IDEJ_RECORDATORIOS_ADEUDO_ACTIVOS', false),
        'dias_antes_vencimiento' => (int) env('IDEJ_RECORDATORIOS_DIAS_ANTES', 3),
        'dias_despues_vencimiento' => (int) env('IDEJ_RECORDATORIOS_DIAS_DESPUES', 30),
        'hora_envio' => env('IDEJ_RECORDATORIOS_HORA', '09:00'),
        'limite_diario' => (int) env('IDEJ_RECORDATORIOS_LIMITE_DIARIO', 150),
    ],

    'canales' => [
        'email' => [
            'activo' => env('IDEJ_RECORDATORIOS_EMAIL', false),
        ],
    ],

    'cargos_recurrentes' => [
        'activo' => env('IDEJ_CARGOS_RECURRENTES_ACTIVOS', false),
        'hora_generacion' => env('IDEJ_CARGOS_RECURRENTES_HORA', '06:30'),
    ],

    'notificaciones' => [
        'polling_milisegundos' => (int) env('IDEJ_NOTIFICACIONES_POLLING_MS', 3000),
    ],
];
