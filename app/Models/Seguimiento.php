<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';

    public const TIPO_LLAMADA = 'Llamada';
    public const TIPO_WHATSAPP = 'WhatsApp';
    public const TIPO_CORREO = 'Correo';
    public const TIPO_VISITA = 'Visita';
    public const TIPO_DOCUMENTO = 'Documento';
    public const TIPO_ACUERDO_PAGO = 'Acuerdo de pago';
    public const TIPO_ACADEMICO = 'Académico';
    public const TIPO_GENERAL = 'General';

    public const PRIORIDAD_BAJA = 'Baja';
    public const PRIORIDAD_NORMAL = 'Normal';
    public const PRIORIDAD_ALTA = 'Alta';
    public const PRIORIDAD_URGENTE = 'Urgente';

    public const ESTATUS_ABIERTO = 'Abierto';
    public const ESTATUS_EN_PROCESO = 'En proceso';
    public const ESTATUS_CERRADO = 'Cerrado';
    public const ESTATUS_CANCELADO = 'Cancelado';

    protected $fillable = [
        'alumno_id',
        'prospecto_id',
        'usuario_id',
        'area',
        'tipo',
        'prioridad',
        'estatus',
        'asunto',
        'descripcion',
        'resultado',
        'fecha_contacto',
        'fecha_proximo_contacto',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
        'fecha_proximo_contacto' => 'datetime',
        'fecha_cierre' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function tipos(): array
    {
        return [
            self::TIPO_LLAMADA,
            self::TIPO_WHATSAPP,
            self::TIPO_CORREO,
            self::TIPO_VISITA,
            self::TIPO_DOCUMENTO,
            self::TIPO_ACUERDO_PAGO,
            self::TIPO_ACADEMICO,
            self::TIPO_GENERAL,
        ];
    }

    public static function prioridades(): array
    {
        return [
            self::PRIORIDAD_BAJA,
            self::PRIORIDAD_NORMAL,
            self::PRIORIDAD_ALTA,
            self::PRIORIDAD_URGENTE,
        ];
    }

    public static function estatusDisponibles(): array
    {
        return [
            self::ESTATUS_ABIERTO,
            self::ESTATUS_EN_PROCESO,
            self::ESTATUS_CERRADO,
            self::ESTATUS_CANCELADO,
        ];
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopeAbiertos($query)
    {
        return $query->whereIn('estatus', [self::ESTATUS_ABIERTO, self::ESTATUS_EN_PROCESO]);
    }

    public function scopeVencidos($query)
    {
        return $query->abiertos()
            ->whereNotNull('fecha_proximo_contacto')
            ->where('fecha_proximo_contacto', '<', now());
    }

    public function scopeProximos($query)
    {
        return $query->abiertos()
            ->whereNotNull('fecha_proximo_contacto')
            ->whereBetween('fecha_proximo_contacto', [now(), now()->addDays(7)]);
    }
}
