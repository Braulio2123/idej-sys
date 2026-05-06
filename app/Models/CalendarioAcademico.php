<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarioAcademico extends Model
{
    use HasFactory;

    protected $table = 'calendarios_academicos';

    public const ESTATUS_BORRADOR = 'Borrador';
    public const ESTATUS_PLANEADO = 'Planeado';
    public const ESTATUS_APROBADO = 'Aprobado';
    public const ESTATUS_EN_CURSO = 'En curso';
    public const ESTATUS_FINALIZADO = 'Finalizado';
    public const ESTATUS_CANCELADO = 'Cancelado';

    public const MODALIDAD_PRESENCIAL = 'Presencial';
    public const MODALIDAD_VIRTUAL = 'Virtual';
    public const MODALIDAD_MIXTA = 'Mixta';

    public const TIPO_POSGRADO_VIERNES_SABADO = 'Posgrado viernes-sábado';
    public const TIPO_LICENCIATURA_SABATINA = 'Licenciatura sabatina';
    public const TIPO_LICENCIATURA_MATUTINA = 'Licenciatura matutina';
    public const TIPO_LICENCIATURA_VESPERTINA = 'Licenciatura vespertina';
    public const TIPO_PERSONALIZADO = 'Personalizado';

    protected $fillable = [
        'grupo_id',
        'ciclo_escolar_id',
        'nombre',
        'periodo',
        'modalidad',
        'tipo_calendario',
        'estatus',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'creado_por_id',
        'aprobado_por_id',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_aprobacion' => 'datetime',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function cicloEscolar()
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id');
    }

    public function materiasCalendario()
    {
        return $this->hasMany(CalendarioMateria::class, 'calendario_academico_id')->orderBy('orden');
    }

    public function sesiones()
    {
        return $this->hasManyThrough(
            CalendarioSesion::class,
            CalendarioMateria::class,
            'calendario_academico_id',
            'calendario_materia_id',
            'id',
            'id'
        );
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por_id');
    }

    public static function tiposCalendario(): array
    {
        return [
            self::TIPO_POSGRADO_VIERNES_SABADO,
            self::TIPO_LICENCIATURA_SABATINA,
            self::TIPO_LICENCIATURA_MATUTINA,
            self::TIPO_LICENCIATURA_VESPERTINA,
            self::TIPO_PERSONALIZADO,
        ];
    }


    public static function tiposPrincipales(): array
    {
        return [
            self::TIPO_POSGRADO_VIERNES_SABADO,
            self::TIPO_LICENCIATURA_SABATINA,
            self::TIPO_LICENCIATURA_MATUTINA,
            self::TIPO_LICENCIATURA_VESPERTINA,
        ];
    }

    public function esPrincipal(): bool
    {
        return in_array($this->tipo_calendario, self::tiposPrincipales(), true);
    }

    public static function diasPermitidosPorTipo(?string $tipo): array
    {
        return match ($tipo) {
            self::TIPO_POSGRADO_VIERNES_SABADO => [5, 6],
            self::TIPO_LICENCIATURA_SABATINA => [6],
            self::TIPO_LICENCIATURA_MATUTINA => [1, 2, 3, 4, 5],
            self::TIPO_LICENCIATURA_VESPERTINA => [1, 2, 3, 4, 5],
            default => [1, 2, 3, 4, 5, 6, 7],
        };
    }

    public static function textoDiasPermitidosPorTipo(?string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_POSGRADO_VIERNES_SABADO => 'viernes y sábado',
            self::TIPO_LICENCIATURA_SABATINA => 'sábado',
            self::TIPO_LICENCIATURA_MATUTINA => 'lunes a viernes',
            self::TIPO_LICENCIATURA_VESPERTINA => 'lunes a viernes',
            default => 'cualquier día',
        };
    }

    public function tiposCompatiblesParaVistaPrevia(): array
    {
        return match ($this->tipo_calendario) {
            self::TIPO_POSGRADO_VIERNES_SABADO => [self::TIPO_POSGRADO_VIERNES_SABADO],
            self::TIPO_LICENCIATURA_SABATINA => [self::TIPO_LICENCIATURA_SABATINA],
            self::TIPO_LICENCIATURA_MATUTINA => [self::TIPO_LICENCIATURA_MATUTINA],
            self::TIPO_LICENCIATURA_VESPERTINA => [self::TIPO_LICENCIATURA_VESPERTINA],
            default => self::tiposCalendario(),
        };
    }

    public function scopeOperativos($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADO, self::ESTATUS_FINALIZADO]);
    }

    public function getRangoFechasAttribute(): string
    {
        $inicio = $this->fecha_inicio?->format('d/m/Y') ?? 'Sin inicio';
        $fin = $this->fecha_fin?->format('d/m/Y') ?? 'Sin fin';
        return $inicio.' - '.$fin;
    }
}
