<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bitacora extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bitacoras';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'accion',
        'modulo',
        'descripcion',
        'alumno_id',
        'modelo_type',
        'modelo_id',
        'ip_address',
        'user_agent',
        'url',
        'metodo_http',
        'fecha_evento',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'fecha_evento' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
