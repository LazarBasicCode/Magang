<?php

namespace App\Support;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\Rekognisi;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Daftar menu yang bisa dicetak. Satu entri = satu laporan.
 * Menambah menu baru cukup menambah satu entri di sini (lalu route/controller/view tidak perlu diubah).
 *
 * Kunci entri = slug di URL: /cetak/{slug}
 *
 *  - title    judul laporan di kop
 *  - menu     kunci menu di sistem hak akses (HakAkses::MENUS)
 *  - model    model Eloquent sumber data
 *  - with     relasi yang di-eager-load
 *  - year     null (tanpa filter periode) atau ['column' => 'tahun'] / ['column' => 'tanggal_mulai', 'date' => true]
 *  - landscape kertas A4 mendatar (untuk tabel lebar)
 *  - stats    kartu ringkasan: [label, ikon, warna rp-c-*, fn(Collection $items): int]
 *  - columns  kolom tabel: [judul, fn($item): string, kelas opsional]
 */
class PrintReports
{
    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /** Ubah 'conference_internasional' -> 'Conference Internasional'. */
    public static function label(?string $v): string
    {
        return $v === null || $v === '' ? '-' : ucwords(str_replace('_', ' ', $v));
    }

    private static function period($item): string
    {
        $a = optional($item->tanggal_mulai)->translatedFormat('d M Y');
        $b = optional($item->tanggal_selesai)->translatedFormat('d M Y');

        return $a === $b ? ($a ?: '-') : trim(($a ?: '-') . ' – ' . ($b ?: '-'));
    }

