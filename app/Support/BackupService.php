<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use ZipArchive;

/**
 * Backup & restore seluruh data SIDA.
 *
 * Format file: ZIP berisi
 *   - manifest.json : info backup (waktu, pembuat, jumlah baris & kolom per tabel, checksum)
 *   - data.json     : isi semua tabel data, {"users":[{...}], "mahasiswa":[...], ...}
 *
 * Yang dibackup hanya tabel data aplikasi (lihat TABLES). Sesi, cache, dan token
 * reset password sengaja tidak ikut karena sifatnya sementara.
 */
class BackupService
{
    public const FORMAT = 1;

    /** Whitelist tabel yang boleh dibackup & dipulihkan (tabel lain di file backup diabaikan). */
    public const TABLES = [
        'users'              => 'Akun Pengguna',
        'mahasiswa'          => 'Profil Mahasiswa',
        'dosen'              => 'Profil Dosen',
        'hak_akses'          => 'Hak Akses',
        'kemahasiswaan'      => 'Kemahasiswaan',
        'lppm_mahasiswa'     => 'LPPM Mahasiswa',
        'lppm_dosen'         => 'LPPM Dosen',
        'rekognisi'          => 'Rekognisi',
        'kerja_sama'         => 'Kerja Sama',
        'user_notifications' => 'Notifikasi',
        'login_attempts'     => 'Log Percobaan Login',
    ];

    /** Tabel inti: backup tanpa salah satunya dianggap tidak lengkap dan ditolak saat restore. */
    public const CORE = [
        'users', 'mahasiswa', 'dosen', 'hak_akses',
        'kemahasiswaan', 'lppm_mahasiswa', 'lppm_dosen', 'rekognisi', 'kerja_sama',
    ];

    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

    public static function dir(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    public static function uploadDir(): string
    {
        $dir = self::dir() . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    public static function zipAvailable(): bool
    {
        return class_exists(ZipArchive::class);
    }

    private static function ensureZip(): void
    {
        if (!self::zipAvailable()) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif di server. Aktifkan extension=zip di php.ini lalu restart server.');
        }
    }

    /** Jumlah baris tiap tabel saat ini (hanya tabel yang ada di database). */
    public static function currentCounts(): array
    {
        $out = [];
        foreach (self::TABLES as $table => $label) {
            if (Schema::hasTable($table)) {
                $out[$table] = DB::table($table)->count();
            }
        }

        return $out;
    }

    /** Buat file backup di storage/app/backups dan kembalikan path-nya. */
    public static function create(string $createdBy, string $label = ''): string
    {
        self::ensureZip();
        set_time_limit(300);

        $tables = array_values(array_filter(array_keys(self::TABLES), fn ($t) => Schema::hasTable($t)));

        $dataPath = tempnam(sys_get_temp_dir(), 'sida-data-');
        $fh = fopen($dataPath, 'wb');
        fwrite($fh, '{');

        $meta = [];
        foreach ($tables as $i => $table) {
            $columns = Schema::getColumnListing($table);
            $orderBy = in_array('id', $columns, true) ? 'id' : $columns[0];

            fwrite($fh, ($i ? ',' : '') . json_encode($table) . ':[');
            $first = true;
            $count = 0;
            DB::table($table)->orderBy($orderBy)->chunk(500, function ($rows) use ($fh, &$first, &$count) {
                foreach ($rows as $row) {
                    fwrite($fh, ($first ? '' : ',') . json_encode($row, self::JSON_FLAGS));
                    $first = false;
                    $count++;
                }
            });
            fwrite($fh, ']');

            $meta[$table] = ['rows' => $count, 'columns' => $columns];
        }
        fwrite($fh, '}');
        fclose($fh);

        $manifest = [
            'app'             => 'SIDA',
            'format'          => self::FORMAT,
            'created_at'      => now()->toIso8601String(),
            'created_by'      => $createdBy,
            'label'           => $label,
            'db_driver'       => DB::getDriverName(),
            'tables'          => $meta,
            'checksum_sha256' => hash_file('sha256', $dataPath),
        ];

        $suffix = $label !== '' ? '-' . preg_replace('/[^A-Za-z0-9]+/', '-', $label) : '';
        $zipPath = self::dir() . DIRECTORY_SEPARATOR . 'sida-backup-' . now()->format('Ymd-His') . $suffix . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($dataPath);
            throw new RuntimeException('Tidak bisa membuat file ZIP di folder storage/app/backups. Periksa izin tulis folder.');
        }
        $zip->addFromString('manifest.json', json_encode($manifest, self::JSON_FLAGS | JSON_PRETTY_PRINT));
        $zip->addFile($dataPath, 'data.json');
        $zip->close();
        @unlink($dataPath);

        return $zipPath;
    }

