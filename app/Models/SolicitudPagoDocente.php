<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudPagoDocente extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_pago_docentes';

    public const ESTATUS_PENDIENTE = 'Pendiente';
    public const ESTATUS_OBSERVADA = 'Observada';
    public const ESTATUS_AUTORIZADA = 'Autorizada';
    public const ESTATUS_PAGADA = 'Pagada';
    public const ESTATUS_RECHAZADA = 'Rechazada';
    public const ESTATUS_CANCELADA = 'Cancelada';

    public const ORIGEN_CALENDARIO = 'Calendario académico';
    public const ORIGEN_EDUCACION_CONTINUA = 'Educación continua';
    public const ORIGEN_MANUAL = 'Manual';

    public const TIPO_LICENCIATURA = 'Licenciatura';
    public const TIPO_MAESTRIA = 'Maestría';
    public const TIPO_DOCTORADO = 'Doctorado';
    public const TIPO_CURSO = 'Curso';
    public const TIPO_DIPLOMADO = 'Diplomado';

    public const ESQUEMA_SESION = 'Por sesión';
    public const ESQUEMA_HORA = 'Por hora';
    public const ESQUEMA_FIJO = 'Monto fijo';

    public const CONCEPTO_EDUCACION_CONTINUA_HORAS = 'Educación continua - pago por horas';
    public const CONCEPTO_EDUCACION_PROGRAMATICA_NIVEL_MATERIA = 'Educación Programática - pago por nivel y materia';

    protected $fillable = [
        'folio', 'docente_id', 'creado_por_id', 'valorado_por_id', 'procesado_por_id',
        'autorizado_por_id', 'cancelado_por_id', 'rechazado_por_id',
        'calendario_materia_id', 'curso_id', 'curso_sesion_id',
        'calendario_sesion_ids', 'curso_sesion_ids', 'fechas_clase',
        'origen', 'tipo_clase', 'concepto_pago', 'nivel', 'programa_grupo',
        'materia_actividad', 'periodo', 'modalidad', 'numero_sesiones', 'horas_totales',
        'esquema_pago', 'tarifa_unitaria', 'tarifa_hora', 'monto',
        'fecha_solicitud', 'fecha_inicio_periodo', 'fecha_fin_periodo',
        'fecha_limite_pago', 'fecha_tentativa_pago', 'fecha_autorizacion',
        'fecha_valoracion', 'fecha_pago', 'fecha_cancelacion', 'fecha_rechazo',
        'prioridad', 'metodo_pago', 'referencia_pago', 'banco_pago',
        'comprobante_pago_path', 'comprobante_pago_original', 'comprobante_pago_mime',
        'comprobante_pago_tamano', 'comprobante_pago_sha256', 'pago_operacion_uuid',
        'observaciones_academica', 'observaciones_administracion', 'motivo_observacion',
        'motivo_cancelacion', 'motivo_rechazo', 'observaciones', 'estatus',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'tarifa_hora' => 'decimal:2',
        'tarifa_unitaria' => 'decimal:2',
        'horas_totales' => 'decimal:2',
        'numero_sesiones' => 'integer',
        'fecha_solicitud' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'fecha_limite_pago' => 'date',
        'fecha_tentativa_pago' => 'date',
        'calendario_sesion_ids' => 'array',
        'curso_sesion_ids' => 'array',
        'fechas_clase' => 'array',
        'fecha_autorizacion' => 'datetime',
        'fecha_valoracion' => 'datetime',
        'fecha_pago' => 'date',
        'fecha_cancelacion' => 'datetime',
        'fecha_rechazo' => 'datetime',
        'comprobante_pago_tamano' => 'integer',
    ];

    public static function estatuses(): array
    {
        return [self::ESTATUS_PENDIENTE, self::ESTATUS_OBSERVADA, self::ESTATUS_AUTORIZADA,
            self::ESTATUS_PAGADA, self::ESTATUS_RECHAZADA, self::ESTATUS_CANCELADA];
    }

    public static function origenes(): array
    {
        return [self::ORIGEN_CALENDARIO, self::ORIGEN_EDUCACION_CONTINUA, self::ORIGEN_MANUAL];
    }

    public static function tiposClase(): array
    {
        return [self::TIPO_LICENCIATURA, self::TIPO_MAESTRIA, self::TIPO_DOCTORADO,
            self::TIPO_CURSO, self::TIPO_DIPLOMADO];
    }

    public static function esquemasPago(): array
    {
        return [self::ESQUEMA_SESION, self::ESQUEMA_HORA, self::ESQUEMA_FIJO];
    }

    public static function conceptos(): array
    {
        return [self::CONCEPTO_EDUCACION_CONTINUA_HORAS, self::CONCEPTO_EDUCACION_PROGRAMATICA_NIVEL_MATERIA];
    }

    public static function prioridades(): array
    {
        return ['Normal', 'Alta', 'Urgente'];
    }

    public static function metodosPago(): array
    {
        return ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta', 'Otro'];
    }

    public static function conceptoParaTipo(?string $tipoClase): string
    {
        return in_array($tipoClase, [self::TIPO_CURSO, self::TIPO_DIPLOMADO], true)
            ? self::CONCEPTO_EDUCACION_CONTINUA_HORAS
            : self::CONCEPTO_EDUCACION_PROGRAMATICA_NIVEL_MATERIA;
    }

    public function docente() { return $this->belongsTo(Docente::class, 'docente_id'); }
    public function creadoPor() { return $this->belongsTo(Usuario::class, 'creado_por_id'); }
    public function valoradoPor() { return $this->belongsTo(Usuario::class, 'valorado_por_id'); }
    public function procesadoPor() { return $this->belongsTo(Usuario::class, 'procesado_por_id'); }
    public function autorizadoPor() { return $this->belongsTo(Usuario::class, 'autorizado_por_id'); }
    public function canceladoPor() { return $this->belongsTo(Usuario::class, 'cancelado_por_id'); }
    public function rechazadoPor() { return $this->belongsTo(Usuario::class, 'rechazado_por_id'); }
    public function calendarioMateria() { return $this->belongsTo(CalendarioMateria::class, 'calendario_materia_id'); }
    public function curso() { return $this->belongsTo(CursoEducacionContinua::class, 'curso_id'); }
    public function cursoSesion() { return $this->belongsTo(CursoSesion::class, 'curso_sesion_id'); }

    public function scopePendientes($query) { return $query->where('estatus', self::ESTATUS_PENDIENTE); }
    public function scopeAutorizadas($query) { return $query->where('estatus', self::ESTATUS_AUTORIZADA); }
    public function scopePagadas($query) { return $query->where('estatus', self::ESTATUS_PAGADA); }
    public function scopeOperativas($query) { return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADA, self::ESTATUS_RECHAZADA]); }

    public function estaCerrada(): bool
    {
        return in_array($this->estatus, [self::ESTATUS_PAGADA, self::ESTATUS_RECHAZADA, self::ESTATUS_CANCELADA], true);
    }

    public function puedeEditarAcademica(): bool
    {
        return in_array($this->estatus, [self::ESTATUS_PENDIENTE, self::ESTATUS_OBSERVADA], true);
    }

    public function getResumenServicioAttribute(): string
    {
        return $this->materia_actividad ?: ($this->tipo_clase ?: 'Servicio docente');
    }

    public function getFechasClaseOrdenadasAttribute(): array
    {
        $fechas = collect($this->fechas_clase ?? [])
            ->filter()
            ->map(fn ($fecha) => (string) $fecha)
            ->unique()
            ->sort()
            ->values();

        return $fechas->all();
    }
}
