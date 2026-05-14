<?php

namespace Database\Seeders;

use App\Models\PortalAlumno\AvisoPortal;
use Illuminate\Database\Seeder;

/**
 * Seeder opcional para cargar avisos de ejemplo del Portal Alumno.
 * Ejecutar manualmente con:
 * php artisan db:seed --class=PortalAlumnoAvisoSeeder
 */
class PortalAlumnoAvisoSeeder extends Seeder
{
    public function run(): void
    {
        AvisoPortal::query()->firstOrCreate(
            ['titulo' => 'Bienvenido al Portal Alumno IDEJ'],
            [
                'contenido' => 'Desde este espacio podras consultar tu horario, materias, calificaciones, avisos institucionales y datos de ubicacion del plantel.',
                'categoria' => 'General',
                'prioridad' => AvisoPortal::PRIORIDAD_IMPORTANTE,
                'destino_tipo' => AvisoPortal::DESTINO_TODOS,
                'visible_desde' => now(),
                'activo' => true,
            ]
        );

        AvisoPortal::query()->firstOrCreate(
            ['titulo' => 'Actualiza tus datos en control escolar'],
            [
                'contenido' => 'Si tu telefono o correo han cambiado, acude al area correspondiente para mantener tu informacion actualizada.',
                'categoria' => 'Control escolar',
                'prioridad' => AvisoPortal::PRIORIDAD_NORMAL,
                'destino_tipo' => AvisoPortal::DESTINO_TODOS,
                'visible_desde' => now(),
                'activo' => true,
            ]
        );
    }
}
