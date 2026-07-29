<?php

namespace Tests\Feature\UX;

use Tests\TestCase;

class PostRedirectGetTest extends TestCase
{
    public function test_critical_success_flows_use_explicit_get_destinations(): void
    {
        $checks = [
            app_path('Http/Controllers/SeguimientoController.php') => [
                "route('alumnos.seguimientos.index', \$alumno)",
            ],
            app_path('Http/Controllers/SolicitudPagoDocenteController.php') => [
                "route('solicitudes_pago.show', \$solicitud_pago)",
            ],
            app_path('Http/Controllers/DiaNoLaboralController.php') => [
                "route('dias_no_laborales.index'",
            ],
            app_path('Http/Controllers/CursoEducacionContinuaController.php') => [
                "route('educacion_continua.show', \$educacionContinua)",
            ],
            app_path('Http/Controllers/MantenimientoController.php') => [
                "route('sistema.mantenimiento.index')",
            ],
        ];

        foreach ($checks as $file => $expectedFragments) {
            $contents = file_get_contents($file);

            foreach ($expectedFragments as $fragment) {
                $this->assertStringContainsString($fragment, $contents, "Falta PRG explícito en {$file}");
            }
        }
    }

    public function test_both_internal_layouts_include_form_recovery_script(): void
    {
        foreach ([
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/guest.blade.php'),
        ] as $layout) {
            $contents = file_get_contents($layout);
            $this->assertStringContainsString("@include('partials.idempotent-forms')", $contents);
        }
    }
}
