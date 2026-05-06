<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoMasivo extends Model
{
    use HasFactory;

    protected $table = 'cargos_masivos';

    protected $fillable = [
        'concepto_id',
        'monto',
        'fecha_vencimiento',
        'descripcion',
        'programa_id',
        'grupo_id',
        'ciclo_escolar_id',
        'total_alumnos',
        'usuario_id',
    ];

    /**
     * Concepto de pago asociado.
     */
    public function concepto()
    {
        return $this->belongsTo(ConceptoPago::class, 'concepto_id');
    }

    /**
     * Usuario que ejecutó la operación masiva.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Programa académico filtrado.
     */
    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    /**
     * Grupo académico filtrado.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    /**
     * Ciclo escolar filtrado.
     */
    public function cicloEscolar()
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id');
    }
}
