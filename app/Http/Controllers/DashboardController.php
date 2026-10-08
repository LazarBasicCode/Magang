<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\Rekognisi;
use Illuminate\Http\Request;
use App\Support\AdminDashboardData;
use App\Support\DashboardCharts;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Dashboard versi lengkap dibuat untuk role mahasiswa, dosen, dan admin.
        if ($user->role === 'dosen') {
            return $this->dosen($user);
        }

        // Admin & superadmin: satu view (dashboard-admin) dengan isi menyesuaikan
        // menu yang dipegang admin itu (lihat AdminDashboardData::modeFor()).
        if (in_array($user->role, ['admin', 'superadmin'], true)) {
            return view('dashboard-admin', AdminDashboardData::build($user));
        }

        if ($user->role !== 'mahasiswa') {
            return view('dashboard-generic', [
                'user' => $user,
            ]);
        }

        $mahasiswa = $user->mahasiswa;

        $kemahasiswaan = $mahasiswa
            ? Kemahasiswaan::where('mahasiswa_id', $mahasiswa->id)->get()
            : collect();
        $lppmMahasiswa = $mahasiswa
            ? LppmMahasiswa::where('mahasiswa_id', $mahasiswa->id)->get()
            : collect();
        $rekognisi = Rekognisi::where('user_id', $user->id)
            ->where('tipe_user', 'mahasiswa')->get();
        $kerjaSama = KerjaSama::where('user_id', $user->id)
            ->where('tipe_user', 'mahasiswa')->get();

        $totalKegiatan = $kemahasiswaan->count() + $lppmMahasiswa->count()
            + $rekognisi->count() + $kerjaSama->count();

        // Persentase capaian internasional (dari 4 sumber yang punya makna "tingkat/jenis internasional")
        $internasionalCount = $kemahasiswaan->where('tingkat', 'internasional')->count()
            + $rekognisi->where('jenis', 'internasional')->count();
        $internasionalBase = $kemahasiswaan->count() + $rekognisi->count();
        $internasionalPct = $internasionalBase > 0
            ? round($internasionalCount / $internasionalBase * 100)
            : 0;

        // Rata-rata kegiatan per bulan (dari bulan pertama tercatat s.d sekarang)
        $earliestDate = collect([$kemahasiswaan, $lppmMahasiswa, $rekognisi, $kerjaSama])
            ->flatMap(fn ($c) => $c->pluck('created_at'))
            ->filter()
            ->min();
        $monthsActive = $earliestDate
            ? max(1, Carbon::parse($earliestDate)->diffInMonths(now()) + 1)
            : 1;
        $avgPerMonth = round($totalKegiatan / $monthsActive, 1);

        $stats = [
            'total'              => $totalKegiatan,
            'kemahasiswaan'      => $kemahasiswaan->count(),
            'lppm_mahasiswa'     => $lppmMahasiswa->count(),
            'rekognisi'          => $rekognisi->count(),
            'kerja_sama'         => $kerjaSama->count(),
            'internasional_pct'  => $internasionalPct,
            'avg_per_month'      => $avgPerMonth,
        ];

        // Donut 1: distribusi kegiatan per kategori/menu
        $kategoriDonut = [
            ['label' => 'Kemahasiswaan', 'value' => $kemahasiswaan->count(), 'color' => 'var(--chart-1)'],
            ['label' => 'LPPM Mahasiswa', 'value' => $lppmMahasiswa->count(), 'color' => 'var(--chart-2)'],
            ['label' => 'Rekognisi', 'value' => $rekognisi->count(), 'color' => 'var(--chart-3)'],
            ['label' => 'Kerja Sama', 'value' => $kerjaSama->count(), 'color' => 'var(--chart-4)'],
        ];

        // Donut 2: distribusi tingkat capaian (dari data Kemahasiswaan)
        $tingkatLabels = ['lokal' => 'Lokal', 'nasional' => 'Nasional', 'internasional' => 'Internasional'];
        $tingkatColors = ['lokal' => 'var(--chart-2)', 'nasional' => 'var(--chart-3)', 'internasional' => 'var(--chart-5)'];
        $tingkatCounts = $kemahasiswaan->countBy('tingkat');
        $tingkatDonut = [];
        foreach ($tingkatLabels as $key => $label) {
            $tingkatDonut[] = [
                'label' => $label,
                'value' => $tingkatCounts->get($key, 0),
                'color' => $tingkatColors[$key],
            ];
        }

        // Label sumbu bawah yang adaptif per granularitas, dipakai bersama
        // oleh kurva tren dan bar chart supaya konsisten:
        // - harian  : nama hari (Mon, Tue, ...)
        // - mingguan: cuma tanggal; nama bulan muncul hanya saat bulan berganti
        // - bulanan : nama bulan tiap titik; tahun muncul hanya saat tahun berganti
        // - tahunan : cuma angka tahun
        $smartLabel = function ($point, $prevPoint, $unit) {
            switch ($unit) {
                case 'day':
                    return $point->translatedFormat('D');
                case 'week':
                    $isNewMonth = !$prevPoint || $point->month !== $prevPoint->month || $point->year !== $prevPoint->year;
                    return $isNewMonth ? $point->translatedFormat('d M') : $point->translatedFormat('d');
                case 'month':
                    $isNewYear = !$prevPoint || $point->year !== $prevPoint->year;
                    return $isNewYear ? $point->translatedFormat('M Y') : $point->translatedFormat('M');
                default:
                    return (string) $point;
            }
        };

        $mapSmartLabels = function ($points, $unit) use ($smartLabel) {
            $prev = null;
            return $points->values()->map(function ($p) use (&$prev, $unit, $smartLabel) {
                $label = $smartLabel($p, $prev, $unit);
                $prev = $p;
                return $label;
            })->values();
        };

        $now = now();
        $dailyPoints = collect(range(13, 0))->map(fn ($i) => $now->copy()->subDays($i)->startOfDay())->values();
        $weeklyPoints = collect(range(7, 0))->map(fn ($i) => $now->copy()->subWeeks($i)->startOfWeek())->values();
        $months = collect(range(11, 0))->map(fn ($i) => $now->copy()->subMonths($i)->startOfMonth())->values();

        $dailyLabels = $mapSmartLabels($dailyPoints, 'day');
        $weeklyLabels = $mapSmartLabels($weeklyPoints, 'week');
        $monthlyLabels = $mapSmartLabels($months, 'month');

        $akademikSource = $kemahasiswaan->concat($lppmMahasiswa);
        $eksternalSource = $rekognisi->concat($kerjaSama);

        $countBetweenFor = function ($collection, $start, $end) {
            return $collection->filter(function ($item) use ($start, $end) {
                return $item->created_at && $item->created_at->between($start, $end);
            })->count();
        };

        $buildTrend = function ($points, $labels, $startFn, $endFn) use ($akademikSource, $eksternalSource, $countBetweenFor) {
            return [
                'labels' => $labels,
                'series' => [
                    [
                        'label' => 'Akademik (Kemahasiswaan + LPPM)',
                        'color' => 'var(--chart-1)',
                        'data' => $points->map(fn ($p) => $countBetweenFor($akademikSource, $startFn($p), $endFn($p)))->values(),
                    ],
                    [
                        'label' => 'Eksternal (Rekognisi + Kerja Sama)',
                        'color' => 'var(--chart-5)',
                        'data' => $points->map(fn ($p) => $countBetweenFor($eksternalSource, $startFn($p), $endFn($p)))->values(),
                    ],
                ],
            ];
        };

        $dailyTrend = $buildTrend(
            $dailyPoints,
            $dailyLabels,
            fn ($d) => $d,
            fn ($d) => $d->copy()->endOfDay()
        );

        $weeklyTrend = $buildTrend(
            $weeklyPoints,
            $weeklyLabels,
            fn ($w) => $w,
            fn ($w) => $w->copy()->endOfWeek()
        );

        $monthlyTrend = $buildTrend(
            $months,
            $monthlyLabels,
            fn ($m) => $m->copy()->startOfMonth(),
            fn ($m) => $m->copy()->endOfMonth()
        );

        // Tahunan: sampai 6 tahun terakhir yang punya data (fallback tahun ini kalau kosong)
        $yearPoints = collect([
            $kemahasiswaan->pluck('created_at'),
            $lppmMahasiswa->pluck('created_at'),
            $rekognisi->pluck('created_at'),
            $kerjaSama->pluck('created_at'),
        ])->flatten()->filter()->map(fn ($d) => $d->year)->unique()->sort()->values();
        if ($yearPoints->isEmpty()) {
            $yearPoints = collect([$now->year]);
        }
        $yearPoints = $yearPoints->slice(-6)->values();
        $yearlyLabels = $yearPoints->map(fn ($y) => (string) $y)->values();
        $yearlyTrend = $buildTrend(
            $yearPoints,
            $yearlyLabels,
            fn ($y) => Carbon::create($y, 1, 1)->startOfYear(),
            fn ($y) => Carbon::create($y, 1, 1)->endOfYear()
        );

        $trendDatasets = [
            'harian'   => $dailyTrend,
            'mingguan' => $weeklyTrend,
            'bulanan'  => $monthlyTrend,
            'tahunan'  => $yearlyTrend,
        ];

        // Bar chart: total kegiatan per tahun (gabungan 4 sumber)
        $yearsFromTahun = $kemahasiswaan->pluck('tahun')
            ->concat($lppmMahasiswa->pluck('tahun'));
        $yearsFromDate = $rekognisi->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->year)
            ->concat($kerjaSama->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->year));
        $allYears = $yearsFromTahun->concat($yearsFromDate)->filter()->unique()->sort()->values();

        if ($allYears->isEmpty()) {
            $allYears = collect([now()->year]);
        }
        // Batasi maksimal 6 tahun terakhir supaya tidak terlalu padat
        $allYears = $allYears->slice(-6)->values();

        $yearlyBar = $allYears->map(function ($year) use ($kemahasiswaan, $lppmMahasiswa, $rekognisi, $kerjaSama) {
            $count = $kemahasiswaan->where('tahun', $year)->count()
                + $lppmMahasiswa->where('tahun', $year)->count()
                + $rekognisi->filter(fn ($i) => $i->tanggal_mulai && $i->tanggal_mulai->year === $year)->count()
                + $kerjaSama->filter(fn ($i) => $i->tanggal_mulai && $i->tanggal_mulai->year === $year)->count();

            return ['label' => (string) $year, 'value' => $count];
        })->values();

        // Bar chart untuk granularitas lain (harian/mingguan/bulanan) memakai
        // waktu input data (created_at) karena field "tahun" tidak punya
        // presisi harian. Digabung dari 4 sumber yang sama seperti di atas.
        // Titik & label memakai $dailyPoints/$weeklyPoints/$months dan
        // $dailyLabels/$weeklyLabels/$monthlyLabels yang sama dengan kurva tren,
        // supaya kedua chart konsisten.
        $allActivities = $kemahasiswaan->concat($lppmMahasiswa)->concat($rekognisi)->concat($kerjaSama);

        $countBetween = function ($start, $end) use ($allActivities) {
            return $allActivities->filter(function ($item) use ($start, $end) {
                return $item->created_at && $item->created_at->between($start, $end);
            })->count();
        };

        $dailyBar = $dailyPoints->map(function ($day, $i) use ($dailyLabels, $countBetween) {
            return [
                'label' => $dailyLabels[$i],
                'value' => $countBetween($day, $day->copy()->endOfDay()),
            ];
        })->values();

        $weeklyBar = $weeklyPoints->map(function ($weekStart, $i) use ($weeklyLabels, $countBetween) {
            return [
                'label' => $weeklyLabels[$i],
                'value' => $countBetween($weekStart, $weekStart->copy()->endOfWeek()),
            ];
        })->values();

        $monthlyBar = $months->map(function ($month, $i) use ($monthlyLabels, $countBetween) {
            return [
                'label' => $monthlyLabels[$i],
                'value' => $countBetween($month->copy()->startOfMonth(), $month->copy()->endOfMonth()),
            ];
        })->values();

        $barDatasets = [
            'harian'  => $dailyBar,
            'mingguan' => $weeklyBar,
            'bulanan' => $monthlyBar,
            'tahunan' => $yearlyBar,
        ];

        // Aktivitas terbaru: gabungan 4 sumber, diurutkan dari yang terbaru (maks. 4)
        $recent = collect()
            ->concat($kemahasiswaan->map(fn ($i) => [
                'title' => $i->nama_kegiatan,
                'menu'  => 'Kemahasiswaan',
                'icon'  => 'school',
                'date'  => $i->created_at,
            ]))
            ->concat($lppmMahasiswa->map(fn ($i) => [
                'title' => $i->judul,
                'menu'  => 'LPPM Mahasiswa',
                'icon'  => 'person',
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

        return view('dashboard-mahasiswa', compact(
            'stats', 'kategoriDonut', 'tingkatDonut', 'trendDatasets', 'barDatasets', 'recent'
        ));
    }

    /**
     * Dashboard khusus dosen. Sumber datanya 3 menu yang relevan untuk dosen:
     * LPPM Dosen (jurnal Q/Sinta, HKI, buku), Rekognisi, dan Kerja Sama.
     * (Kemahasiswaan tidak dipakai karena itu data kegiatan mahasiswa.)
     */
    private function dosen($user)
    {
        $dosen = $user->dosen;

        $lppm = $dosen
            ? LppmDosen::where('dosen_id', $dosen->id)->get()
            : collect();
        $rekognisi = Rekognisi::where('user_id', $user->id)
            ->where('tipe_user', 'dosen')->get();
        $kerjaSama = KerjaSama::where('user_id', $user->id)
            ->where('tipe_user', 'dosen')->get();

        $total = $lppm->count() + $rekognisi->count() + $kerjaSama->count();

        // Capaian internasional: jurnal Q internasional + rekognisi internasional
        // + kerja sama yang jenisnya berlabel internasional, dari seluruh kegiatan.
        $jenisKerjaSamaIntl = ['conference_internasional', 'pengabdian_internasional', 'research_internasional'];
        $internasionalCount = $lppm->where('jenis', 'q_internasional')->count()
            + $rekognisi->where('jenis', 'internasional')->count()
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

        return view('dashboard-dosen', compact(
            'stats', 'kategoriDonut', 'luaranDonut', 'trendDatasets', 'barDatasets', 'recent',
            'kualitasPublikasi', 'kualitasMax', 'kelengkapan', 'ragamKerjaSama', 'berjalan'
        ));
    }
}
