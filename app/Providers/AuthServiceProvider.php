<?php

namespace App\Providers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        foreach (config('idej_permisos.gates', []) as $gate => $definicion) {
            Gate::define($gate, function (Usuario $user) use ($definicion) {
                if (is_string($definicion)) {
                    return $user->tienePermiso($definicion);
                }

                if (is_array($definicion)) {
                    return $user->tieneRol(...$definicion);
                }

                return false;
            });
        }

        foreach (array_keys(config('idej_permisos.permisos', [])) as $permiso) {
            Gate::define($permiso, fn (Usuario $user) => $user->tienePermiso($permiso));
        }

        Gate::define('es-rrpp', fn (Usuario $user) => $user->tieneRol(Rol::RRPP));
    }
}
