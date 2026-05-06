<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\RolMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    // ✅ Tareas programadas
    ->withSchedule(function (Schedule $schedule) {
        // 09:00 - Recordatorios
        $schedule->command('app:enviar-recordatorios')
            ->dailyAt('09:00')
            ->timezone('America/Mexico_City');

        // 10:00 - Moratorios (después de recordatorios)
        $schedule->command('app:aplicar-moratorios')
            ->dailyAt('10:00')
            ->timezone('America/Mexico_City');
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
        ]);

        // 🔹 Alias personalizados
        $middleware->alias([
            'rol' => RolMiddleware::class,
        ]);
    })

    // ✅ Registrar proveedores principales
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
    ])

    // ✅ Manejo de excepciones
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
