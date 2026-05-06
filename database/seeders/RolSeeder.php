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
                'descripcion' => 'Administración técnica de usuarios, bitácora y soporte.',
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
                'descripcion' => 'Atención a alumnos, cargos, pagos y convenios operativos.',
            ],
            [
                'nombre' => 'Relaciones Públicas IDEJ',
                'clave' => Rol::RRPP,
                'descripcion' => 'Seguimiento comercial y consulta de alumnos.',
            ],
            [
                'nombre' => 'Finanzas IDEJ',
                'clave' => Rol::FINANZAS,
                'descripcion' => 'Gestión de pagos, reportes financieros y solicitudes aprobadas.',
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
