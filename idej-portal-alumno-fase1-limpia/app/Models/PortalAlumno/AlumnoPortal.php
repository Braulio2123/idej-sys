<?php

namespace App\Models\PortalAlumno;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo exclusivo para el Portal Alumno PWA.
 *
 * IMPORTANTE:
 * - Usa la misma tabla fisica `alumnos` del sistema administrativo.
 * - No reemplaza ni modifica el modelo administrativo App\Models\Alumno.
 * - Solo concentra la autenticacion y consultas propias del portal del alumno.
 */
class AlumnoPortal extends Authenticatable
{
    use Notifiable;

    protected $table = 'alumnos';

    protected $fillable = [
        'matricula',
        'nombre_completo',
        'apellido_paterno',
        'apellido_materno',
        'correo',
        'telefono',
        'grupo_id',
        'ciclo_escolar_id',
        'estatus_academico',
        'estatus_financiero',
        'condicion_alumno',
        'portal_password',
        'portal_activo',
        'portal_ultimo_acceso_at',
        'portal_remember_token',
    ];

    protected $hidden = [
        'portal_password',
        'portal_remember_token',
    ];

    protected $casts = [
        'portal_activo' => 'boolean',
        'portal_ultimo_acceso_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Laravel debe validar la contrasena contra `portal_password`, no contra `password`.
     */
    public function getAuthPasswordName(): string
    {
        return 'portal_password';
    }

    public function getAuthPassword(): ?string
    {
        return $this->portal_password;
    }

    /**
     * Token separado para el guard del portal alumno.
     */
    public function getRememberTokenName(): string
    {
        return 'portal_remember_token';
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function cicloEscolar()
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id');
    }

    public function getNombreCortoAttribute(): string
    {
        $partes = preg_split('/\s+/', trim((string) $this->nombre_completo));

        return $partes[0] ?? 'Alumno';
    }
}
