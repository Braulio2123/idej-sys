<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\Lock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    private const INPUT_KEY = '_idempotency_key';

    /**
     * Protege formularios internos frente a doble clic, reenvío del navegador,
     * dos pestañas que reutilicen la misma operación y reintentos automáticos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || $request->attributes->get('idej_skip_idempotency') === true) {
            return $next($request);
        }

        try {
            $fingerprint = $this->fingerprint($request);
            $store = (string) config('idej_operations.cache_store', config('cache.default'));
            $completedKey = 'idej:operations:completed:'.$fingerprint;
            $lockKey = 'idej:operations:lock:'.$fingerprint;
            $lockSeconds = max(5, (int) config('idej_operations.lock_seconds', 180));
            $ttlSeconds = max($lockSeconds, (int) config('idej_operations.ttl_seconds', 900));
            $cache = Cache::store($store);

            if ($cache->has($completedKey)) {
                return $this->duplicateResponse($request, false);
            }

            $lock = $cache->lock($lockKey, $lockSeconds);

            if (! $lock->get()) {
                return $this->duplicateResponse($request, true);
            }
        } catch (\Throwable $e) {
            // La idempotencia no debe derribar todo el sistema si el almacén de
            // caché está temporalmente indisponible. Los procesos financieros
            // conservan además sus UUID e índices únicos en base de datos.
            $this->logSafely('warning', 'No fue posible activar el bloqueo de idempotencia.', [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'error' => $e->getMessage(),
            ]);

            return $next($request);
        }

        try {
            $response = $next($request);

            if ($this->operationCompletedSuccessfully($request, $response)) {
                try {
                    $cache->put($completedKey, [
                        'completed_at' => now()->toIso8601String(),
                        'route' => $request->route()?->getName(),
                        'user_id' => $request->user()?->getAuthIdentifier(),
                    ], $ttlSeconds);
                } catch (\Throwable $e) {
                    // La operación principal ya terminó: una falla secundaria de
                    // caché se registra, pero nunca cambia el resultado al usuario.
                    $this->logSafely('warning', 'No fue posible guardar la marca de idempotencia.', [
                        'route' => $request->route()?->getName(),
                        'method' => $request->method(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $response;
        } finally {
            $this->release($lock ?? null);
        }
    }

    private function fingerprint(Request $request): string
    {
        $providedKey = trim((string) $request->input(self::INPUT_KEY, ''));

        if ($providedKey !== '' && Str::isUuid($providedKey)) {
            $operationIdentity = 'uuid:'.strtolower($providedKey);
        } else {
            // Compatibilidad con formularios sin JavaScript y clientes antiguos.
            // Se excluyen secretos y campos volátiles; los archivos se describen
            // por nombre, tamaño y MIME para detectar el mismo reenvío.
            $input = Arr::except($request->all(), [
                '_token',
                '_method',
                self::INPUT_KEY,
                'password',
                'password_confirmation',
                'current_password',
                'password_actual',
            ]);

            $this->sortRecursively($input);

            $files = [];
            foreach ($request->allFiles() as $name => $file) {
                $files[$name] = $this->describeFile($file);
            }
            $this->sortRecursively($files);

            $operationIdentity = 'fallback:'.hash('sha256', json_encode([
                'input' => $input,
                'files' => $files,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }

        $actor = $request->user()?->getAuthIdentifier();

        if ($actor !== null) {
            $actorIdentity = 'user:'.$actor;
        } elseif ($providedKey !== '' && Str::isUuid($providedKey)) {
            // La clave UUID ya identifica la operación. No se incorpora el ID
            // de sesión porque puede rotar entre solicitudes del navegador o
            // durante pruebas, haciendo que el mismo envío parezca distinto.
            $actorIdentity = 'guest:'.hash('sha256', implode('|', [
                (string) $request->ip(),
                (string) $request->userAgent(),
            ]));
        } else {
            // Para clientes antiguos sin UUID, la sesión aporta separación
            // adicional. Si no existe, se conserva una identidad estable por
            // IP y agente para poder detectar el mismo reenvío.
            $guestSession = $request->hasSession() ? $request->session()->getId() : '';
            $actorIdentity = 'guest:'.hash('sha256', implode('|', [
                $guestSession,
                (string) $request->ip(),
                (string) $request->userAgent(),
            ]));
        }

        $route = $request->route()?->getName() ?: $request->path();
        $routeParameters = $this->normalizeRouteParameters($request->route()?->parameters() ?? []);
        $this->sortRecursively($routeParameters);

        return hash('sha256', implode('|', [
            $actorIdentity,
            strtoupper($request->method()),
            (string) $route,
            json_encode($routeParameters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $operationIdentity,
        ]));
    }

    private function operationCompletedSuccessfully(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if (! $request->hasSession()) {
            return true;
        }

        return ! $request->session()->has('errors')
            && ! $request->session()->has('error');
    }

    private function duplicateResponse(Request $request, bool $inProgress): Response
    {
        $message = $inProgress
            ? 'La operación ya se está procesando. No vuelvas a enviarla; espera y verifica el resultado.'
            : 'Esta operación ya fue recibida. Se evitó repetirla; revisa el listado o detalle antes de intentar una acción nueva.';

        $this->logSafely('notice', 'Envío duplicado bloqueado.', [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'in_progress' => $inProgress,
        ]);

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => $message,
                'duplicate' => true,
            ], Response::HTTP_CONFLICT);
        }

        if ($request->hasSession()) {
            $request->session()->flash('info', $message);
        }

        $target = $request->hasSession() ? $request->session()->previousUrl() : null;

        if (! $this->isSafeLocalUrl($request, $target)) {
            $target = $request->headers->get('referer');
        }

        if (! $this->isSafeLocalUrl($request, $target)) {
            $target = $request->getSchemeAndHttpHost().'/dashboard';
        }

        return new RedirectResponse($target);
    }

    private function release(?Lock $lock): void
    {
        if (! $lock) {
            return;
        }

        try {
            $lock->release();
        } catch (\Throwable $e) {
            $this->logSafely('warning', 'No fue posible liberar el bloqueo de idempotencia.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function logSafely(string $level, string $message, array $context = []): void
    {
        try {
            Log::log($level, $message, $context);
        } catch (\Throwable) {
            // Nunca convertir una operación confirmada o un bloqueo efectivo en
            // error 500 únicamente porque el sistema de logs no esté disponible.
        }
    }

    private function normalizeRouteParameters(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parameters[$key] = $this->normalizeRouteParameters($value);
                continue;
            }

            if (is_object($value) && method_exists($value, 'getRouteKey')) {
                $parameters[$key] = get_class($value).':'.(string) $value->getRouteKey();
                continue;
            }

            if ($value instanceof \BackedEnum) {
                $parameters[$key] = $value->value;
                continue;
            }

            if (is_object($value)) {
                $parameters[$key] = get_class($value);
            }
        }

        return $parameters;
    }

    private function isSafeLocalUrl(Request $request, ?string $url): bool
    {
        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        if (! isset($parts['host'])) {
            return str_starts_with($url, '/') && ! str_starts_with($url, '//');
        }

        return strcasecmp((string) $parts['host'], $request->getHost()) === 0
            && (! isset($parts['scheme']) || strcasecmp((string) $parts['scheme'], $request->getScheme()) === 0)
            && (! isset($parts['port']) || (int) $parts['port'] === $request->getPort());
    }

    private function describeFile(mixed $file): mixed
    {
        if (is_array($file)) {
            return array_map(fn ($item) => $this->describeFile($item), $file);
        }

        if (! is_object($file) || ! method_exists($file, 'getClientOriginalName')) {
            return null;
        }

        return [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];
    }

    private function sortRecursively(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursively($item);
            }
        }
        unset($item);

        if (! array_is_list($value)) {
            ksort($value);
        }
    }
}
