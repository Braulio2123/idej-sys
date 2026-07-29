<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionInstitucional;
use Illuminate\Console\Command;

class ActivarRecordatoriosEmail extends Command
{
    protected $signature = 'idej:activar-recordatorios-email';

    protected $description = 'Activa los recordatorios de pago por correo en Configuración institucional.';

    public function handle(): int
    {
        $configuracion = ConfiguracionInstitucional::actual();
        $configuracion->recordatorios_pago_activos = true;
        $configuracion->save();

        $this->info('Recordatorios de pago por correo activados en Configuración institucional. Confirma también IDEJ_RECORDATORIOS_EMAIL=true en .env.');

        return self::SUCCESS;
    }
}
