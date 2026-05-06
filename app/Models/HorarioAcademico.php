<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioAcademico extends Model
{
    use HasFactory;

    public const ESTATUS_ACTIVO = 'Activo';
    public const ESTATUS_SUSPENDIDO = 'Suspendido';
    public const ESTATUS_FINALIZADO = 'Finalizado';

    public const MODALIDAD_PRESENCIAL = 'Presencial';
    public const MODALIDAD_VIRTUAL = 'Virtual';
    public const MODALIDAD_MIXTA = 'Mixta';

    public const DIAS = [
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo',
    ];

    protected $table = 'horarios_academicos';

    protected $fillable = [
        'grupo_id',
        'materia_id',
        'docente_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'aula',
        'modalidad',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estatus', self::ESTATUS_ACTIVO);
    }

    public static function diaActual(): string
    {
        return match ((int) now()->dayOfWeekIso) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            default => 'Domingo',
        };
    }

    public function getHorarioAttribute(): string
    {
        return substr((string) $this->hora_inicio, 0, 5).' - '.substr((string) $this->hora_fin, 0, 5);
    }
}
