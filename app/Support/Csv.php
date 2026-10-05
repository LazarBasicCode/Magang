<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Helper CSV umum (baca, tulis, unduh) untuk fitur unggah/unduh massal.
 * Tidak tahu apa-apa soal modul tertentu (Kerja Sama, Rekognisi, dst).
 */
class Csv
{
    /** Pemisah ";" karena itu yang dipakai Excel dengan pengaturan regional Indonesia. */
    public const DELIMITER = ';';

    /** Alias judul kolom: diberikan oleh controller lewat bulkAliases() (beda tiap menu). */
    private const ALIASES = [];

    /** Kirim file CSV (UTF-8 + BOM) sebagai unduhan streaming. $writer menerima handle output. */
    public static function download(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca sebagai UTF-8
            // Perintah untuk Excel: pakai ";" sebagai pemisah kolom, apa pun pengaturan regional PC-nya.
            // (Dibuang otomatis oleh Csv::read saat file diunggah kembali.)
            fwrite($out, "sep=" . self::DELIMITER . "\r\n");
            $writer($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function line($out, array $row): void
    {
        fputcsv($out, $row, self::DELIMITER, '"', '');
    }

    /** Cegah "CSV injection": sel yang diawali = + - @ akan dibaca Excel sebagai rumus. */
    public static function safeCell($value)
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $value : $value;
    }

    /** Kebalikan safeCell() saat membaca file yang sebelumnya diunduh dari sistem. */
    public static function unsafeCell(string $value): string
    {
        return strlen($value) > 1 && $value[0] === "'" && in_array($value[1], ['=', '+', '-', '@'], true)
            ? substr($value, 1)
            : $value;
    }

    /** "Guest Lecture" / "guest-lecture" / " guest lecture " -> "guest_lecture". */
    public static function normalizeKey($value): string
    {
        return preg_replace('/[\s\-\/]+/', '_', mb_strtolower(trim((string) $value)));
    }

    /**
     * Terima 2026-09-30 (selalu), serta 30/09/2026 (urutan "dmy") atau 09/30/2026 (urutan "mdy").
     * Hasil: Y-m-d, null kalau kosong, false kalau tidak dikenali.
     * Urutan hari/bulan ditentukan per file lewat detectDateOrder().
     */
    public static function parseDate($value, string $order = 'dmy'): string|false|null
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]/', $value, $m)) {
            $value = $m[1];
        }

        $formats = $order === 'mdy'
            ? ['Y-m-d', 'm/d/Y', 'm-d-Y', 'm.d.Y', 'Y/m/d', 'm/d/y']
            : ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d', 'd/m/y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $err = \DateTime::getLastErrors();
            if ($date && (!$err || ($err['warning_count'] === 0 && $err['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return false;
    }

    /**
     * Tentukan urutan tanggal bergaris miring satu file dari seluruh nilainya.
     * Excel di PC berbahasa Inggris menyimpan 17 Sep 2026 sebagai "9/17/2026" (bulan dulu),
     * sedangkan PC Indonesia "17/9/2026" (hari dulu). Tanggal ber-angka >12 jadi petunjuk.
     *
     * @return string 'dmy' | 'mdy' | 'conflict' (campur) | 'ambiguous' (tidak bisa dipastikan, mis. 9/5/2026)
     */
    public static function detectDateOrder(array $values): string
    {
        $dmy = $mdy = $ambiguous = false;
        foreach ($values as $v) {
            if (!preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})$/', trim((string) $v), $m)) {
                continue; // bukan tanggal bergaris miring (mis. ISO 2026-09-30)
            }
            $a = (int) $m[1];
            $b = (int) $m[2];
            if ($a > 12 && $b <= 12) {
                $dmy = true;
            } elseif ($b > 12 && $a <= 12) {
                $mdy = true;
            } elseif ($a !== $b && $a <= 12 && $b <= 12) {
                $ambiguous = true;
            }
        }

        if ($dmy && $mdy) {
            return 'conflict';
        }
        if ($dmy) {
            return 'dmy';
        }
        if ($mdy) {
            return 'mdy';
        }

        return $ambiguous ? 'ambiguous' : 'dmy';
    }

    /**
     * Baca CSV (UTF-8 / Windows-1252, pemisah ; , atau tab).
     * Baris kosong dan baris yang sel pertamanya diawali "#" dilewati.
     *
     * @param  array<string,string>  $aliases  alias tambahan khusus modul (digabung dengan alias bawaan)
     * @return array{0: array<int,string>, 1: array<int,array{line:int,data:array<string,string>}>}
     *
     * @throws \RuntimeException kalau baris judul kolom tidak ditemukan
     */
    public static function read(string $path, array $aliases = []): array
    {
        $aliases = array_merge(self::ALIASES, $aliases);

        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252'); // CSV biasa dari Excel
        }
        // Excel kadang menambahkan baris "sep=;" paling atas.
        $content = preg_replace('/^sep=.\r?\n/i', '', $content);

        // Tebak pemisah dari baris pertama yang bukan komentar/kosong.
        $delimiter = self::DELIMITER;
        foreach (preg_split('/\r\n|\r|\n/', $content) as $first) {
            if (trim($first) === '' || str_starts_with(ltrim($first, " \t\"'"), '#')) {
                continue;
            }
            $counts = [';' => substr_count($first, ';'), ',' => substr_count($first, ','), "\t" => substr_count($first, "\t")];
            arsort($counts);
            $delimiter = array_key_first($counts);
            break;
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $header = null;
        $rows = [];
        $line = 0;
        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $line++;
            $cells = array_map(fn ($c) => trim((string) $c), $cells);

            if (!array_filter($cells, fn ($c) => $c !== '')) {
                continue; // baris kosong
            }
            if (str_starts_with($cells[0], '#')) {
                continue; // baris komentar / petunjuk
            }

            if ($header === null) {
                $header = array_map(function ($h) use ($aliases) {
                    $h = self::normalizeKey($h);

                    return $aliases[$h] ?? $h;
                }, $cells);
                continue;
            }

            $data = [];
            foreach ($header as $i => $name) {
                if ($name !== '' && !isset($data[$name])) {
                    $data[$name] = self::unsafeCell($cells[$i] ?? '');
                }
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }
        fclose($handle);

        if ($header === null) {
            throw new \RuntimeException('Baris judul kolom tidak ditemukan. Gunakan template dari menu Unduh Template.');
        }

        return [array_values(array_filter($header)), $rows];
    }
}