<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\RequireFreshPassword;
use App\Http\Middleware\PermisoMiddleware;
use App\Http\Middleware\EnsureUsuarioInternoActivo;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    // Tareas programadas del sistema interno.
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('idej:notificaciones-operativas')
            ->everyThirtyMinutes()
            ->timezone(config('app.timezone', 'America/Mexico_City'))
            ->withoutOverlapping();
    })

    // ✅ Registrar grupos y alias de middleware
    ->withMiddleware(function (Middleware $middleware) {

        // 🔹 Grupo "web" — requerido por Breeze y las rutas
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            EnsureUsuarioInternoActivo::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // 🔹 Alias personalizados
        $middleware->alias([
            'rol' => RolMiddleware::class,
            'password.fresh' => RequireFreshPassword::class,
            'permiso' => PermisoMiddleware::class,
        ]);
    })

    // ✅ Registrar proveedores principales
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
    ])

    // ✅ Manejo de excepciones
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 403 && $request->user() && ! $request->attributes->get('idej_access_denied_logged')) {
                try {
                    Bitacora::create([
                        'usuario_id' => $request->user()->id,
                        'tipo' => 'Visita',
                        'accion' => 'Acceso denegado',
                        'modulo' => 'Seguridad',
                        'descripcion' => 'Intento de acceso bloqueado. Ruta: '.$request->method().' '.$request->path().'. Mensaje: '.$e->getMessage(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'metodo_http' => $request->method(),
                        'fecha_evento' => now(),
                    ]);
                } catch (\Throwable $throwable) {
                    logger()->warning('No fue posible registrar acceso denegado en bitácora.', [
                        'error' => $throwable->getMessage(),
                    ]);
                }
            }

            return null;
        });
    })
    ->create();
