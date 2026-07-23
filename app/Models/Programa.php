<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    //
    protected $fillable = ['clave', 'nombre', 'nivel', 'modalidad', 'duracion_periodos', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_periodos' => 'integer',
    ];


    public function requisitosDocumentales()
    {
        return $this->hasMany(RequisitoDocumental::class, 'programa_id');
    }

    public function prospectos()
    {
        return $this->hasMany(Prospecto::class, 'programa_id');
    }

    public function materias()
    {
        return $this->hasMany(Materia::class, 'programa_id');
    }
}
