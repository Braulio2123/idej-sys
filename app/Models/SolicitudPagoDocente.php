<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\CalendarioMateria;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;

class SolicitudPagoDocente extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_pago_docentes';

    public const ESTATUS_PENDIENTE = 'Pendiente';
    public const ESTATUS_OBSERVADA = 'Observada';
    public const ESTATUS_AUTORIZADA = 'Autorizada';
    public const ESTATUS_PAGADA = 'Pagada';
    public const ESTATUS_CANCELADA = 'Cancelada';

    public const ORIGEN_CALENDARIO = 'Calendario académico';
    public const ORIGEN_EDUCACION_CONTINUA = 'Educación continua';
    public const ORIGEN_MANUAL = 'Manual';

    public const CONCEPTO_HONORARIOS = 'Honorarios por clase';
    public const CONCEPTO_REPOSICION = 'Reposición de clase';
    public const CONCEPTO_CONFERENCIA = 'Conferencia';
    public const CONCEPTO_MASTERCLASS = 'MasterClass';
    public const CONCEPTO_ASESORIA = 'Asesoría académica';
    public const CONCEPTO_OTRO = 'Otro';

    protected $fillable = [
        'folio',
        'docente_id',
        'creado_por_id',
        'procesado_por_id',
        'autorizado_por_id',
        'cancelado_por_id',
        'calendario_materia_id',
        'curso_id',
        'curso_sesion_id',
        'origen',
        'concepto_pago',
        'nivel',
        'programa_grupo',
        'materia_actividad',
        'periodo',
        'modalidad',
        'numero_sesiones',
        'horas_totales',
        'tarifa_hora',
        'monto',
        'fecha_solicitud',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'fecha_limite_pago',
        'fecha_autorizacion',
        'fecha_pago',
        'fecha_cancelacion',
        'prioridad',
        'metodo_pago',
        'referencia_pago',
        'banco_pago',
        'comprobante_pago_path',
        'comprobante_pago_original',
        'pago_operacion_uuid',
        'observaciones_academica',
        'observaciones_administracion',
        'motivo_observacion',
        'motivo_cancelacion',
        'observaciones',
        'estatus',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'tarifa_hora' => 'decimal:2',
        'horas_totales' => 'decimal:2',
        'numero_sesiones' => 'integer',
        'fecha_solicitud' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'fecha_limite_pago' => 'date',
        'fecha_autorizacion' => 'datetime',
        'fecha_pago' => 'date',
        'fecha_cancelacion' => 'datetime',
        'estatus' => 'string',
    ];

    public static function estatuses(): array
    {
        return [
            self::ESTATUS_PENDIENTE,
            self::ESTATUS_OBSERVADA,
            self::ESTATUS_AUTORIZADA,
            self::ESTATUS_PAGADA,
            self::ESTATUS_CANCELADA,
        ];
    }

    public static function origenes(): array
    {
        return [self::ORIGEN_CALENDARIO, self::ORIGEN_EDUCACION_CONTINUA, self::ORIGEN_MANUAL];
    }

    public static function conceptos(): array
    {
        return [
            self::CONCEPTO_HONORARIOS,
            self::CONCEPTO_REPOSICION,
            self::CONCEPTO_CONFERENCIA,
            self::CONCEPTO_MASTERCLASS,
            self::CONCEPTO_ASESORIA,
            self::CONCEPTO_OTRO,
        ];
    }

    public static function prioridades(): array
    {
        return ['Normal', 'Alta', 'Urgente'];
    }

    public static function metodosPago(): array
    {
        return ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta', 'Otro'];
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function procesadoPor()
    {
        return $this->belongsTo(Usuario::class, 'procesado_por_id');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(Usuario::class, 'cancelado_por_id');
    }

    public function calendarioMateria()
    {
        return $this->belongsTo(CalendarioMateria::class, 'calendario_materia_id');
    }

    public function curso()
    {
        return $this->belongsTo(CursoEducacionContinua::class, 'curso_id');
    }

    public function cursoSesion()
    {
        return $this->belongsTo(CursoSesion::class, 'curso_sesion_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', self::ESTATUS_PENDIENTE);
    }

    public function scopeAutorizadas($query)
    {
        return $query->where('estatus', self::ESTATUS_AUTORIZADA);
    }

    public function scopePagadas($query)
    {
        return $query->where('estatus', self::ESTATUS_PAGADA);
    }

    public function scopeOperativas($query)
    {
        return $query->whereNotIn('estatus', [self::ESTATUS_CANCELADA]);
    }

    public function estaCerrada(): bool
    {
        return in_array($this->estatus, [self::ESTATUS_PAGADA, self::ESTATUS_CANCELADA], true);
    }

    public function puedeEditarAcademica(): bool
    {
        return in_array($this->estatus, [self::ESTATUS_PENDIENTE, self::ESTATUS_OBSERVADA], true);
    }

    public function getResumenServicioAttribute(): string
    {
        return $this->materia_actividad ?: ($this->concepto_pago ?: 'Servicio docente');
    }
}
