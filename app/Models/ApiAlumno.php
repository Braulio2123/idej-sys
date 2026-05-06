<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiAlumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

   
    protected $fillable = [
        'matricula',
        'nombre_completo',
        'correo',
        'telefono',
        'estatus_financiero',
        'estatus_academico',
        'beca_porcentaje',
        'condicion_alumno',   // ← nuevo
        'grupo_id',
    ];
}
