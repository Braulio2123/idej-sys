<?php

use App\Models\ConfiguracionInstitucional;
use App\Models\NotificacionInterna;
use App\Models\Usuario;
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


if (! function_exists('usuarioTienePermiso')) {
    function usuarioTienePermiso(string $permiso): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->tienePermiso($permiso);
    }
}

if (! function_exists('rolesParaPermiso')) {
    function rolesParaPermiso(string $permiso): array
    {
        return config("idej_permisos.permisos.{$permiso}.roles", []);
    }
}



if (! function_exists('resumenNotificacionesInternas')) {
    function resumenNotificacionesInternas(?Usuario $usuario = null): array
    {
        $usuario = $usuario ?: Auth::user();

        if (! $usuario || ! \Illuminate\Support\Facades\Schema::hasTable('notificaciones_internas')) {
            return [
                'pendientes' => 0,
                'criticas' => 0,
                'altas' => 0,
            ];
        }

        return [
            'pendientes' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->count(),
            'criticas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->where('severidad', NotificacionInterna::SEVERIDAD_CRITICA)->count(),
            'altas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->where('severidad', NotificacionInterna::SEVERIDAD_ALTA)->count(),
        ];
    }
}

if (! function_exists('notificacionesInternasRecientes')) {
    function notificacionesInternasRecientes(?Usuario $usuario = null, int $limite = 5): \Illuminate\Support\Collection
    {
        $usuario = $usuario ?: Auth::user();

        if (! $usuario || ! \Illuminate\Support\Facades\Schema::hasTable('notificaciones_internas')) {
            return collect();
        }

        return NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->noLeidas()
            ->latest()
            ->limit($limite)
            ->get();
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
