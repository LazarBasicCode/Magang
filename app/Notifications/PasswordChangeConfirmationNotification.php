<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email konfirmasi untuk pengguna yang sedang login dan ingin mengganti password (menu profil → Ganti Password).
 * Email hanya berisi persetujuan "Ya, ini saya"; password baru dibuat di halaman SIDA yang sedang menunggu.
 * Dikirim ke alamat email pemulihan akun (anonymous notifiable), lihat PasswordChangeController::request().
 */
class PasswordChangeConfirmationNotification extends Notification
{
    public function __construct(
        public string $name,
        public string $url,
        public int $minutes,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Konfirmasi ganti password akun SIDA')
            ->greeting('Halo, ' . $this->name . '!')
            ->line('Ada yang sedang masuk ke akun SIDA Anda dan ingin mengganti password. Apakah itu Anda?')
            ->action('✔ Ya, ini saya', $this->url)
            ->line("Setelah Anda menekan tombol, kembali ke halaman SIDA yang sedang terbuka: halaman akan otomatis lanjut ke pembuatan password baru. Tautan berlaku {$this->minutes} menit.")
            ->line('Jika bukan Anda, abaikan email ini dan segera hubungi Admin. Password Anda tidak akan berubah.');
    }
}
