<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $with = ['rol'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'usuario_id');
    }

    public function prospectosAsignados()
    {
        return $this->hasMany(Prospecto::class, 'asesor_id');
    }

    public function cortesCaja()
    {
        return $this->hasMany(CorteCaja::class, 'usuario_id');
    }

    public function ajustesCaja()
    {
        return $this->hasMany(AjusteCaja::class, 'usuario_id');
    }

    public function cajaAbierta()
    {
        return $this->hasOne(CorteCaja::class, 'usuario_id')
            ->where('estatus', CorteCaja::ESTATUS_ABIERTA)
            ->latestOfMany('fecha_apertura');
    }

    public function documentosSubidos()
    {
        return $this->hasMany(DocumentoAlumno::class, 'usuario_subio_id');
    }

    public function documentosRevisados()
    {
        return $this->hasMany(DocumentoAlumno::class, 'usuario_reviso_id');
    }

    public function becasAutorizadas()
    {
        return $this->hasMany(Beca::class, 'autorizado_por_id');
    }

    public function becasRegistradas()
    {
        return $this->hasMany(Beca::class, 'registrado_por_id');
    }

    public function becasCanceladas()
    {
        return $this->hasMany(Beca::class, 'cancelado_por_id');
    }

    public function cursosResponsableEducacionContinua()
    {
        return $this->hasMany(CursoEducacionContinua::class, 'responsable_id');
    }

    public function cursosCreadosEducacionContinua()
    {
        return $this->hasMany(CursoEducacionContinua::class, 'creado_por_id');
    }

    /**
     * Alias compatible con componentes de Breeze que esperan $user->name.
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['nombre'] ?? null;
    }

    public function rolClave(): ?string
    {
        return $this->rol?->clave;
    }

    public function tieneRol(string ...$claves): bool
    {
        $rol = $this->rolClave();

        if ($rol === Rol::ADMIN) {
            return true;
        }

        return in_array($rol, $claves, true);
    }

    public function esAdmin(): bool
    {
        return $this->rolClave() === Rol::ADMIN;
    }

    public function esSistemas(): bool
    {
        return $this->rolClave() === Rol::SISTEMAS;
    }
}
