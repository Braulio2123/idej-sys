<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    public const TIPO_ENTRADA = 'Entrada';
    public const TIPO_SALIDA = 'Salida';

    public const ESTATUS_APLICADO = 'Aplicado';
    public const ESTATUS_CANCELADO = 'Cancelado';

    protected $fillable = [
        'operacion_uuid',
        'corte_caja_id',
        'usuario_id',
        'cancelado_por_id',
        'tipo',
        'concepto',
        'monto',
        'metodo_pago',
        'referencia',
        'comprobante_path',
        'comprobante_original',
        'comprobante_mime',
        'comprobante_tamano',
        'comprobante_sha256',
        'observaciones',
        'fecha_movimiento',
        'estatus',
        'fecha_cancelacion',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'comprobante_tamano' => 'integer',
    ];

    public static function tipos(): array
    {
        return [self::TIPO_ENTRADA, self::TIPO_SALIDA];
    }

    public static function metodosPago(): array
    {
        return ['Efectivo', 'Transferencia', 'Tarjeta', 'Otro'];
    }

    public static function conceptosSugeridos(): array
    {
        return [
            'Cambio inicial o adicional',
            'Compra de agua',
            'Papelería / insumos',
            'Reembolso autorizado',
            'Gasto operativo menor',
            'Otro',
        ];
    }

    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por_id');
    }

    public function scopeAplicados($query)
    {
        return $query->where('estatus', self::ESTATUS_APLICADO);
    }

    public function estaCancelado(): bool
    {
        return $this->estatus === self::ESTATUS_CANCELADO;
    }

    public function esSalida(): bool
    {
        return $this->tipo === self::TIPO_SALIDA;
    }

    public function montoFirmado(): float
    {
        $monto = (float) $this->monto;

        return $this->esSalida() ? -1 * $monto : $monto;
    }
}
