<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    use HasFactory;

    protected $table = 'prospectos';

    public const ESTATUS_NUEVO = 'Nuevo';
    public const ESTATUS_CONTACTADO = 'Contactado';
    public const ESTATUS_INTERESADO = 'Interesado';
    public const ESTATUS_EN_SEGUIMIENTO = 'En seguimiento';
    public const ESTATUS_INSCRITO = 'Inscrito';
    public const ESTATUS_DESCARTADO = 'Descartado';

    public const PRIORIDAD_BAJA = 'Baja';
    public const PRIORIDAD_NORMAL = 'Normal';
    public const PRIORIDAD_ALTA = 'Alta';
    public const PRIORIDAD_URGENTE = 'Urgente';

    protected $fillable = [
        'nombre_completo',
        'correo',
        'telefono',
        'whatsapp',
        'programa_id',
        'nivel_interes',
        'medio_contacto',
        'origen',
        'asesor_id',
        'estatus',
        'prioridad',
        'fecha_contacto',
        'fecha_proximo_contacto',
        'observaciones',
        'motivo_descarte',
        'alumno_id',
        'fecha_conversion',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
        'fecha_proximo_contacto' => 'datetime',
        'fecha_conversion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function estatusDisponibles(): array
    {
        return [
            self::ESTATUS_NUEVO,
            self::ESTATUS_CONTACTADO,
            self::ESTATUS_INTERESADO,
            self::ESTATUS_EN_SEGUIMIENTO,
            self::ESTATUS_INSCRITO,
            self::ESTATUS_DESCARTADO,
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

    public static function mediosContacto(): array
    {
        return [
            'Facebook',
            'Instagram',
            'WhatsApp',
            'Llamada',
            'Página web',
            'Referido',
            'Visita presencial',
            'Campaña',
            'Otro',
        ];
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'asesor_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'prospecto_id');
    }

    public function cursosEducacionContinua()
    {
        return $this->hasMany(CursoInscrito::class, 'prospecto_id');
    }

    public function scopeActivos($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_INSCRITO, self::ESTATUS_DESCARTADO]);
    }

    public function scopeVencidos($query)
    {
        return $query->activos()
            ->whereNotNull('fecha_proximo_contacto')
            ->where('fecha_proximo_contacto', '<', now());
    }

    public function scopeProximos($query)
    {
        return $query->activos()
            ->whereNotNull('fecha_proximo_contacto')
            ->whereBetween('fecha_proximo_contacto', [now(), now()->addDays(7)]);
    }

    public function estaConvertido(): bool
    {
        return $this->estatus === self::ESTATUS_INSCRITO && ! is_null($this->alumno_id);
    }
}
