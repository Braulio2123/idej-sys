<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Docente;
use App\Models\Usuario;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        // 🧑‍💻 Asegurar que exista un usuario creador (Admin 1)
        $adminId = Usuario::first()->id ?? 1;

        $docentes = [
            [
                'nombre_completo'  => 'María Fernanda Navarro',
                'email'            => 'mfernanda@example.com',
                'telefono'         => '3312345678',
                'domicilio'        => 'Av. Patria 123, Zapopan',
                'area_especialidad'=> 'Derecho Civil',
                'rfc'              => 'NAVF890123AB3',
                'numero_cuenta'    => '12345678901',
            ],
            [
                'nombre_completo'  => 'Roberto González Martínez',
                'email'            => 'roberto.gm@example.com',
                'telefono'         => '3323456789',
                'domicilio'        => 'Lopez Mateos 560, Guadalajara',
                'area_especialidad'=> 'Derecho Penal',
                'rfc'              => 'GOMR850812CD4',
                'numero_cuenta'    => '98765432109',
            ],
            [
                'nombre_completo'  => 'Claudia Ruiz Hernández',
                'email'            => 'claudia.rh@example.com',
                'telefono'         => '3334567890',
                'domicilio'        => 'Av. México 2005, Guadalajara',
                'area_especialidad'=> 'Derecho Constitucional',
                'rfc'              => 'RUHC900212EF5',
                'numero_cuenta'    => '11223344556',
            ],
            [
                'nombre_completo'  => 'Carlos Alberto Mejía',
                'email'            => 'carlos.mejia@example.com',
                'telefono'         => '3311122233',
                'domicilio'        => 'Av. Tepeyac 90, Zapopan',
                'area_especialidad'=> 'Derecho Mercantil',
                'rfc'              => 'MEJC870101GH6',
                'numero_cuenta'    => '66778899001',
            ],
            [
                'nombre_completo'  => 'Ana Sofía Carrillo',
                'email'            => 'sofia.carrillo@example.com',
                'telefono'         => '3314456677',
                'domicilio'        => 'Niños Héroes 320, Guadalajara',
                'area_especialidad'=> 'Derecho Familiar',
                'rfc'              => 'CARA910923HJ7',
                'numero_cuenta'    => '77889900112',
            ],
            [
                'nombre_completo'  => 'Miguel Ángel Torres',
                'email'            => 'miguel.torres@example.com',
                'telefono'         => '3329988776',
                'domicilio'        => 'Av. Vallarta 4500',
                'area_especialidad'=> 'Derecho Administrativo',
                'rfc'              => 'TORM860430LK8',
                'numero_cuenta'    => '55667788990',
            ],
            [
                'nombre_completo'  => 'Julieta Mendoza López',
                'email'            => 'julieta.mendoza@example.com',
                'telefono'         => '3332211199',
                'domicilio'        => 'Circunvalación Sur 350',
                'area_especialidad'=> 'Derechos Humanos',
                'rfc'              => 'MELJ930102MN9',
                'numero_cuenta'    => '99887766554',
            ],
            [
                'nombre_completo'  => 'Luis Fernando Andrade',
                'email'            => 'landrade@example.com',
                'telefono'         => '3315567788',
                'domicilio'        => 'Av. Américas 1200',
                'area_especialidad'=> 'Derecho Internacional',
                'rfc'              => 'ANDF820630PQ1',
                'numero_cuenta'    => '33445566778',
            ],
            [
                'nombre_completo'  => 'Paola Herrera Silva',
                'email'            => 'paola.herrera@example.com',
                'telefono'         => '3337654321',
                'domicilio'        => 'Camino Real 300, Zapopan',
                'area_especialidad'=> 'Criminología',
                'rfc'              => 'HESP950512QR2',
                'numero_cuenta'    => '22001133445',
            ],
            [
                'nombre_completo'  => 'Diego Alejandro Vázquez',
                'email'            => 'diego.vazquez@example.com',
                'telefono'         => '3321105498',
                'domicilio'        => 'Lázaro Cárdenas 600',
                'area_especialidad'=> 'Juicios Orales',
                'rfc'              => 'VAZD880101ST3',
                'numero_cuenta'    => '66779922334',
            ],
            [
                'nombre_completo'  => 'Gabriela Rivera Torres',
                'email'            => 'gabriela.rivera@example.com',
                'telefono'         => '3323459998',
                'domicilio'        => 'Plaza del Sol 255',
                'area_especialidad'=> 'Derecho Laboral',
                'rfc'              => 'RITG900801UV4',
                'numero_cuenta'    => '88332211009',
            ],
            [
                'nombre_completo'  => 'Héctor Manuel Rojas',
                'email'            => 'hector.rojas@example.com',
                'telefono'         => '3344981122',
                'domicilio'        => 'Paseos del Sol 900',
                'area_especialidad'=> 'Litigación Estratégica',
                'rfc'              => 'ROJH850330WX5',
                'numero_cuenta'    => '44112233445',
            ],
            [
                'nombre_completo'  => 'Regina Soto Álvarez',
                'email'            => 'regina.soto@example.com',
                'telefono'         => '3338769900',
                'domicilio'        => 'Sta. Margarita 103',
                'area_especialidad'=> 'Metodología Jurídica',
                'rfc'              => 'SORR980412YZ6',
                'numero_cuenta'    => '55119988770',
            ],
            [
                'nombre_completo'  => 'Marco Antonio Delgado',
                'email'            => 'marco.delgado@example.com',
                'telefono'         => '3312314598',
                'domicilio'        => 'Providencia 2250',
                'area_especialidad'=> 'Derecho Fiscal',
                'rfc'              => 'DELM810909AA7',
                'numero_cuenta'    => '30889945661',
            ],
            [
                'nombre_completo'  => 'Sofía Chávez Sandoval',
                'email'            => 'sofia.chavez@example.com',
                'telefono'         => '3339015566',
                'domicilio'        => 'Contry Sol 200',
                'area_especialidad'=> 'Derecho Ambiental',
                'rfc'              => 'CHAS970530BB8',
                'numero_cuenta'    => '19445566771',
            ],
        ];

        foreach ($docentes as $data) {
            Docente::create([
                ...$data,
                'creado_por_id' => $adminId,
                'estatus' => 'Activo',
            ]);
        }
    }
}
