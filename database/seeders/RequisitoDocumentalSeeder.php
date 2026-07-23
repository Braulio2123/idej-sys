<?php

namespace Database\Seeders;

use App\Models\RequisitoDocumental;
use Illuminate\Database\Seeder;

class RequisitoDocumentalSeeder extends Seeder
{
    public function run(): void
    {
        $requisitos = [
            ['tipo_documento' => 'Acta de nacimiento', 'descripcion' => 'Documento legible, completo y sin alteraciones.', 'orden' => 10],
            ['tipo_documento' => 'CURP', 'descripcion' => 'CURP actualizada en formato oficial.', 'orden' => 20],
            ['tipo_documento' => 'Identificación oficial', 'descripcion' => 'INE, pasaporte o identificación oficial vigente.', 'orden' => 30],
            ['tipo_documento' => 'Comprobante de domicilio', 'descripcion' => 'No mayor a tres meses.', 'orden' => 40],
            ['tipo_documento' => 'Solicitud de inscripción', 'descripcion' => 'Formato institucional firmado por el alumno.', 'orden' => 50],
            ['tipo_documento' => 'Carta compromiso / Reglamento', 'descripcion' => 'Carta compromiso o reglamento institucional firmado.', 'orden' => 60],
            ['tipo_documento' => 'Certificado de Bachillerato', 'descripcion' => 'Antecedente académico para licenciatura o cuando aplique.', 'orden' => 70],
            ['tipo_documento' => 'Título profesional', 'descripcion' => 'Título del grado inmediato anterior cuando aplique.', 'orden' => 80],
            ['tipo_documento' => 'Cédula profesional', 'descripcion' => 'Cédula profesional del grado inmediato anterior cuando aplique.', 'orden' => 90],
            ['tipo_documento' => 'Fotografía', 'descripcion' => 'Fotografía reciente para expediente y credencialización.', 'orden' => 100],
        ];

        foreach ($requisitos as $requisito) {
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
    }
}
