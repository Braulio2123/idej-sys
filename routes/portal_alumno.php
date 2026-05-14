<?php

use App\Http\Controllers\PortalAlumno\AuthController;
use App\Http\Controllers\PortalAlumno\AvisoController;
use App\Http\Controllers\PortalAlumno\CalificacionController;
use App\Http\Controllers\PortalAlumno\DashboardController;
use App\Http\Controllers\PortalAlumno\HorarioController;
use App\Http\Controllers\PortalAlumno\MateriaController;
use App\Http\Controllers\PortalAlumno\PerfilController;
use App\Http\Controllers\PortalAlumno\UbicacionController;
use App\Http\Middleware\PortalAlumno\EnsurePortalAlumnoAuthenticated;
use App\Http\Middleware\PortalAlumno\RedirectIfPortalAlumnoAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Alumno PWA - Christian
|--------------------------------------------------------------------------
|
| Este archivo concentra exclusivamente las rutas del portal del alumno.
| No pertenece al panel administrativo ni modifica los CRUD internos del IDEJ.
|
| Prefijo publico: /portal-alumno
| Nombre de rutas: portal.alumno.*
|
*/

Route::prefix('portal-alumno')
    ->name('portal.alumno.')
    ->group(function () {
        Route::middleware(RedirectIfPortalAlumnoAuthenticated::class)->group(function () {
            Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        });

        Route::middleware(EnsurePortalAlumnoAuthenticated::class)->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/horario', [HorarioController::class, 'index'])->name('horario');
            Route::get('/materias', [MateriaController::class, 'index'])->name('materias');
            Route::get('/calificaciones', [CalificacionController::class, 'index'])->name('calificaciones');
            Route::get('/avisos', [AvisoController::class, 'index'])->name('avisos');
            Route::get('/ubicacion', [UbicacionController::class, 'index'])->name('ubicacion');
            Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });
