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

        // Kurva tren bulanan (12 bulan terakhir, termasuk bulan ini): Akademik vs Eksternal
        $months = collect(range(11, 0))->map(fn ($i) => now()->copy()->subMonths($i)->startOfMonth());
        $monthLabels = $months->map(fn ($m) => $m->translatedFormat('M'))->values();

        $akademikSource = $kemahasiswaan->concat($lppmMahasiswa);
        $eksternalSource = $rekognisi->concat($kerjaSama);

        $countByMonth = function ($collection) use ($months) {
            return $months->map(function ($month) use ($collection) {
                return $collection->filter(function ($item) use ($month) {
                    return $item->created_at && $item->created_at->isSameMonth($month) && $item->created_at->isSameYear($month);
                })->count();
            })->values();
        };

        $trend = [
            'labels' => $monthLabels,
            'series' => [
                ['label' => 'Akademik (Kemahasiswaan + LPPM)', 'color' => 'var(--chart-1)', 'data' => $countByMonth($akademikSource)],
                ['label' => 'Eksternal (Rekognisi + Kerja Sama)', 'color' => 'var(--chart-5)', 'data' => $countByMonth($eksternalSource)],
            ],
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

        // Aktivitas terbaru: gabungan 4 sumber, diurutkan dari yang terbaru
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
            ->take(6)
            ->values();

        return view('dashboard-mahasiswa', compact(
            'stats', 'kategoriDonut', 'tingkatDonut', 'trend', 'yearlyBar', 'recent'
        ));
    }
}
