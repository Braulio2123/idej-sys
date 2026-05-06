<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaNoLaboral extends Model
{
    use HasFactory;

    protected $table = 'dias_no_laborales';

    protected $fillable = [
        'fecha',
        'nombre',
        'tipo',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    public const TIPO_LEY = 'Ley';
    public const TIPO_INSTITUCIONAL = 'Institucional';
    public const TIPO_INTERNO = 'Interno';

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public static function existeEnFecha(string $fecha): ?self
    {
        return self::activos()->whereDate('fecha', $fecha)->first();
    }
}
