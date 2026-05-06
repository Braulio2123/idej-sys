<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    public const ADMIN = 'Admin';
    public const SISTEMAS = 'Sistemas';
    public const DIRECCION = 'Direccion';
    public const CADMIN = 'CAdmin';
    public const ACADEMICA = 'Academica';
    public const RECEPCION = 'Recepcion';
    public const RRPP = 'RRPP';
    public const FINANZAS = 'Finanzas';

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'clave',
        'descripcion',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}