    /**
     * Validasi file backup tanpa mengubah apa pun, dan kembalikan ringkasannya.
     * Melempar RuntimeException berisi pesan yang aman ditampilkan ke user.
     */
    public static function inspect(string $path): array
    {
        self::ensureZip();

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File bukan arsip ZIP yang valid.');
        }

        try {
            $raw = $zip->getFromName('manifest.json');
            if ($raw === false) {
                throw new RuntimeException('manifest.json tidak ditemukan. Ini bukan file backup SIDA.');
            }
            $manifest = json_decode($raw, true);
            if (!is_array($manifest) || ($manifest['app'] ?? null) !== 'SIDA' || !is_array($manifest['tables'] ?? null)) {
                throw new RuntimeException('Isi manifest tidak dikenali. Ini bukan file backup SIDA.');
            }
            if ((int) ($manifest['format'] ?? 0) > self::FORMAT) {
                throw new RuntimeException('Backup ini dibuat dengan versi SIDA yang lebih baru dan belum didukung.');
            }

            $stream = $zip->getStream('data.json');
            if (!$stream) {
                throw new RuntimeException('data.json tidak ditemukan di dalam backup.');
            }
            $ctx = hash_init('sha256');
            while (!feof($stream)) {
                hash_update($ctx, fread($stream, 65536));
            }
            fclose($stream);
            if (!hash_equals((string) ($manifest['checksum_sha256'] ?? ''), hash_final($ctx))) {
                throw new RuntimeException('Checksum tidak cocok: file backup rusak atau sudah diubah.');
            }
        } finally {
            $zip->close();
        }

        $missingCore = array_values(array_diff(self::CORE, array_keys($manifest['tables'])));
        if ($missingCore) {
            throw new RuntimeException('Backup tidak lengkap, tabel inti tidak ada: ' . implode(', ', $missingCore) . '.');
        }

        $current = self::currentCounts();
        $compare = [];
        $warnings = [];
        foreach (self::TABLES as $table => $label) {
            if (!Schema::hasTable($table)) {
                if (isset($manifest['tables'][$table])) {
                    $warnings[] = "Tabel \"{$table}\" ada di backup tapi tidak ada di database ini, dilewati.";
                }
                continue;
            }
            $inBackup = $manifest['tables'][$table]['rows'] ?? null;
            $compare[] = [
                'table'   => $table,
                'label'   => $label,
                'backup'  => $inBackup,
                'current' => $current[$table] ?? 0,
            ];
            if ($inBackup === null) {
                $warnings[] = "Backup ini belum punya tabel \"{$table}\" (dibuat sebelum fitur itu ada), datanya akan dikosongkan.";
                continue;
            }
            $unknown = array_diff($manifest['tables'][$table]['columns'] ?? [], Schema::getColumnListing($table));
            if ($unknown) {
                $warnings[] = "Kolom tidak dikenal di \"{$table}\" akan diabaikan: " . implode(', ', $unknown) . '.';
            }
        }

