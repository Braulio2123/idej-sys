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

    protected $fillable = [
        'nombre_completo',
        'email',
        'telefono',
        'domicilio',
        'area_especialidad',
        'creado_por_id',
        'rfc',
        'numero_cuenta',
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
            set: fn ($value) => $value ? encrypt($value) : null,
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


    /**
     * ================================
     *  CÁLCULO AUTOMÁTICO DE ESTATUS
     * ================================
     */
    public function calcularEstatus()
    {
        $campos = [
            'nombre_completo',
            'email',
            'telefono',
            'domicilio',
            'area_especialidad',
            'rfc',
            'numero_cuenta',
        ];

        foreach ($campos as $campo) {
            if (empty($this->$campo)) {
                return 'Pendiente de Datos';
            }
        }

        return 'Activo';
    }
}
