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
        'activo',
        'ultimo_acceso_at',
        'ultimo_login_ip',
        'ultimo_user_agent',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'activo' => 'boolean',
        'ultimo_acceso_at' => 'datetime',
        'password_changed_at' => 'datetime',
    ];

    protected $with = ['rol'];


    public function estaActivo(): bool
    {
        // Compatibilidad durante despliegue: si la migración aún no ha corrido, no bloquear el login.
        return ! array_key_exists('activo', $this->attributes) || (bool) $this->activo;
    }

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

    public function tienePermiso(string $permiso): bool
    {
        $rol = $this->rolClave();

        if (! $rol) {
            return false;
        }

        if ($rol === Rol::ADMIN) {
            return true;
        }

        $configuracion = config("idej_permisos.permisos.{$permiso}");

        if (! is_array($configuracion)) {
            return false;
        }

        $roles = $configuracion['roles'] ?? [];

        return in_array($rol, $roles, true);
    }

    public function esRolCritico(): bool
    {
        $rol = $this->rolClave();

        return $rol !== null && in_array($rol, config('idej_permisos.roles_criticos', []), true);
    }


    public function notificacionesInternas()
    {
        return $this->hasMany(NotificacionInterna::class, 'usuario_id');
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
