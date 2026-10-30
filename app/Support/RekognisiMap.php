<?php

namespace App\Support;

/**
 * Pemetaan halaman Rekognisi terpadu (Mahasiswa + Dosen + Alumni dalam satu halaman) ke tiga tabel yang sudah ada:
 *
 *   tabel          dipakai untuk
 *   lppm_mahasiswa publikasi mahasiswa  (jurnal, conference)
 *   lppm_dosen     publikasi dosen      (jurnal, HKI, buku)
 *   rekognisi      rekognisi mitra mahasiswa / dosen & rekognisi alumni
 *
 * Di layar pengguna hanya melihat: Tipe User, Jenis, Skala (Nasional / Internasional) — "internasional" dan
 * "nasional" TIDAK lagi menempel di nama jenis. Kelas ini menerjemahkan pilihan itu ke kolom tabel lama
 * dan sebaliknya, sehingga dasbor, laporan, dan alur persetujuan yang sudah ada tetap bekerja tanpa diubah.
 * Murni PHP (tanpa Laravel) supaya mudah diuji.
 */
final class RekognisiMap
{
    public const TIPE_USER = ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'alumni' => 'Alumni'];

    public const SKALA = ['nasional' => 'Nasional', 'internasional' => 'Internasional'];

    public const JENIS = [
        'jurnal'     => 'Jurnal',
        'conference' => 'Conference',
        'hki'        => 'Hak Kekayaan Intelektual',
        'buku'       => 'Buku',
        'rekognisi'  => 'Rekognisi Mitra',
    ];

    /** Jenis yang tersedia per tipe user (urutan = urutan di dropdown). */
    public const JENIS_BY_TIPE = [
        'mahasiswa' => ['jurnal', 'conference', 'rekognisi'],
        'dosen'     => ['jurnal', 'hki', 'buku', 'rekognisi'],
        'alumni'    => ['rekognisi'],
    ];

    /** Jenis yang memakai dropdown Skala. */
    public const JENIS_BERSKALA = ['jurnal', 'conference', 'rekognisi'];

    /** Conference selalu internasional (sama seperti sebelumnya: conference_internasional). */
    public const SKALA_TETAP = ['conference' => 'internasional'];

    public const LABEL_PERINGKAT = [
        'nasional'      => 'Peringkat SINTA (S1-S6)',
        'internasional' => 'Kuartil Scopus/WoS (Q1-Q4)',
    ];

    /**
     * (tipe user, jenis, skala) -> [tabel, nilai kolom `jenis` di tabel itu]. null = kombinasi tidak sah.
     *
     * @return array{0: string, 1: string}|null  tabel: 'mhs' | 'dsn' | 'rek'
     */
    public static function target(string $tipe, string $jenis, ?string $skala): ?array
    {
        if (! in_array($jenis, self::JENIS_BY_TIPE[$tipe] ?? [], true)) {
            return null;
        }
        $skala = self::SKALA_TETAP[$jenis] ?? $skala;
        // Alumni tidak memakai Skala (di tabel rekognisi, alumni disimpan sebagai jenis 'alumni')
        if ($tipe !== 'alumni' && in_array($jenis, self::JENIS_BERSKALA, true) && ! isset(self::SKALA[$skala ?? ''])) {
            return null;
        }

        return match (true) {
            $jenis === 'rekognisi' && $tipe === 'alumni' => ['rek', 'alumni'],
            $jenis === 'rekognisi'                       => ['rek', $skala],
            $tipe === 'mahasiswa' && $jenis === 'conference' => ['mhs', 'conference_internasional'],
            $tipe === 'mahasiswa' && $jenis === 'jurnal'     => ['mhs', $skala === 'nasional' ? 'sinta_nasional' : 'jurnal_internasional'],
            $tipe === 'dosen' && $jenis === 'jurnal'         => ['dsn', $skala === 'nasional' ? 'sinta_nasional' : 'q_internasional'],
            $tipe === 'dosen' && $jenis === 'hki'            => ['dsn', 'hki'],
            $tipe === 'dosen' && $jenis === 'buku'           => ['dsn', 'book'],
            default => null,
        };
    }

    /**
     * Kebalikan target(): baris di tabel lama -> pilihan di layar.
     *
     * @return array{tipe_user: string, jenis: string, skala: ?string}
     */
    public static function present(string $table, string $dbJenis, ?string $tipeUserKolom = null): array
    {
        return match ($table) {
            'mhs' => [
                'tipe_user' => 'mahasiswa',
                'jenis'     => $dbJenis === 'conference_internasional' ? 'conference' : 'jurnal',
                'skala'     => $dbJenis === 'sinta_nasional' ? 'nasional' : 'internasional',
            ],
            'dsn' => [
                'tipe_user' => 'dosen',
                'jenis'     => match ($dbJenis) { 'hki' => 'hki', 'book' => 'buku', default => 'jurnal' },
                'skala'     => match ($dbJenis) { 'sinta_nasional' => 'nasional', 'q_internasional' => 'internasional', default => null },
            ],
            default => [   // 'rek'
                'tipe_user' => $dbJenis === 'alumni' ? 'alumni' : ($tipeUserKolom ?: 'mahasiswa'),
                'jenis'     => 'rekognisi',
                'skala'     => in_array($dbJenis, ['nasional', 'internasional'], true) ? $dbJenis : null,
            ],
        };
    }

    /** Prefiks id baris di layar: "mhs-12", "dsn-7", "rek-3" (dipakai tombol edit/hapus & pilihan massal). */
    public static function rowKey(string $table, int|string $id): string
    {
        return $table . '-' . $id;
    }

    /** @return array{0: string, 1: int}|null  ["mhs", 12] dari "mhs-12" */
    public static function parseRowKey(string $key): ?array
    {
        if (! preg_match('/^(mhs|dsn|rek)-(\d+)$/', $key, $m)) {
            return null;
        }

        return [$m[1], (int) $m[2]];
    }

    /** Label tampilan "Jurnal · Internasional" untuk kolom Jenis di tabel. */
    public static function label(string $jenis, ?string $skala): string
    {
        $base = self::JENIS[$jenis] ?? ucfirst($jenis);

        return $skala && isset(self::SKALA[$skala]) ? $base . ' · ' . self::SKALA[$skala] : $base;
    }
}
