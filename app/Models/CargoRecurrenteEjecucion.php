<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargoRecurrenteEjecucion extends Model
{
    use HasFactory;

    protected $table = 'cargo_recurrente_ejecuciones';

    protected $fillable = [
        'plan_cargo_recurrente_id',
        'alumno_id',
        'cargo_id',
        'periodo',
        'fecha_vencimiento',
        'monto_original',
        'monto_adeudo',
        'estatus',
        'motivo',
        'ejecutado_at',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'monto_original' => 'decimal:2',
        'monto_adeudo' => 'decimal:2',
        'ejecutado_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanCargoRecurrente::class, 'plan_cargo_recurrente_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }
}
