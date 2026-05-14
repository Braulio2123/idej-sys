<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolicitudPagoDocente;
use App\Models\Docente;
use App\Models\Usuario;

class SolicitudPagoDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $docentes = Docente::pluck('id')->toArray();
        $usuarios = Usuario::pluck('id')->toArray();

        if (empty($docentes) || empty($usuarios)) {
            echo "⚠️ No hay docentes o usuarios para generar solicitudes.\n";
            return;
        }

        $niveles = ['Licenciatura', 'Maestría', 'Doctorado', 'Educación continua'];
        $estatusLista = [
            SolicitudPagoDocente::ESTATUS_PENDIENTE,
            SolicitudPagoDocente::ESTATUS_OBSERVADA,
            SolicitudPagoDocente::ESTATUS_AUTORIZADA,
            SolicitudPagoDocente::ESTATUS_PAGADA,
        ];

        for ($i = 1; $i <= 20; $i++) {
            $estatus = $estatusLista[array_rand($estatusLista)];
            $fechaSolicitud = fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d');
            $monto = fake()->randomFloat(2, 800, 9000);

            SolicitudPagoDocente::create([
                'folio' => 'SPD-'.now()->format('Ym').'-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'docente_id' => $docentes[array_rand($docentes)],
                'creado_por_id' => $usuarios[array_rand($usuarios)],
                'autorizado_por_id' => in_array($estatus, [SolicitudPagoDocente::ESTATUS_AUTORIZADA, SolicitudPagoDocente::ESTATUS_PAGADA], true) ? $usuarios[array_rand($usuarios)] : null,
                'procesado_por_id' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? $usuarios[array_rand($usuarios)] : null,
                'origen' => fake()->randomElement(SolicitudPagoDocente::origenes()),
                'concepto_pago' => fake()->randomElement(SolicitudPagoDocente::conceptos()),
                'nivel' => fake()->randomElement($niveles),
                'programa_grupo' => fake()->randomElement(['Maestría 4 · Grupo 2-A', 'Doctorado 5', 'Licenciatura sabatina', 'MASC 2026']),
                'materia_actividad' => fake()->randomElement(['Derecho Constitucional', 'Taller de tesis', 'MasterClass', 'Sesión MASC', 'Conferencia']),
                'periodo' => '2026 A',
                'modalidad' => fake()->randomElement(['Presencial', 'Virtual', 'Mixta']),
                'numero_sesiones' => fake()->numberBetween(1, 6),
                'horas_totales' => fake()->randomFloat(2, 2, 24),
                'tarifa_hora' => fake()->randomFloat(2, 300, 900),
                'monto' => $monto,
                'fecha_solicitud' => $fechaSolicitud,
                'fecha_inicio_periodo' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
                'fecha_fin_periodo' => fake()->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
                'fecha_limite_pago' => fake()->dateTimeBetween('now', '+20 days')->format('Y-m-d'),
                'fecha_autorizacion' => in_array($estatus, [SolicitudPagoDocente::ESTATUS_AUTORIZADA, SolicitudPagoDocente::ESTATUS_PAGADA], true) ? now()->subDays(fake()->numberBetween(1, 8)) : null,
                'fecha_pago' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d') : null,
                'prioridad' => fake()->randomElement(SolicitudPagoDocente::prioridades()),
                'metodo_pago' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? fake()->randomElement(SolicitudPagoDocente::metodosPago()) : null,
                'referencia_pago' => $estatus === SolicitudPagoDocente::ESTATUS_PAGADA ? strtoupper(fake()->bothify('REF-####-????')) : null,
                'observaciones_academica' => fake()->boolean(40) ? fake()->sentence(10) : null,
                'observaciones_administracion' => fake()->boolean(30) ? fake()->sentence(8) : null,
                'motivo_observacion' => $estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA ? 'Falta confirmar fechas o monto autorizado.' : null,
                'estatus' => $estatus,
            ]);
        }

        echo "✅ Seeder: Solicitudes de pago a docentes generadas correctamente.\n";
    }
}