        return [
            'created_at' => $manifest['created_at'] ?? null,
            'created_by' => $manifest['created_by'] ?? null,
            'label'      => $manifest['label'] ?? '',
            'compare'    => $compare,
            'warnings'   => $warnings,
        ];
    }

    /**
     * Pulihkan database dari file backup. Otomatis membuat snapshot "pre-restore"
     * dari kondisi saat ini lebih dulu, supaya bisa dibatalkan.
     *
     * @return array{snapshot: string, restored: array<string,int>}
     */
    public static function restore(string $path, string $actorName): array
    {
        self::inspect($path); // validasi penuh sebelum menyentuh data apa pun
        set_time_limit(600);

        $snapshot = self::create($actorName, 'pre-restore');

        $zip = new ZipArchive();
        $zip->open($path);
        $data = json_decode((string) $zip->getFromName('data.json'), true, 512, JSON_THROW_ON_ERROR);
        $zip->close();

        $restored = [];

        Schema::disableForeignKeyConstraints();
        try {
            DB::transaction(function () use ($data, &$restored) {
                foreach (array_keys(self::TABLES) as $table) {
                    if (!Schema::hasTable($table)) {
                        continue;
                    }
                    DB::table($table)->delete();

                    $rows = $data[$table] ?? [];
                    $allowed = array_flip(Schema::getColumnListing($table));
                    foreach (array_chunk($rows, 300) as $chunk) {
                        DB::table($table)->insert(
                            array_map(fn ($row) => array_intersect_key($row, $allowed), $chunk)
                        );
                    }
                    $restored[$table] = count($rows);
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        // Akun & hak akses bisa berubah total: paksa semua orang login ulang.
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->delete();
        }

        return ['snapshot' => basename($snapshot), 'restored' => $restored];
    }

    /** Daftar backup yang tersimpan di server, terbaru dulu. */
    public static function list(): array
    {
        $items = [];
        foreach (glob(self::dir() . DIRECTORY_SEPARATOR . 'sida-backup-*.zip') ?: [] as $file) {
            $info = ['createdBy' => null, 'label' => '', 'rows' => null, 'createdAt' => null];

            if (self::zipAvailable()) {
                $zip = new ZipArchive();
                if ($zip->open($file) === true) {
                    $m = json_decode((string) $zip->getFromName('manifest.json'), true);
                    $zip->close();
                    if (is_array($m)) {
                        $info['createdBy'] = $m['created_by'] ?? null;
                        $info['label'] = $m['label'] ?? '';
                        $info['createdAt'] = $m['created_at'] ?? null;
                        $info['rows'] = array_sum(array_column($m['tables'] ?? [], 'rows'));
                    }
                }
            }

            $items[] = [
                'name'       => basename($file),
                'size'       => filesize($file),
                'mtime'      => filemtime($file),
                'created_by' => $info['createdBy'],
                'label'      => $info['label'],
                'rows'       => $info['rows'],
            ];
        }
        usort($items, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        return $items;
    }

    /** Path file backup tersimpan berdasarkan nama, atau null kalau nama tidak valid / tidak ada. */
    public static function resolveStored(string $name): ?string
    {
        if (!preg_match('/^sida-backup-[A-Za-z0-9._-]+\.zip$/', $name) || str_contains($name, '..')) {
            return null;
        }
        $path = self::dir() . DIRECTORY_SEPARATOR . $name;

        return is_file($path) ? $path : null;
    }

    /** Path file hasil upload sementara berdasarkan token (32 hex), atau null. */
    public static function resolveUpload(string $token): ?string
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        $path = self::uploadDir() . DIRECTORY_SEPARATOR . $token . '.zip';

        return is_file($path) ? $path : null;
    }

    /** Hapus upload sementara yang sudah lebih dari 1 jam. */
    public static function cleanupUploads(): void
    {
        foreach (glob(self::uploadDir() . DIRECTORY_SEPARATOR . '*.zip') ?: [] as $file) {
            if (filemtime($file) < time() - 3600) {
                @unlink($file);
            }
        }
    }
}
