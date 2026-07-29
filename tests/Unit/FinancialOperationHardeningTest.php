<?php

namespace Tests\Unit;

use App\Models\Cargo;
use App\Models\CargoMasivo;
use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinancialOperationHardeningTest extends TestCase
{
    #[Test]
    public function detecta_diferencias_por_metodo_aunque_el_total_se_compense(): void
    {
        $this->assertTrue(CorteCaja::tieneDiferencia(0.00, 100.00, -100.00, 0.00, 0.00));
        $this->assertFalse(CorteCaja::tieneDiferencia(0.00, 0.004, -0.004, 0.00, 0.00));
    }

    #[Test]
    public function los_movimientos_y_cargos_exponen_sus_claves_de_idempotencia(): void
    {
        $this->assertTrue((new MovimientoCaja())->isFillable('operacion_uuid'));
        $this->assertTrue((new Cargo())->isFillable('operacion_uuid'));
        $this->assertTrue((new CargoMasivo())->isFillable('operacion_uuid'));
    }

    #[Test]
    public function los_cargos_conservan_el_origen_masivo_y_el_saldo_aplicado(): void
    {
        $cargo = new Cargo();

        $this->assertTrue($cargo->isFillable('cargo_masivo_id'));
        $this->assertTrue($cargo->isFillable('saldo_favor_aplicado'));
    }
}
