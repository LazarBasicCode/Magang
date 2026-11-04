<?php

namespace Tests\Unit;

use App\Support\DuplicateDetector;
use PHPUnit\Framework\TestCase;

/**
 * Pembandingan murni di memori (tanpa database) untuk semua profil deteksi duplikat.
 */
class DuplicateDetectorTest extends TestCase
{
    private function kegiatan(array $override = []): array
    {
        return array_merge([
            'id' => 5,
            'mahasiswa_id' => 1,
            'jenis' => 'kemahasiswaan',
            'tab' => 'akademik',
            'tingkat' => 'nasional',
            'tahun' => 2025,
            'nama_kegiatan' => 'Juara Lomba Karya Tulis',
        ], $override);
    }

    private function baru(array $existing, array $override = [], array $extra = []): array
    {
        return ['data' => array_merge($existing, ['id' => null], $override)] + $extra;
    }

    public function test_nama_yang_sama_persis_walau_beda_huruf_dan_tanda_baca_dianggap_duplikat(): void
    {
        $existing = $this->kegiatan();
        $result = DuplicateDetector::for('kegiatan')->scan(
            [$this->baru($existing, ['nama_kegiatan' => '  juara lomba KARYA tulis!! '])],
            [$existing]
        );

        $this->assertCount(1, $result['exact']);
        $this->assertSame(5, $result['exact'][0]['id']);
    }

    public function test_nama_yang_mirip_hanya_menghasilkan_peringatan(): void
    {
        $existing = $this->kegiatan();
        $result = DuplicateDetector::for('kegiatan')->scan(
            [$this->baru($existing, ['nama_kegiatan' => 'Juara Lomba Karya Tuli'])],
            [$existing]
        );

        $this->assertSame([], $result['exact']);
        $this->assertCount(1, $result['similar']);
        $this->assertStringStartsWith('Nama kegiatan “', $result['similar'][0]);
        $this->assertStringNotContainsString('ID #', $result['similar'][0]);
    }

    public function test_konteks_yang_berbeda_bukan_duplikat(): void
    {
        $existing = $this->kegiatan();
        $detector = DuplicateDetector::for('kegiatan');

        foreach ([['tahun' => 2024], ['mahasiswa_id' => 2], ['tingkat' => 'internasional'], ['tab' => 'minat']] as $override) {
            $result = $detector->scan([$this->baru($existing, $override)], [$existing]);
            $this->assertSame([], $result['exact'], json_encode($override));
            $this->assertSame([], $result['similar'], json_encode($override));
        }
    }

    public function test_data_yang_sedang_diubah_tidak_dibandingkan_dengan_dirinya_sendiri(): void
    {
        $existing = $this->kegiatan();
        $result = DuplicateDetector::for('kegiatan')->scan(
            [$this->baru($existing, [], ['ignore_id' => 5])],
            [$existing]
        );

        $this->assertSame([], $result['exact']);
    }

    public function test_baris_dalam_berkas_yang_sama_saling_dibandingkan_dan_membawa_nomor_baris(): void
    {
        $base = $this->kegiatan();
        $result = DuplicateDetector::for('kegiatan')->scan([
            $this->baru($base, ['nama_kegiatan' => 'Lomba Robot Nasional'], ['row' => 2]),
            $this->baru($base, ['nama_kegiatan' => 'lomba robot nasional'], ['row' => 3]),
            $this->baru($base, ['nama_kegiatan' => 'Lomba Robot Nasionl'], ['row' => 4]),
        ]);

        $this->assertCount(1, $result['exact']);
        $this->assertSame(3, $result['exact'][0]['row']);
        $this->assertArrayNotHasKey('id', $result['exact'][0]);
        $this->assertStringStartsWith('Baris 4: ', $result['similar'][0]);
    }

    public function test_akun_hanya_dibandingkan_dalam_role_yang_sama_dan_peringatan_memuat_id(): void
    {
        $detector = DuplicateDetector::for('akun');

        $exact = $detector->scan(
            [['data' => ['name' => 'Budi Santoso', 'role' => 'dosen']]],
            [['id' => 9, 'name' => 'budi santoso', 'role' => 'dosen'], ['id' => 10, 'name' => 'Budi Santoso', 'role' => 'mahasiswa']]
        );
        $this->assertCount(1, $exact['exact']);
        $this->assertSame(9, $exact['exact'][0]['id']);

        $similar = $detector->scan(
            [['data' => ['name' => 'Budi Santosa', 'role' => 'dosen']]],
            [['id' => 9, 'name' => 'Budi Santoso', 'role' => 'dosen']]
        );
        $this->assertSame([], $similar['exact']);
        $this->assertStringContainsString('(ID #9)', $similar['similar'][0]);
    }

