<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    // 🔥 Nombre real y fijo de la tabla
    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'ciclo_escolar_id',
        'programa_id',
        'semestre_o_cuatrimestre',
        'turno',
        'aula',
        'cupo_maximo',
        'activo',
        'archivado_at',
        'archivado_por_id',
        'motivo_archivo',
    ];


    protected $casts = [
        'activo' => 'boolean',
        'archivado_at' => 'datetime',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function archivadoPor()
    {
        return $this->belongsTo(Usuario::class, 'archivado_por_id');
    }

    public function cicloEscolar()
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }


    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'grupo_id');
    }

    public function horariosAcademicos()
    {
        return $this->hasMany(HorarioAcademico::class, 'grupo_id');
    }

    public function calendariosAcademicos()
    {
        return $this->hasMany(CalendarioAcademico::class, 'grupo_id');
    }

    public function calendarioMaterias()
    {
        return $this->hasManyThrough(
            CalendarioMateria::class,
            CalendarioAcademico::class,
            'grupo_id',
            'calendario_academico_id',
            'id',
            'id'
        );
    }
}
