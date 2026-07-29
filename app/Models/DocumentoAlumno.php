<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class DocumentoAlumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documentos_alumnos';

    public const ESTATUS_PENDIENTE = 'Pendiente';
    public const ESTATUS_ENTREGADO = 'Entregado';
    public const ESTATUS_EN_REVISION = 'En revisión';
    public const ESTATUS_ACEPTADO = 'Aceptado';
    public const ESTATUS_RECHAZADO = 'Rechazado';

    public const CLASIFICACION_IDENTIDAD = 'Identidad';
    public const CLASIFICACION_ACADEMICA = 'Académica';
    public const CLASIFICACION_FINANCIERA = 'Financiera';
    public const CLASIFICACION_ADMISION = 'Admisión';
    public const CLASIFICACION_RESTRINGIDA = 'Restringida';

    protected $fillable = [
        'alumno_id',
        'requisito_documental_id',
        'usuario_subio_id',
        'usuario_reviso_id',
        'tipo_documento',
        'nombre_original',
        'archivo_path',
        'mime_type',
        'extension',
        'tamano_bytes',
        'archivo_sha256',
        'archivo_verificado_at',
        'estatus',
        'fecha_documento',
        'fecha_entrega',
        'fecha_revision',
        'observaciones',
        'motivo_rechazo',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
        'fecha_entrega' => 'datetime',
        'fecha_revision' => 'datetime',
        'tamano_bytes' => 'integer',
        'archivo_verificado_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static function tiposDisponibles(): array
    {
        return [
            'Acta de nacimiento',
            'CURP',
            'Identificación oficial',
            'Comprobante de domicilio',
            'Certificado de estudios',
            'Título profesional',
            'Cédula profesional',
            'Comprobante de pago',
            'Solicitud de inscripción',
            'Contrato / reglamento firmado',
            'Fotografía',
            'Otro',
        ];
    }

    public static function estatusDisponibles(): array
    {
        return [
            self::ESTATUS_PENDIENTE,
            self::ESTATUS_ENTREGADO,
            self::ESTATUS_EN_REVISION,
            self::ESTATUS_ACEPTADO,
            self::ESTATUS_RECHAZADO,
        ];
    }

    public static function clasificacionParaTipo(?string $tipo): string
    {
        return match (mb_strtolower(trim((string) $tipo))) {
            'acta de nacimiento', 'curp', 'identificación oficial', 'comprobante de domicilio', 'fotografía' => self::CLASIFICACION_IDENTIDAD,
            'certificado de estudios', 'título profesional', 'cédula profesional' => self::CLASIFICACION_ACADEMICA,
            'comprobante de pago' => self::CLASIFICACION_FINANCIERA,
            'solicitud de inscripción', 'contrato / reglamento firmado' => self::CLASIFICACION_ADMISION,
            default => self::CLASIFICACION_RESTRINGIDA,
        };
    }

    public function clasificacion(): string
    {
        return self::clasificacionParaTipo($this->tipo_documento);
    }

    public static function clasificacionesVisiblesPara(?Usuario $usuario): array
    {
        $rol = $usuario?->rolClave();

        return match ($rol) {
            Rol::ADMIN, Rol::CADMIN => [
                self::CLASIFICACION_IDENTIDAD,
                self::CLASIFICACION_ACADEMICA,
                self::CLASIFICACION_FINANCIERA,
                self::CLASIFICACION_ADMISION,
                self::CLASIFICACION_RESTRINGIDA,
            ],
            Rol::RECEPCION => [
                self::CLASIFICACION_IDENTIDAD,
                self::CLASIFICACION_ACADEMICA,
                self::CLASIFICACION_FINANCIERA,
                self::CLASIFICACION_ADMISION,
            ],
            Rol::ACADEMICA => [self::CLASIFICACION_ACADEMICA, self::CLASIFICACION_ADMISION],
            Rol::DIRECCION => [
                self::CLASIFICACION_ACADEMICA,
                self::CLASIFICACION_FINANCIERA,
                self::CLASIFICACION_ADMISION,
            ],
            default => [],
        };
    }

    public static function tiposVisiblesPara(?Usuario $usuario): array
    {
        $clasificaciones = self::clasificacionesVisiblesPara($usuario);

        return array_values(array_filter(
            self::tiposDisponibles(),
            fn (string $tipo) => in_array(self::clasificacionParaTipo($tipo), $clasificaciones, true)
        ));
    }

    public function puedeVer(?Usuario $usuario): bool
    {
        return in_array($this->clasificacion(), self::clasificacionesVisiblesPara($usuario), true);
    }

    public function puedeDescargar(?Usuario $usuario): bool
    {
        return ($usuario?->tienePermiso('documentos.descargar') ?? false)
            && $this->puedeVer($usuario)
            && filled($this->archivo_path);
    }

    public function puedeGestionar(?Usuario $usuario): bool
    {
        $rol = $usuario?->rolClave();
        $clasificacion = $this->clasificacion();

        return match ($rol) {
            Rol::ADMIN, Rol::CADMIN => true,
            Rol::RECEPCION => $clasificacion !== self::CLASIFICACION_RESTRINGIDA,
            Rol::ACADEMICA => in_array($clasificacion, [self::CLASIFICACION_ACADEMICA, self::CLASIFICACION_ADMISION], true),
            default => false,
        };
    }

    public static function usuarioPuedeGestionarTipo(?Usuario $usuario, string $tipo): bool
    {
        $documento = new self(['tipo_documento' => $tipo]);

        return $documento->puedeGestionar($usuario);
    }

    public static function usuarioPuedeRevisarTipo(?Usuario $usuario, string $tipo): bool
    {
        $documento = new self(['tipo_documento' => $tipo]);

        return $documento->puedeRevisar($usuario);
    }

    public function puedeRevisar(?Usuario $usuario): bool
    {
        $rol = $usuario?->rolClave();
        $clasificacion = $this->clasificacion();

        return match ($rol) {
            Rol::ADMIN, Rol::CADMIN => true,
            Rol::RECEPCION => in_array($clasificacion, [self::CLASIFICACION_IDENTIDAD, self::CLASIFICACION_ADMISION], true),
            Rol::ACADEMICA => in_array($clasificacion, [self::CLASIFICACION_ACADEMICA, self::CLASIFICACION_ADMISION], true),
            default => false,
        };
    }

    public function scopeVisiblesPara(Builder $query, ?Usuario $usuario): Builder
    {
        $tipos = self::tiposVisiblesPara($usuario);
        $puedeVerRestringidos = in_array(self::CLASIFICACION_RESTRINGIDA, self::clasificacionesVisiblesPara($usuario), true);

        if ($tipos === [] && ! $puedeVerRestringidos) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $subquery) use ($tipos, $puedeVerRestringidos) {
            if ($tipos !== []) {
                $subquery->whereIn('tipo_documento', $tipos);
            }

            if ($puedeVerRestringidos) {
                $subquery->orWhereNotIn('tipo_documento', self::tiposDisponibles());
            }
        });
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function requisitoDocumental()
    {
        return $this->belongsTo(RequisitoDocumental::class, 'requisito_documental_id');
    }

    public function usuarioSubio()
    {
        return $this->belongsTo(Usuario::class, 'usuario_subio_id');
    }

    public function usuarioReviso()
    {
        return $this->belongsTo(Usuario::class, 'usuario_reviso_id');
    }

    public function getUrlAttribute(): ?string
    {
        // Los documentos de alumnos son sensibles. No se expone URL pública directa.
        return null;
    }

    public function getTamanoLegibleAttribute(): string
    {
        if (! $this->tamano_bytes) {
            return '—';
        }

        if ($this->tamano_bytes >= 1048576) {
            return number_format($this->tamano_bytes / 1048576, 2).' MB';
        }

        return number_format($this->tamano_bytes / 1024, 1).' KB';
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estatus', [
            self::ESTATUS_PENDIENTE,
            self::ESTATUS_RECHAZADO,
        ]);
    }

    public function scopeAceptados($query)
    {
        return $query->where('estatus', self::ESTATUS_ACEPTADO);
    }
}
