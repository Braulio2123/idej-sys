<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteCaja extends Model
{
    use HasFactory;

    public const TIPO_CANCELACION_PAGO_CERRADO = 'Cancelación de pago en caja cerrada';
    public const ESTATUS_APLICADO = 'Aplicado';

    protected $table = 'ajustes_caja';

    protected $fillable = [
        'corte_caja_id',
        'pago_id',
        'alumno_id',
        'usuario_id',
        'tipo',
        'metodo_pago',
        'monto_ajuste',
        'estatus',
        'motivo',
        'observaciones',
        'fecha_aplicacion',
    ];

    protected $casts = [
        'monto_ajuste' => 'decimal:2',
        'fecha_aplicacion' => 'datetime',
    ];

    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopeAplicados($query)
    {
        return $query->where('estatus', self::ESTATUS_APLICADO);
    }

    public function esNegativo(): bool
    {
        return (float) $this->monto_ajuste < 0;
    }
}
