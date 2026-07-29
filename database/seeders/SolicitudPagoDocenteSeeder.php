<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Rol;
use App\Models\SolicitudPagoDocente;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SolicitudPagoDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = Docente::pluck('id')->all();
        $academicos = Usuario::whereHas('rol', fn ($q) => $q->where('clave', Rol::ACADEMICA))->pluck('id')->all();
        $administrativos = Usuario::whereHas('rol', fn ($q) => $q->whereIn('clave', [Rol::CADMIN, Rol::ADMIN]))->pluck('id')->all();

        if ($docentes === [] || $academicos === [] || $administrativos === []) {
            echo "⚠️ Faltan docentes o usuarios de Académica/CAdmin para generar solicitudes.\n";
            return;
        }

        $estatuses = [
            SolicitudPagoDocente::ESTATUS_PENDIENTE,
            SolicitudPagoDocente::ESTATUS_OBSERVADA,
            SolicitudPagoDocente::ESTATUS_AUTORIZADA,
            SolicitudPagoDocente::ESTATUS_PAGADA,
            SolicitudPagoDocente::ESTATUS_RECHAZADA,
        ];

        for ($i = 1; $i <= 20; $i++) {
            $estatus = fake()->randomElement($estatuses);
            $tipo = fake()->randomElement(SolicitudPagoDocente::tiposClase());
            $inicio = fake()->dateTimeBetween('-2 months', '-10 days');
            $fechas = collect(range(0, fake()->numberBetween(0, 4)))
                ->map(fn ($offset) => (clone $inicio)->modify('+'.($offset * 7).' days')->format('Y-m-d'))
                ->values()->all();
            $valorada = in_array($estatus, [SolicitudPagoDocente::ESTATUS_AUTORIZADA, SolicitudPagoDocente::ESTATUS_PAGADA], true);
            $rechazada = $estatus === SolicitudPagoDocente::ESTATUS_RECHAZADA;
            $adminId = fake()->randomElement($administrativos);
            $monto = $valorada ? fake()->randomFloat(2, 800, 9000) : 0;

            SolicitudPagoDocente::create([
                'folio' => 'SPD-'.now()->format('Ym').'-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'docente_id' => fake()->randomElement($docentes),
                'creado_por_id' => fake()->randomElement($academicos),
                'valorado_por_id' => $valorada ? $adminId : null,
                'autorizado_por_id' => $valorada ? $adminId : null,
                'procesado_por_id' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? $adminId : null,
                'rechazado_por_id' => $rechazada ? $adminId : null,
                'origen' => in_array($tipo, [SolicitudPagoDocente::TIPO_CURSO, SolicitudPagoDocente::TIPO_DIPLOMADO], true)
                    ? SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA : SolicitudPagoDocente::ORIGEN_CALENDARIO,
                'tipo_clase' => $tipo,
                'fechas_clase' => $fechas,
                'concepto_pago' => $valorada ? SolicitudPagoDocente::conceptoParaTipo($tipo) : null,
                'nivel' => $tipo,
                'programa_grupo' => fake()->randomElement(['Grupo 2-A', 'Doctorado 5', 'Licenciatura sabatina', 'MASC 2026']),
                'materia_actividad' => fake()->randomElement(['Derecho Constitucional', 'Taller de tesis', 'MasterClass', 'Sesión MASC', 'Conferencia']),
                'periodo' => '2026 A',
                'modalidad' => fake()->randomElement(['Presencial', 'Virtual', 'Mixta']),
                'numero_sesiones' => count($fechas),
                'horas_totales' => fake()->randomFloat(2, 2, 24),
                'esquema_pago' => $valorada ? fake()->randomElement(SolicitudPagoDocente::esquemasPago()) : null,
                'tarifa_unitaria' => $valorada ? fake()->randomFloat(2, 300, 900) : null,
                'monto' => $monto,
                'fecha_solicitud' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
                'fecha_inicio_periodo' => min($fechas),
                'fecha_fin_periodo' => max($fechas),
                'fecha_tentativa_pago' => $valorada ? now()->addDays(fake()->numberBetween(3, 20))->toDateString() : null,
                'fecha_valoracion' => $valorada ? now()->subDays(fake()->numberBetween(1, 5)) : null,
                'fecha_autorizacion' => $valorada ? now()->subDays(fake()->numberBetween(1, 5)) : null,
                'fecha_pago' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? now()->subDays(fake()->numberBetween(0, 3))->toDateString() : null,
                'fecha_rechazo' => $rechazada ? now()->subDays(fake()->numberBetween(1, 5)) : null,
                'motivo_rechazo' => $rechazada ? 'Coordinación Administrativa determinó que la solicitud no procede.' : null,
                'motivo_observacion' => $estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA ? 'Falta confirmar una o más fechas impartidas.' : null,
                'prioridad' => fake()->randomElement(SolicitudPagoDocente::prioridades()),
                'estatus' => $estatus,
            ]);
        }

        echo "✅ Seeder: solicitudes docentes con flujo Académica → CAdmin generadas.\n";
    }
}
