<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Password awal akun yang dibuat massal: nama depan (huruf kecil) + tahun angkatan.
 * Contoh: "Budi Santoso" angkatan 2022 -> "budi2022".
 * Tanpa angkatan (mis. dosen) dipakai tahun terbaru.
 * Gelar di depan nama (Dr., Prof., Ir., H., dst.) dilewati.
 */
class DefaultPassword
{
    private const TITLES = ['dr', 'drs', 'dra', 'ir', 'prof', 'h', 'hj', 'haji', 'hajah', 'ns', 'mr', 'mrs'];

    private const MIN_LENGTH = 6; // sama dengan aturan min:6 di form pengguna

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
