<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\HakAkses;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // =============================
        // 1. BUAT 10 USER MAHASISWA
        // =============================
        $mahasiswas = [
            ['name' => 'Ahmad Fauzi',        'email' => 'ahmad.fauzi@student.ac.id',    'nim' => '2021001'],
            ['name' => 'Siti Nurhaliza',     'email' => 'siti.nurhaliza@student.ac.id', 'nim' => '2021002'],
            ['name' => 'Budi Santoso',       'email' => 'budi.santoso@student.ac.id',   'nim' => '2021003'],
            ['name' => 'Dewi Lestari',       'email' => 'dewi.lestari@student.ac.id',   'nim' => '2021004'],
            ['name' => 'Rizky Pratama',      'email' => 'rizky.pratama@student.ac.id',  'nim' => '2021005'],
            ['name' => 'Nur Aisyah',         'email' => 'nur.aisyah@student.ac.id',     'nim' => '2021006'],
            ['name' => 'Fajar Hidayat',      'email' => 'fajar.hidayat@student.ac.id',  'nim' => '2021007'],
            ['name' => 'Putri Anggraini',    'email' => 'putri.anggraini@student.ac.id','nim' => '2021008'],
            ['name' => 'Andi Wijaya',        'email' => 'andi.wijaya@student.ac.id',    'nim' => '2021009'],
            ['name' => 'Maya Sari',          'email' => 'maya.sari@student.ac.id',      'nim' => '2021010'],
        ];

        $mahasiswaIds = [];

        // Ambil default level untuk role mahasiswa dari model HakAkses
        // supaya seeder selalu sinkron dengan aturan di aplikasi.
        $defaultLevels = HakAkses::defaultsForRole('mahasiswa');

        foreach ($mahasiswas as $m) {
            // Insert ke tabel users
            $userId = DB::table('users')->insertGetId([
                'name'       => $m['name'],
                'email'      => $m['email'],
                'password'   => Hash::make('password123'),
                'role'       => 'mahasiswa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert ke tabel mahasiswa
            $mahasiswaId = DB::table('mahasiswa')->insertGetId([
                'user_id'    => $userId,
                'nim'        => $m['nim'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $mahasiswaIds[] = $mahasiswaId;

            // =============================
            // Hak Akses default untuk mahasiswa
            // ---------------------------------
            // Mengikuti HakAkses::defaultsForRole('mahasiswa'):
            //   dashboard       => biasa
            //   kemahasiswaan   => biasa
            //   lppm_mahasiswa  => biasa
            //   lppm_dosen      => none
            //   rekognisi       => biasa
            //   kerja_sama      => biasa
            //   data_master     => none
            //   hak_akses       => none
            //   log             => none
            // =============================
            foreach (HakAkses::MENUS as $menuKey => $menuMeta) {
                $level = $defaultLevels[$menuKey] ?? 'none';

                // Kalau level bukan salah satu yang valid, paksa ke 'none'
                if (!HakAkses::isValidLevel($level)) {
                    $level = 'none';
                }

                DB::table('hak_akses')->insert([
                    'user_id'    => $userId,
                    'menu'       => $menuKey,
                    'level'      => $level,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // =============================
        // 2. BUAT 5 DATA KEMAHASISWAAN
        // =============================
        $kemahasiswaanData = [
            [
                'mahasiswa_id'   => $mahasiswaIds[0],
                'jenis'          => 'inbis',
                'tab'            => 'non_akademik',
                'tingkat'        => 'nasional',
                'tahun'          => 2024,
                'nama_kegiatan'  => 'Kompetisi Bisnis Plan Nasional 2024',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/inbis-nasional-2024',
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[1],
                'jenis'          => 'kemahasiswaan',
                'tab'            => 'akademik',
                'tingkat'        => 'lokal',
                'tahun'          => 2023,
                'nama_kegiatan'  => 'Olimpiade Matematika Tingkat Universitas',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/olimpiade-matematika-2023',
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[2],
                'jenis'          => 'inbis',
                'tab'            => 'non_akademik',
                'tingkat'        => 'internasional',
                'tahun'          => 2024,
                'nama_kegiatan'  => 'International Business Case Competition',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/ibcc-2024',
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[3],
                'jenis'          => 'kemahasiswaan',
                'tab'            => 'non_akademik',
                'tingkat'        => 'nasional',
                'tahun'          => 2025,
                'nama_kegiatan'  => 'Pekan Olahraga Mahasiswa Nasional (POMNAS)',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/pomnas-2025',
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[4],
                'jenis'          => 'kemahasiswaan',
                'tab'            => 'akademik',
                'tingkat'        => 'lokal',
                'tahun'          => 2024,
                'nama_kegiatan'  => 'Lomba Karya Tulis Ilmiah Tingkat Fakultas',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/kti-fakultas-2024',
            ],
        ];

        foreach ($kemahasiswaanData as $k) {
            DB::table('kemahasiswaan')->insert(array_merge($k, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // =============================
        // 3. BUAT 5 DATA LPPM MAHASISWA
        // =============================
        $lppmData = [
            [
                'mahasiswa_id'   => $mahasiswaIds[0],
                'jenis'          => 'sinta_nasional',
                'judul'          => 'Analisis Sentimen Media Sosial Terhadap Kebijakan Pendidikan',
                'penulis'        => 'Ahmad Fauzi, Dr. Bambang S.',
                'nama_jurnal'    => 'Jurnal Teknologi Informasi Indonesia',
                'peringkat'      => 'S2',
                'link_doi'       => 'https://doi.org/10.1234/jtii.2024.001',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/sinta-nasional-2024',
                'tahun'          => 2024,
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[1],
                'jenis'          => 'conference_internasional',
                'judul'          => 'Machine Learning for Early Detection of Diabetes',
                'penulis'        => 'Siti Nurhaliza, Prof. John Doe',
                'nama_jurnal'    => null,
                'peringkat'      => null,
                'link_doi'       => null,
                'bukti_kegiatan' => 'https://drive.google.com/bukti/conf-int-2024',
                'tahun'          => 2024,
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[2],
                'jenis'          => 'jurnal_internasional',
                'judul'          => 'Blockchain Technology for Supply Chain Transparency',
                'penulis'        => 'Budi Santoso, Dr. Jane Smith',
                'nama_jurnal'    => 'International Journal of Computer Science',
                'peringkat'      => 'Q2',
                'link_doi'       => 'https://doi.org/10.5678/ijcs.2024.045',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/jurnal-int-2024',
                'tahun'          => 2024,
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[3],
                'jenis'          => 'sinta_nasional',
                'judul'          => 'Pengembangan Sistem Informasi Akademik Berbasis Web',
                'penulis'        => 'Dewi Lestari, Dr. Ahmad Yani',
                'nama_jurnal'    => 'Jurnal Sistem Informasi Nasional',
                'peringkat'      => 'S3',
                'link_doi'       => 'https://doi.org/10.9012/jsin.2023.078',
                'bukti_kegiatan' => 'https://drive.google.com/bukti/sinta-nasional-2023',
                'tahun'          => 2023,
            ],
            [
                'mahasiswa_id'   => $mahasiswaIds[4],
                'jenis'          => 'conference_internasional',
                'judul'          => 'IoT-Based Smart Farming for Sustainable Agriculture',
                'penulis'        => 'Rizky Pratama, Dr. Michael Lee',
                'nama_jurnal'    => null,
                'peringkat'      => null,
                'link_doi'       => null,
                'bukti_kegiatan' => 'https://drive.google.com/bukti/conf-iot-2025',
                'tahun'          => 2025,
            ],
        ];

        foreach ($lppmData as $l) {
            DB::table('lppm_mahasiswa')->insert(array_merge($l, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}