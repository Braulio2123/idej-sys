<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proxies confiables
    |--------------------------------------------------------------------------
    |
    | Laravel consulta esta configuración cuando atiende la solicitud HTTP.
    | Puede contener una IP, un CIDR, una lista separada por comas o "*" solo
    | cuando el servidor sea accesible exclusivamente mediante el proxy.
    |
    */
    'proxies' => env('TRUSTED_PROXIES'),
];
