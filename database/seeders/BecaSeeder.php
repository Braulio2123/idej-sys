<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class BecaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::ADMIN))->first();

        Alumno::where('beca_porcentaje', '>', 0)
            ->orderBy('id')
            ->each(function (Alumno $alumno) use ($admin) {
                Beca::firstOrCreate(
                    [
                        'alumno_id' => $alumno->id,
                        'estatus' => Beca::ESTATUS_ACTIVA,
                    ],
                    [
                        'tipo' => 'Institucional',
                        'porcentaje' => $alumno->beca_porcentaje,
                        'motivo' => 'Beca inicial cargada desde datos semilla.',
                        'observaciones' => 'Registro generado automáticamente para mantener compatibilidad con alumnos sembrados previamente.',
                        'fecha_inicio' => now()->startOfMonth()->toDateString(),
                        'fecha_fin' => null,
                        'autorizado_por_id' => $admin?->id,
                        'registrado_por_id' => $admin?->id,
                    ]
                );
            });
    }
}
