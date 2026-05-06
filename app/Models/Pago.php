<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'alumno_id',
        'usuario_id',
        'cancelado_por_id',
        'corte_caja_id',
        'metodo_pago',
        'monto_total_pagado',
        'saldo_a_favor_generado',
        'estatus',
        'fecha_pago',
        'fecha_cancelacion',
        'folio_recibo',
        'recibo_uuid',
        'recibo_emitido_at',
        'recibo_version',
        'referencia_bancaria',
        'archivo_comprobante',
        'banco_emisor',
        'cuenta_origen',
        'numero_autorizacion',
        'clave_rastreo',
        'concepto_transferencia',
        'fecha_transferencia',
        'banco_destino',
        'observaciones',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'monto_total_pagado' => 'decimal:2',
        'saldo_a_favor_generado' => 'decimal:2',
        'fecha_pago' => 'date',
        'fecha_cancelacion' => 'datetime',
        'fecha_transferencia' => 'datetime',
        'recibo_emitido_at' => 'datetime',
        'recibo_version' => 'integer',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }

    public function cargos()
    {
        return $this->belongsToMany(Cargo::class, 'cargo_pago')
            ->using(CargoPago::class)
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    public function parcialidades()
    {
        return $this->belongsToMany(ParcialidadConvenio::class, 'pago_parcialidad', 'pago_id', 'parcialidad_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por_id');
    }

    public function ajustesCaja()
    {
        return $this->hasMany(AjusteCaja::class, 'pago_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estatus', 'Activo');
    }

    public function scopeCancelados($query)
    {
        return $query->where('estatus', 'Cancelado');
    }

    public function estaCancelado(): bool
    {
        return $this->estatus === 'Cancelado';
    }

    public function estaActivo(): bool
    {
        return $this->estatus !== 'Cancelado';
    }

    public function getReferenciaPrincipalAttribute(): ?string
    {
        return $this->referencia_bancaria
            ?: $this->clave_rastreo
            ?: $this->numero_autorizacion
            ?: $this->folio_recibo;
    }
}
