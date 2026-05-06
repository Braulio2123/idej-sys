<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'matricula',
        'nombre_completo',
        'apellido_paterno',
        'apellido_materno',
        'correo',
        'telefono',
        'estatus_financiero',
        'estatus_academico',
        'beca_porcentaje',
        'condicion_alumno',
        'grupo_id',
        'ciclo_escolar_id',
        'saldo_a_favor',
    ];

    protected $casts = [
        'estatus_financiero' => 'string',
        'estatus_academico'  => 'string',
        'beca_porcentaje'    => 'integer',
        'saldo_a_favor'       => 'decimal:2',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    // ==========================
    // RELACIONES
    // ==========================

    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'alumno_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'alumno_id');
    }

    public function convenios()
    {
        return $this->hasMany(Convenio::class, 'alumno_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'alumno_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'alumno_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoAlumno::class, 'alumno_id');
    }

    public function becas()
    {
        return $this->hasMany(Beca::class, 'alumno_id');
    }

    public function ajustesCaja()
    {
        return $this->hasMany(AjusteCaja::class, 'alumno_id');
    }

    public function cursosEducacionContinua()
    {
        return $this->hasMany(CursoInscrito::class, 'alumno_id');
    }


    public function becaActiva()
    {
        return $this->hasOne(Beca::class, 'alumno_id')
            ->where('estatus', Beca::ESTATUS_ACTIVA)
            ->whereDate('fecha_inicio', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', now()->toDateString());
            })
            ->latestOfMany('fecha_inicio');
    }

    public function becaVigente(): ?Beca
    {
        return $this->becas()
            ->vigentes()
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    public function prospectoOrigen()
    {
        return $this->hasOne(Prospecto::class, 'alumno_id');
    }

    public function requisitosDocumentalesEsperados()
    {
        return RequisitoDocumental::paraAlumno($this)
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->get();
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Obtiene programa desde el grupo
    public function getProgramaAttribute()
    {
        return $this->grupo->programa->nombre ?? null;
    }
}
