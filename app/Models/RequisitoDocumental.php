<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoDocumental extends Model
{
    use HasFactory;

    protected $table = 'requisitos_documentales';

    protected $fillable = [
        'programa_id',
        'nivel',
        'tipo_documento',
        'descripcion',
        'obligatorio',
        'activo',
        'orden',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function tiposDisponibles(): array
    {
        return DocumentoAlumno::tiposDisponibles();
    }

    public static function nivelesDisponibles(): array
    {
        return [
            'Licenciatura',
            'Maestría',
            'Doctorado',
            'Diplomado',
            'Curso',
            'General',
        ];
    }


    public static function siguienteOrden(?int $programaId = null, ?string $nivel = null): int
    {
        $max = self::query()
            ->when($programaId, fn ($q) => $q->where('programa_id', $programaId), fn ($q) => $q->whereNull('programa_id'))
            ->when($programaId === null && $nivel, fn ($q) => $q->where('nivel', $nivel))
            ->when($programaId === null && ! $nivel, fn ($q) => $q->whereNull('nivel'))
            ->max('orden');

        return ((int) floor(((int) $max) / 10) + 1) * 10;
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoAlumno::class, 'requisito_documental_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeParaAlumno($query, Alumno $alumno)
    {
        $programa = $alumno->grupo?->programa;
        $programaId = $programa?->id;
        $nivel = $programa?->nivel;

        return $query->where('activo', true)
            ->where(function ($q) use ($programaId, $nivel) {
                $q->whereNull('programa_id')->whereNull('nivel');

                if ($programaId) {
                    $q->orWhere('programa_id', $programaId);
                }

                if ($nivel) {
                    $q->orWhere(function ($sub) use ($nivel) {
                        $sub->whereNull('programa_id')->where('nivel', $nivel);
                    });
                }
            });
    }

    public function getAlcanceAttribute(): string
    {
        if ($this->programa) {
            return 'Programa: '.$this->programa->nombre;
        }

        if ($this->nivel) {
            return 'Nivel: '.$this->nivel;
        }

        return 'General';
    }
}
