<?php

namespace App\Support;

/**
 * Query gabungan untuk halaman Rekognisi terpadu: satu daftar dari tiga tabel (lppm_mahasiswa, lppm_dosen,
 * rekognisi) dengan kolom seragam, lengkap dengan filter, pencarian, dan paginasi — semuanya di database.
 *
 * Hanya MEMBUAT SQL + binding (dijalankan lewat DB::select di controller), jadi bisa diuji tanpa Laravel.
 * SQL sengaja memakai fungsi standar (CASE, SUBSTR, CAST) agar jalan di MySQL maupun SQLite.
 *
 * Kolom hasil: source (mhs|dsn|rek), id, user_id, tipe_user, jenis, skala, judul, penulis, detail, tahun,
 *              bukti, approval_status, approval_note, created_at, nama, nim_nidn, foto
 */
final class RekognisiUnion
{
    public const PER_PAGE = 50;

    /** SELECT gabungan tiga tabel dengan kolom seragam. */
    public static function unionSql(): string
    {
        return <<<'SQL'
SELECT 'mhs' AS source, lm.id AS id, m.user_id AS user_id, 'mahasiswa' AS tipe_user,
       CASE lm.jenis WHEN 'conference_internasional' THEN 'conference' ELSE 'jurnal' END AS jenis,
       CASE lm.jenis WHEN 'sinta_nasional' THEN 'nasional' ELSE 'internasional' END AS skala,
       lm.judul AS judul, lm.penulis AS penulis, lm.peringkat AS detail, lm.tahun AS tahun,
       lm.bukti_kegiatan AS bukti, lm.approval_status AS approval_status, lm.approval_note AS approval_note, lm.created_at AS created_at
  FROM lppm_mahasiswa lm
  JOIN mahasiswa m ON m.id = lm.mahasiswa_id
UNION ALL
SELECT 'dsn', ld.id, d.user_id, 'dosen',
       CASE ld.jenis WHEN 'hki' THEN 'hki' WHEN 'book' THEN 'buku' ELSE 'jurnal' END,
       CASE ld.jenis WHEN 'sinta_nasional' THEN 'nasional' WHEN 'q_internasional' THEN 'internasional' ELSE NULL END,
       ld.judul, ld.penulis, COALESCE(ld.peringkat, ld.jenis_hki, ld.kategori_buku), ld.tahun,
       ld.bukti_kegiatan, ld.approval_status, ld.approval_note, ld.created_at
  FROM lppm_dosen ld
  JOIN dosen d ON d.id = ld.dosen_id
UNION ALL
SELECT 'rek', r.id, r.user_id,
       CASE WHEN r.jenis = 'alumni' THEN 'alumni' ELSE r.tipe_user END,
       'rekognisi',
       CASE WHEN r.jenis IN ('nasional', 'internasional') THEN r.jenis ELSE NULL END,
       r.mitra, NULL, r.jabatan, CAST(SUBSTR(r.tanggal_mulai, 1, 4) AS SIGNED),
       r.bukti_kegiatan, r.approval_status, r.approval_note, r.created_at
  FROM rekognisi r
SQL;
    }

