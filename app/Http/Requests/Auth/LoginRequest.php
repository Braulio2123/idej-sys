<?php

namespace App\Http\Requests\Auth;

use App\Models\Bitacora;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt([
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
            'activo' => true,
        ], false)) {
            RateLimiter::hit($this->throttleKey());

            $this->registrarEventoSeguridad(
                'Intento fallido de inicio de sesión',
                'Email capturado: '.$this->emailSeguro().'. Intento no autenticado desde IP '.$this->ip().'.'
            );

            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no coinciden con un usuario interno activo.',
            ]);
        }

        $usuario = Auth::user();

        if ($usuario && method_exists($usuario, 'requiereCambioPassword') && $usuario->requiereCambioPassword() && $usuario->temporary_password_expires_at?->isPast()) {
            $this->registrarEventoSeguridad(
                'Intento de ingreso con contraseña temporal vencida',
                'Usuario intentó iniciar sesión con una contraseña temporal vencida: '.$usuario->email.'. IP '.$this->ip().'.',
                $usuario->id
            );

            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'La contraseña temporal ya venció. Solicita a Sistemas o Administración una nueva contraseña temporal.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->registrarEventoSeguridad(
            'Bloqueo temporal de inicio de sesión',
            'Demasiados intentos de login para '.$this->emailSeguro().'. IP '.$this->ip().'. Tiempo restante: '.$seconds.' segundos.'
        );

        throw ValidationException::withMessages([
            'email' => 'Demasiados intentos de acceso. Intenta nuevamente en '.$seconds.' segundos.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    private function emailSeguro(): string
    {
        return Str::limit(Str::lower((string) $this->input('email')), 120, '');
    }

    private function registrarEventoSeguridad(string $accion, string $descripcion, ?int $usuarioId = null): void
    {
        try {
            Bitacora::create([
                'usuario_id' => $usuarioId,
                'tipo' => 'Visita',
                'accion' => Str::limit($accion, 120, ''),
                'modulo' => 'Seguridad',
                'descripcion' => $descripcion,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'url' => $this->fullUrl(),
                'metodo_http' => $this->method(),
                'fecha_evento' => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('No fue posible registrar evento de seguridad de login.', [
                'accion' => $accion,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