    public static function all(): array
    {
        $count = fn (string $col, $val) => fn (Collection $c) => $c->whereIn($col, (array) $val)->count();

        return [

            'kemahasiswaan' => [
                'title'     => 'Prestasi & Kegiatan Mahasiswa',
                'menu'      => 'kemahasiswaan',
                'model'     => Kemahasiswaan::class,
                'with'      => ['mahasiswa.user'],
                'year'      => ['column' => 'tahun'],
                'landscape' => true,
                'stats'     => [
                    ['Total Kegiatan', 'emoji_events', 'blue',   fn (Collection $c) => $c->count()],
                    ['Nasional',       'flag',         'teal',   $count('tingkat', 'nasional')],
                    ['Internasional',  'public',       'violet', $count('tingkat', 'internasional')],
                    ['Unit Inbis',     'storefront',   'orange', $count('jenis', 'inbis')],
                ],
                'columns'   => [
                    ['NIM',           fn ($i) => $i->mahasiswa->nim ?? '-'],
                    ['Mahasiswa',     fn ($i) => $i->mahasiswa->user->name ?? 'Tanpa Nama'],
                    ['Nama Kegiatan', fn ($i) => $i->nama_kegiatan, 'wrap'],
                    ['Jenis',         fn ($i) => self::label($i->jenis)],
                    ['Bidang',        fn ($i) => self::label($i->tab)],
                    ['Tingkat',       fn ($i) => self::label($i->tingkat)],
                    ['Tahun',         fn ($i) => $i->tahun, 'num'],
                ],
            ],

            'lppm-mahasiswa' => [
                'title'     => 'Publikasi Mahasiswa (LPPM)',
                'menu'      => 'lppm_mahasiswa',
                'model'     => LppmMahasiswa::class,
                'with'      => ['mahasiswa.user'],
                'year'      => ['column' => 'tahun'],
                'landscape' => true,
                'stats'     => [
                    ['Total Publikasi',  'menu_book',  'blue',   fn (Collection $c) => $c->count()],
                    ['SINTA Nasional',   'school',     'teal',   $count('jenis', 'sinta_nasional')],
                    ['Conference Int\'l', 'groups',    'violet', $count('jenis', 'conference_internasional')],
                    ['Jurnal Int\'l',     'public',    'orange', $count('jenis', 'jurnal_internasional')],
                ],
                'columns'   => [
                    ['NIM',               fn ($i) => $i->mahasiswa->nim ?? '-'],
                    ['Penulis (Mahasiswa)', fn ($i) => $i->mahasiswa->user->name ?? 'Tanpa Nama'],
                    ['Judul Publikasi',   fn ($i) => $i->judul, 'wrap'],
                    ['Jenis',             fn ($i) => self::label($i->jenis)],
                    ['Jurnal / Peringkat', fn ($i) => trim(($i->nama_jurnal ?: '') . ($i->peringkat ? ' (' . $i->peringkat . ')' : '')) ?: '-', 'wrap'],
                    ['Tahun',             fn ($i) => $i->tahun, 'num'],
                ],
            ],

            'lppm-dosen' => [
                'title'     => 'Publikasi Dosen (LPPM)',
                'menu'      => 'lppm_dosen',
                'model'     => LppmDosen::class,
                'with'      => ['dosen.user'],
                'year'      => ['column' => 'tahun'],
                'landscape' => true,
                'stats'     => [
                    ['Total Karya',      'menu_book',      'blue',   fn (Collection $c) => $c->count()],
                    ['Jurnal Q Int\'l',  'public',         'teal',   $count('jenis', 'q_internasional')],
                    ['SINTA Nasional',   'school',         'violet', $count('jenis', 'sinta_nasional')],
                    ['HKI & Buku',       'workspace_premium', 'orange', $count('jenis', ['hki', 'book'])],
                ],
                'columns'   => [
                    ['NIDN',          fn ($i) => $i->dosen->nidn ?? '-'],
                    ['Penulis (Dosen)', fn ($i) => $i->dosen->user->name ?? 'Tanpa Nama'],
                    ['Judul Karya',   fn ($i) => $i->judul, 'wrap'],
                    ['Jenis',         fn ($i) => self::label($i->jenis)],
                    ['Kategori / Peringkat', fn ($i) => self::label($i->peringkat ?: ($i->jenis_hki ?: $i->kategori_buku))],
                    ['Tahun',         fn ($i) => $i->tahun, 'num'],
                ],
            ],

            'rekognisi' => [
                'title'     => 'Rekognisi',
                'menu'      => 'rekognisi',
                'model'     => Rekognisi::class,
                'with'      => ['user'],
                'year'      => ['column' => 'tanggal_mulai', 'date' => true],
                'landscape' => true,
                'stats'     => [
                    ['Total Rekognisi', 'workspace_premium', 'blue',   fn (Collection $c) => $c->count()],
                    ['Nasional',        'flag',              'teal',   $count('jenis', 'nasional')],
                    ['Internasional',   'public',            'violet', $count('jenis', 'internasional')],
                    ['Alumni',          'school',            'orange', $count('jenis', 'alumni')],
                ],
                'columns'   => [
                    ['NIM / NIDN', fn ($i) => $i->user->nim_nidn ?? '-'],
                    ['Nama',       fn ($i) => $i->user->name ?? 'Tanpa Nama'],
                    ['Tipe',       fn ($i) => self::label($i->tipe_user)],
                    ['Mitra',      fn ($i) => $i->mitra, 'wrap'],
                    ['Jenis',      fn ($i) => self::label($i->jenis)],
                    ['Jabatan',    fn ($i) => $i->jabatan ?: '-'],
                    ['Periode',    fn ($i) => self::period($i)],
                ],
            ],

            'kerja-sama' => [
                'title'     => 'Kerja Sama',
                'menu'      => 'kerja_sama',
                'model'     => KerjaSama::class,
                'with'      => ['user'],
                'year'      => ['column' => 'tanggal_mulai', 'date' => true],
                'landscape' => true,
                'stats'     => [
                    ['Total Kegiatan', 'handshake',     'blue',   fn (Collection $c) => $c->count()],
                    ['Mahasiswa',      'person',        'teal',   $count('tipe_user', 'mahasiswa')],
                    ['Dosen',          'co_present',    'violet', $count('tipe_user', 'dosen')],
                    ['Internasional',  'public',        'orange', $count('jenis', ['conference_internasional', 'guest_lecture', 'pengabdian_internasional', 'research_internasional'])],
                ],
                'columns'   => [
                    ['NIM / NIDN',      fn ($i) => $i->user->nim_nidn ?? '-'],
                    ['Nama',            fn ($i) => $i->user->name ?? 'Tanpa Nama'],
                    ['Judul Kegiatan',  fn ($i) => $i->judul_kegiatan, 'wrap'],
                    ['Jenis',           fn ($i) => $i->jenis === 'lainnya' && $i->jenis_lainnya ? 'Lainnya: ' . $i->jenis_lainnya : self::label($i->jenis)],
                    ['Tipe',            fn ($i) => self::label($i->tipe_user)],
                    ['Arah',            fn ($i) => self::label($i->arah)],
                    ['Mitra',           fn ($i) => $i->mitra, 'wrap'],
                    ['Periode',         fn ($i) => self::period($i)],
                ],
            ],

            'data-master' => [
                'title'     => 'Data Master Pengguna',
                'menu'      => 'data_master',
                'model'     => User::class,
                'with'      => ['mahasiswa', 'dosen'],
                'year'      => null,
                'landscape' => false,
                'stats'     => [
                    ['Total Pengguna', 'groups',            'blue',   fn (Collection $c) => $c->count()],
                    ['Mahasiswa',      'person',            'teal',   $count('role', 'mahasiswa')],
                    ['Dosen',          'co_present',        'violet', $count('role', 'dosen')],
                    ['Admin & Superadmin', 'admin_panel_settings', 'red', $count('role', ['admin', 'superadmin'])],
                ],
                'columns'   => [
                    ['ID',           fn ($i) => 'USR-' . str_pad($i->id, 3, '0', STR_PAD_LEFT)],
                    ['Nama Lengkap', fn ($i) => $i->name],
                    ['Role',         fn ($i) => ucfirst($i->role)],
                    ['NIM / NIDN',   fn ($i) => ($i->role === 'mahasiswa' ? optional($i->mahasiswa)->nim : ($i->role === 'dosen' ? optional($i->dosen)->nidn : $i->nim_nidn)) ?: '-'],
                    ['Email',        fn ($i) => $i->email ?: '-'],
                ],
            ],
        ];
    }
}
