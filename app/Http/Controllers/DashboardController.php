<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LppmMahasiswa;
use App\Models\Rekognisi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Dashboard versi lengkap saat ini baru dibuat untuk role mahasiswa.
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
                'title' => $i->jabatan ?? $i->mitra,
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
}
