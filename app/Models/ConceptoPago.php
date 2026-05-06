<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
}
