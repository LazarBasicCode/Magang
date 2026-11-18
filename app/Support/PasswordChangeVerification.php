<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Persetujuan lewat email sebelum pengguna yang SUDAH login boleh mengganti password (menu profil → Ganti Password).
 *
 * Alur:
 *   1. start()    : server membuat token acak, menyimpan HASH-nya di cache (per akun) dan mengikat permintaan ke SESI
 *                   pemohon. Tokennya hanya ada di tautan email.
 *   2. confirm()  : pengguna menekan "Ya, ini saya" di email (bisa dari perangkat lain) -> token ditandai terkonfirmasi.
 *   3. status()   : halaman SIDA yang sedang menunggu memeriksa status. Begitu terkonfirmasi, sesi pemohon diberi izin
 *                   singkat (GRANT_MINUTES) dan token dihabiskan -> pop-up buat password baru langsung dibuka.
 *   4. isVerified(): AccountController::changePassword hanya menerima password baru bila izin itu ada.
 *
 * Izin menempel di SESI pemohon, jadi sesi lain milik akun yang sama tidak bisa memakainya.
 */
final class PasswordChangeVerification
{
    /** Masa berlaku tautan di email (menit). */
    public const LINK_MINUTES = 15;

    /** Setelah terkonfirmasi: waktu untuk menyimpan password baru (menit). */
    public const GRANT_MINUTES = 10;

    /** Jeda minimal antar pengiriman email (detik). */
    public const RESEND_SECONDS = 60;

    private const SESSION_REQUEST = 'pw_change_request';
    private const SESSION_GRANT = 'pw_change_verified_until';

    private static function key(User $user): string
    {
        return 'pw_change:' . $user->id;
    }

    /** Mulai permintaan baru (menggantikan yang lama) dan kembalikan token untuk tautan email. */
    public static function start(User $user): string
    {
        $token = Str::random(48);

        Cache::put(self::key($user), [
            'hash'      => hash('sha256', $token),
            'confirmed' => false,
            'sent_at'   => time(),
        ], now()->addMinutes(self::LINK_MINUTES));

        session([self::SESSION_REQUEST => hash('sha256', $token)]);
        session()->forget(self::SESSION_GRANT);

        return $token;
    }

    /** Batalkan permintaan (mis. email gagal terkirim). */
    public static function forget(User $user): void
    {
        Cache::forget(self::key($user));
        session()->forget(self::SESSION_REQUEST);
    }

    /** Sisa detik sebelum email boleh dikirim ulang (0 = boleh sekarang). */
    public static function secondsUntilResend(User $user): int
    {
        $entry = Cache::get(self::key($user));
        if (! is_array($entry)) {
            return 0;
        }

        return max(0, (int) ($entry['sent_at'] ?? 0) + self::RESEND_SECONDS - time());
    }

    /** Sisa detik masa berlaku tautan email yang sedang menunggu (0 = tidak ada / sudah habis). */
    public static function secondsUntilExpiry(User $user): int
    {
        $entry = Cache::get(self::key($user));
        if (! is_array($entry)) {
            return 0;
        }

        return max(0, (int) ($entry['sent_at'] ?? 0) + self::LINK_MINUTES * 60 - time());
    }

    /** Dipanggil dari tautan email. True bila token cocok dengan permintaan yang masih berlaku. */
    public static function confirm(User $user, string $token): bool
    {
        $entry = Cache::get(self::key($user));
        if (! is_array($entry) || ! hash_equals((string) ($entry['hash'] ?? ''), hash('sha256', $token))) {
            return false;
        }

        $entry['confirmed'] = true;
        Cache::put(self::key($user), $entry, now()->addMinutes(self::LINK_MINUTES));

        return true;
    }

    /**
     * Status permintaan milik SESI ini.
     *
     * @return 'verified'|'pending'|'expired'|'none'
     */
    public static function status(User $user): string
    {
        if (self::isVerified()) {
            return 'verified';
        }

        $mine = (string) session(self::SESSION_REQUEST, '');
        if ($mine === '') {
            return 'none';
        }

        $entry = Cache::get(self::key($user));
        if (! is_array($entry) || ! hash_equals((string) ($entry['hash'] ?? ''), $mine)) {
            session()->forget(self::SESSION_REQUEST);

            return 'expired';
        }

        if (! empty($entry['confirmed'])) {
            session([self::SESSION_GRANT => now()->addMinutes(self::GRANT_MINUTES)->timestamp]);
            session()->forget(self::SESSION_REQUEST);
            Cache::forget(self::key($user));

            return 'verified';
        }

        return 'pending';
    }

    /** Apakah sesi ini sudah boleh menyimpan password baru? */
    public static function isVerified(): bool
    {
        return (int) session(self::SESSION_GRANT, 0) > time();
    }

    /** "ha***@gmail.com" — agar alamat lengkap tidak terbaca orang yang mengintip layar. */
    public static function maskEmail(?string $email): string
    {
        $email = (string) $email;
        if (! str_contains($email, '@')) {
            return '';
        }

        [$local, $domain] = explode('@', $email, 2);
        $keep = mb_substr($local, 0, min(2, max(1, mb_strlen($local) - 1)));

        return $keep . str_repeat('*', max(3, mb_strlen($local) - mb_strlen($keep))) . '@' . $domain;
    }
}
