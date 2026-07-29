<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
        ];

        if ($this->puedeModificarCorreo()) {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Usuario::class, 'email')->ignore($this->user()->id),
            ];
        }

        return $rules;
    }

    public function puedeModificarCorreo(): bool
    {
        $usuario = $this->user();

        return $usuario
            && method_exists($usuario, 'esAdmin')
            && $usuario->esAdmin();
    }
}
