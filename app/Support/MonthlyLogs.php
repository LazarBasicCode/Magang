<?php

namespace App\Support;

/**
 * Perapian berkas log: gabungkan berkas harian lama (prefix-YYYY-MM-DD.log) menjadi berkas
 * bulanan (prefix-YYYY-MM.log), lalu hapus bulan-bulan yang melebihi batas simpan.
 * Murni PHP (tanpa dependensi Laravel) supaya mudah diuji. Dipakai perintah `logs:merge-monthly`.
 */
class MonthlyLogs
{
    /**
     * @return array{merged:int, months:array<int,string>, deleted:array<int,string>}
     */
    public static function merge(string $dir, string $prefix, int $keep): array
    {
        $dir = rtrim($dir, '/\\');
        $quoted = preg_quote($prefix, '/');
        $perMonth = [];

        foreach (glob($dir . DIRECTORY_SEPARATOR . $prefix . '-*.log') ?: [] as $path) {
            if (preg_match('/^' . $quoted . '-(\d{4}-\d{2})-\d{2}\.log$/', basename($path), $m)) {
                $perMonth[$m[1]][] = $path;
            }
        }
        ksort($perMonth);

        $merged = 0;
        foreach ($perMonth as $month => $files) {
            sort($files); // urut tanggal naik
            $target = $dir . DIRECTORY_SEPARATOR . "{$prefix}-{$month}.log";

            // Entri harian lebih tua daripada isi berkas bulanan yang mungkin sudah ada -> taruh di depan.
            $content = '';
            foreach ($files as $f) {
                $content .= rtrim((string) file_get_contents($f), "\r\n") . "\n";
            }
            if (is_file($target)) {
                $content .= (string) file_get_contents($target);
            }

            $tmp = $target . '.tmp';
            if (file_put_contents($tmp, $content) === false || !rename($tmp, $target)) {
                @unlink($tmp);
                continue; // gagal: berkas harian dibiarkan utuh
            }
            foreach ($files as $f) {
                @unlink($f);
                $merged++;
            }
        }

        return ['merged' => $merged, 'months' => array_keys($perMonth), 'deleted' => self::prune($dir, $prefix, $keep)];
    }

    /**
     * Simpan hanya $keep berkas bulanan terbaru; yang lebih lama dihapus.
     *
     * @return array<int,string> nama berkas yang dihapus
     */
    public static function prune(string $dir, string $prefix, int $keep): array
    {
        if ($keep <= 0) {
            return [];
        }

        $files = [];
        foreach (glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $prefix . '-*.log') ?: [] as $path) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-\d{4}-\d{2}\.log$/', basename($path))) {
                $files[] = $path;
            }
        }
        rsort($files); // bulan terbaru dulu

        $deleted = [];
        foreach (array_slice($files, $keep) as $path) {
            if (@unlink($path)) {
                $deleted[] = basename($path);
            }
        }

        return $deleted;
    }
}
