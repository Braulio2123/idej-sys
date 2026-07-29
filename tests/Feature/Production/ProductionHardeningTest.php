<?php

namespace Tests\Feature\Production;

use App\Http\Middleware\SecurityHeaders;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    public function test_backups_are_post_only_and_reserved_for_admin(): void
    {
        foreach (['sistema.mantenimiento.backup-db', 'sistema.mantenimiento.backup-archivos'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route);
            $this->assertSame(['POST'], $route->methods());
            $this->assertContains('rol:Admin', $route->gatherMiddleware());
            $this->assertContains('password.fresh:900', $route->gatherMiddleware());
            $this->assertContains('permiso:mantenimiento.backups', $route->gatherMiddleware());
            $this->assertContains('idempotent', $route->gatherMiddleware());
        }
    }

    public function test_institutional_configuration_is_admin_only(): void
    {
        foreach (['configuracion.institucional.edit', 'configuracion.institucional.update'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route);
            $this->assertContains('rol:Admin', $route->gatherMiddleware());
            $this->assertContains('permiso:configuracion.editar', $route->gatherMiddleware());
        }

        $permisos = config('idej_permisos.permisos', []);

        $this->assertSame(
            [Rol::ADMIN],
            $permisos['configuracion.editar']['roles'] ?? null
        );
        $this->assertSame(
            [Rol::ADMIN],
            $permisos['mantenimiento.backups']['roles'] ?? null
        );
    }

    public function test_authenticated_internal_responses_disable_browser_cache(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => new class
        {
        });

        $response = (new SecurityHeaders())->handle(
            $request,
            fn () => new Response('ok')
        );

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString("form-action 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_production_validation_command_is_registered(): void
    {
        $this->assertArrayHasKey('idej:validar-produccion', Artisan::all());
    }
}
