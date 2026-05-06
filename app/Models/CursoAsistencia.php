<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoAsistencia extends Model
{
    use HasFactory;

    protected $table = 'curso_asistencias';

    public const ESTATUS_ASISTIO = 'Asistió';
    public const ESTATUS_FALTA = 'Falta';
    public const ESTATUS_RETARDO = 'Retardo';
    public const ESTATUS_JUSTIFICADO = 'Justificado';

    protected $fillable = [
        'curso_sesion_id',
        'curso_inscrito_id',
        'estatus',
        'horas_acreditadas',
        'registrado_por_id',
        'observaciones',
    ];

    protected $casts = [
        'horas_acreditadas' => 'decimal:2',
    ];

    public static function estatuses(): array
    {
        return [self::ESTATUS_ASISTIO, self::ESTATUS_FALTA, self::ESTATUS_RETARDO, self::ESTATUS_JUSTIFICADO];
    }

    public function sesion()
    {
        return $this->belongsTo(CursoSesion::class, 'curso_sesion_id');
    }

    public function inscrito()
    {
        return $this->belongsTo(CursoInscrito::class, 'curso_inscrito_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_id');
    }
}
