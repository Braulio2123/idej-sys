<?php

namespace Database\Seeders;

use App\Models\Programa;
use App\Models\RequisitoDocumental;
use Illuminate\Database\Seeder;

class RequisitoDocumentalSeeder extends Seeder
{
    public function run(): void
    {
        $generales = [
            ['tipo_documento' => 'Acta de nacimiento', 'descripcion' => 'Documento legible, completo y sin alteraciones.', 'orden' => 10],
            ['tipo_documento' => 'CURP', 'descripcion' => 'CURP actualizada en formato oficial.', 'orden' => 20],
            ['tipo_documento' => 'Identificación oficial', 'descripcion' => 'INE, pasaporte o identificación oficial vigente.', 'orden' => 30],
            ['tipo_documento' => 'Comprobante de domicilio', 'descripcion' => 'No mayor a tres meses.', 'orden' => 40],
            ['tipo_documento' => 'Solicitud de inscripción', 'descripcion' => 'Formato institucional firmado por el alumno.', 'orden' => 50],
            ['tipo_documento' => 'Contrato / reglamento firmado', 'descripcion' => 'Acuse o reglamento institucional firmado.', 'orden' => 60],
        ];

        foreach ($generales as $requisito) {
            RequisitoDocumental::updateOrCreate(
                [
                    'programa_id' => null,
                    'nivel' => null,
                    'tipo_documento' => $requisito['tipo_documento'],
                ],
                array_merge($requisito, [
                    'obligatorio' => true,
                    'activo' => true,
                ])
            );
        }

        $porNivel = [
            'Licenciatura' => [
                ['tipo_documento' => 'Certificado de estudios', 'descripcion' => 'Certificado de bachillerato o antecedente académico requerido.', 'orden' => 100],
            ],
            'Maestría' => [
                ['tipo_documento' => 'Título profesional', 'descripcion' => 'Título o acta de titulación de licenciatura.', 'orden' => 100],
                ['tipo_documento' => 'Cédula profesional', 'descripcion' => 'Cédula profesional de licenciatura.', 'orden' => 110],
            ],
            'Doctorado' => [
                ['tipo_documento' => 'Título profesional', 'descripcion' => 'Título de maestría o antecedente requerido.', 'orden' => 100],
                ['tipo_documento' => 'Cédula profesional', 'descripcion' => 'Cédula profesional correspondiente.', 'orden' => 110],
            ],
        ];

        foreach ($porNivel as $nivel => $requisitos) {
            foreach ($requisitos as $requisito) {
                RequisitoDocumental::updateOrCreate(
                    [
                        'programa_id' => null,
                        'nivel' => $nivel,
                        'tipo_documento' => $requisito['tipo_documento'],
                    ],
                    array_merge($requisito, [
                        'obligatorio' => true,
                        'activo' => true,
                    ])
                );
            }
        }

        $licDerecho = Programa::where('nombre', 'Licenciatura en Derecho')->first();
        if ($licDerecho) {
            RequisitoDocumental::updateOrCreate(
                [
                    'programa_id' => $licDerecho->id,
                    'nivel' => null,
                    'tipo_documento' => 'Fotografía',
                ],
                [
                    'descripcion' => 'Fotografía reciente para expediente y credencialización.',
                    'obligatorio' => false,
                    'activo' => true,
                    'orden' => 150,
                ]
            );
        }
    }
}
