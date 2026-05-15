<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificacionInterna extends Model
{
    use HasFactory;

    public const SEVERIDAD_BAJA = 'baja';
    public const SEVERIDAD_MEDIA = 'media';
    public const SEVERIDAD_ALTA = 'alta';
    public const SEVERIDAD_CRITICA = 'critica';

    protected $table = 'notificaciones_internas';

    protected $fillable = [
        'usuario_id',
        'rol_clave',
        'tipo',
        'modulo',
        'titulo',
        'mensaje',
        'url',
        'severidad',
        'referencia_tipo',
        'referencia_id',
        'hash',
        'metadata',
        'leida_at',
        'archivada_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'leida_at' => 'datetime',
        'archivada_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopeVisiblesPara(Builder $query, Usuario $usuario): Builder
    {
        $rol = $usuario->rolClave();

        return $query->where(function (Builder $q) use ($usuario, $rol) {
            $q->where('usuario_id', $usuario->id);

            if ($rol) {
                $q->orWhere('rol_clave', $rol);
            }

            $q->orWhere(function (Builder $global) {
                $global->whereNull('usuario_id')->whereNull('rol_clave');
            });
        });
    }

    public function scopeNoArchivadas(Builder $query): Builder
    {
        return $query->whereNull('archivada_at');
    }

    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->whereNull('leida_at');
    }

    public function puedeVer(Usuario $usuario): bool
    {
        if ($this->usuario_id !== null) {
            return (int) $this->usuario_id === (int) $usuario->id;
        }

        if ($this->rol_clave !== null) {
            return $this->rol_clave === $usuario->rolClave();
        }

        return true;
    }

    public function marcarComoLeida(): void
    {
        if ($this->leida_at === null) {
            $this->forceFill(['leida_at' => now()])->save();
        }
    }

    public function marcarComoNoLeida(): void
    {
        $this->forceFill(['leida_at' => null])->save();
    }

    public function archivar(): void
    {
        $this->forceFill([
            'archivada_at' => now(),
            'leida_at' => $this->leida_at ?? now(),
        ])->save();
    }

    public static function sincronizar(array $datos): self
    {
        $hash = $datos['hash'] ?? self::generarHash($datos);

        $notificacion = self::firstOrNew(['hash' => $hash]);
        $notificacion->fill(array_merge($datos, ['hash' => $hash]));
        $notificacion->save();

        return $notificacion;
    }

    public static function generarHash(array $datos): string
    {
        $base = implode('|', [
            $datos['usuario_id'] ?? 'global',
            $datos['rol_clave'] ?? 'sin-rol',
            $datos['tipo'] ?? 'general',
            $datos['referencia_tipo'] ?? 'sin-ref',
            $datos['referencia_id'] ?? '0',
            Str::slug($datos['titulo'] ?? 'notificacion'),
        ]);

        return sha1($base);
    }

    public function etiquetaSeveridad(): string
    {
        return match ($this->severidad) {
            self::SEVERIDAD_CRITICA => 'Crítica',
            self::SEVERIDAD_ALTA => 'Alta',
            self::SEVERIDAD_BAJA => 'Baja',
            default => 'Media',
        };
    }

    public function clasesSeveridad(): string
    {
        return match ($this->severidad) {
            self::SEVERIDAD_CRITICA => 'bg-red-100 text-red-800 border-red-200',
            self::SEVERIDAD_ALTA => 'bg-orange-100 text-orange-800 border-orange-200',
            self::SEVERIDAD_BAJA => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-amber-100 text-amber-800 border-amber-200',
        };
    }
}
