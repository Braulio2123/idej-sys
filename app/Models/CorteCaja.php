<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    use HasFactory;

    public const ESTATUS_ABIERTA = 'Abierta';
    public const ESTATUS_CERRADA = 'Cerrada';
    public const TOLERANCIA_DIFERENCIA = 0.009;

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
        'otro_reportado',
        'total_reportado',
        'diferencia_efectivo',
        'diferencia_transferencia',
        'diferencia_tarjeta',
        'diferencia_otro',
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
        'otro_reportado' => 'decimal:2',
        'total_reportado' => 'decimal:2',
        'diferencia_efectivo' => 'decimal:2',
        'diferencia_transferencia' => 'decimal:2',
        'diferencia_tarjeta' => 'decimal:2',
        'diferencia_otro' => 'decimal:2',
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

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'corte_caja_id');
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

    public static function tieneDiferencia(float ...$diferencias): bool
    {
        foreach ($diferencias as $diferencia) {
            if (abs(round($diferencia, 2)) > self::TOLERANCIA_DIFERENCIA) {
                return true;
            }
        }

        return false;
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

    public function resumenMovimientos(): array
    {
        $metodos = MovimientoCaja::metodosPago();
        $resumen = [];

        foreach ($metodos as $metodo) {
            $clave = strtolower($metodo);
            $movimientos = $this->movimientos()->aplicados();

            if ($metodo === 'Otro') {
                $movimientos->whereNotIn('metodo_pago', ['Efectivo', 'Transferencia', 'Tarjeta']);
            } else {
                $movimientos->where('metodo_pago', $metodo);
            }

            $entradas = (float) (clone $movimientos)->where('tipo', MovimientoCaja::TIPO_ENTRADA)->sum('monto');
            $salidas = (float) (clone $movimientos)->where('tipo', MovimientoCaja::TIPO_SALIDA)->sum('monto');

            $resumen["entradas_{$clave}"] = round($entradas, 2);
            $resumen["salidas_{$clave}"] = round($salidas, 2);
            $resumen["neto_{$clave}"] = round($entradas - $salidas, 2);
        }

        $entradasTotal = array_sum(array_map(
            fn (string $metodo): float => (float) $resumen['entradas_'.strtolower($metodo)],
            $metodos
        ));
        $salidasTotal = array_sum(array_map(
            fn (string $metodo): float => (float) $resumen['salidas_'.strtolower($metodo)],
            $metodos
        ));

        return array_merge($resumen, [
            'entradas_total' => round($entradasTotal, 2),
            'salidas_total' => round($salidasTotal, 2),
            'neto_total' => round($entradasTotal - $salidasTotal, 2),
            'cantidad' => (int) $this->movimientos()->aplicados()->count(),
        ]);
    }

    public function efectivoDisponible(): float
    {
        $totales = $this->calcularTotalesSistema();
        $movimientos = $this->resumenMovimientos();

        return round(
            (float) $this->saldo_inicial
            + (float) $totales['efectivo_sistema']
            + (float) $movimientos['neto_efectivo'],
            2
        );
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
