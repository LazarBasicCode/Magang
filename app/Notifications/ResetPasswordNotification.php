<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email tautan reset password (bahasa Indonesia). Tautan mengarah ke halaman
 * /reset-password milik SIDA, tempat pengguna membuat password baru + konfirmasi.
 */
class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        // Email hanya berisi konfirmasi. Password TIDAK dibuat di email: setelah tombol diklik,
        // sistem SIDA membuka pop-up untuk membuat & mengonfirmasi password baru.
        return (new MailMessage)
            ->subject('Konfirmasi reset password akun SIDA')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Seseorang meminta reset password untuk akun SIDA Anda. Apakah itu Anda?')
            ->action('✔ Ya, ini saya', $url)
            ->line("Setelah Anda menekan tombol, halaman SIDA akan terbuka dan Anda bisa membuat password baru di sana. Tautan berlaku {$minutes} menit.")
            ->line('Jika bukan Anda, abaikan email ini. Password Anda tidak akan berubah.');
    }
}
