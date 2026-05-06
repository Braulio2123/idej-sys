<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    use HasFactory;

    protected $table = 'convenios';

    protected $fillable = [
        'alumno_id',
        'cargo_original_id',
        'descripcion',
        'total_reestructurado',
        'numero_parcialidades',
        'estatus',
    ];

    protected $casts = [
        'total_reestructurado' => 'decimal:2',
        'numero_parcialidades' => 'integer',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    /**
     * Relación de compatibilidad con el primer cargo usado para crear el convenio.
     * La relación formal de todos los cargos está en cargos().
     */
    public function cargoOriginal()
    {
        return $this->belongsTo(Cargo::class, 'cargo_original_id');
    }

    public function cargos()
    {
        return $this->belongsToMany(Cargo::class, 'cargo_convenio')
            ->withPivot('monto_original', 'monto_adeudo_original', 'estatus_original')
            ->withTimestamps();
    }

    public function parcialidades()
    {
        return $this->hasMany(ParcialidadConvenio::class, 'convenio_id');
    }

    public function estaPagado(): bool
    {
        return ! $this->parcialidades()
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->exists();
    }
}
