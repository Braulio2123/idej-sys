<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoEducacionContinua extends Model
{
    use HasFactory;

    protected $table = 'cursos_educacion_continua';

    public const TIPO_MASC = 'MASC';
    public const TIPO_ORATORIA = 'Oratoria';
    public const TIPO_MASTERCLASS = 'MasterClass';
    public const TIPO_TALLER = 'Taller';
    public const TIPO_DIPLOMADO = 'Diplomado';
    public const TIPO_CURSO = 'Curso';
    public const TIPO_CONFERENCIA = 'Conferencia';
    public const TIPO_OTRO = 'Otro';

    public const MODALIDAD_PRESENCIAL = 'Presencial';
    public const MODALIDAD_VIRTUAL = 'Virtual';
    public const MODALIDAD_MIXTA = 'Mixta';

    public const ESTATUS_PLANEADO = 'Planeado';
    public const ESTATUS_ABIERTO = 'Abierto';
    public const ESTATUS_EN_CURSO = 'En curso';
    public const ESTATUS_FINALIZADO = 'Finalizado';
    public const ESTATUS_CANCELADO = 'Cancelado';

    protected $fillable = [
        'nombre',
        'tipo',
        'modalidad',
        'horas_totales',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
        'responsable_id',
        'creado_por_id',
        'cupo_maximo',
        'costo',
        'requiere_equipo',
        'equipo_requerido',
        'observaciones',
    ];

    protected $casts = [
        'horas_totales' => 'decimal:2',
        'costo' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'requiere_equipo' => 'boolean',
        'equipo_requerido' => 'array',
    ];

    public static function tipos(): array
    {
        return [
            self::TIPO_MASC,
            self::TIPO_ORATORIA,
            self::TIPO_MASTERCLASS,
            self::TIPO_TALLER,
            self::TIPO_DIPLOMADO,
            self::TIPO_CURSO,
            self::TIPO_CONFERENCIA,
            self::TIPO_OTRO,
        ];
    }

    public static function modalidades(): array
    {
        return [self::MODALIDAD_PRESENCIAL, self::MODALIDAD_VIRTUAL, self::MODALIDAD_MIXTA];
    }

    public static function estatuses(): array
    {
        return [
            self::ESTATUS_PLANEADO,
            self::ESTATUS_ABIERTO,
            self::ESTATUS_EN_CURSO,
            self::ESTATUS_FINALIZADO,
            self::ESTATUS_CANCELADO,
        ];
    }

    public static function equiposDisponibles(): array
    {
        return ['Cámara', 'Micrófono', 'Bocina', 'Laptop', 'Proyector', 'Zoom', 'Grabación', 'Streaming'];
    }

    public function sesiones()
    {
        return $this->hasMany(CursoSesion::class, 'curso_id')->orderBy('fecha')->orderBy('hora_inicio');
    }

    public function inscritos()
    {
        return $this->hasMany(CursoInscrito::class, 'curso_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function scopeOperativos($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_FINALIZADO, self::ESTATUS_CANCELADO]);
    }

    public function scopeProximos($query)
    {
        return $query->whereDate('fecha_fin', '>=', now()->toDateString())
            ->whereNotIn('estatus', [self::ESTATUS_FINALIZADO, self::ESTATUS_CANCELADO]);
    }

    public function horasProgramadas(): float
    {
        return (float) $this->sesiones()
            ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])
            ->sum('duracion_horas');
    }

    public function horasImpartidas(): float
    {
        return (float) $this->sesiones()
            ->where('estatus', CursoSesion::ESTATUS_IMPARTIDA)
            ->sum('duracion_horas');
    }

    public function getAvanceProgramadoAttribute(): float
    {
        $total = (float) $this->horas_totales;
        return $total > 0 ? round(($this->horasProgramadas() / $total) * 100, 2) : 0;
    }
}
