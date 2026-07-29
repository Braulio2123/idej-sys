<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class ValidarProduccion extends Command
{
    protected $signature = 'idej:validar-produccion
        {--strict : Devuelve código de error cuando existe cualquier incumplimiento crítico}
        {--sin-bd : Omite conexión y estado de migraciones}';

    protected $description = 'Valida configuración, extensiones, almacenamiento y controles mínimos antes de publicar IDEJ-SYS.';

    /** @var array<int, array{nivel:string, control:string, detalle:string}> */
    private array $resultados = [];

    public function handle(): int
    {
        $this->info('Validación de preparación para producción IDEJ-SYS');

        $this->validarAplicacion();
        $this->validarPhp();
        $this->validarSesion();
        $this->validarCorreo();
        $this->validarInfraestructura();
        $this->validarAutomatizaciones();

        if (! $this->option('sin-bd')) {
            $this->validarBaseDatos();
        }

        $this->newLine();
        $this->table(
            ['Nivel', 'Control', 'Resultado'],
            array_map(fn (array $fila) => [
                strtoupper($fila['nivel']),
                $fila['control'],
                $fila['detalle'],
            ], $this->resultados)
        );

        $criticos = count(array_filter($this->resultados, fn (array $fila) => $fila['nivel'] === 'error'));
        $advertencias = count(array_filter($this->resultados, fn (array $fila) => $fila['nivel'] === 'warning'));

        $this->newLine();
        $this->line("Controles críticos incumplidos: {$criticos}. Advertencias: {$advertencias}.");

        if ($criticos > 0) {
            $this->line('<error>IDEJ-SYS no debe publicarse hasta corregir los controles críticos.</error>');

            return $this->option('strict') ? self::FAILURE : self::SUCCESS;
        }

        $this->info('No se detectaron bloqueos críticos de configuración.');

        return self::SUCCESS;
    }

    private function validarAplicacion(): void
    {
        $this->check(
            app()->environment('production'),
            'APP_ENV',
            'Configurado como production.',
            'Debe ser production en el servidor final.'
        );

        $this->check(
            ! config('app.debug'),
            'APP_DEBUG',
            'Los errores detallados están desactivados.',
            'Debe ser false para no exponer consultas, rutas y stack traces.'
        );

        $key = (string) config('app.key');
        $keyBytes = Str::startsWith($key, 'base64:')
            ? base64_decode(Str::after($key, 'base64:'), true)
            : $key;
        $keyValida = is_string($keyBytes)
            && Encrypter::supported($keyBytes, (string) config('app.cipher'))
            && ! Str::contains(Str::lower($key), ['cambiar', 'example', 'generada']);

        $this->check(
            $keyValida,
            'APP_KEY',
            'La llave tiene formato compatible con el cifrado configurado.',
            'Falta una APP_KEY válida o todavía contiene un valor de ejemplo.'
        );

        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        $httpsRequerido = (bool) config('idej_security.production.require_https', true);
        $urlValida = filled($host)
            && ! Str::contains(Str::lower($url), ['tu-dominio', 'example.com'])
            && (! $httpsRequerido || Str::startsWith($url, 'https://'));

        $this->check(
            $urlValida,
            'APP_URL',
            'La URL institucional es válida y utiliza HTTPS.',
            'Configura la URL final con HTTPS y sin marcadores de ejemplo.'
        );

        $this->check(
            ! File::exists(public_path('.env')),
            'Archivo .env público',
            'No existe public/.env.',
            'Se detectó un archivo .env dentro de public/. Debe retirarse de inmediato.'
        );

        $this->warning(
            config('logging.channels.'.config('logging.default').'.level', config('logging.channels.single.level')) !== 'debug',
            'Nivel de logs',
            'El nivel de logs no está configurado en debug.',
            'Evita LOG_LEVEL=debug en producción para reducir exposición y crecimiento de disco.'
        );
    }

    private function validarPhp(): void
    {
        foreach (['ctype', 'dom', 'fileinfo', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extension) {
            $this->check(
                extension_loaded($extension),
                'Extensión PHP: '.$extension,
                'Disponible.',
                'La extensión es necesaria para Laravel, validaciones o pruebas.'
            );
        }

        $driver = (string) config('database.connections.'.config('database.default').'.driver');
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->check(
                extension_loaded('pdo_mysql'),
                'Extensión PHP: pdo_mysql',
                'Disponible para MySQL/MariaDB.',
                'No es posible conectar con la base de datos sin pdo_mysql.'
            );
        }

        $this->warning(
            class_exists(\ZipArchive::class),
            'Extensión PHP: zip',
            'Disponible para respaldos de archivos.',
            'Sin ext-zip no se podrán generar respaldos ZIP desde mantenimiento.'
        );
    }

    private function validarSesion(): void
    {
        $this->check(
            in_array(config('session.driver'), ['database', 'redis'], true),
            'SESSION_DRIVER',
            'Las sesiones usan almacenamiento centralizado.',
            'Usa database o redis para poder invalidar sesiones de usuarios.'
        );
        $this->check((bool) config('session.encrypt'), 'SESSION_ENCRYPT', 'Contenido cifrado.', 'Debe ser true.');
        $this->check((bool) config('session.secure'), 'SESSION_SECURE_COOKIE', 'Cookie limitada a HTTPS.', 'Debe ser true en producción.');
        $this->check((bool) config('session.http_only'), 'SESSION_HTTP_ONLY', 'Cookie no accesible desde JavaScript.', 'Debe ser true.');
        $this->check(
            in_array(config('session.same_site'), ['lax', 'strict'], true),
            'SESSION_SAME_SITE',
            'Política SameSite segura.',
            'Utiliza lax o strict para reducir solicitudes cruzadas.'
        );
    }

    private function validarCorreo(): void
    {
        $mailer = (string) config('mail.default');
        $transport = (string) config("mail.mailers.{$mailer}.transport", $mailer);
        $mailerReal = ! in_array($transport, ['log', 'array'], true);

        if (config('idej_security.production.require_real_mailer', true)) {
            $this->check(
                $mailerReal,
                'MAIL_MAILER',
                'Existe un transporte real para recuperación de contraseña.',
                'MAIL_MAILER=log/array no entrega enlaces de recuperación al usuario.'
            );
        }

        if ($transport === 'smtp') {
            $host = (string) config('mail.mailers.smtp.host');
            $this->check(
                filled($host) && ! in_array($host, ['127.0.0.1', 'localhost'], true),
                'MAIL_HOST',
                'Servidor SMTP externo configurado.',
                'Configura el servidor SMTP institucional.'
            );
        }

        $from = (string) config('mail.from.address');
        $this->check(
            filter_var($from, FILTER_VALIDATE_EMAIL)
                && ! Str::endsWith(Str::lower($from), ['@idej.test', '@example.com']),
            'MAIL_FROM_ADDRESS',
            'Dirección remitente institucional válida.',
            'Sustituye correos de prueba por una dirección institucional real.'
        );
    }

    private function validarInfraestructura(): void
    {
        $this->check(
            in_array(config('cache.default'), ['database', 'redis', 'memcached'], true),
            'CACHE_STORE',
            'Caché centralizada compatible con bloqueos de idempotencia.',
            'Usa database o redis; file no protege correctamente varias instancias.'
        );
        $this->check(
            in_array(config('queue.default'), ['database', 'redis'], true),
            'QUEUE_CONNECTION',
            'Cola persistente configurada.',
            'Usa database o redis para trabajos que deban sobrevivir reinicios.'
        );
        $this->check(
            config('filesystems.default') === 'local'
                && realpath(storage_path('app/private')) !== realpath(public_path()),
            'Almacenamiento privado',
            'El disco predeterminado apunta fuera de public.',
            'Los documentos sensibles deben permanecer en storage/app/private.'
        );
        $this->check(is_writable(storage_path()), 'Permisos storage', 'La carpeta es escribible.', 'Laravel no puede escribir archivos, logs o sesiones.');
        $this->check(is_writable(base_path('bootstrap/cache')), 'Permisos bootstrap/cache', 'La carpeta es escribible.', 'Laravel no puede generar cachés de producción.');

        $publicStorage = public_path('storage');
        if (File::exists($publicStorage)) {
            $publicReal = realpath($publicStorage);
            $privateReal = realpath(storage_path('app/private'));
            $this->check(
                ! $publicReal || ! $privateReal || $publicReal !== $privateReal,
                'Enlace public/storage',
                'No apunta al almacenamiento privado.',
                'public/storage no debe exponer storage/app/private.'
            );
        }

        $this->warning(
            filled(config('idej_security.trusted_proxies')),
            'TRUSTED_PROXIES',
            'Proxy inverso configurado.',
            'Configura las IP/CIDR del proxy. Usa * solo cuando el servidor no sea accesible directamente.'
        );
    }

    private function validarAutomatizaciones(): void
    {
        $this->check(
            ! config('idej_recordatorios.alumnos_adeudo.activo')
                && ! config('idej_recordatorios.canales.email.activo')
                && ! config('idej_recordatorios.cargos_recurrentes.activo'),
            'Automatizaciones fuera de alcance',
            'Cobranza por correo y cargos recurrentes automáticos están desactivados.',
            'Mantén IDEJ_RECORDATORIOS_* e IDEJ_CARGOS_RECURRENTES_ACTIVOS en false hasta autorización expresa.'
        );
    }

    private function validarBaseDatos(): void
    {
        try {
            DB::connection()->getPdo();
            $this->ok('Conexión a base de datos', 'La conexión responde correctamente.');
        } catch (Throwable $e) {
            $this->addError('Conexión a base de datos', 'No fue posible conectar. Revisa credenciales, red y driver PDO.');

            return;
        }

        try {
            Artisan::call('migrate:status', ['--no-interaction' => true]);
            $output = Artisan::output();
            $pendientes = Str::contains($output, 'Pending');
            $this->check(
                ! $pendientes,
                'Migraciones',
                'No se detectaron migraciones pendientes.',
                'Ejecuta php artisan migrate --force antes de publicar.'
            );
        } catch (Throwable $e) {
            $this->addError('Migraciones', 'No fue posible consultar su estado.');
        }
    }

    private function check(bool $cumple, string $control, string $ok, string $error): void
    {
        $cumple ? $this->ok($control, $ok) : $this->addError($control, $error);
    }

    private function warning(bool $cumple, string $control, string $ok, string $warning): void
    {
        $cumple ? $this->ok($control, $ok) : $this->resultados[] = [
            'nivel' => 'warning',
            'control' => $control,
            'detalle' => $warning,
        ];
    }

    private function ok(string $control, string $detalle): void
    {
        $this->resultados[] = ['nivel' => 'ok', 'control' => $control, 'detalle' => $detalle];
    }

    private function addError(string $control, string $detalle): void
    {
        $this->resultados[] = ['nivel' => 'error', 'control' => $control, 'detalle' => $detalle];
    }
}
