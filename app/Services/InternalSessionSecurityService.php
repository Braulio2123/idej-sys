<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InternalSessionSecurityService
{
    /**
     * Incrementa la versión de autenticación. El modelo debe guardarse dentro
     * de la misma transacción que modifica la credencial o el estado de acceso.
     */
    public function incrementAuthenticationVersion(Usuario $usuario): int
    {
        $usuario->auth_version = max(1, (int) ($usuario->auth_version ?? 1)) + 1;

        return (int) $usuario->auth_version;
    }

    /**
     * Sincroniza la sesión que debe permanecer abierta después de una acción
     * legítima realizada por el propio usuario.
     */
    public function synchronizeCurrentSession(Request $request, Usuario $usuario): void
    {
        $request->session()->put('auth.version', $usuario->versionAutenticacion());

        if ($usuario->password_changed_at) {
            $request->session()->put(
                'auth.password_changed_at',
                $usuario->password_changed_at->timestamp
            );
        } else {
            $request->session()->forget('auth.password_changed_at');
        }
    }

    /**
     * Elimina sesiones persistidas en base de datos. Para otros drivers, la
     * versión de autenticación obliga el cierre en la siguiente petición.
     */
    public function invalidatePersistedSessions(Usuario $usuario, ?string $exceptSessionId = null): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        $table = (string) config('session.table', 'sessions');
        $connection = config('session.connection') ?: config('database.default');

        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return 0;
        }

        try {
            $schema = Schema::connection($connection);

            if (! $schema->hasTable($table) || ! $schema->hasColumn($table, 'user_id')) {
                return 0;
            }

            $query = DB::connection($connection)
                ->table($table)
                ->where('user_id', $usuario->getKey());

            if (filled($exceptSessionId)) {
                $query->where('id', '!=', $exceptSessionId);
            }

            return $query->delete();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    /**
     * Invalida cualquier enlace de recuperación emitido antes de un cambio
     * administrativo de credenciales o de estado de la cuenta.
     */
    public function revokePasswordResetTokens(Usuario $usuario): void
    {
        try {
            Password::broker('usuarios')->deleteToken($usuario);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Elimina tokens asociados a un correo anterior. Es indispensable cuando
     * se cambia el email de una cuenta, porque el broker ya consultaría el
     * correo nuevo y dejaría la fila anterior sin poder localizarse.
     */
    public function revokePasswordResetTokenForEmail(string $email): void
    {
        $table = (string) config('auth.passwords.usuarios.table', 'password_reset_tokens');

        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return;
        }

        try {
            DB::table($table)
                ->where('email', Str::lower(trim($email)))
                ->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
