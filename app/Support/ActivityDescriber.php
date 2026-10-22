<?php

namespace App\Support;

/**
 * Mengubah informasi sebuah request (nama route, parameter, ringkasan respons)
 * menjadi kalimat aktivitas berbahasa Indonesia untuk log, mis.
 *
 *   menghapus data Kemahasiswaan "Juara 1 Lomba Karya Tulis" (#12)
 *   mengunggah file Excel "data.xlsx" ke LPPM Dosen — 20 ditambahkan, 3 diperbarui
 *
 * Sengaja bebas dari ketergantungan Laravel (cuma array & string) supaya
 * mudah diuji, dan TIDAK PERNAH menerima isi request (password, token, dsb.) —
 * hanya nama route, id/label data, nama file, dan angka ringkasan.
 */
final class ActivityDescriber
{
    /** Awalan nama route -> nama modul untuk ditampilkan. */
    private const MODULES = [
        'kemahasiswaan'     => 'Kemahasiswaan',
        'lppm.mahasiswa'    => 'LPPM Mahasiswa',
        'lppm.dosen'        => 'LPPM Dosen',
        'lppm.rekognisi'    => 'Rekognisi',
        'kerja-sama'        => 'Kerja Sama',
        'data-master.users' => 'Data Master (akun pengguna)',
    ];

    /** Slug menu pada URL /pilihan/{menu} & /cetak/{menu}. */
    private const MENU_SLUGS = [
        'kemahasiswaan'  => 'Kemahasiswaan',
        'lppm-mahasiswa' => 'LPPM Mahasiswa',
        'lppm-dosen'     => 'LPPM Dosen',
        'rekognisi'      => 'Rekognisi',
        'kerja-sama'     => 'Kerja Sama',
        'data-master'    => 'Data Master',
    ];

    /** Atribut yang dipakai sebagai "nama" sebuah data, urut prioritas. */
    private const LABEL_KEYS = [
        'nama_kegiatan', 'judul', 'judul_kegiatan', 'mitra', 'name', 'nama', 'nim_nidn',
    ];

    /** Route yang sengaja TIDAK dicatat middleware (sudah dicatat tempat lain / terlalu berisik). */
    private const IGNORED = [
        'persetujuan.aksi',      // dicatat langsung oleh Approval::log() lengkap dengan alasan
        'persetujuan.riwayat',
        'notifications.read',
        'notifications.mark-all-read',
        'logout',                // dicatat oleh AuthController (user sudah keluar saat middleware jalan)
    ];

    public static function isIgnored(?string $routeName): bool
    {
        return $routeName === null || in_array($routeName, self::IGNORED, true);
    }

    /** Route GET yang tetap perlu dicatat (unduh / buka cetak). Selain itu GET dianggap sekadar membuka halaman. */
    public static function isLoggedGet(?string $routeName): bool
    {
        if ($routeName === null) {
            return false;
        }

        return str_ends_with($routeName, '.export')
            || str_ends_with($routeName, '.template')
            || $routeName === 'backup.download'
            || $routeName === 'cetak.show';
    }

