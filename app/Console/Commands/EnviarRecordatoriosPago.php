<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioPago;
use App\Models\ConfiguracionInstitucional;
use App\Models\Alumno;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatoriosPago extends Command
{
    /**
     * Nombre del comando: php artisan app:enviar-recordatorios
     */
    protected $signature = 'app:enviar-recordatorios';

    /**
     * Descripción visible en "php artisan list"
     */
    protected $description = "Envía correos de recordatorio a alumnos con estatus 'Con Adeudo'";

    public function handle(): int
    {
        $this->info('🔔 Iniciando envío de recordatorios de pago...');

        if (! ConfiguracionInstitucional::actual()->recordatorios_pago_activos) {
            $this->warn('Los recordatorios de pago están desactivados en Configuración Institucional.');
            return self::SUCCESS;
        }

        $alumnos = Alumno::where('estatus_financiero', 'Con Adeudo')
            ->whereNotNull('correo')
            ->where('correo', '!=', '')
            ->get();

        if ($alumnos->isEmpty()) {
            $this->warn('No hay alumnos con adeudo para notificar.');
            return self::SUCCESS;
        }

        $enviados = 0;

        foreach ($alumnos as $alumno) {
            Mail::to($alumno->correo)->send(new RecordatorioPago($alumno));
            $this->line("📨 Enviado a: {$alumno->nombre_completo} <{$alumno->correo}>");
            $enviados++;
        }

        $this->info("✅ Proceso finalizado. Correos enviados: {$enviados}");
        return self::SUCCESS;
    }
}
