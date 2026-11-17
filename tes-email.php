<?php
// Tes alur reset password. Jalankan dari ROOT proyek:  php tes-reset.php
// Hapus file ini setelah selesai dites.

$email = 'halonalady@gmail.com';   // email akun yang direset (kolom users.email)

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Password;

$user = User::where('email', $email)->first();
echo "Akun         : " . ($user ? "{$user->name} (id {$user->id}, role {$user->role})" : 'TIDAK DITEMUKAN') . PHP_EOL;
echo "Mailer       : " . config('mail.default') . PHP_EOL;
echo "Queue        : " . config('queue.default') . PHP_EOL;
echo "Kelas notif  : " . (class_exists(App\Notifications\ResetPasswordNotification::class) ? 'ada' : 'TIDAK ADA') . PHP_EOL;

if (class_exists(App\Notifications\ResetPasswordNotification::class)) {
    $r = new ReflectionClass(App\Notifications\ResetPasswordNotification::class);
    echo "Antrean      : " . ($r->implementsInterface(Illuminate\Contracts\Queue\ShouldQueue::class) ? 'YA (butuh queue:work)' : 'tidak') . PHP_EOL;
}
if (!$user) exit(1);

// Hapus token lama supaya batas 60 detik tidak menghalangi tes ini.
Password::broker()->deleteToken($user);

try {
    $status = Password::sendResetLink(['email' => $email]);
    echo "Status       : $status" . PHP_EOL;
    echo ($status === Password::RESET_LINK_SENT)
        ? "Email reset dikirim. Cari di Kotak Masuk/Spam $email (pengirim: " . config('mail.from.address') . ")." . PHP_EOL
        : "TIDAK dikirim." . PHP_EOL;
} catch (Throwable $e) {
    echo "GAGAL: " . get_class($e) . PHP_EOL . $e->getMessage() . PHP_EOL;
}