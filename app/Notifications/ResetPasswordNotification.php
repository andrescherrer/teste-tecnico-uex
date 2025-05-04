<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/reset-password?token=' . $this->token . '&email=' . $notifiable->email);

        return (new MailMessage)
            ->subject('Redefinição de Senha')
            ->line('Você está recebendo este email porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir Senha', $url)
            ->line('Este link de redefinição de senha expirará em 60 minutos.')
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
    }
}
