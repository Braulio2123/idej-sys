<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoSesion extends Model
{
    use HasFactory;

    protected $table = 'curso_sesiones';

    public const ESTATUS_PROGRAMADA = 'Programada';
    public const ESTATUS_CONFIRMADA = 'Confirmada';
    public const ESTATUS_IMPARTIDA = 'Impartida';
    public const ESTATUS_CANCELADA = 'Cancelada';

    protected $fillable = [
        'curso_id',
        'docente_id',
        'expositor_nombre',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'duracion_horas',
        'aula_liga',
        'modalidad',
        'estatus',
        'requiere_equipo',
        'equipo_requerido',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'duracion_horas' => 'decimal:2',
        'requiere_equipo' => 'boolean',
        'equipo_requerido' => 'array',
    ];

    public static function estatuses(): array
    {
        return [self::ESTATUS_PROGRAMADA, self::ESTATUS_CONFIRMADA, self::ESTATUS_IMPARTIDA, self::ESTATUS_CANCELADA];
    }

    public function curso()
    {
        return $this->belongsTo(CursoEducacionContinua::class, 'curso_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function solicitudesPagoDocente()
    {
        return $this->hasMany(SolicitudPagoDocente::class, 'curso_sesion_id');
    }

    public function solicitudesPagoDocenteOperativas()
    {
        return $this->solicitudesPagoDocente()
            ->whereNotIn('estatus', [SolicitudPagoDocente::ESTATUS_CANCELADA]);
    }

    public function asistencias()
    {
        return $this->hasMany(CursoAsistencia::class, 'curso_sesion_id');
    }

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADA]);
    }

    public function getHorarioAttribute(): string
    {
        return substr((string) $this->hora_inicio, 0, 5).' - '.substr((string) $this->hora_fin, 0, 5);
    }

    public function getExpositorAttribute(): string
    {
        return $this->docente->nombre_completo ?? $this->expositor_nombre ?? 'Expositor no asignado';
    }

    public function calcularDuracion(): float
    {
        if (!$this->hora_inicio || !$this->hora_fin) {
            return 0;
        }

        $inicio = \Carbon\Carbon::parse($this->hora_inicio);
        $fin = \Carbon\Carbon::parse($this->hora_fin);

        if ($fin->lessThanOrEqualTo($inicio)) {
            return 0;
        }

        return round($inicio->diffInMinutes($fin) / 60, 2);
    }
}
