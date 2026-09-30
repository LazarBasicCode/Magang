<?php

namespace App\Support;

use App\Models\Dosen;
use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LoginAttempt;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Rekognisi;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Data untuk halaman Laporan (khusus superadmin).
 *
 * Halaman ini adalah versi LENGKAP dari ringkasan di dashboard superadmin:
 * semua menu, rincian per jenis/tingkat, tren per tahun, kontributor,
 * kualitas data, dan keamanan login — bisa difilter per tahun dan dicetak.
 */
class ReportData
{
    public static function build(?int $tahun): array
    {
        $kem = Kemahasiswaan::with('mahasiswa.user')->get();
        $lm  = LppmMahasiswa::with('mahasiswa.user')->get();
        $ld  = LppmDosen::with('dosen.user')->get();
        $rek = Rekognisi::with('user')->get();
        $ks  = KerjaSama::with('user')->get();

        // Daftar tahun yang tersedia untuk filter
        $years = $kem->concat($lm)->concat($ld)->concat($rek)->concat($ks)
            ->map(fn ($i) => self::year($i))->filter()->push((int) now()->year)
            ->unique()->sortDesc()->values()->all();

        $inYear = fn (Collection $c) => $tahun
            ? $c->filter(fn ($i) => self::year($i) === $tahun)->values()
            : $c;

        $kemP = $inYear($kem);
        $lmP  = $inYear($lm);
        $ldP  = $inYear($ld);
        $rekP = $inYear($rek);
        $ksP  = $inYear($ks);

        $ksIntl = ['conference_internasional', 'pengabdian_internasional', 'research_internasional'];
        $intl = fn ($k, $l, $d, $r, $s) => $k->where('tingkat', 'internasional')->count()
            + $l->whereIn('jenis', ['jurnal_internasional', 'conference_internasional'])->count()
            + $d->where('jenis', 'q_internasional')->count()
            + $r->where('jenis', 'internasional')->count()
            + $s->whereIn('jenis', $ksIntl)->count();

        $totalPeriode = $kemP->count() + $lmP->count() + $ldP->count() + $rekP->count() + $ksP->count();
        $intlPeriode  = $intl($kemP, $lmP, $ldP, $rekP, $ksP);

        // ---- Rekap per modul ----
        $modules = [
            ['label' => 'Kemahasiswaan',  'icon' => 'school',            'all' => $kem->count(), 'periode' => $kemP->count(), 'intl' => $kemP->where('tingkat', 'internasional')->count()],
            ['label' => 'LPPM Mahasiswa', 'icon' => 'person',            'all' => $lm->count(),  'periode' => $lmP->count(),  'intl' => $lmP->whereIn('jenis', ['jurnal_internasional', 'conference_internasional'])->count()],
            ['label' => 'LPPM Dosen',     'icon' => 'co_present',        'all' => $ld->count(),  'periode' => $ldP->count(),  'intl' => $ldP->where('jenis', 'q_internasional')->count()],
            ['label' => 'Rekognisi',      'icon' => 'workspace_premium', 'all' => $rek->count(), 'periode' => $rekP->count(), 'intl' => $rekP->where('jenis', 'internasional')->count()],
            ['label' => 'Kerja Sama',     'icon' => 'handshake',         'all' => $ks->count(),  'periode' => $ksP->count(),  'intl' => $ksP->whereIn('jenis', $ksIntl)->count()],
        ];
        foreach ($modules as &$m) {
            $m['share'] = self::pct($m['periode'], $totalPeriode);
        }
        unset($m);

        // ---- Akun & partisipasi ----
        $roles = User::selectRaw('role, count(*) as n')->groupBy('role')->pluck('n', 'role');
        $mhsTotal = Mahasiswa::count();
        $dsnTotal = Dosen::count();

        $mhsIds = $kemP->map(fn ($i) => $i->mahasiswa?->user_id)
            ->concat($lmP->map(fn ($i) => $i->mahasiswa?->user_id))
            ->concat($rekP->where('tipe_user', 'mahasiswa')->pluck('user_id'))
            ->concat($ksP->where('tipe_user', 'mahasiswa')->pluck('user_id'))
            ->filter()->unique()->count();
        $dsnIds = $ldP->map(fn ($i) => $i->dosen?->user_id)
            ->concat($rekP->where('tipe_user', 'dosen')->pluck('user_id'))
            ->concat($ksP->where('tipe_user', 'dosen')->pluck('user_id'))
            ->filter()->unique()->count();

        // ---- Rincian per jenis/tingkat (periode terpilih) ----
        $breakdowns = [
            self::group('Kemahasiswaan · Tingkat', $kemP, 'tingkat', ['lokal' => 'Lokal', 'nasional' => 'Nasional', 'internasional' => 'Internasional']),
            self::group('Kemahasiswaan · Jenis', $kemP, 'jenis', ['inbis' => 'Inbis', 'kemahasiswaan' => 'Kemahasiswaan']),
            self::group('Kemahasiswaan · Bidang', $kemP, 'tab', ['akademik' => 'Akademik', 'non_akademik' => 'Non Akademik']),
            self::group('LPPM Mahasiswa · Jenis', $lmP, 'jenis', ['sinta_nasional' => 'Jurnal Sinta Nasional', 'jurnal_internasional' => 'Jurnal Internasional', 'conference_internasional' => 'Conference Internasional']),
            self::group('LPPM Dosen · Jenis', $ldP, 'jenis', ['q_internasional' => 'Jurnal Q Internasional', 'sinta_nasional' => 'Jurnal Sinta Nasional', 'hki' => 'HKI', 'book' => 'Buku']),
            self::group('LPPM Dosen · Peringkat Jurnal', $ldP->whereNotNull('peringkat'), 'peringkat', null),
            self::group('Rekognisi · Jenis', $rekP, 'jenis', ['nasional' => 'Nasional', 'internasional' => 'Internasional', 'alumni' => 'Alumni']),
            self::group('Rekognisi · Mitra Terbanyak', $rekP, 'mitra', null, 5),
            self::group('Kerja Sama · Jenis', $ksP, 'jenis', null),
            self::group('Kerja Sama · Peserta', $ksP, 'tipe_user', ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen']),
            self::group('Kerja Sama · Mitra Terbanyak', $ksP, 'mitra', null, 5),
        ];
        $breakdowns = array_values(array_filter($breakdowns, fn ($g) => $g['total'] > 0));

        // ---- Tren per tahun (semua tahun, maks. 6 terakhir) ----
        $trendYears = collect($years)->sort()->slice(-6)->values();
        $trend = $trendYears->map(function ($y) use ($kem, $lm, $ld, $rek, $ks) {
            $c = fn (Collection $col) => $col->filter(fn ($i) => self::year($i) === $y)->count();
            $row = ['tahun' => $y, 'kem' => $c($kem), 'lm' => $c($lm), 'ld' => $c($ld), 'rek' => $c($rek), 'ks' => $c($ks)];
            $row['total'] = $row['kem'] + $row['lm'] + $row['ld'] + $row['rek'] + $row['ks'];

            return $row;
        })->all();

        // ---- Kontributor teraktif (periode terpilih) ----
        $owner = fn ($name, $role) => $name ? [$name . '|' . $role] : [];
        $contrib = collect()
            ->concat($kemP->flatMap(fn ($i) => $owner($i->mahasiswa?->user?->name, 'Mahasiswa')))
            ->concat($lmP->flatMap(fn ($i) => $owner($i->mahasiswa?->user?->name, 'Mahasiswa')))
            ->concat($ldP->flatMap(fn ($i) => $owner($i->dosen?->user?->name, 'Dosen')))
            ->concat($rekP->flatMap(fn ($i) => $owner($i->user?->name, ucfirst((string) $i->tipe_user))))
            ->concat($ksP->flatMap(fn ($i) => $owner($i->user?->name, ucfirst((string) $i->tipe_user))))
            ->countBy()->sortDesc()->take(10)
            ->map(function ($n, $key) {
                [$name, $role] = explode('|', $key);

                return ['name' => $name, 'role' => $role, 'value' => $n];
            })->values()->all();

        // ---- Kualitas data ----
        $noDoi = $lmP->whereIn('jenis', ['jurnal_internasional', 'sinta_nasional'])->filter(fn ($i) => blank($i->link_doi))->count()
            + $ldP->whereIn('jenis', ['q_internasional', 'sinta_nasional'])->filter(fn ($i) => blank($i->link_doi))->count();
        $noEmail = User::where(fn ($q) => $q->whereNull('email')->orWhere('email', ''))->count();
        $quality = [
            ['label' => 'Jurnal tanpa link DOI', 'value' => $noDoi, 'note' => 'LPPM Mahasiswa & Dosen pada periode ini', 'url' => null],
            ['label' => 'Akun belum punya email', 'value' => $noEmail, 'note' => 'Tidak bisa memakai fitur lupa password', 'url' => url('/data-master/users')],
        ];

        // ---- Keamanan login (30 hari terakhir) ----
        $since = now()->subDays(30);
        $attempts = LoginAttempt::where('created_at', '>=', $since);
        $byStatus = (clone $attempts)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $security = [
            'success'   => (int) ($byStatus['success'] ?? 0),
            'failed'    => (int) ($byStatus['failed'] ?? 0),
            'locked'    => (int) ($byStatus['locked'] ?? 0),
            'unique_ip' => (clone $attempts)->distinct('ip_address')->count('ip_address'),
            'top_ip'    => (clone $attempts)->where('status', '!=', 'success')
                ->selectRaw('ip_address as label, count(*) as n')->groupBy('ip_address')->orderByDesc('n')->limit(5)->get()
                ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->n])->all(),
            'top_user'  => (clone $attempts)->where('status', 'failed')
                ->selectRaw('username_input as label, count(*) as n')->groupBy('username_input')->orderByDesc('n')->limit(5)->get()
                ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->n])->all(),
        ];

        return [
            'tahun'        => $tahun,
            'years'        => $years,
            'generated_at' => now(),
            'kpi' => [
                'total'      => $totalPeriode,
                'intl_pct'   => self::pct($intlPeriode, $totalPeriode),
                'intl'       => $intlPeriode,
                'accounts'   => (int) $roles->sum(),
                'roles'      => $roles->all(),
                'mhs_total'  => $mhsTotal,
                'mhs_active' => $mhsIds,
                'mhs_pct'    => self::pct($mhsIds, $mhsTotal),
                'dsn_total'  => $dsnTotal,
                'dsn_active' => $dsnIds,
                'dsn_pct'    => self::pct($dsnIds, $dsnTotal),
            ],
            'modules'      => $modules,
            'breakdowns'   => $breakdowns,
            'trend'        => $trend,
            'contributors' => $contrib,
            'quality'      => $quality,
            'security'     => $security,
        ];
    }

    /** Tahun kegiatan: kolom "tahun" (kemahasiswaan/LPPM) atau tahun tanggal_mulai (rekognisi/kerja sama). */
    private static function year($item): ?int
    {
        $attrs = $item->getAttributes();
        if (!empty($attrs['tahun'])) {
            return (int) $attrs['tahun'];
        }

        return array_key_exists('tanggal_mulai', $attrs) && $item->tanggal_mulai
            ? (int) $item->tanggal_mulai->year
            : null;
    }

    /** Satu kartu rincian: hitung per nilai kolom, urut terbanyak. */
    private static function group(string $title, Collection $items, string $field, ?array $labels, int $limit = 0): array
    {
        $counts = $items->pluck($field)->filter(fn ($v) => filled($v))->countBy()->sortDesc();
        if ($limit) {
            $counts = $counts->take($limit);
        }
        $total = $counts->sum();
        $rows = $counts->map(fn ($n, $k) => [
            'label' => $labels[$k] ?? Str::headline((string) $k),
            'value' => (int) $n,
            'pct'   => self::pct((int) $n, (int) $total),
        ])->values()->all();

        return ['title' => $title, 'total' => (int) $total, 'rows' => $rows];
    }

    private static function pct(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round($part / $whole * 100) : 0;
    }
}
