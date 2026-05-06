<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CargoPago extends Pivot
{
    protected $table = 'cargo_pago';

    protected $fillable = ['cargo_id', 'pago_id', 'monto_aplicado'];

    protected $casts = [
        'monto_aplicado' => 'decimal:2',
    ];
}
