<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParcialidadConvenio extends Model
{
    use HasFactory;

    protected $table = 'parcialidades_convenio';

    protected $fillable = [
        'convenio_id',
        'monto_parcialidad',
        'monto_adeudo', // ✅ agregar este campo
        'fecha_vencimiento',
        'estatus'
    ];

    protected $casts = [
        'monto_parcialidad' => 'decimal:2',
        'monto_adeudo'      => 'decimal:2', // ✅ asegurarse que lo tenga
        'fecha_vencimiento' => 'date',
        'estatus'           => 'string',
    ];

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }


    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'pago_parcialidad', 'parcialidad_id', 'pago_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }
}
