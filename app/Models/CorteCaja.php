<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    use HasFactory;

    public const ESTATUS_ABIERTA = 'Abierta';
    public const ESTATUS_CERRADA = 'Cerrada';

    protected $table = 'cortes_caja';

    protected $fillable = [
        'usuario_id',
        'usuario_caja_abierta_id',
        'fecha_apertura',
        'fecha_cierre',
        'saldo_inicial',
        'efectivo_sistema',
        'transferencia_sistema',
        'tarjeta_sistema',
        'total_sistema',
        'cantidad_pagos',
        'efectivo_reportado',
        'transferencia_reportado',
        'tarjeta_reportado',
        'total_reportado',
        'diferencia_efectivo',
        'diferencia_total',
        'estatus',
        'observaciones_apertura',
        'observaciones_cierre',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'efectivo_sistema' => 'decimal:2',
        'transferencia_sistema' => 'decimal:2',
        'tarjeta_sistema' => 'decimal:2',
        'total_sistema' => 'decimal:2',
        'efectivo_reportado' => 'decimal:2',
        'transferencia_reportado' => 'decimal:2',
        'tarjeta_reportado' => 'decimal:2',
        'total_reportado' => 'decimal:2',
        'diferencia_efectivo' => 'decimal:2',
        'diferencia_total' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'corte_caja_id');
    }

    public function ajustes()
    {
        return $this->hasMany(AjusteCaja::class, 'corte_caja_id');
    }

    public function scopeAbierta($query)
    {
        return $query->where('estatus', self::ESTATUS_ABIERTA);
    }

    public function scopeCerrada($query)
    {
        return $query->where('estatus', self::ESTATUS_CERRADA);
    }

    public function scopeDeUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function estaAbierta(): bool
    {
        return $this->estatus === self::ESTATUS_ABIERTA;
    }

    public function estaCerrada(): bool
    {
        return $this->estatus === self::ESTATUS_CERRADA;
    }

    public function calcularTotalesSistema(): array
    {
        $pagos = $this->pagos()->activos();

        $efectivo = (float) (clone $pagos)->where('metodo_pago', 'Efectivo')->sum('monto_total_pagado');
        $transferencia = (float) (clone $pagos)->where('metodo_pago', 'Transferencia')->sum('monto_total_pagado');
        $tarjeta = (float) (clone $pagos)->where('metodo_pago', 'Tarjeta')->sum('monto_total_pagado');
        $cantidad = (int) (clone $pagos)->count();
        $total = round($efectivo + $transferencia + $tarjeta, 2);

        return [
            'efectivo_sistema' => round($efectivo, 2),
            'transferencia_sistema' => round($transferencia, 2),
            'tarjeta_sistema' => round($tarjeta, 2),
            'total_sistema' => $total,
            'cantidad_pagos' => $cantidad,
        ];
    }

    public function sincronizarTotalesSistema(): void
    {
        $this->forceFill($this->calcularTotalesSistema())->save();
    }

    public function resumenAjustes(): array
    {
        $ajustes = $this->ajustes()->aplicados();

        $efectivo = (float) (clone $ajustes)->where('metodo_pago', 'Efectivo')->sum('monto_ajuste');
        $transferencia = (float) (clone $ajustes)->where('metodo_pago', 'Transferencia')->sum('monto_ajuste');
        $tarjeta = (float) (clone $ajustes)->where('metodo_pago', 'Tarjeta')->sum('monto_ajuste');
        $cantidad = (int) (clone $ajustes)->count();
        $total = round($efectivo + $transferencia + $tarjeta, 2);

        return [
            'efectivo_ajustes' => round($efectivo, 2),
            'transferencia_ajustes' => round($transferencia, 2),
            'tarjeta_ajustes' => round($tarjeta, 2),
            'total_ajustes' => $total,
            'cantidad_ajustes' => $cantidad,
        ];
    }
}

