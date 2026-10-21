<?php

namespace App\Support;

use App\Models\User;

/**
 * Preferensi notifikasi per akun (disimpan di users.notification_prefs, JSON).
 * Kategori yang bisa dimatikan: data & persetujuan. Notifikasi keamanan SELALU aktif.
 * Dipakai: halaman Profil (modal Preferensi Notifikasi) dan UserNotification::send().
 */
class NotificationPrefs
{
    public const CATEGORIES = [
        'data' => [
            'label' => 'Perubahan data',
            'desc'  => 'Saat data kegiatan Anda ditambahkan, diubah, atau dihapus oleh orang lain.',
            'icon'  => 'edit_note',
            'types' => ['data_updated'],
        ],
        'persetujuan' => [
            'label' => 'Persetujuan',
            'desc'  => 'Pengajuan baru, keputusan disetujui/ditolak, dan komentar pada pengajuan.',
            'icon'  => 'fact_check',
            'types' => ['approval_request', 'approval_approved', 'approval_rejected', 'approval_comment'],
        ],
    ];

    /** Selalu aktif (tidak bisa dimatikan demi keamanan akun). */
    public const LOCKED = [
        'label' => 'Keamanan akun',
        'desc'  => 'Login di perangkat lain, perubahan password, dan penghapusan akun.',
        'icon'  => 'gpp_maybe',
    ];

    public static function categoryOf(string $type): ?string
    {
        foreach (self::CATEGORIES as $key => $cat) {
            if (in_array($type, $cat['types'], true)) {
                return $key;
            }
        }

        return null; // keamanan / tipe lain: tidak bisa dimatikan
    }

    /** @return array<string,bool> kategori => aktif? (default aktif) */
    public static function forUser(User $user): array
    {
        $saved = is_array($user->notification_prefs) ? $user->notification_prefs : [];

        return collect(self::CATEGORIES)->map(fn ($c, $key) => ($saved[$key] ?? true) !== false)->all();
    }

    public static function allows(?User $user, string $type): bool
    {
        $category = self::categoryOf($type);
        if (!$user || $category === null) {
            return true;
        }

        return self::forUser($user)[$category];
    }
}
