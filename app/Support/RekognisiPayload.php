<?php

namespace App\Support;

/**
 * Menerjemahkan isian form Rekognisi terpadu <-> kolom tabel lama. Murni PHP (mudah diuji).
 *
 * Form terpadu mengirim: tipe_user, user_id, jenis, skala, judul, penulis, nama_jurnal, peringkat, link_doi,
 * jenis_hki, kategori_buku, tahun, bukti_kegiatan, mitra, jabatan, tanggal_mulai, tanggal_selesai, bukti_tambahan.
 * Hanya kolom yang RELEVAN untuk jenisnya yang diteruskan; sisanya dikosongkan supaya data lama tidak tercampur.
 */
final class RekognisiPayload
{
    /**
     * Isian form -> isian untuk controller lama (LppmMahasiswa / LppmDosen / LppmRekognisi).
     *
     * @param  array{0:string,1:string} $target hasil RekognisiMap::target() => [tabel, jenis di tabel itu]
     * @param  array $in    isian form
     * @param  array $ids   ['mahasiswa_id' => ?int, 'dosen_id' => ?int, 'user_role' => ?string]
     */
    public static function toLegacy(array $target, array $in, array $ids): array
    {
        [$table, $dbJenis] = $target;
        $get = static fn (string $k) => (isset($in[$k]) && $in[$k] !== '') ? $in[$k] : null;

        $common = ['jenis' => $dbJenis, 'bukti_kegiatan' => $get('bukti_kegiatan')];

        if ($table === 'mhs') {
            $jurnal = $dbJenis !== 'conference_internasional';
            return $common + [
                'mahasiswa_id' => $ids['mahasiswa_id'] ?? null,
                'judul'        => $get('judul'),
                'penulis'      => $get('penulis'),
                'nama_jurnal'  => $jurnal ? $get('nama_jurnal') : null,
                'peringkat'    => $get('peringkat'),
                'link_doi'     => $get('link_doi'),
                'tahun'        => $get('tahun'),
            ];
        }

        if ($table === 'dsn') {
            $jurnal = in_array($dbJenis, ['q_internasional', 'sinta_nasional'], true);
            return $common + [
                'dosen_id'      => $ids['dosen_id'] ?? null,
                'judul'         => $get('judul'),
                'penulis'       => $get('penulis'),
                'nama_jurnal'   => $jurnal ? $get('nama_jurnal') : null,
                'peringkat'     => $jurnal ? $get('peringkat') : null,
                'jenis_hki'     => $dbJenis === 'hki' ? $get('jenis_hki') : null,
                'kategori_buku' => $dbJenis === 'book' ? $get('kategori_buku') : null,
                'link_doi'      => $jurnal ? $get('link_doi') : null,
                'tahun'         => $get('tahun'),
            ];
        }

        // 'rek': rekognisi mitra mahasiswa/dosen, atau rekognisi alumni
        $alumni = $dbJenis === 'alumni';
        return $common + [
            'user_id'         => $get('user_id'),
            // kolom tipe_user di tabel rekognisi hanya mahasiswa|dosen; alumni = peran asli akunnya
            'tipe_user'       => $alumni ? ($ids['user_role'] ?? 'mahasiswa') : $get('tipe_user'),
            'mitra'           => $get('mitra'),
            'jabatan'         => $alumni ? $get('jabatan') : null,
            'tanggal_mulai'   => $get('tanggal_mulai'),
            'tanggal_selesai' => $get('tanggal_selesai'),
            'bukti_tambahan'  => $get('bukti_tambahan'),
        ];
    }

    /**
     * Record tabel lama (array atribut) -> isian form terpadu, untuk mengisi modal ubah.
     *
     * @param array $row     atribut model (jenis, judul, ...)
     * @param int   $userId  id akun pemilik
     */
    public static function fromLegacy(string $table, array $row, int $userId): array
    {
        $p = RekognisiMap::present($table, (string) ($row['jenis'] ?? ''), $row['tipe_user'] ?? null);
        $keep = ['judul', 'penulis', 'nama_jurnal', 'peringkat', 'link_doi', 'jenis_hki', 'kategori_buku', 'tahun',
                 'bukti_kegiatan', 'mitra', 'jabatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_tambahan'];

        $out = ['key' => RekognisiMap::rowKey($table, $row['id'] ?? 0), 'user_id' => $userId] + $p;
        foreach ($keep as $k) {
            $v = $row[$k] ?? null;
            $out[$k] = $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v;
        }

        return $out;
    }
}
