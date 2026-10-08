<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mengeluarkan (logout paksa) semua sesi login sebuah akun — dipakai setiap kali password akun berubah,
 * supaya siapa pun yang sempat masuk (termasuk penyusup yang sudah punya sesi) langsung terpental.
 *
 * Cara kerja:
 *  1. Ambil semua sesi akun dari tabel `sessions` (SESSION_DRIVER=database), kecuali $exceptSessionId.
 *  2. Catat "alasan" di cache per id sesi, lalu hapus sesinya. Browser yang terpental masih memegang
 *     cookie id yang sama, jadi saat ia diarahkan ke halaman login, AuthController::showLogin()
 *     membaca catatan itu lewat pullNotice() dan menampilkan pemberitahuan kenapa ia keluar.
 *  3. remember_token diganti, supaya cookie "ingat saya" lama (kalau ada) tidak bisa login ulang.
 *
 * Pemakai: AccountController::changePassword, AuthController::resetPassword,
 *          UserController::update & import (password diubah admin).
 */
class SessionRevoker
{
    public const PASSWORD_CHANGED = 'password_changed'; // pemilik akun mengganti password sendiri
    public const PASSWORD_RESET   = 'password_reset';   // reset lewat tautan email "Lupa password"
    public const PASSWORD_ADMIN   = 'password_admin';   // password diubah admin/superadmin

    private const CACHE_PREFIX = 'session_revoked:';

    /**
     * Keluarkan semua sesi $user (kecuali $exceptSessionId bila diisi).
     *
     * @return int jumlah sesi yang dikeluarkan
     */
    public static function revoke(User $user, string $reason, ?string $exceptSessionId = null): int
    {
        if (config('session.driver') !== 'database') {
            // Sesi tidak bisa ditelusuri per akun di driver lain (file/cookie) — jangan diam-diam gagal.
            Log::warning('SessionRevoker: SESSION_DRIVER bukan "database", sesi akun lain TIDAK bisa dikeluarkan.', ['user_id' => $user->id]);

            return 0;
        }

        $ids = DB::table('sessions')
            ->where('user_id', $user->id)
            ->when($exceptSessionId, fn ($q) => $q->where('id', '!=', $exceptSessionId))
            ->pluck('id');

        // Penanda alasan hanya pelengkap tampilan; kalau cache bermasalah, sesi TETAP harus dihapus.
        try {
            $expires = now()->addMinutes((int) config('session.lifetime', 120));
            $payload = ['reason' => $reason, 'at' => now()->toIso8601String()];
            foreach ($ids as $id) {
                Cache::put(self::CACHE_PREFIX . $id, $payload, $expires);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($ids->isNotEmpty()) {
            DB::table('sessions')->whereIn('id', $ids)->delete();
        }

        DB::table('users')->where('id', $user->id)->update(['remember_token' => Str::random(60)]);

        return $ids->count();
    }

    /**
     * Dipanggil halaman login: kalau sesi pengunjung ini baru saja dikeluarkan paksa,
     * kembalikan [title, message] untuk ditampilkan (hanya sekali), selain itu null.
     *
     * @return array{title:string,message:string}|null
     */
    public static function pullNotice(string $sessionId): ?array
    {
        try {
            $record = Cache::pull(self::CACHE_PREFIX . $sessionId);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        if (!is_array($record) || empty($record['reason'])) {
            return null;
        }

        return [
            'title'   => 'Sesi Anda telah dikeluarkan',
            'message' => self::message($record['reason']),
        ];
    }

    public static function message(string $reason): string
    {
        return match ($reason) {
            self::PASSWORD_CHANGED => 'Password akun ini baru saja diganti, sehingga semua perangkat yang sedang masuk dikeluarkan demi keamanan. Silakan masuk dengan password baru. Kalau bukan Anda yang menggantinya, segera hubungi Admin.',
            self::PASSWORD_RESET   => 'Password akun ini baru saja direset lewat tautan email, sehingga semua sesi login lama dikeluarkan demi keamanan. Silakan masuk dengan password baru.',
            self::PASSWORD_ADMIN   => 'Akun ini dimodifikasi oleh administrator (password diubah), sehingga sesi login Anda dikeluarkan demi keamanan. Silakan masuk kembali dengan password baru dari administrator.',
            default                => 'Akun ini dimodifikasi, sehingga sesi login Anda dikeluarkan demi keamanan. Silakan masuk kembali.',
        };
    }
}
