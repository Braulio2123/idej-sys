<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Usuario;
use App\Models\Docente;

class SolicitudPagoDocente extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_pago_docentes';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'docente_id',
        'creado_por_id',
        'procesado_por_id',
        'nivel',
        'monto',
        'fecha_solicitud',
        'fecha_pago',
        'observaciones',
        'estatus'
    ];

    /**
     * Cast automáticos
     */
    protected $casts = [
        'monto'            => 'decimal:2',
        'fecha_solicitud'  => 'date',
        'fecha_pago'       => 'date',
        'estatus'          => 'string',
    ];

    /**
     * Relaciones
     */

    // Docente asignado a la solicitud
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    // Usuario que creó la solicitud
    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    // Usuario que procesó/pagó la solicitud
    public function procesadoPor()
    {
        return $this->belongsTo(Usuario::class, 'procesado_por_id');
    }
}
