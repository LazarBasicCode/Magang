<?php

namespace App\Support;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
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

    /** Ringkasan detail karya Rekognisi: jurnal, HKI, buku, prosiding, atau paten. */
    private static function detailKarya($item): string
    {
        $statusPaten = ['terdaftar' => 'Terdaftar (Filed)', 'granted' => 'Granted (Bersertifikat)'];
        $join = fn (array $parts) => implode(' · ', array_filter($parts, fn ($v) => filled($v))) ?: '-';

        return match ($item->jenis) {
            'jurnal', 'sinta' => $join([$item->nama_jurnal, $item->peringkat]),
            'hki'             => self::label($item->jenis_hki),
            'book'            => self::label($item->kategori_buku),
            'prosiding'       => $join([$item->nama_konferensi, $item->nomor_issn_isbn]),
            'paten'           => $join([$statusPaten[$item->status_paten] ?? null, $item->nomor_paten]),
            default           => '-',
        };
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
                    ['Jenis Prestasi', fn ($i) => self::label($i->jenis)],
                    ['Jenis Prestasi', fn ($i) => self::label($i->tab)],
                    ['Skala',          fn ($i) => self::label($i->tingkat)],
                    ['Tahun',         fn ($i) => $i->tahun, 'num'],
                ],
            ],

            'rekognisi' => [
                'title'     => 'Rekognisi',
                'menu'      => 'rekognisi',
                'model'     => Rekognisi::class,
                'with'      => ['mahasiswa.user', 'dosen.user'],
                'year'      => ['column' => 'tanggal_mulai', 'date' => true],
                'landscape' => true,
                'stats'     => [
                    ['Total Rekognisi', 'workspace_premium', 'blue',   fn (Collection $c) => $c->count()],
                    ['Nasional',        'flag',              'teal',   $count('skala', 'nasional')],
                    ['Internasional',   'public',            'violet', $count('skala', 'internasional')],
                    ['Alumni',          'school',            'orange', fn (Collection $c) => $c->filter(fn ($i) => $i->tipe_user_efektif === 'alumni')->count()],
                ],
                'columns'   => [
                    ['NIM / NIDN', fn ($i) => $i->owner_identifier ?? '-'],
                    ['Nama',       fn ($i) => $i->owner_name],
                    ['Tipe',       fn ($i) => self::label($i->tipe_user_efektif)],
                    ['Judul Karya', fn ($i) => $i->judul ?: '-', 'wrap'],
                    ['Penulis',    fn ($i) => $i->penulis ?: '-', 'wrap'],
                    ['Mitra',      fn ($i) => $i->mitra ?: '-', 'wrap'],
                    ['Jenis',      fn ($i) => self::label($i->jenis)],
                    ['Detail Karya', fn ($i) => self::detailKarya($i), 'wrap'],
                    ['Skala',      fn ($i) => self::label($i->skala ?: '-')],
                    ['Jabatan',    fn ($i) => $i->jabatan_efektif ?: '-'],
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
