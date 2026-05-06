<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConceptoPago;


class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';

    protected $fillable = [
        'alumno_id',
        'concepto_id',
        'beca_id',
        'descripcion_cargo',
        'monto_original',
        'beca_porcentaje_aplicado',
        'beca_monto_aplicado',
        'monto_adeudo',
        'fecha_vencimiento',
        'estatus',
        'moratorio_aplicado'
    ];

    protected $casts = [
        'monto_original' => 'decimal:2',
        'beca_porcentaje_aplicado' => 'integer',
        'beca_monto_aplicado' => 'decimal:2',
        'monto_adeudo' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'estatus' => 'string',
        'moratorio_aplicado' => 'boolean',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoPago::class, 'concepto_id');
    }

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }


    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'cargo_pago')
            ->using(CargoPago::class)
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    public function convenio()
    {
        return $this->hasOne(Convenio::class, 'cargo_original_id');
    }

    public function convenios()
    {
        return $this->belongsToMany(Convenio::class, 'cargo_convenio')
            ->withPivot('monto_original', 'monto_adeudo_original', 'estatus_original')
            ->withTimestamps();
    }
}
