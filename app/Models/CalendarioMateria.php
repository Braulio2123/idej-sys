<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarioMateria extends Model
{
    use HasFactory;

    protected $table = 'calendario_materias';

    public const ESTATUS_PROGRAMADA = 'Programada';
    public const ESTATUS_CONFIRMADA = 'Confirmada';
    public const ESTATUS_IMPARTIDA = 'Impartida';
    public const ESTATUS_CANCELADA = 'Cancelada';

    protected $fillable = [
        'calendario_academico_id',
        'materia_id',
        'docente_id',
        'orden',
        'nombre_materia_snapshot',
        'docente_snapshot',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function calendario()
    {
        return $this->belongsTo(CalendarioAcademico::class, 'calendario_academico_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function sesiones()
    {
        return $this->hasMany(CalendarioSesion::class, 'calendario_materia_id')->orderBy('fecha')->orderBy('hora_inicio');
    }

    public function getNombreMateriaAttribute(): string
    {
        return $this->nombre_materia_snapshot ?: ($this->materia->nombre ?? 'Materia no disponible');
    }

    public function getNombreDocenteAttribute(): string
    {
        return $this->docente_snapshot ?: ($this->docente->nombre_completo ?? 'Docente no disponible');
    }
}
