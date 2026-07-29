<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use App\Notifications\ResetPasswordUsuarioInterno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class InternalAuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_authenticate_and_receives_generic_message(): void
    {
        $usuario = Usuario::factory()->inactivo()->create();

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'El correo o la contraseña no coinciden con un usuario interno activo.',
        ]);
    }

    public function test_unknown_and_inactive_recovery_requests_return_same_public_response(): void
    {
        Notification::fake();

        $usuario = Usuario::factory()->inactivo()->create();
        $mensaje = 'Si la información coincide con un usuario interno activo, recibirás instrucciones para restablecer tu contraseña.';

        $this->post('/forgot-password', ['email' => $usuario->email])
            ->assertSessionHas('status', $mensaje)
            ->assertSessionHasNoErrors();

        $this->post('/forgot-password', ['email' => 'no-existe@idej.test'])
            ->assertSessionHas('status', $mensaje)
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $usuario->email]);
    }

    public function test_active_user_receives_institutional_reset_notification(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create();

        $this->post('/forgot-password', ['email' => $usuario->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($usuario, ResetPasswordUsuarioInterno::class);
    }

    public function test_reset_notification_contains_internal_route_and_email(): void
    {
        $usuario = Usuario::factory()->create();
        $notification = new ResetPasswordUsuarioInterno('token-prueba');
        $mail = $notification->toMail($usuario);

        $this->assertStringContainsString('/reset-password/token-prueba', $mail->actionUrl);
        $this->assertStringContainsString(urlencode($usuario->email), $mail->actionUrl);
        $this->assertSame('Recuperación de acceso interno IDEJ-SYS', $mail->subject);
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create();

        $this->post('/forgot-password', ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPasswordUsuarioInterno::class, function ($notification) use ($usuario) {
            DB::table('password_reset_tokens')
                ->where('email', $usuario->email)
                ->update(['created_at' => now()->subMinutes(61)]);

            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $usuario->email,
                'password' => 'Nueva-Clave#2026',
                'password_confirmation' => 'Nueva-Clave#2026',
            ])->assertSessionHasErrors('email');

            return true;
        });
    }

    public function test_recent_password_cannot_be_reused_during_recovery(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create([
            'password' => Hash::make('Clave-Actual#2026'),
        ]);

        $this->post('/forgot-password', ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPasswordUsuarioInterno::class, function ($notification) use ($usuario) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $usuario->email,
                'password' => 'Clave-Actual#2026',
                'password_confirmation' => 'Clave-Actual#2026',
            ])->assertSessionHasErrors('password');

            $this->assertTrue(Password::broker('usuarios')->tokenExists($usuario, $notification->token));

            return true;
        });
    }

    public function test_password_reset_increments_authentication_version(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create(['auth_version' => 4]);

        $this->post('/forgot-password', ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPasswordUsuarioInterno::class, function ($notification) use ($usuario) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $usuario->email,
                'password' => 'Nueva-Clave#2026',
                'password_confirmation' => 'Nueva-Clave#2026',
            ])->assertRedirect(route('login'));

            $this->assertSame(5, $usuario->refresh()->auth_version);

            return true;
        });
    }

    public function test_token_issued_before_authentication_version_change_is_rejected(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create([
            'auth_version' => 2,
            'password' => Hash::make('Clave-Actual#2026'),
        ]);

        $this->post('/forgot-password', ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPasswordUsuarioInterno::class, function ($notification) use ($usuario) {
            $usuario->forceFill(['auth_version' => 3])->save();

            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $usuario->email,
                'password' => 'Nueva-Clave#2026',
                'password_confirmation' => 'Nueva-Clave#2026',
            ])->assertSessionHasErrors('email');

            $this->assertTrue(Hash::check('Clave-Actual#2026', $usuario->refresh()->password));

            return true;
        });
    }

    public function test_expired_temporary_password_cannot_start_session(): void
    {
        $usuario = Usuario::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->subMinute(),
        ]);

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_forced_password_user_cannot_skip_profile(): void
    {
        $usuario = Usuario::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addHour(),
            'auth_version' => 2,
        ]);

        $response = $this
            ->actingAs($usuario)
            ->withSession([
                'auth.version' => 2,
                'auth.password_changed_at' => $usuario->password_changed_at->timestamp,
            ])
            ->get('/dashboard');

        $response->assertRedirect(route('profile.edit'));
    }

    public function test_stale_authentication_version_closes_session(): void
    {
        $usuario = Usuario::factory()->create(['auth_version' => 3]);

        $response = $this
            ->actingAs($usuario)
            ->withSession([
                'auth.version' => 2,
                'auth.password_changed_at' => $usuario->password_changed_at->timestamp,
            ])
            ->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_invalidate_other_sessions_and_keep_current_session(): void
    {
        $usuario = Usuario::factory()->create(['auth_version' => 2]);

        $response = $this
            ->actingAs($usuario)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('profile.sessions.destroy-others'));

        $response->assertRedirect(route('profile.edit'));
        $this->assertAuthenticatedAs($usuario);
        $this->assertSame(3, $usuario->refresh()->auth_version);
        $this->assertSame(3, session('auth.version'));
    }

    public function test_profile_email_change_revokes_token_for_previous_email(): void
    {
        Notification::fake();
        $usuario = Usuario::factory()->create(['auth_version' => 1]);
        $emailAnterior = $usuario->email;

        $this->post('/forgot-password', ['email' => $emailAnterior]);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $emailAnterior]);

        $this->actingAs($usuario)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->patch('/profile', [
                'nombre' => $usuario->nombre,
                'email' => 'nuevo-correo@idej.test',
            ])
            ->assertRedirect('/profile');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $emailAnterior]);
        $this->assertSame(2, $usuario->refresh()->auth_version);
        $this->assertSame(2, session('auth.version'));
    }

    public function test_profile_email_change_requires_recent_password_confirmation(): void
    {
        $usuario = Usuario::factory()->create();
        $emailAnterior = $usuario->email;

        $this->actingAs($usuario)
            ->patch('/profile', [
                'nombre' => $usuario->nombre,
                'email' => 'correo-sin-confirmacion@idej.test',
            ])
            ->assertRedirect(route('password.confirm'));

        $this->assertSame($emailAnterior, $usuario->refresh()->email);
    }
}
