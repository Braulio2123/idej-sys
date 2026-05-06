<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    public const ESTATUS_ACTIVA = 'Activa';
    public const ESTATUS_INACTIVA = 'Inactiva';

    protected $table = 'materias';

    protected $fillable = [
        'programa_id',
        'clave',
        'nombre',
        'nivel',
        'semestre_o_cuatrimestre',
        'creditos',
        'horas_teoricas',
        'horas_practicas',
        'estatus',
        'descripcion',
    ];

    protected $casts = [
        'semestre_o_cuatrimestre' => 'integer',
        'creditos' => 'integer',
        'horas_teoricas' => 'integer',
        'horas_practicas' => 'integer',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioAcademico::class, 'materia_id');
    }

    public function calendarioMaterias()
    {
        return $this->hasMany(CalendarioMateria::class, 'materia_id');
    }


    public function scopeActivas($query)
    {
        return $query->where('estatus', self::ESTATUS_ACTIVA);
    }
}
