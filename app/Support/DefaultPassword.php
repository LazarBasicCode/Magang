<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Password default akun.
 *
 * - Mahasiswa & dosen: password awal = NIM / NIDN akun itu sendiri (lihat forIdentifier()).
 *   Dipakai saat akun dibuat dan saat admin menekan "Reset ke default". Akun yang memakainya
 *   ditandai users.must_change_password = true sehingga dipaksa ganti lewat pop-up di dashboard.
 * - make(): pola lama nama depan + tahun angkatan (mis. "Budi Santoso" 2022 -> "budi2022"),
 *   dipertahankan untuk kompatibilitas. Gelar di depan nama (Dr., Prof., Ir., H., dst.) dilewati.
 */
class DefaultPassword
{
    private const TITLES = ['dr', 'drs', 'dra', 'ir', 'prof', 'h', 'hj', 'haji', 'hajah', 'ns', 'mr', 'mrs'];

    private const MIN_LENGTH = 6; // sama dengan aturan min:6 di form pengguna

    /** Role yang memakai NIM/NIDN sebagai password default. */
    public const ROLES = ['mahasiswa', 'dosen'];

    /** Apakah role ini memakai password default NIM/NIDN? */
    public static function appliesTo(?string $role): bool
    {
        return in_array($role, self::ROLES, true);
    }

    /** Password default = NIM/NIDN apa adanya (tanpa spasi di pinggir). */
    public static function forIdentifier(string $identifier): string
    {
        return trim($identifier);
    }

    public static function firstName(string $name): string
    {
        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $token) {
            $clean = Str::lower(Str::ascii(preg_replace('/[^\p{L}\p{N}]/u', '', $token) ?? ''));
            if ($clean !== '' && !in_array($clean, self::TITLES, true)) {
                return $clean;
            }
        }

        return 'user';
    }

    public static function make(string $name, ?int $angkatan = null): string
    {
        $password = self::firstName($name) . ($angkatan ?: now()->year);

        return str_pad($password, self::MIN_LENGTH, '0');
    }
}
