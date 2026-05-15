<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class SeguridadPermisoController extends Controller
{
    public function index(): View
    {
        $roles = config('idej_permisos.roles', []);
        $rolesCriticos = config('idej_permisos.roles_criticos', []);

        $permisos = collect(config('idej_permisos.permisos', []))
            ->map(function (array $definicion, string $clave) {
                return [
                    'clave' => $clave,
                    'modulo' => $definicion['modulo'] ?? 'General',
                    'nombre' => $definicion['nombre'] ?? $clave,
                    'roles' => $definicion['roles'] ?? [],
                    'sensible' => (bool) ($definicion['sensible'] ?? false),
                ];
            })
            ->sortBy(['modulo', 'nombre'])
            ->groupBy('modulo');

        $resumenPorRol = collect($roles)->mapWithKeys(function (string $rol) {
            $total = collect(config('idej_permisos.permisos', []))
                ->filter(fn (array $definicion) => in_array($rol, $definicion['roles'] ?? [], true) || $rol === 'Admin')
                ->count();

            return [$rol => $total];
        });

        return view('seguridad.permisos.index', [
            'roles' => $roles,
            'rolesCriticos' => $rolesCriticos,
            'permisosPorModulo' => $permisos,
            'resumenPorRol' => $resumenPorRol,
        ]);
    }
}
