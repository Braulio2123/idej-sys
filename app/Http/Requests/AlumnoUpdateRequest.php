<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlumnoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'matricula'         => "required|string|max:50|unique:alumnos,matricula,$id",
            'nombre_completo'   => 'required|string|max:255',
            'apellido_paterno'  => 'required|string|max:255',
            'apellido_materno'  => 'nullable|string|max:255',
            'correo'            => "required|email|unique:alumnos,correo,$id",
            'telefono'          => 'nullable|string|max:20',
            'grupo_id'          => 'nullable|integer',
            'estatus_financiero'=> 'nullable|string|max:100',
            'estatus_academico' => 'nullable|string|max:100',
            'condicion_alumno'  => 'nullable|string|max:100',
            'beca_porcentaje'   => 'nullable|numeric|min:0|max:100',
            'saldo_a_favor'     => 'nullable|numeric|min:0',
            'ciclo_escolar_id'  => 'nullable|integer',
        ];
    }
}