    /**
     * @param  string  $routeName
     * @param  array   $ctx  ['model' => ['type'=>..., 'id'=>..., 'label'=>...]|null,
     *                        'params' => array route string params,
     *                        'file' => nama file unggahan|null,
     *                        'count' => jumlah id terpilih|null,
     *                        'summary' => array JSON respons (created/updated/unchanged/deleted...)]
     * @return array{action:string,text:string}|null  null = tidak perlu dicatat
     */
    public static function describe(string $routeName, array $ctx = []): ?array
    {
        $module = self::moduleFor($routeName);
        $subject = self::subject($ctx['model'] ?? null);
        $summary = $ctx['summary'] ?? [];
        $file = isset($ctx['file']) && $ctx['file'] !== '' ? ' "' . self::clean($ctx['file'], 120) . '"' : '';

        // --- /pilihan/{menu}/* dan /cetak/{menu} (baris terpilih / cetak laporan) ---
        if (str_starts_with($routeName, 'pilihan.') || $routeName === 'cetak.show') {
            $menuLabel = self::MENU_SLUGS[$ctx['params']['menu'] ?? ''] ?? ($ctx['params']['menu'] ?? 'menu');
            $n = (int) ($ctx['count'] ?? 0);
            $jumlah = $n > 0 ? "{$n} data terpilih" : 'data terpilih';

            return match ($routeName) {
                'pilihan.export' => ['export', "mengekspor {$jumlah} {$menuLabel} ke Excel"],
                'pilihan.cetak'  => ['print', "mencetak {$jumlah} {$menuLabel}"],
                'pilihan.hapus'  => ['delete', 'menghapus ' . (isset($summary['deleted']) && is_array($summary['deleted']) ? count($summary['deleted']) . ' data' : $jumlah) . " {$menuLabel}"],
                'pilihan.ubah'   => ['update', "mengubah massal {$jumlah} {$menuLabel}"],
                'cetak.show'     => ['print', "membuka cetak laporan {$menuLabel}"],
                default          => null,
            };
        }

        // --- akun sendiri ---
        if ($routeName === 'account.password') {
            return ['password', 'mengganti password akunnya'];
        }
        if ($routeName === 'account.recovery-email') {
            return ['account', 'mengubah email pemulihan akunnya'];
        }
        if ($routeName === 'account.avatar') {
            return ['upload', "mengunggah foto profil{$file}"];
        }
        if ($routeName === 'account.avatar.remove') {
            return ['account', 'menghapus foto profilnya'];
        }

        // --- hak akses ---
        if ($routeName === 'hak-akses.update') {
            return ['permission', 'mengubah hak akses' . ($subject ? " pengguna {$subject}" : '')];
        }

        // --- backup database ---
        if (str_starts_with($routeName, 'backup.')) {
            $f = $ctx['params']['file'] ?? null;
            $named = $f ? ' "' . self::clean((string) $f, 120) . '"' : '';

            return match ($routeName) {
                'backup.create'   => ['backup', 'membuat backup database'],
                'backup.download' => ['backup', "mengunduh file backup{$named}"],
                'backup.inspect'  => ['upload', "mengunggah file backup untuk diperiksa{$file}"],
                'backup.restore'  => ['restore', 'MERESTORE database dari file backup' . ($file !== '' ? $file : $named)],
                'backup.destroy'  => ['delete', "menghapus file backup{$named}"],
                default           => null,
            };
        }

        // --- tambah calon mahasiswa/dosen dari Data Master ---
        if ($routeName === 'data-master.users.people') {
            return ['create', 'menambahkan mahasiswa/dosen baru lewat Data Master' . ($subject ? " — {$subject}" : '')];
        }

        // --- CRUD & import/export per modul ---
        if ($module !== null) {
            $suffix = substr($routeName, strrpos($routeName, '.') + 1);

            switch ($suffix) {
                case 'store':
                    return ['create', "menambah data {$module}" . ($subject ? " {$subject}" : '')];
                case 'update':
                    return ['update', "mengubah data {$module}" . ($subject ? " {$subject}" : '')];
                case 'destroy':
                    return ['delete', "menghapus data {$module}" . ($subject ? " {$subject}" : '')];
                case 'export':
                    return ['export', "mengunduh (export) data {$module} ke Excel"];
                case 'template':
                    return ['export', "mengunduh template import {$module}"];
                case 'import':
                    $detail = '';
                    if (isset($summary['created']) || isset($summary['updated'])) {
                        $detail = sprintf(
                            ' — %d ditambahkan, %d diperbarui, %d tidak berubah',
                            (int) ($summary['created'] ?? 0),
                            (int) ($summary['updated'] ?? 0),
                            (int) ($summary['unchanged'] ?? 0)
                        );
                    }

                    return ['upload', "mengunggah file Excel{$file} ke {$module}{$detail}"];
            }
        }

        return null; // route lain yang tidak dikenal: dicatat generik oleh pemanggil
    }

    /** Kalimat generik untuk route yang belum punya pemetaan khusus. */
    public static function generic(string $method, string $routeName): array
    {
        return ['action', 'melakukan aksi ' . strtoupper($method) . " ({$routeName})"];
    }

    /** Nama modul dari awalan nama route, mis. "lppm.mahasiswa.store" -> "LPPM Mahasiswa". */
    private static function moduleFor(string $routeName): ?string
    {
        foreach (self::MODULES as $prefix => $label) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
                return $label;
            }
        }

        return null;
    }

    /** ['type'=>..,'id'=>..,'label'=>..] -> `"Label" (#id)`, atau null. */
    private static function subject(?array $model): ?string
    {
        if (!$model) {
            return null;
        }

        $label = isset($model['label']) && $model['label'] !== '' ? '"' . self::clean((string) $model['label'], 90) . '"' : '';
        $id = isset($model['id']) && $model['id'] !== '' ? '(#' . $model['id'] . ')' : '';

        $text = trim("{$label} {$id}");

        return $text === '' ? null : $text;
    }

    /** Ambil label sebuah data dari array atribut (atau hasil JSON respons). */
    public static function labelFrom(array $attributes): ?string
    {
        foreach (self::LABEL_KEYS as $key) {
            $v = $attributes[$key] ?? null;
            if (is_scalar($v) && trim((string) $v) !== '') {
                return trim((string) $v);
            }
        }

        return null;
    }

    /**
     * Bersihkan teks buatan user sebelum masuk log: buang karakter kontrol
     * (termasuk baris baru, supaya satu entri tidak bisa "memalsukan" entri
     * log lain) lalu batasi panjangnya.
     */
    public static function clean(string $text, int $max = 200): string
    {
        $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text) ?? '';
        $text = trim(preg_replace('/\s{2,}/u', ' ', $text) ?? '');

        if (mb_strlen($text) > $max) {
            $text = rtrim(mb_substr($text, 0, $max - 1)) . '…';
        }

        return $text;
    }
}
