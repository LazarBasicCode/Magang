<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $table = 'hak_akses';

    protected $fillable = [
        'user_id',
        'menu',
        'level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semua menu yang diatur lewat Hak Akses, beserta label & ikon untuk UI.
     * "kerja_sama" tidak disebutkan eksplisit di daftar default milik pengguna,
     * jadi levelnya mengikuti aturan yang sama seperti "kemahasiswaan" per role
     * (lihat defaultsForRole()).
     */
    public const MENUS = [
        'dashboard'       => ['label' => 'Dashboard', 'icon' => 'dashboard'],
        'kemahasiswaan'   => ['label' => 'Kemahasiswaan', 'icon' => 'school'],
        'lppm_mahasiswa'  => ['label' => 'LPPM Mahasiswa', 'icon' => 'person'],
        'lppm_dosen'      => ['label' => 'LPPM Dosen', 'icon' => 'co_present'],
        'rekognisi'       => ['label' => 'Rekognisi', 'icon' => 'workspace_premium'],
        'kerja_sama'      => ['label' => 'Kerja Sama', 'icon' => 'handshake'],
        'data_master'     => ['label' => 'Data Master', 'icon' => 'manage_accounts'],
        'hak_akses'       => ['label' => 'Hak Akses', 'icon' => 'shield_person'],
        // Khusus admin/superadmin: mahasiswa & dosen tidak pernah punya akses
        // ke menu ini (lihat defaultsForRole dan filter di halaman Hak Akses).
        'log'             => ['label' => 'Log Aktivitas', 'icon' => 'history'],
    ];

    /**
     * Menu yang memang hanya diperuntukkan admin/superadmin. Dipakai untuk
     * menyembunyikannya total dari daftar menu saat mengatur hak akses milik
     * mahasiswa/dosen (bukan cuma dikunci ke "none", tapi tidak ditampilkan
     * sama sekali karena memang tidak relevan buat role tsb).
     */
    public const ADMIN_ONLY_MENUS = ['log', 'hak_akses', 'data_master'];

    public const LEVELS = ['none', 'readonly', 'biasa', 'penuh'];

    /** Urutan tingkatan akses, dipakai untuk perbandingan "minimal level X". */
    public const RANK = [
        'none'     => 0,
        'readonly' => 1,
        'biasa'    => 2,
        'penuh'    => 3,
    ];

    public static function isValidLevel(?string $level): bool
    {
        return in_array($level, self::LEVELS, true);
    }

    /**
     * Default hak akses per role, dipakai selama superadmin belum pernah
     * mengubah/menyimpan baris hak_akses eksplisit untuk user tsb.
     */
    public static function defaultsForRole(string $role): array
    {
        return match ($role) {
            'superadmin' => array_fill_keys(array_keys(self::MENUS), 'penuh'),
            'admin' => [
                'dashboard'      => 'penuh',
                'kemahasiswaan'  => 'penuh',
                'lppm_mahasiswa' => 'penuh',
                'lppm_dosen'     => 'penuh',
                'rekognisi'      => 'penuh',
                'kerja_sama'     => 'penuh',
                'data_master'    => 'penuh',
                'hak_akses'      => 'none', // tergantung diatur superadmin
                'log'            => 'none', // tergantung diatur superadmin
            ],
            'dosen' => [
                'dashboard'      => 'biasa',
                'kemahasiswaan'  => 'biasa',
                'lppm_mahasiswa' => 'biasa',
                'lppm_dosen'     => 'biasa',
                'rekognisi'      => 'biasa',
                'kerja_sama'     => 'biasa',
                'data_master'    => 'none',
                'hak_akses'      => 'none',
                'log'            => 'none', // dosen tidak pernah punya akses log
            ],
            'mahasiswa' => [
                'dashboard'      => 'biasa',
                'kemahasiswaan'  => 'biasa',
                'lppm_mahasiswa' => 'biasa',
                'lppm_dosen'     => 'none',
                'rekognisi'      => 'biasa',
                'kerja_sama'     => 'biasa',
                'data_master'    => 'none',
                'hak_akses'      => 'none',
                'log'            => 'none', // mahasiswa tidak pernah punya akses log
            ],
            default => array_fill_keys(array_keys(self::MENUS), 'none'),
        };
    }

    /**
     * Ringkas status keseluruhan seorang user dari kumpulan level per-menu-nya,
     * sesuai aturan: semua "none" -> Nonaktif, semua "readonly" -> Read,
     * kalau ada "biasa"/"penuh" di salah satu menu -> Aktif.
     */
    public static function summarizeStatus(array $levelsByMenu): string
    {
        $levels = array_values($levelsByMenu);
        if (empty($levels)) return 'nonaktif';

        $hasActive = false;
        $hasReadonly = false;
        $hasNone = false;

        foreach ($levels as $level) {
            if (in_array($level, ['biasa', 'penuh'], true)) $hasActive = true;
            elseif ($level === 'readonly') $hasReadonly = true;
            else $hasNone = true;
        }

        if ($hasActive) return 'aktif';
        if ($hasReadonly) return 'read';
        return 'nonaktif';
    }
}
