<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanCargoRecurrente extends Model
{
    use HasFactory;

    public const ALCANCE_TODOS = 'todos';
    public const ALCANCE_PROGRAMA = 'programa';
    public const ALCANCE_GRUPO = 'grupo';

    protected $table = 'planes_cargos_recurrentes';

    protected $fillable = [
        'nombre',
        'concepto_id',
        'alcance',
        'programa_id',
        'grupo_id',
        'monto',
        'dia_vencimiento',
        'frecuencia_meses',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'activo',
        'enviar_recordatorio_email',
        'creado_por_id',
        'actualizado_por_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'dia_vencimiento' => 'integer',
        'frecuencia_meses' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'enviar_recordatorio_email' => 'boolean',
    ];

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoPago::class, 'concepto_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por_id');
    }

    public function ejecuciones(): HasMany
    {
        return $this->hasMany(CargoRecurrenteEjecucion::class, 'plan_cargo_recurrente_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'cargo_recurrente_plan_id');
    }

    public function alcanceDescripcion(): string
    {
        return match ($this->alcance) {
            self::ALCANCE_TODOS => 'Todos los alumnos activos',
            self::ALCANCE_PROGRAMA => 'Programa: '.($this->programa?->nombre ?? 'sin programa'),
            self::ALCANCE_GRUPO => 'Grupo: '.($this->grupo?->nombre ?? 'sin grupo'),
            default => 'Alcance no definido',
        };
    }
}
