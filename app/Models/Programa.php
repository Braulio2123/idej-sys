<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    //
    protected $fillable = ['nombre', 'nivel'];


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
