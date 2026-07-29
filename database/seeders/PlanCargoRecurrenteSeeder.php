<?php

namespace Database\Seeders;

use App\Models\ConceptoPago;
use App\Models\Grupo;
use App\Models\PlanCargoRecurrente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PlanCargoRecurrenteSeeder extends Seeder
{
    public function run(): void
    {
        $concepto = ConceptoPago::where('nombre', 'Colegiatura Mensual')->first();
        $grupo = Grupo::orderBy('id')->first();
        $admin = Usuario::whereHas('rol', fn ($query) => $query->where('clave', Rol::ADMIN))->first();

        if (! $concepto || ! $grupo) {
            return;
        }

        PlanCargoRecurrente::firstOrCreate(
            ['nombre' => 'Colegiatura mensual demo'],
            [
                'concepto_id' => $concepto->id,
                'alcance' => PlanCargoRecurrente::ALCANCE_GRUPO,
                'grupo_id' => $grupo->id,
                'programa_id' => null,
                'monto' => null,
                'dia_vencimiento' => 10,
                'frecuencia_meses' => 1,
                'fecha_inicio' => now()->startOfMonth()->toDateString(),
                'fecha_fin' => null,
                'descripcion' => 'Colegiatura mensual generada automáticamente para pruebas locales.',
                'activo' => true,
                'enviar_recordatorio_email' => true,
                'creado_por_id' => $admin?->id,
                'actualizado_por_id' => $admin?->id,
            ]
        );
    }
}
