<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Usuario;
use App\Models\SolicitudPagoDocente;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docentes';

    public const ESTATUS_PENDIENTE = 'Pendiente de Datos';
    public const ESTATUS_ACTIVO = 'Activo';
    public const ESTATUS_INACTIVO = 'Inactivo';

    protected $fillable = [
        'nombre_completo',
        'email',
        'telefono',
        'domicilio',
        'area_especialidad',
        'creado_por_id',
        'rfc',
        'numero_cuenta',
        'banco',
        'curriculum_path',
        'curriculum_original',
        'curriculum_sha256',
        'titulo_cedula_path',
        'titulo_cedula_original',
        'titulo_cedula_sha256',
        'constancia_fiscal_path',
        'constancia_fiscal_original',
        'constancia_fiscal_sha256',
        'estatus'
    ];

    protected $casts = [
        'estatus' => 'string',
    ];

    /**
     * ================================
     *  ENCRIPTACIÓN DE DATOS SENSIBLES
     * ================================
     */
    protected function rfc(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt(mb_strtoupper(trim($value))) : null,
        );
    }

    protected function numeroCuenta(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }


    /**
     * ================================
     *  RELACIONES
     * ================================
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id');
    }

    public function solicitudesPago()
    {
        return $this->hasMany(SolicitudPagoDocente::class, 'docente_id');
    }

    public function horariosAcademicos()
    {
        return $this->hasMany(HorarioAcademico::class, 'docente_id');
    }

    public function calendarioMaterias()
    {
        return $this->hasMany(CalendarioMateria::class, 'docente_id');
    }

    public function cursoSesiones()
    {
        return $this->hasMany(CursoSesion::class, 'docente_id');
    }

    public function calendarioSesiones()
    {
        return $this->hasManyThrough(
            CalendarioSesion::class,
            CalendarioMateria::class,
            'docente_id',
            'calendario_materia_id',
            'id',
            'id'
        );
    }



    /**
     * ================================
     *  ALIAS PARA MOSTRAR NOMBRE
     * ================================
     */
    public function getNombreAttribute()
    {
        return $this->nombre_completo;
    }

    public static function estatuses(): array
    {
        return [
            self::ESTATUS_PENDIENTE,
            self::ESTATUS_ACTIVO,
            self::ESTATUS_INACTIVO,
        ];
    }


    /**
     * ================================
     *  CÁLCULO AUTOMÁTICO DE ESTATUS
     * ================================
     */
    public function calcularEstatus()
    {
        if ($this->estatus === self::ESTATUS_INACTIVO) {
            return self::ESTATUS_INACTIVO;
        }

        $campos = [
            'nombre_completo',
            'email',
            'telefono',
            'area_especialidad',
            'rfc',
            'numero_cuenta',
            'banco',
        ];

        foreach ($campos as $campo) {
            if (empty($this->$campo)) {
                return self::ESTATUS_PENDIENTE;
            }
        }

        return self::ESTATUS_ACTIVO;
    }
}
