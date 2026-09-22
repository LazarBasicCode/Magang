<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('SIDA · Permintaan Reset Password')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun SIDA Anda.')
            ->action('Atur Ulang Password', $url)
            ->line('Tautan ini hanya berlaku selama '.$expireMinutes.' menit.')
            ->line('Jika Anda tidak merasa meminta reset password, abaikan saja email ini — password Anda tidak akan berubah.');
    }
}
