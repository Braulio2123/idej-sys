<?php

namespace App\Models\PortalAlumno;

use App\Models\Grupo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Avisos exclusivos del Portal Alumno PWA.
 *
 * Esta tabla no invade el modulo administrativo existente de alumnos.
 * Puede crecer despues con un CRUD administrativo propio, si el equipo lo decide.
 */
class AvisoPortal extends Model
{
    protected $table = 'portal_alumno_avisos';

    public const DESTINO_TODOS = 'todos';
    public const DESTINO_GRUPO = 'grupo';

    public const PRIORIDAD_NORMAL = 'normal';
    public const PRIORIDAD_IMPORTANTE = 'importante';
    public const PRIORIDAD_URGENTE = 'urgente';

    protected $fillable = [
        'titulo',
        'contenido',
        'categoria',
        'prioridad',
        'destino_tipo',
        'grupo_id',
        'visible_desde',
        'visible_hasta',
        'activo',
        'publicado_por_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'visible_desde' => 'datetime',
        'visible_hasta' => 'datetime',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function publicadoPor()
    {
        return $this->belongsTo(Usuario::class, 'publicado_por_id');
    }

    public function scopeVisiblesParaAlumno(Builder $query, ?AlumnoPortal $alumno): Builder
    {
        $ahora = now();

        return $query
            ->where('activo', true)
            ->where(function (Builder $query) use ($ahora) {
                $query->whereNull('visible_desde')
                    ->orWhere('visible_desde', '<=', $ahora);
            })
            ->where(function (Builder $query) use ($ahora) {
                $query->whereNull('visible_hasta')
                    ->orWhere('visible_hasta', '>=', $ahora);
            })
            ->where(function (Builder $query) use ($alumno) {
                $query->where('destino_tipo', self::DESTINO_TODOS);

                if ($alumno?->grupo_id) {
                    $query->orWhere(function (Builder $query) use ($alumno) {
                        $query->where('destino_tipo', self::DESTINO_GRUPO)
                            ->where('grupo_id', $alumno->grupo_id);
                    });
                }
            });
    }

    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(prioridad, 'urgente', 'importante', 'normal')")
            ->orderByDesc('visible_desde')
            ->orderByDesc('created_at');
    }
}
