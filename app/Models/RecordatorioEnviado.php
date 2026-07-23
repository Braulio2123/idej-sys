<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordatorioEnviado extends Model
{
    protected $table = 'recordatorios_enviados';

    protected $fillable = [
        'alumno_id',
        'tipo',
        'canal',
        'fecha_recordatorio',
        'destinatario',
        'referencia_hash',
        'estatus',
        'respuesta',
        'enviado_at',
    ];

    protected $casts = [
        'fecha_recordatorio' => 'date',
        'enviado_at' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public static function hash(string $tipo, string $canal, ?int $alumnoId, string $fecha, ?string $extra = null): string
    {
        return sha1(implode('|', [$tipo, $canal, $alumnoId ?: 'sin-alumno', $fecha, $extra ?: 'sin-extra']));
    }
}
