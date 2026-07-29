<?php

namespace Tests\Feature\UX;

use App\Http\Middleware\PreventDuplicateSubmission;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InternalMutationRoutesIdempotencyTest extends TestCase
{
    public function test_all_internal_mutation_routes_use_idempotency_protection(): void
    {
        $exemptRoutes = [
            'cargos.masivo.filtrar', // Consulta AJAX sin modificación de datos.
        ];

        $unprotected = collect(Route::getRoutes())
            ->filter(fn (IlluminateRoute $route) => $this->isInternalMutation($route))
            ->reject(fn (IlluminateRoute $route) => in_array($route->getName(), $exemptRoutes, true))
            ->reject(function (IlluminateRoute $route) {
                return collect($route->gatherMiddleware())
                    ->contains(fn ($middleware) => $middleware === 'idempotent'
                        || $middleware === PreventDuplicateSubmission::class);
            })
            ->map(fn (IlluminateRoute $route) => implode('|', $route->methods()).' '.$route->uri())
            ->values()
            ->all();

        $this->assertSame([], $unprotected, 'Rutas internas sin protección: '.implode(', ', $unprotected));
    }

    public function test_read_only_mass_filter_is_explicitly_exempt(): void
    {
        $route = Route::getRoutes()->getByName('cargos.masivo.filtrar');

        $this->assertNotNull($route);
        $this->assertContains('idempotent', $route->excludedMiddleware());
    }

    private function isInternalMutation(IlluminateRoute $route): bool
    {
        if ($route->uri() === '/' || str_starts_with($route->uri(), 'portal-alumno')) {
            return false;
        }

        return collect($route->methods())->contains(
            fn (string $method) => in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
        );
    }
}
