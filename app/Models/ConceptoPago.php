<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ConceptoPago extends Model
{
    use HasFactory;

    protected $table = 'conceptos_pagos'; // 👈 plural correcto

    protected $fillable = [
    'nombre',
    'monto_base',
    'es_becable',
];

protected $casts = [
    'es_becable' => 'boolean',
];

    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'concepto_id');
    }

    public function aplicaIvaEducacionContinua(): bool
    {
        $nombre = Str::lower(Str::ascii((string) $this->nombre));

        return Str::contains($nombre, [
            'educacion continua',
            'curso',
            'taller',
            'diplomado',
            'masterclass',
            'masc',
            'oratoria',
        ]);
    }

    public function montoConIvaEducacionContinua(float $montoBase): float
    {
        $montoBase = round($montoBase, 2);

        return $this->aplicaIvaEducacionContinua()
            ? round($montoBase * 1.16, 2)
            : $montoBase;
    }
}

