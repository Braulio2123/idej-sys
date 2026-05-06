<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarioSesion extends Model
{
    use HasFactory;

    protected $table = 'calendario_sesiones';

    public const TIPO_CLASE = 'Clase';
    public const TIPO_COLOQUIO = 'Coloquio';
    public const TIPO_CONFERENCIA = 'Conferencia';
    public const TIPO_EXAMEN = 'Examen';
    public const TIPO_OTRO = 'Otro';

    public const ESTATUS_PROGRAMADA = 'Programada';
    public const ESTATUS_CONFIRMADA = 'Confirmada';
    public const ESTATUS_IMPARTIDA = 'Impartida';
    public const ESTATUS_SUSPENDIDA = 'Suspendida';
    public const ESTATUS_CANCELADA = 'Cancelada';

    protected $fillable = [
        'calendario_materia_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'aula',
        'modalidad',
        'tipo_sesion',
        'estatus',
        'observaciones',
        'sesion_origen_id',
        'cancelada_por_id',
        'reprogramada_por_id',
        'fecha_reprogramacion',
        'motivo_cancelacion',
        'motivo_reprogramacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_reprogramacion' => 'datetime',
    ];

    public function calendarioMateria()
    {
        return $this->belongsTo(CalendarioMateria::class, 'calendario_materia_id');
    }


    public function sesionOrigen()
    {
        return $this->belongsTo(self::class, 'sesion_origen_id');
    }

    public function reposiciones()
    {
        return $this->hasMany(self::class, 'sesion_origen_id');
    }

    public function canceladaPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelada_por_id');
    }

    public function reprogramadaPor()
    {
        return $this->belongsTo(Usuario::class, 'reprogramada_por_id');
    }

    public function getHorarioAttribute(): string
    {
        if (!$this->hora_inicio || !$this->hora_fin) {
            return 'Horario pendiente';
        }

        return substr((string) $this->hora_inicio, 0, 5).' - '.substr((string) $this->hora_fin, 0, 5);
    }

    public function getDiaSemanaAttribute(): string
    {
        return match ((int) $this->fecha->dayOfWeekIso) {
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            default => 'Domingo',
        };
    }

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADA, self::ESTATUS_SUSPENDIDA]);
    }

    public function scopeActivos($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADA, self::ESTATUS_SUSPENDIDA]);
    }
}
