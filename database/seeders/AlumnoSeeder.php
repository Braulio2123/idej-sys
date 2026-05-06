<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumno;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        Alumno::insert([
            // GRUPO 1 – 10 ALUMNOS
            [
                'matricula' => 'A001',
                'nombre_completo' => 'Juan Pérez López',
                'correo' => 'juan.perez@example.com',
                'telefono' => '3311122233',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A002',
                'nombre_completo' => 'María González Torres',
                'correo' => 'maria.gonzalez@example.com',
                'telefono' => '3312345678',
                'beca_porcentaje' => 20,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A003',
                'nombre_completo' => 'Carlos Sánchez Díaz',
                'correo' => 'carlos.sanchez@example.com',
                'telefono' => '3322334455',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A004',
                'nombre_completo' => 'Fernanda Ramírez Soto',
                'correo' => 'fernanda.ramirez@example.com',
                'telefono' => '3322113344',
                'beca_porcentaje' => 50,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A005',
                'nombre_completo' => 'Ricardo Torres Gómez',
                'correo' => 'ricardo.torres@example.com',
                'telefono' => '3314789652',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A006',
                'nombre_completo' => 'Jessica López Martínez',
                'correo' => 'jessica.lopez@example.com',
                'telefono' => '3325897412',
                'beca_porcentaje' => 30,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A007',
                'nombre_completo' => 'Héctor Ruiz Delgado',
                'correo' => 'hector.ruiz@example.com',
                'telefono' => '3332157648',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A008',
                'nombre_completo' => 'Daniela Cruz Rivera',
                'correo' => 'daniela.cruz@example.com',
                'telefono' => '3336985471',
                'beca_porcentaje' => 10,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A009',
                'nombre_completo' => 'Luis Hernández Peña',
                'correo' => 'luis.hernandez@example.com',
                'telefono' => '3311597536',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 1
            ],
            [
                'matricula' => 'A010',
                'nombre_completo' => 'Patricia Sandoval Lugo',
                'correo' => 'patricia.sandoval@example.com',
                'telefono' => '3348596321',
                'beca_porcentaje' => 15,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 1
            ],

            // GRUPO 2 – 10 ALUMNOS (A011–A020)
            [
                'matricula' => 'A011',
                'nombre_completo' => 'Roberto Ortiz Jiménez',
                'correo' => 'roberto.ortiz@example.com',
                'telefono' => '3317729101',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A012',
                'nombre_completo' => 'Silvia Vega Romero',
                'correo' => 'silvia.vega@example.com',
                'telefono' => '3337810022',
                'beca_porcentaje' => 40,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A013',
                'nombre_completo' => 'Jorge Ramírez Ochoa',
                'correo' => 'jorge.ramirez@example.com',
                'telefono' => '3319028473',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A014',
                'nombre_completo' => 'Ximena León Carrillo',
                'correo' => 'ximena.leon@example.com',
                'telefono' => '3339901827',
                'beca_porcentaje' => 25,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A015',
                'nombre_completo' => 'Emilio Solís Vázquez',
                'correo' => 'emilio.solis@example.com',
                'telefono' => '3325489071',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A016',
                'nombre_completo' => 'Valeria Gómez Pacheco',
                'correo' => 'valeria.gomez@example.com',
                'telefono' => '3341125678',
                'beca_porcentaje' => 35,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A017',
                'nombre_completo' => 'José Martínez Neri',
                'correo' => 'jose.martinez@example.com',
                'telefono' => '3329014487',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A018',
                'nombre_completo' => 'Lucía Herrera Bonilla',
                'correo' => 'lucia.herrera@example.com',
                'telefono' => '3312234098',
                'beca_porcentaje' => 50,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A019',
                'nombre_completo' => 'David Pineda González',
                'correo' => 'david.pineda@example.com',
                'telefono' => '3327894411',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 2
            ],
            [
                'matricula' => 'A020',
                'nombre_completo' => 'Sofía Acosta Molina',
                'correo' => 'sofia.acosta@example.com',
                'telefono' => '3331109988',
                'beca_porcentaje' => 20,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 2
            ],

            // GRUPO 3 – 10 ALUMNOS (A021–A030)
            [
                'matricula' => 'A021',
                'nombre_completo' => 'Pablo Torres Medina',
                'correo' => 'pablo.torres@example.com',
                'telefono' => '3312217788',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A022',
                'nombre_completo' => 'Alejandra Navarro Velasco',
                'correo' => 'alejandra.navarro@example.com',
                'telefono' => '3319984732',
                'beca_porcentaje' => 45,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A023',
                'nombre_completo' => 'Manuel Ramos Aguilar',
                'correo' => 'manuel.ramos@example.com',
                'telefono' => '3332019874',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A024',
                'nombre_completo' => 'Renata Fierro Delgado',
                'correo' => 'renata.fierro@example.com',
                'telefono' => '3321447856',
                'beca_porcentaje' => 10,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A025',
                'nombre_completo' => 'Isaac Aguirre Torres',
                'correo' => 'isaac.aguirre@example.com',
                'telefono' => '3318542077',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A026',
                'nombre_completo' => 'Victoria Salazar Ramos',
                'correo' => 'victoria.salazar@example.com',
                'telefono' => '3322145896',
                'beca_porcentaje' => 30,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A027',
                'nombre_completo' => 'Tomás Villalobos Peña',
                'correo' => 'tomas.villalobos@example.com',
                'telefono' => '3334178952',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A028',
                'nombre_completo' => 'Paola Madrigal Soto',
                'correo' => 'paola.madrigal@example.com',
                'telefono' => '3325187044',
                'beca_porcentaje' => 25,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A029',
                'nombre_completo' => 'Ángel Durán Becerra',
                'correo' => 'angel.duran@example.com',
                'telefono' => '3335712489',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 3
            ],
            [
                'matricula' => 'A030',
                'nombre_completo' => 'Claudia Prado Luna',
                'correo' => 'claudia.prado@example.com',
                'telefono' => '3314412290',
                'beca_porcentaje' => 50,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 3
            ],

            // GRUPO 4 – 10 ALUMNOS (A031–A040)
            [
                'matricula' => 'A031',
                'nombre_completo' => 'Adrián Bravo Pérez',
                'correo' => 'adrian.bravo@example.com',
                'telefono' => '3321569874',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A032',
                'nombre_completo' => 'Montserrat Corona Silva',
                'correo' => 'montserrat.corona@example.com',
                'telefono' => '3336541208',
                'beca_porcentaje' => 40,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A033',
                'nombre_completo' => 'Erick Chávez Mendoza',
                'correo' => 'erick.chavez@example.com',
                'telefono' => '3312208976',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A034',
                'nombre_completo' => 'Yareli Rivas Delgado',
                'correo' => 'yareli.rivas@example.com',
                'telefono' => '3337410258',
                'beca_porcentaje' => 20,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A035',
                'nombre_completo' => 'Omar Escamilla Torres',
                'correo' => 'omar.escamilla@example.com',
                'telefono' => '3322415980',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A036',
                'nombre_completo' => 'Saraí Beltrán Pérez',
                'correo' => 'sarai.beltran@example.com',
                'telefono' => '3339021470',
                'beca_porcentaje' => 30,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A037',
                'nombre_completo' => 'Gerardo Álvarez Varela',
                'correo' => 'gerardo.alvarez@example.com',
                'telefono' => '3311547892',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Con Adeudo',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A038',
                'nombre_completo' => 'Elena Carmona Rojas',
                'correo' => 'elena.carmona@example.com',
                'telefono' => '3328471596',
                'beca_porcentaje' => 50,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A039',
                'nombre_completo' => 'Alberto Villaseñor Ruiz',
                'correo' => 'alberto.villasenor@example.com',
                'telefono' => '3314789651',
                'beca_porcentaje' => 0,
                'estatus_financiero' => 'Al Corriente',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Normal',
                'grupo_id' => 4
            ],
            [
                'matricula' => 'A040',
                'nombre_completo' => 'Miriam Soto Camacho',
                'correo' => 'miriam.soto@example.com',
                'telefono' => '3331598472',
                'beca_porcentaje' => 15,
                'estatus_financiero' => 'Becado',
                'estatus_academico' => 'Activo',
                'condicion_alumno' => 'Becado',
                'grupo_id' => 4
            ],
        ]);
    }
}
