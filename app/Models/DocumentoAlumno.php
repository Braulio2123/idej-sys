<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentoAlumno extends Model
{
    use HasFactory;

    protected $table = 'documentos_alumnos';

    public const ESTATUS_PENDIENTE = 'Pendiente';
    public const ESTATUS_ENTREGADO = 'Entregado';
    public const ESTATUS_EN_REVISION = 'En revisión';
    public const ESTATUS_ACEPTADO = 'Aceptado';
    public const ESTATUS_RECHAZADO = 'Rechazado';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        return $this->archivo_path ? Storage::disk('public')->url($this->archivo_path) : null;
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
