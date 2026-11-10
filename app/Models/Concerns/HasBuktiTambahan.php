<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Kolom `bukti_tambahan`: daftar bukti tambahan, tiap item berupa ['nama' => ..., 'url' => ...].
 * `nama` diisi pengisi data (mis. "Sertifikat", "Foto kegiatan") supaya verifikator tahu itu bukti apa.
 *
 * Di database disimpan sebagai JSON array (kolom TEXT). Data lama tetap terbaca:
 *  - JSON array berisi link biasa  -> [['nama' => '', 'url' => ...], ...]
 *  - satu link biasa (bukan JSON)  -> [['nama' => '', 'url' => ...]]
 * Setter menerima array (item string atau ['nama','url']) atau string multibaris
 * (satu bukti per baris: "Nama | URL" atau cukup "URL", mis. dari impor Excel).
 * Dipakai Rekognisi, Kemahasiswaan, Kerja Sama.
 */
trait HasBuktiTambahan
{
    /** Rapikan satu item (string link / array nama+url) -> ['nama','url'] atau null bila link kosong. */
    protected static function normalizeBuktiItem($item): ?array
    {
        if (is_string($item)) $item = ['nama' => '', 'url' => $item];
        if (!is_array($item)) return null;
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '') return null;
        return ['nama' => mb_substr(trim((string) ($item['nama'] ?? '')), 0, 100), 'url' => $url];
    }

    /** Daftar item mentah -> daftar rapi tanpa item kosong & tanpa link ganda. */
    protected static function normalizeBuktiList($items): array
    {
        $out = [];
        foreach ((array) $items as $item) {
            $n = static::normalizeBuktiItem($item);
            if ($n && !isset($out[$n['url']])) $out[$n['url']] = $n;
        }
        return array_values($out);
    }

    /**
     * Teks multibaris (impor Excel) -> daftar item, atau null bila kosong.
     * Satu bukti per baris: "Nama | URL" atau "URL".
     */
    public static function parseBuktiTambahan(?string $text): ?array
    {
        $items = [];
        foreach (preg_split('/\R/', (string) $text, -1, PREG_SPLIT_NO_EMPTY) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            // "Nama | https://link" -> nama + link. Tanpa pola itu, seluruh baris dianggap link
            // (sehingga tanda | di dalam URL tidak memotong link).
            $items[] = preg_match('/^(.*?)\s*\|\s*(https?:\/\/\S+)$/iu', $line, $m)
                ? ['nama' => trim($m[1]), 'url' => $m[2]]
                : ['nama' => '', 'url' => $line];
        }
        return static::normalizeBuktiList($items) ?: null;
    }

    /** Daftar item -> teks multibaris (ekspor Excel), kebalikan parseBuktiTambahan. */
    public static function formatBuktiTambahan(array $items): string
    {
        return implode("\n", array_map(
            fn ($b) => ($b['nama'] !== '' ? $b['nama'] . ' | ' : '') . $b['url'],
            $items
        ));
    }

    protected function buktiTambahan(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null || trim((string) $value) === '') return [];
                $decoded = json_decode($value, true);
                if (is_array($decoded)) return static::normalizeBuktiList($decoded);
                return static::normalizeBuktiList([trim((string) $value)]); // data lama: satu link biasa
            },
            set: function ($value) {
                if (is_string($value)) $value = static::parseBuktiTambahan($value) ?? [];
                $list = static::normalizeBuktiList($value);
                return ['bukti_tambahan' => $list ? json_encode($list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null];
            },
        );
    }
}
