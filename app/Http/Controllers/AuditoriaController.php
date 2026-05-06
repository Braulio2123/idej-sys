<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = [
        'usuario_id',
        'accion',
        'modelo',
        'registro_id',
        'descripcion',
        'ip',
        'user_agent',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
