<?php

use App\Models\ConfiguracionInstitucional;
use Illuminate\Support\Facades\Auth;

if (! function_exists('rolActual')) {
    function rolActual(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        return Auth::user()?->rolClave();
    }
}

if (! function_exists('usuarioTieneRol')) {
    function usuarioTieneRol(string ...$roles): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->tieneRol(...$roles);
    }
}

if (! function_exists('configuracionInstitucional')) {
    function configuracionInstitucional(): ConfiguracionInstitucional
    {
        return ConfiguracionInstitucional::actual();
    }
}

if (! function_exists('configInstitucional')) {
    function configInstitucional(?string $campo = null, mixed $default = null): mixed
    {
        $configuracion = configuracionInstitucional();

        if ($campo === null) {
            return $configuracion;
        }

        return data_get($configuracion, $campo, $default);
    }
}

if (! function_exists('logoInstitucionalUrl')) {
    function logoInstitucionalUrl(): string
    {
        return configuracionInstitucional()->logoUrl();
    }
}

if (! function_exists('logoInstitucionalPathPdf')) {
    function logoInstitucionalPathPdf(): string
    {
        return configuracionInstitucional()->logoPathPdf();
    }
}
