<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador IDEJ',
                'clave' => Rol::ADMIN,
                'descripcion' => 'Acceso total al sistema.',
            ],
            [
                'nombre' => 'Sistemas IDEJ',
                'clave' => Rol::SISTEMAS,
                'descripcion' => 'Soporte técnico, diagnóstico y mantenimiento; consulta básica de usuarios sin gestión de credenciales.',
            ],
            [
                'nombre' => 'Dirección IDEJ',
                'clave' => Rol::DIRECCION,
                'descripcion' => 'Consulta ejecutiva de reportes e información institucional.',
            ],
            [
                'nombre' => 'Coordinación Administrativa IDEJ',
                'clave' => Rol::CADMIN,
                'descripcion' => 'Gestión administrativa, financiera y operativa.',
            ],
            [
                'nombre' => 'Coordinación Académica IDEJ',
                'clave' => Rol::ACADEMICA,
                'descripcion' => 'Gestión académica, docentes, grupos y solicitudes.',
            ],
            [
                'nombre' => 'Recepción IDEJ',
                'clave' => Rol::RECEPCION,
                'descripcion' => 'Atención a alumnos, recepción documental y cobro limitado en su propia caja.',
            ],
            [
                'nombre' => 'Relaciones Públicas IDEJ',
                'clave' => Rol::RRPP,
                'descripcion' => 'Captación, prospectos, seguimientos comerciales y consulta de la oferta académica.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }
    }
}