    /**
     * @param  array{tipe_user?:?string, jenis?:?string, skala?:?string, tahun?:?int, q?:?string, own_user_id?:?int} $f
     * @return array{0: string, 1: array}  [klausa WHERE, binding]
     */
    public static function where(array $f): array
    {
        $w = [];
        $b = [];
        foreach (['tipe_user', 'jenis', 'skala'] as $col) {
            $v = $f[$col] ?? null;
            if ($v !== null && $v !== '' && $v !== 'semua') {
                $w[] = "u.$col = ?";
                $b[] = $v;
            }
        }
        if (! empty($f['tahun']) && ctype_digit((string) $f['tahun'])) {   // 'semua' / teks lain = tanpa filter tahun
            $w[] = 'u.tahun = ?';
            $b[] = (int) $f['tahun'];
        }
        // Hak akses per tabel asal: 'all' (lihat semua), 'own' (hanya milik viewer_id), 'none' (disembunyikan)
        if (isset($f['access'])) {
            $parts = [];
            foreach (['mhs', 'dsn', 'rek'] as $src) {
                $lvl = $f['access'][$src] ?? 'none';
                if ($lvl === 'all') {
                    $parts[] = "u.source = '$src'";
                } elseif ($lvl === 'own') {
                    $parts[] = "(u.source = '$src' AND u.user_id = ?)";
                    $b[] = (int) ($f['viewer_id'] ?? 0);
                }
            }
            $w[] = $parts ? '(' . implode(' OR ', $parts) . ')' : '1 = 0';
        }
        if (! empty($f['own_user_id'])) {
            $w[] = 'u.user_id = ?';
            $b[] = (int) $f['own_user_id'];
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            // Karakter khusus LIKE dilolos dengan '!' (ESCAPE '!' berlaku sama di MySQL & SQLite;
            // ESCAPE '\\' tidak bisa dipakai karena di MySQL backslash ikut melolos tanda kutip)
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%';
            $w[] = "(us.name LIKE ? ESCAPE '!' OR us.nim_nidn LIKE ? ESCAPE '!' OR u.judul LIKE ? ESCAPE '!'"
                 . " OR u.penulis LIKE ? ESCAPE '!' OR u.detail LIKE ? ESCAPE '!')";
            array_push($b, $like, $like, $like, $like, $like);
        }

        return [$w ? 'WHERE ' . implode(' AND ', $w) : '', $b];
    }

    /** @return array{0: string, 1: array} [SQL, binding] untuk satu halaman data */
    public static function listQuery(array $filters, int $page = 1, int $perPage = self::PER_PAGE): array
    {
        [$where, $b] = self::where($filters);
        $page = max(1, $page);
        $sql = 'SELECT u.*, us.name AS nama, us.nim_nidn AS nim_nidn, us.avatar_url AS foto FROM (' . self::unionSql() . ') u '
             . 'JOIN users us ON us.id = u.user_id ' . $where
             . ' ORDER BY u.created_at DESC, u.id DESC, u.source ASC LIMIT ' . (int) $perPage . ' OFFSET ' . (($page - 1) * (int) $perPage);

        return [$sql, $b];
    }

    /** @return array{0: string, 1: array} jumlah total baris yang cocok filter */
    public static function countQuery(array $filters): array
    {
        [$where, $b] = self::where($filters);

        return ['SELECT COUNT(*) AS n FROM (' . self::unionSql() . ') u JOIN users us ON us.id = u.user_id ' . $where, $b];
    }

    /** Hitungan per tipe user & jenis untuk kartu statistik (hanya cakupan akses, tanpa filter pencarian). */
    public static function statsQuery(array $scope = []): array
    {
        [$where, $b] = self::where($scope);

        return ['SELECT u.tipe_user AS tipe_user, u.jenis AS jenis, COUNT(*) AS n FROM (' . self::unionSql() . ") u $where GROUP BY u.tipe_user, u.jenis", $b];
    }

    /** Daftar tahun yang ada (untuk dropdown filter), terbaru dulu. */
    public static function yearsQuery(array $scope = []): array
    {
        [$where, $b] = self::where($scope);
        $where = $where === '' ? 'WHERE u.tahun IS NOT NULL' : $where . ' AND u.tahun IS NOT NULL';

        return ['SELECT DISTINCT u.tahun AS tahun FROM (' . self::unionSql() . ") u $where ORDER BY u.tahun DESC", $b];
    }

    /** Satu baris (untuk dikirim balik setelah tambah / ubah). */
    public static function oneQuery(string $source, int $id): array
    {
        return ['SELECT u.*, us.name AS nama, us.nim_nidn AS nim_nidn, us.avatar_url AS foto FROM (' . self::unionSql() . ') u JOIN users us ON us.id = u.user_id WHERE u.source = ? AND u.id = ?', [$source, $id]];
    }
}
