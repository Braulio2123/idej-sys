<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seguridad de infraestructura IDEJ-SYS
    |--------------------------------------------------------------------------
    |
    | TRUSTED_PROXIES debe contener las IP/CIDR del proxy inverso. En plataformas
    | administradas que no publican rangos estables puede utilizarse "*", siempre
    | que la aplicación no sea accesible directamente sin pasar por dicho proxy.
    |
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'force_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),

    'production' => [
        'require_https' => env('PRODUCTION_REQUIRE_HTTPS', true),
        'require_real_mailer' => env('PRODUCTION_REQUIRE_REAL_MAILER', true),
    ],
];
