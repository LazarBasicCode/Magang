<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\Rekognisi;
use App\Support\AdminDashboardData;
use App\Support\DashboardCharts;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Satu-satunya controller dashboard. Semua role dirender oleh satu view
 * (dashboard.blade.php). Controller ini HANYA mengambil & menghitung data;
 * tampilan per role ada di partials/dashboard/{admin,dosen,mahasiswa}.blade.php
 * yang semuanya memakai kartu-kartu di partials/dashboard/content.blade.php.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        [$role, $data] = match (true) {
            in_array($user->role, ['admin', 'superadmin'], true) => ['admin', AdminDashboardData::build($user)],
            $user->role === 'dosen'     => ['dosen', $this->dosen($user)],
            $user->role === 'mahasiswa' => ['mahasiswa', $this->mahasiswa($user)],
            default                     => ['generic', []],
        };

        return view('dashboard', $data + ['role' => $role, 'user' => $user]);
    }

    /**
     * Dashboard mahasiswa: 4 sumber (Kemahasiswaan, LPPM Mahasiswa, Rekognisi, Kerja Sama).
     */
    private function mahasiswa($user): array
    {
        $mahasiswa = $user->mahasiswa;

        $kemahasiswaan = $mahasiswa
            ? Kemahasiswaan::approved()->where('mahasiswa_id', $mahasiswa->id)->get()
            : collect();
        $lppmMahasiswa = $mahasiswa
            ? LppmMahasiswa::approved()->where('mahasiswa_id', $mahasiswa->id)->get()
            : collect();
        $rekognisi = Rekognisi::approved()->where('user_id', $user->id)
            ->where('tipe_user', 'mahasiswa')->get();
        $kerjaSama = KerjaSama::approved()->where('user_id', $user->id)
            ->where('tipe_user', 'mahasiswa')->get();

        $total = $kemahasiswaan->count() + $lppmMahasiswa->count()
            + $rekognisi->count() + $kerjaSama->count();

        // Capaian internasional: dari 2 sumber yang punya makna "tingkat/jenis internasional"
        $internasionalBase = $kemahasiswaan->count() + $rekognisi->count();
        $internasionalPct = $internasionalBase > 0
            ? round(($kemahasiswaan->where('tingkat', 'internasional')->count()
                + $rekognisi->where('skala', 'internasional')->count()) / $internasionalBase * 100)
            : 0;

        $earliest = collect([$kemahasiswaan, $lppmMahasiswa, $rekognisi, $kerjaSama])
            ->flatMap(fn ($c) => $c->pluck('created_at'))->filter()->min();
        $monthsActive = $earliest ? max(1, Carbon::parse($earliest)->diffInMonths(now()) + 1) : 1;

        $stats = [
            'total'             => $total,
            'kemahasiswaan'     => $kemahasiswaan->count(),
            'lppm_mahasiswa'    => $lppmMahasiswa->count(),
            'rekognisi'         => $rekognisi->count(),
            'kerja_sama'        => $kerjaSama->count(),
            'internasional_pct' => $internasionalPct,
            'avg_per_month'     => round($total / $monthsActive, 1),
        ];

        $kategoriDonut = [
            ['label' => 'Kemahasiswaan', 'value' => $kemahasiswaan->count(), 'color' => 'var(--chart-1)'],
            ['label' => 'LPPM Mahasiswa', 'value' => $lppmMahasiswa->count(), 'color' => 'var(--chart-2)'],
            ['label' => 'Rekognisi', 'value' => $rekognisi->count(), 'color' => 'var(--chart-3)'],
            ['label' => 'Kerja Sama', 'value' => $kerjaSama->count(), 'color' => 'var(--chart-4)'],
        ];

        $tingkatCounts = $kemahasiswaan->countBy('tingkat');
        $tingkatDonut = [];
        foreach ([
            'lokal'         => ['Lokal', 'var(--chart-2)'],
            'nasional'      => ['Nasional', 'var(--chart-3)'],
            'internasional' => ['Internasional', 'var(--chart-5)'],
        ] as $key => [$label, $color]) {
            $tingkatDonut[] = ['label' => $label, 'value' => $tingkatCounts->get($key, 0), 'color' => $color];
        }

        $charts = DashboardCharts::build(
            $kemahasiswaan->concat($lppmMahasiswa),
            $rekognisi->concat($kerjaSama),
            'Akademik (Kemahasiswaan + LPPM)',
            'Eksternal (Rekognisi + Kerja Sama)'
        );
        $trendDatasets = $charts['trend'];
        $barDatasets = $charts['bar'];

        $recent = collect()
            ->concat($kemahasiswaan->map(fn ($i) => ['title' => $i->nama_kegiatan, 'menu' => 'Kemahasiswaan', 'icon' => 'school', 'date' => $i->created_at]))
            ->concat($lppmMahasiswa->map(fn ($i) => ['title' => $i->judul, 'menu' => 'LPPM Mahasiswa', 'icon' => 'person', 'date' => $i->created_at]))
            ->concat($rekognisi->map(fn ($i) => ['title' => $i->jabatan_efektif ?? $i->mitra, 'menu' => 'Rekognisi', 'icon' => 'workspace_premium', 'date' => $i->created_at]))
            ->concat($kerjaSama->map(fn ($i) => ['title' => $i->judul_kegiatan, 'menu' => 'Kerja Sama', 'icon' => 'handshake', 'date' => $i->created_at]))
            ->filter(fn ($i) => !is_null($i['date']))
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return compact('stats', 'kategoriDonut', 'tingkatDonut', 'trendDatasets', 'barDatasets', 'recent');
    }

    /**
     * Dashboard khusus dosen. Sumber datanya 3 menu yang relevan untuk dosen:
     * LPPM Dosen (jurnal Q/Sinta, HKI, buku), Rekognisi, dan Kerja Sama.
     * (Kemahasiswaan tidak dipakai karena itu data kegiatan mahasiswa.)
     */
    private function dosen($user): array
    {
        $dosen = $user->dosen;

        $lppm = $dosen
            ? LppmDosen::where('dosen_id', $dosen->id)->get()
            : collect();
        $rekognisi = Rekognisi::approved()->where('user_id', $user->id)
            ->where('tipe_user', 'dosen')->get();
        $kerjaSama = KerjaSama::approved()->where('user_id', $user->id)
            ->where('tipe_user', 'dosen')->get();

        $total = $lppm->count() + $rekognisi->count() + $kerjaSama->count();

        // Capaian internasional: jurnal Q internasional + rekognisi internasional
        // + kerja sama yang jenisnya berlabel internasional, dari seluruh kegiatan.
        $jenisKerjaSamaIntl = ['conference_internasional', 'pengabdian_internasional', 'research_internasional'];
        $internasionalCount = $lppm->where('jenis', 'q_internasional')->count()
            + $rekognisi->where('skala', 'internasional')->count()
            + $kerjaSama->whereIn('jenis', $jenisKerjaSamaIntl)->count();
        $internasionalPct = $total > 0 ? round($internasionalCount / $total * 100) : 0;

        $earliestDate = collect([$lppm, $rekognisi, $kerjaSama])
            ->flatMap(fn ($c) => $c->pluck('created_at'))
            ->filter()
            ->min();
        $monthsActive = $earliestDate
            ? max(1, Carbon::parse($earliestDate)->diffInMonths(now()) + 1)
            : 1;

        $stats = [
            'total'             => $total,
            'lppm_dosen'        => $lppm->count(),
            'publikasi_q'       => $lppm->where('jenis', 'q_internasional')->count(),
            'rekognisi'         => $rekognisi->count(),
            'kerja_sama'        => $kerjaSama->count(),
            'internasional_pct' => $internasionalPct,
            'avg_per_month'     => round($total / $monthsActive, 1),
            'sinta_nasional'    => $lppm->where('jenis', 'sinta_nasional')->count(),
            'hki'               => $lppm->where('jenis', 'hki')->count(),
            'buku'              => $lppm->where('jenis', 'book')->count(),
            'mitra_unik'        => $rekognisi->concat($kerjaSama)
                ->pluck('mitra')
                ->map(fn ($m) => mb_strtolower(trim((string) $m)))
                ->filter()
                ->unique()
                ->count(),
        ];

        $kategoriDonut = [
            ['label' => 'LPPM Dosen', 'value' => $lppm->count(), 'color' => 'var(--chart-2)'],
            ['label' => 'Rekognisi', 'value' => $rekognisi->count(), 'color' => 'var(--chart-3)'],
            ['label' => 'Kerja Sama', 'value' => $kerjaSama->count(), 'color' => 'var(--chart-4)'],
        ];

        // Donut 2: luaran LPPM per jenis
        $jenisLppm = [
            'q_internasional' => ['Jurnal Q Internasional', 'var(--chart-5)'],
            'sinta_nasional'  => ['Jurnal Sinta Nasional', 'var(--chart-3)'],
            'hki'             => ['HKI', 'var(--chart-2)'],
            'book'            => ['Buku', 'var(--chart-4)'],
        ];
        $jenisCounts = $lppm->countBy('jenis');
        $luaranDonut = [];
        foreach ($jenisLppm as $key => [$label, $color]) {
            $luaranDonut[] = ['label' => $label, 'value' => $jenisCounts->get($key, 0), 'color' => $color];
        }

        // Kualitas publikasi: sebaran peringkat jurnal (Q1-Q4 untuk jurnal
        // internasional, S1-S6 untuk Sinta). Peringkat kosong/di luar pola
        // dikelompokkan ke "Lainnya" supaya tidak ada data yang hilang.
        $jurnal = $lppm->whereIn('jenis', ['q_internasional', 'sinta_nasional']);
        $kualitasCounts = $jurnal
            ->map(function ($i) {
                $r = strtoupper(trim((string) $i->peringkat));
                return preg_match('/^(Q[1-4]|S[1-6])$/', $r) ? $r : 'Lainnya';
            })
            ->countBy();
        $kualitasPublikasi = [];
        foreach (['Q1', 'Q2', 'Q3', 'Q4', 'S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'Lainnya'] as $key) {
            $val = $kualitasCounts->get($key, 0);
            if ($val > 0) {
                $kualitasPublikasi[] = ['label' => $key, 'value' => $val];
            }
        }
        $kualitasMax = max(1, collect($kualitasPublikasi)->max('value') ?? 1);

        // Kelengkapan data: jurnal yang sudah punya link DOI
        $jurnalDenganDoi = $jurnal->filter(fn ($i) => filled($i->link_doi))->count();
        $jurnalTotal = $jurnal->count();
        $kelengkapan = [
            'total'      => $jurnalTotal,
            'lengkap'    => $jurnalDenganDoi,
            'pct'        => $jurnalTotal > 0 ? round($jurnalDenganDoi / $jurnalTotal * 100) : 0,
            'perlu'      => $jurnal->filter(fn ($i) => blank($i->link_doi))
                ->sortByDesc('created_at')
                ->take(3)
                ->map(fn ($i) => ['title' => $i->judul, 'jenis' => $i->jenis === 'q_internasional' ? 'Jurnal Q' : 'Sinta'])
                ->values(),
        ];

        // Ragam kerja sama per jenis (jenis "lainnya" ikut dihitung sendiri)
        $jenisKs = [
            'keynote_session'          => ['Keynote Session', 'var(--chart-1)'],
            'guest_lecture'            => ['Guest Lecture', 'var(--chart-2)'],
            'pengabdian_internasional' => ['Pengabdian Intl.', 'var(--chart-3)'],
            'research_internasional'   => ['Research Intl.', 'var(--chart-4)'],
            'conference_internasional' => ['Conference Intl.', 'var(--chart-5)'],
            'lainnya'                  => ['Lainnya', 'var(--border)'],
        ];
        $ksCounts = $kerjaSama->countBy('jenis');
        $ragamKerjaSama = [];
        foreach ($jenisKs as $key => [$label, $color]) {
            $ragamKerjaSama[] = ['label' => $label, 'value' => $ksCounts->get($key, 0), 'color' => $color];
        }

        // Kegiatan berlangsung / akan datang (kerja sama + rekognisi yang belum selesai)
        $today = now()->startOfDay();
        $berjalan = $kerjaSama->map(fn ($i) => [
                'title' => $i->judul_kegiatan,
                'menu'  => 'Kerja Sama',
                'icon'  => 'handshake',
                'start' => $i->tanggal_mulai,
                'end'   => $i->tanggal_selesai,
            ])
            ->concat($rekognisi->map(fn ($i) => [
                'title' => $i->jabatan_efektif ?? $i->mitra,
                'menu'  => 'Rekognisi',
                'icon'  => 'workspace_premium',
                'start' => $i->tanggal_mulai,
                'end'   => $i->tanggal_selesai,
            ]))
            ->filter(fn ($i) => $i['start'] && $i['end'] && $i['end']->greaterThanOrEqualTo($today))
            ->map(function ($i) use ($today) {
                $i['status'] = $i['start']->greaterThan($today) ? 'Akan Datang' : 'Berlangsung';
                return $i;
            })
            ->sortBy('start')
            ->values();
        $stats['kegiatan_aktif'] = $berjalan->count();
        $berjalan = $berjalan->take(4)->values();

        $charts = DashboardCharts::build(
            $lppm,
            $rekognisi->concat($kerjaSama),
            'Riset & Publikasi (LPPM)',
            'Eksternal (Rekognisi + Kerja Sama)'
        );
        $trendDatasets = $charts['trend'];
        $barDatasets = $charts['bar'];

        $recent = collect()
            ->concat($lppm->map(fn ($i) => [
                'title' => $i->judul,
                'menu'  => 'LPPM Dosen',
                'icon'  => 'co_present',
                'date'  => $i->created_at,
            ]))
            ->concat($rekognisi->map(fn ($i) => [
                'title' => $i->jabatan_efektif ?? $i->mitra,
                'menu'  => 'Rekognisi',
                'icon'  => 'workspace_premium',
                'date'  => $i->created_at,
            ]))
            ->concat($kerjaSama->map(fn ($i) => [
                'title' => $i->judul_kegiatan,
                'menu'  => 'Kerja Sama',
                'icon'  => 'handshake',
                'date'  => $i->created_at,
            ]))
            ->filter(fn ($i) => !is_null($i['date']))
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return compact(
            'stats', 'kategoriDonut', 'luaranDonut', 'trendDatasets', 'barDatasets', 'recent',
            'kualitasPublikasi', 'kualitasMax', 'kelengkapan', 'ragamKerjaSama', 'berjalan'
        );
    }
}
