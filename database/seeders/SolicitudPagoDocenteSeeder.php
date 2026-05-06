<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolicitudPagoDocente;
use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class SolicitudPagoDocenteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener docentes y usuarios válidos
        $docentes = Docente::pluck('id')->toArray();
        $usuarios = Usuario::pluck('id')->toArray();

        if (empty($docentes) || empty($usuarios)) {
            echo "⚠️ No hay docentes o usuarios para generar solicitudes.\n";
            return;
        }

        $niveles = ['Licenciatura', 'Maestría', 'Doctorado'];
        $estatusLista = [
            'Pendiente',
            'Aprobada',
            'Pagada'
        ];

        // Generar 20 solicitudes
        for ($i = 0; $i < 20; $i++) {

            $estatus = $estatusLista[array_rand($estatusLista)];

            // Solo si está pagado se llena procesado_por y fecha_pago
            $procesado = ($estatus === 'Pagada')
                ? $usuarios[array_rand($usuarios)]
                : null;

            $fechaPago = ($estatus === 'Pagada')
                ? fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d')
                : null;

            SolicitudPagoDocente::create([
                'docente_id'       => $docentes[array_rand($docentes)],
                'creado_por_id'    => $usuarios[array_rand($usuarios)],
                'procesado_por_id' => $procesado,
                'nivel'            => $niveles[array_rand($niveles)],
                'monto'            => fake()->randomFloat(2, 500, 8000),
                'fecha_solicitud'  => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
                'fecha_pago'       => $fechaPago,
                'observaciones'    => fake()->boolean(40) ? fake()->sentence(8) : null,
                'estatus'          => $estatus,
            ]);
        }

        echo "✅ Seeder: Solicitudes de pago a docentes generadas correctamente.\n";
    }
}
