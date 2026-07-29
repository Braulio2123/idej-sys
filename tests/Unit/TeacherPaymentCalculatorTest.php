<?php

namespace Tests\Unit\Services;

use App\Models\SolicitudPagoDocente;
use App\Services\TeacherPaymentCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TeacherPaymentCalculatorTest extends TestCase
{
    private TeacherPaymentCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new TeacherPaymentCalculator();
    }

    public function test_calculates_payment_by_sessions(): void
    {
        $this->assertSame('450.00', $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_SESION,
            '450',
            1,
            '4.45',
            '4'
        ));

        $this->assertSame('5000.00', $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_SESION,
            '2500',
            2,
            '8',
            '4'
        ));
    }

    public function test_calculates_payment_by_hours_with_commercial_rounding(): void
    {
        $this->assertSame('2002.50', $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_HORA,
            '450.00',
            1,
            '4.45',
            '4'
        ));

        $this->assertSame('33.37', $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_HORA,
            '10.02',
            1,
            '3.33',
            null
        ));
    }

    public function test_fixed_payment_uses_only_manual_total(): void
    {
        $this->assertSame('1750.25', $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_FIJO,
            '999',
            9,
            '9',
            '1750.25'
        ));
    }

    public function test_rejects_hourly_scheme_without_reported_hours(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate(
            SolicitudPagoDocente::ESQUEMA_HORA,
            '450',
            1,
            null,
            null
        );
    }
}
