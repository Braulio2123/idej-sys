<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{
    use HasFactory;

    public const ESTATUS_PROGRAMADA = 'Programada';
    public const ESTATUS_ACTIVA = 'Activa';
    public const ESTATUS_VENCIDA = 'Vencida';
    public const ESTATUS_CANCELADA = 'Cancelada';

    protected $table = 'becas';

    protected $fillable = [
        'alumno_id',
        'autorizado_por_id',
        'registrado_por_id',
        'cancelado_por_id',
        'tipo',
        'porcentaje',
        'motivo',
        'observaciones',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
        'fecha_cancelacion',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'porcentaje' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_cancelacion' => 'datetime',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por_id');
    }

    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'beca_id');
    }

    public function scopeVigentes(Builder $query): Builder
    {
        $hoy = Carbon::today()->toDateString();

        return $query
            ->whereIn('estatus', [self::ESTATUS_ACTIVA, self::ESTATUS_PROGRAMADA])
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function (Builder $q) use ($hoy) {
                $q->whereNull('fecha_fin')
                  ->orWhereDate('fecha_fin', '>=', $hoy);
            });
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estatus', self::ESTATUS_ACTIVA);
    }

    public function scopeProgramadas(Builder $query): Builder
    {
        return $query->where('estatus', self::ESTATUS_PROGRAMADA);
    }

    public function estaVigente(): bool
    {
        $hoy = Carbon::today();

        return in_array($this->estatus, [self::ESTATUS_ACTIVA, self::ESTATUS_PROGRAMADA], true)
            && $this->fecha_inicio->lte($hoy)
            && (is_null($this->fecha_fin) || $this->fecha_fin->gte($hoy));
    }

    public function estaCancelada(): bool
    {
        return $this->estatus === self::ESTATUS_CANCELADA;
    }

    public static function estatusDisponibles(): array
    {
        return [
            self::ESTATUS_PROGRAMADA,
            self::ESTATUS_ACTIVA,
            self::ESTATUS_VENCIDA,
            self::ESTATUS_CANCELADA,
        ];
    }

    public static function tiposDisponibles(): array
    {
        return [
            'Institucional',
            'Convenio especial',
            'Excelencia académica',
            'Apoyo económico',
            'Promoción',
            'Otro',
        ];
    }
}
