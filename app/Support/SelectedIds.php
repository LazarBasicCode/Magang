<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Membaca daftar id data terpilih dari request (fitur "pilih beberapa baris" pada tabel).
 * Menerima ids sebagai array [1,2,3] atau string "1,2,3". Hanya angka bulat positif yang dipakai.
 * Kembalian [] = tidak ada pilihan (pemanggil yang menentukan artinya: abaikan filter / tolak).
 */
class SelectedIds
{
    public const MAX = 2000;

    /** @return int[] */
    public static function from(Request $request): array
    {
        $raw = $request->input('ids');
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        if (!is_array($raw)) {
            return [];
        }

        $ids = collect($raw)
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => ctype_digit($v) && $v !== '0')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        abort_if($ids->count() > self::MAX, 422, 'Terlalu banyak data terpilih (maksimal ' . self::MAX . ').');

        return $ids->all();
    }
}
