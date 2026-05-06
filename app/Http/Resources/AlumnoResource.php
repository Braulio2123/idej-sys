<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matricula' => $this->matricula,
            'nombre_completo' => $this->nombre_completo,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'grupo_id' => $this->grupo_id,
            'estatus_financiero' => $this->estatus_financiero,
            'estatus_academico' => $this->estatus_academico,
            'condicion_alumno' => $this->condicion_alumno,
            'beca_porcentaje' => $this->beca_porcentaje,
            'saldo_a_favor' => $this->saldo_a_favor,
            'ciclo_escolar_id' => $this->ciclo_escolar_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
