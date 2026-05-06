<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CicloEscolar extends Model
{
    use HasFactory;

    protected $table = 'ciclos_escolares';

    protected $fillable = [
        'nombre',
        'tipo_periodo',
        'fecha_inicio_inscripcion',
        'fecha_fin_inscripcion',
        'fecha_inicio_clases',
        'fecha_fin_clases',
        'activo',
    ];

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'ciclo_escolar_id');
    }
}
