<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HistoricalIntegrityTest extends TestCase
{
    #[Test]
    public function destructive_controllers_preserve_historical_records(): void
    {
        $sources = [
            'GrupoController.php' => file_get_contents(app_path('Http/Controllers/GrupoController.php')),
            'ConvenioController.php' => file_get_contents(app_path('Http/Controllers/ConvenioController.php')),
            'ParcialidadConvenioController.php' => file_get_contents(app_path('Http/Controllers/ParcialidadConvenioController.php')),
            'ProspectoController.php' => file_get_contents(app_path('Http/Controllers/ProspectoController.php')),
            'SeguimientoController.php' => file_get_contents(app_path('Http/Controllers/SeguimientoController.php')),
        ];

        $this->assertStringContainsString("'activo' => false", $sources['GrupoController.php']);
        $this->assertStringContainsString("'estatus' => 'Cancelado'", $sources['ConvenioController.php']);
        $this->assertStringContainsString('Las parcialidades no se eliminan', $sources['ParcialidadConvenioController.php']);
        $this->assertStringContainsString("'archivado_at' => now()", $sources['ProspectoController.php']);
        $this->assertStringContainsString("'estatus' => Seguimiento::ESTATUS_CANCELADO", $sources['SeguimientoController.php']);
    }

    #[Test]
    public function payments_only_accept_partialities_from_locked_active_agreements(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/PagoController.php'));

        $this->assertStringContainsString('use App\\Models\\Convenio;', $source);
        $this->assertStringContainsString("->where('estatus', 'Activo')", $source);
        $this->assertStringContainsString('->lockForUpdate()', $source);
        $this->assertStringContainsString('convenio cancelado, finalizado o ajeno', $source);
    }

    #[Test]
    public function finalized_academic_calendars_are_read_only_history(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/CalendarioAcademicoController.php'));
        $sessionController = file_get_contents(app_path('Http/Controllers/CalendarioSesionController.php'));

        $this->assertStringContainsString('ESTATUS_FINALIZADO', $controller);
        $this->assertStringContainsString('no puede cancelarse de forma retroactiva', $controller);
        $this->assertStringContainsString('cancelado o finalizado', $sessionController);
    }

    #[Test]
    public function critical_financial_and_academic_foreign_keys_are_restrictive(): void
    {
        $migrations = [
            database_path('migrations/2026_01_08_204257_create_cargo_pago_table.php'),
            database_path('migrations/2026_01_08_204300_create_pago_parcialidad_table.php'),
            database_path('migrations/2026_01_08_204318_create_ajustes_caja_table.php'),
            database_path('migrations/2026_01_08_204330_create_solicitudes_pago_docentes_table.php'),
            database_path('migrations/2026_01_08_204252_create_grupos_table.php'),
        ];

        foreach ($migrations as $migration) {
            $source = file_get_contents($migration);
            $this->assertStringContainsString('restrictOnDelete()', $source, basename($migration));
        }
    }

    #[Test]
    public function compatibility_migration_does_not_restore_destructive_cascades(): void
    {
        $source = file_get_contents(database_path('migrations/2026_07_24_000003_preserve_historical_integrity.php'));

        $this->assertStringContainsString('replaceDestructiveForeignKeys', $source);
        $this->assertStringContainsString('ON DELETE RESTRICT', $source);
        $this->assertStringNotContainsString('cascadeOnDelete()', $source);
        $this->assertStringContainsString('No se restauran cascadas destructivas', $source);
    }

    #[Test]
    public function destructive_routes_require_recent_password_where_applicable(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertMatchesRegularExpression("/alumnos\/\{alumno\}.*password\.fresh:900/s", $routes);
        $this->assertMatchesRegularExpression("/alumnos\/\{alumno\}\/convenios\/\{convenio\}.*password\.fresh:900/s", $routes);
        $this->assertMatchesRegularExpression("/grupos\/\{grupo\}.*password\.fresh:900/s", $routes);
    }
}
