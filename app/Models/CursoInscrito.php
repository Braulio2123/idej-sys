<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoInscrito extends Model
{
    use HasFactory;

    protected $table = 'curso_inscritos';

    public const TIPO_ALUMNO = 'Alumno';
    public const TIPO_PROSPECTO = 'Prospecto';
    public const TIPO_EXTERNO = 'Externo';

    public const ESTATUS_INSCRITO = 'Inscrito';
    public const ESTATUS_BAJA = 'Baja';
    public const ESTATUS_FINALIZADO = 'Finalizado';

    protected $fillable = [
        'curso_id',
        'alumno_id',
        'prospecto_id',
        'tipo_participante',
        'nombre_externo',
        'correo_externo',
        'telefono_externo',
        'estatus',
        'fecha_inscripcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
    ];

    public static function tiposParticipante(): array
    {
        return [self::TIPO_ALUMNO, self::TIPO_PROSPECTO, self::TIPO_EXTERNO];
    }

    public static function estatuses(): array
    {
        return [self::ESTATUS_INSCRITO, self::ESTATUS_BAJA, self::ESTATUS_FINALIZADO];
    }

    public function curso()
    {
        return $this->belongsTo(CursoEducacionContinua::class, 'curso_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    public function asistencias()
    {
        return $this->hasMany(CursoAsistencia::class, 'curso_inscrito_id');
    }

    public function getNombreAttribute(): string
    {
        return $this->alumno->nombre_completo
            ?? $this->prospecto->nombre_completo
            ?? $this->nombre_externo
            ?? 'Participante sin nombre';
    }

    public function getCorreoAttribute(): ?string
    {
        return $this->alumno->correo ?? $this->prospecto->correo ?? $this->correo_externo;
    }

    public function getTelefonoAttribute(): ?string
    {
        return $this->alumno->telefono ?? $this->prospecto->telefono ?? $this->telefono_externo;
    }

    public function horasAsistidas(): float
    {
        return (float) $this->asistencias()
            ->whereIn('estatus', [CursoAsistencia::ESTATUS_ASISTIO, CursoAsistencia::ESTATUS_RETARDO, CursoAsistencia::ESTATUS_JUSTIFICADO])
            ->sum('horas_acreditadas');
    }

    public function porcentajeAvance(): float
    {
        $total = (float) $this->curso?->horas_totales;
        return $total > 0 ? round(($this->horasAsistidas() / $total) * 100, 2) : 0;
    }
}
