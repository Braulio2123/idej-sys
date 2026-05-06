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

        Gate::define('es-admin', fn (Usuario $user) => $user->tieneRol(Rol::ADMIN));
        Gate::define('es-sistemas', fn (Usuario $user) => $user->tieneRol(Rol::SISTEMAS));
        Gate::define('es-direccion', fn (Usuario $user) => $user->tieneRol(Rol::DIRECCION));
        Gate::define('es-cadmin', fn (Usuario $user) => $user->tieneRol(Rol::CADMIN));
        Gate::define('es-recepcion', fn (Usuario $user) => $user->tieneRol(Rol::RECEPCION));
        Gate::define('es-academica', fn (Usuario $user) => $user->tieneRol(Rol::ACADEMICA));
        Gate::define('es-finanzas', fn (Usuario $user) => $user->tieneRol(Rol::FINANZAS));

        Gate::define('puede-ver-alumnos', function (Usuario $user) {
            return $user->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION, Rol::RRPP, Rol::ACADEMICA);
        });



        Gate::define('puede-ver-academica', function (Usuario $user) {
            return $user->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::DIRECCION, Rol::SISTEMAS);
        });

        Gate::define('puede-ver-prospectos', function (Usuario $user) {
            return $user->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::DIRECCION);
        });

        Gate::define('puede-ver-finanzas', function (Usuario $user) {
            return $user->tieneRol(Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION);
        });

        Gate::define('puede-operar-caja', function (Usuario $user) {
            return $user->tieneRol(Rol::CADMIN, Rol::FINANZAS, Rol::RECEPCION);
        });

        Gate::define('puede-administrar-usuarios', function (Usuario $user) {
            return $user->tieneRol(Rol::SISTEMAS);
        });

        Gate::define('puede-mantenimiento-sistema', function (Usuario $user) {
            return $user->tieneRol(Rol::ADMIN, Rol::SISTEMAS);
        });

        Gate::define('puede-ver-bitacora', function (Usuario $user) {
            return $user->tieneRol(Rol::SISTEMAS, Rol::DIRECCION);
        });
    }
}
