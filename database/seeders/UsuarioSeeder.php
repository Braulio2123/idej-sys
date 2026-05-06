<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolIds = Rol::pluck('id', 'clave');

        $usuarios = [
            [
                'nombre' => 'Administrador IDEJ',
                'email' => 'admin@idej.test',
                'password' => Hash::make('admin123'),
                'rol_id' => $rolIds[Rol::ADMIN] ?? null,
            ],
            [
                'nombre' => 'Sistemas IDEJ',
                'email' => 'sistemas@idej.test',
                'password' => Hash::make('sistemas123'),
                'rol_id' => $rolIds[Rol::SISTEMAS] ?? null,
            ],
            [
                'nombre' => 'Dirección IDEJ',
                'email' => 'direccion@idej.test',
                'password' => Hash::make('direccion123'),
                'rol_id' => $rolIds[Rol::DIRECCION] ?? null,
            ],
            [
                'nombre' => 'Coordinación Administrativa IDEJ',
                'email' => 'cadmin@idej.test',
                'password' => Hash::make('cadmin123'),
                'rol_id' => $rolIds[Rol::CADMIN] ?? null,
            ],
            [
                'nombre' => 'Coordinación Académica IDEJ',
                'email' => 'academica@idej.test',
                'password' => Hash::make('academica123'),
                'rol_id' => $rolIds[Rol::ACADEMICA] ?? null,
            ],
            [
                'nombre' => 'Recepción IDEJ',
                'email' => 'recepcion@idej.test',
                'password' => Hash::make('recepcion123'),
                'rol_id' => $rolIds[Rol::RECEPCION] ?? null,
            ],
            [
                'nombre' => 'Relaciones Públicas IDEJ',
                'email' => 'rrpp@idej.test',
                'password' => Hash::make('rrpp123'),
                'rol_id' => $rolIds[Rol::RRPP] ?? null,
            ],
            [
                'nombre' => 'Finanzas IDEJ',
                'email' => 'finanzas@idej.test',
                'password' => Hash::make('finanzas123'),
                'rol_id' => $rolIds[Rol::FINANZAS] ?? null,
            ],
        ];

        foreach ($usuarios as $usuario) {
            if (! $usuario['rol_id']) {
                continue;
            }

            Usuario::updateOrCreate(
                ['email' => $usuario['email']],
                $usuario
            );
        }
    }
}
