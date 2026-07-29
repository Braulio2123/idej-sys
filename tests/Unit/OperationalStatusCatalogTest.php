<?php

namespace Tests\Unit;

use App\Http\Controllers\PagoController;
use App\Models\CalendarioAcademico;
use App\Models\Docente;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OperationalStatusCatalogTest extends TestCase
{
    public function test_calendario_academico_uses_the_operational_status_catalog(): void
    {
        $this->assertSame([
            CalendarioAcademico::ESTATUS_AGENDADO,
            CalendarioAcademico::ESTATUS_EN_CURSO,
            CalendarioAcademico::ESTATUS_FINALIZADO,
            CalendarioAcademico::ESTATUS_CANCELADO,
        ], CalendarioAcademico::estatuses());
    }

    public function test_docente_status_catalog_includes_inactive(): void
    {
        $this->assertSame([
            Docente::ESTATUS_PENDIENTE,
            Docente::ESTATUS_ACTIVO,
            Docente::ESTATUS_INACTIVO,
        ], Docente::estatuses());
    }

    public function test_inactive_docente_is_not_reactivated_by_automatic_recalculation(): void
    {
        $docente = new Docente();
        $docente->forceFill(['estatus' => Docente::ESTATUS_INACTIVO]);

        $this->assertSame(Docente::ESTATUS_INACTIVO, $docente->calcularEstatus());
    }

    public function test_pago_controller_contains_the_academic_payment_notification_handler(): void
    {
        $controller = new ReflectionClass(PagoController::class);

        $this->assertTrue($controller->hasMethod('notificarPagoAcademicoSiAplica'));
        $this->assertTrue($controller->getMethod('notificarPagoAcademicoSiAplica')->isPrivate());
    }
}
