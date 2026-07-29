<?php

namespace App\Console\Commands;

use App\Services\RecordatorioPagoEmailService;
use Illuminate\Console\Command;

class EnviarRecordatoriosPago extends Command
{
    protected $signature = 'app:enviar-recordatorios
        {--canal=email : Canal de salida. En Fase 35 solo se permite email.}
        {--limite=100 : Cantidad máxima de alumnos a procesar por ejecución}
        {--dry-run : Simula el proceso sin enviar correos ni registrar envíos}
        {--solo-vencidos : Solo alumnos con cargos vencidos}
        {--programa= : Filtrar por programa académico}
        {--grupo= : Filtrar por grupo académico}';

    protected $description = 'Envía recordatorios institucionales de pago exclusivamente por correo electrónico.';

    public function handle(RecordatorioPagoEmailService $recordatorios): int
    {
        $canal = strtolower((string) $this->option('canal'));

        if ($canal !== 'email') {
            $this->error('En esta fase los recordatorios externos solo se envían por correo electrónico. SMS y WhatsApp quedaron fuera del alcance.');
            return self::FAILURE;
        }

        $limite = max(1, (int) $this->option('limite'));
        $dryRun = (bool) $this->option('dry-run');
        $filtros = [
            'solo_vencidos' => (bool) $this->option('solo-vencidos'),
            'programa_id' => $this->option('programa') ?: null,
            'grupo_id' => $this->option('grupo') ?: null,
        ];

        $resultado = $recordatorios->procesar($limite, $dryRun, $filtros);

        if (! $resultado['activo']) {
            $this->warn($resultado['mensaje']);
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Simulación finalizada. Correos que se enviarían: {$resultado['simulados']}. Omitidos: {$resultado['omitidos']}. Errores: {$resultado['errores']}.");
            return self::SUCCESS;
        }

        $this->info("Proceso finalizado. Correos enviados: {$resultado['enviados']}. Omitidos: {$resultado['omitidos']}. Errores: {$resultado['errores']}.");

        return $resultado['errores'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
