<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordUsuarioInterno extends Notification
{
    public function __construct(
        public readonly string $token
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutos = (int) config('auth.passwords.usuarios.expire', 60);
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Recuperación de acceso interno IDEJ-SYS')
            ->greeting('Hola, '.$notifiable->nombre.'.')
            ->line('Se solicitó restablecer la contraseña de tu usuario interno del Instituto de Altos Estudios Jurídicos de Jalisco.')
            ->action('Crear nueva contraseña', $url)
            ->line("El enlace vencerá en {$minutos} minutos y solo puede utilizarse una vez.")
            ->line('Si no realizaste esta solicitud, ignora este mensaje y comunica cualquier actividad sospechosa al área de Sistemas.')
            ->salutation('Área de Sistemas · IDEJ');
    }
}
