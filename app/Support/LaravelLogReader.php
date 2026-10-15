<?php

namespace App\Support;

/**
 * Pembaca file log Laravel (storage/logs/*.log) untuk halaman Log Aktivitas.
 *
 * - Hanya membaca BAGIAN TERBARU file (TAIL_BYTES terakhir) supaya file log besar tidak memenuhi memori.
 * - Satu entri = satu baris berawalan "[YYYY-MM-DD HH:MM:SS] env.LEVEL: pesan", baris sesudahnya
 *   (stack trace dst.) menjadi detail entri tersebut.
 * Kelas ini murni PHP (tanpa dependensi Laravel) supaya mudah diuji.
 */
class LaravelLogReader
{
    /** Batas bacaan dari akhir file. */
    public const TAIL_BYTES = 3 * 1024 * 1024;

    /** Batas panjang teks yang dikirim ke browser per entri. */
    public const MAX_MESSAGE = 2000;
    public const MAX_DETAILS = 12000;

    /** Kelompok level untuk tab filter. */
    public const GROUPS = [
        'error'   => ['emergency', 'alert', 'critical', 'error'],
        'warning' => ['warning'],
        'info'    => ['notice', 'info'],
        'debug'   => ['debug'],
    ];

    private const ENTRY_RE = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}:?\d{2})?)\] ([A-Za-z0-9_-]+)\.([A-Za-z]+): ?(.*)$/';

    /**
     * Daftar file *.log di folder, terbaru dulu.
     *
     * @return array<int, array{name:string, size:int, modified:int}>
     */
    public static function files(string $dir): array
    {
        $out = [];
        foreach (glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.log') ?: [] as $path) {
            if (is_file($path)) {
                $out[] = ['name' => basename($path), 'size' => (int) filesize($path), 'modified' => (int) filemtime($path)];
            }
        }
        usort($out, fn ($a, $b) => $b['modified'] <=> $a['modified'] ?: strcmp($b['name'], $a['name']));

        return $out;
    }

    /**
     * Baca bagian terbaru file dan urai jadi entri (TERBARU DULU).
     *
     * @return array{entries: array<int, array<string,mixed>>, truncated: bool}
     */
    public static function read(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ['entries' => [], 'truncated' => false];
        }

        $size = (int) filesize($path);
        $truncated = $size > self::TAIL_BYTES;
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            return ['entries' => [], 'truncated' => false];
        }
        if ($truncated) {
            fseek($fh, $size - self::TAIL_BYTES);
        }
        $text = (string) stream_get_contents($fh);
        fclose($fh);

        $entries = self::parse($text);

        return ['entries' => array_reverse($entries), 'truncated' => $truncated];
    }

    /**
     * Urai teks log mentah jadi entri berurutan (lama -> baru).
     * Teks sebelum entri pertama (mis. potongan entri yang terpotong di awal bacaan) dibuang.
     *
     * @return array<int, array{time:string, env:string, level:string, group:string, message:string, details:string}>
     */
    public static function parse(string $text): array
    {
        $entries = [];
        $cur = null;
        $detail = [];

        $flush = function () use (&$entries, &$cur, &$detail) {
            if ($cur === null) {
                return;
            }
            $details = trim(implode("\n", $detail));
            $cur['message'] = self::cut($cur['message'], self::MAX_MESSAGE);
            $cur['details'] = self::cut($details, self::MAX_DETAILS);
            $entries[] = $cur;
            $cur = null;
            $detail = [];
        };

        foreach (preg_split('/\r\n|\n|\r/', $text) ?: [] as $line) {
            if (preg_match(self::ENTRY_RE, $line, $m)) {
                $flush();
                $level = strtolower($m[3]);
                $cur = [
                    'time'    => $m[1],
                    'env'     => $m[2],
                    'level'   => $level,
                    'group'   => self::groupOf($level),
                    'message' => $m[4],
                    'details' => '',
                ];
            } elseif ($cur !== null) {
                $detail[] = $line;
            }
        }
        $flush();

        return $entries;
    }

    public static function groupOf(string $level): string
    {
        foreach (self::GROUPS as $group => $levels) {
            if (in_array($level, $levels, true)) {
                return $group;
            }
        }

        return 'info';
    }

    private static function cut(string $s, int $max): string
    {
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max) . "\n… (dipotong, terlalu panjang)" : $s;
    }
}