    public function test_rekognisi_dibandingkan_per_pemilik_jenis_dan_tahun(): void
    {
        $detector = DuplicateDetector::for('rekognisi');
        $existing = ['id' => 3, 'mahasiswa_id' => null, 'dosen_id' => 40, 'jenis' => 'prosiding', 'tahun' => 2026, 'judul' => 'Deep Learning untuk Padi', 'mitra' => null, 'jabatan' => null];

        // ID profil & tahun dari form berupa string, dari database berupa angka: tetap cocok.
        $same = $detector->scan(
            [['data' => ['mahasiswa_id' => null, 'dosen_id' => '40', 'jenis' => 'prosiding', 'tahun' => '2026', 'judul' => 'deep learning untuk padi']]],
            [$existing]
        );
        $this->assertCount(1, $same['exact']);

        // Judul yang sama tetapi jenisnya Paten bukan duplikat dari Prosiding.
        $otherKind = $detector->scan(
            [['data' => ['mahasiswa_id' => null, 'dosen_id' => 40, 'jenis' => 'paten', 'tahun' => 2026, 'judul' => 'Deep Learning untuk Padi']]],
            [$existing]
        );
        $this->assertSame([], $otherKind['exact']);

        // Pemilik lain tidak dihitung.
        $otherOwner = $detector->scan(
            [['data' => ['mahasiswa_id' => null, 'dosen_id' => 41, 'jenis' => 'prosiding', 'tahun' => 2026, 'judul' => 'Deep Learning untuk Padi']]],
            [$existing]
        );
        $this->assertSame([], $otherOwner['exact']);
    }

    public function test_rekognisi_alumni_tanpa_judul_dibandingkan_lewat_mitra_dan_jabatan(): void
    {
        $detector = DuplicateDetector::for('rekognisi');
        $existing = ['id' => 8, 'mahasiswa_id' => 50, 'dosen_id' => null, 'jenis' => 'jurnal', 'tahun' => null, 'judul' => null, 'mitra' => 'PT Maju', 'jabatan' => 'Manager'];
        $row = ['mahasiswa_id' => 50, 'dosen_id' => null, 'jenis' => 'jurnal', 'tahun' => null, 'judul' => null, 'mitra' => 'pt maju', 'jabatan' => 'manager'];

        $this->assertCount(1, $detector->scan([['data' => $row]], [$existing])['exact']);
        $this->assertSame([], $detector->scan([['data' => array_merge($row, ['jabatan' => 'Director'])]], [$existing])['exact']);
        $this->assertSame(
            ['exact' => [], 'similar' => []],
            $detector->scan([['data' => array_merge($row, ['mitra' => null, 'jabatan' => null])]], [$existing])
        );
    }

    public function test_kerja_sama_dibandingkan_lewat_mitra_dan_judul_per_pemilik_dan_jenis(): void
    {
        $detector = DuplicateDetector::for('kerja_sama');
        $existing = ['id' => 2, 'user_id' => 7, 'jenis' => 'penelitian', 'mitra' => 'Universitas Gadjah Mada', 'judul_kegiatan' => 'Riset Padi Hibrida'];
        $row = ['user_id' => 7, 'jenis' => 'penelitian', 'mitra' => 'universitas gadjah mada', 'judul_kegiatan' => 'riset padi hibrida'];

        $this->assertCount(1, $detector->scan([['data' => $row]], [$existing])['exact']);
        // Mitra sama tetapi judul berbeda = kerja sama lain.
        $this->assertSame([], $detector->scan([['data' => array_merge($row, ['judul_kegiatan' => 'Workshop Dosen'])]], [$existing])['exact']);
        // Jenis berbeda bukan duplikat.
        $this->assertSame([], $detector->scan([['data' => array_merge($row, ['jenis' => 'pengabdian'])]], [$existing])['exact']);
    }

    public function test_normalisasi_dan_kemiripan_teks(): void
    {
        $this->assertSame('héllo world', DuplicateDetector::normalize("  Héllo,   WORLD!! "));
        $this->assertTrue(DuplicateDetector::isSimilar('Juara Lomba', 'Juara Lombaa'));
        $this->assertFalse(DuplicateDetector::isSimilar('abc', 'abc'));
        $this->assertFalse(DuplicateDetector::isSimilar('abc', 'xyz'));
    }

    public function test_peringatan_dibatasi_lima_dengan_jumlah_total(): void
    {
        $fields = DuplicateDetector::warnings(['exact' => [], 'similar' => array_fill(0, 7, 'x')]);

        $this->assertCount(5, $fields['warnings']);
        $this->assertSame(7, $fields['warning_count']);
    }

    public function test_profil_tidak_dikenal_melempar_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DuplicateDetector::for('tidak-ada');
    }
}
