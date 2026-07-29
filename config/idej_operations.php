<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Protección contra doble envío
    |--------------------------------------------------------------------------
    |
    | Los formularios internos generan una clave por entrada del historial del
    | navegador. El servidor mantiene un bloqueo corto mientras procesa y una
    | marca temporal después de completar la operación.
    |
    */
    'cache_store' => env('IDEMPOTENCY_CACHE_STORE', env('CACHE_STORE', 'database')),
    'lock_seconds' => (int) env('IDEMPOTENCY_LOCK_SECONDS', 180),
    'ttl_seconds' => (int) env('IDEMPOTENCY_TTL_SECONDS', 900),
];
