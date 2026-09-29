<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pembangun data grafik dashboard (kurva tren + bar chart) untuk 4 granularitas:
 * harian, mingguan, bulanan, tahunan.
 *
 * Dua kelompok data:
 * - $akademik : item yang punya kolom "tahun" (mis. LPPM)
 * - $eksternal: item yang punya kolom "tanggal_mulai" (mis. Rekognisi, Kerja Sama)
 *
 * Logikanya sama dengan yang dipakai dashboard mahasiswa di DashboardController.
 */
class DashboardCharts
{
    /**
     * @return array{trend: array, bar: array}
     */
    public static function build(
        Collection $akademik,
        Collection $eksternal,
        string $akademikLabel,
        string $eksternalLabel
    ): array {
        $now = now();
        $dailyPoints = collect(range(13, 0))->map(fn ($i) => $now->copy()->subDays($i)->startOfDay())->values();
        $weeklyPoints = collect(range(7, 0))->map(fn ($i) => $now->copy()->subWeeks($i)->startOfWeek())->values();
        $months = collect(range(11, 0))->map(fn ($i) => $now->copy()->subMonths($i)->startOfMonth())->values();

        $dailyLabels = self::mapLabels($dailyPoints, 'day');
        $weeklyLabels = self::mapLabels($weeklyPoints, 'week');
        $monthlyLabels = self::mapLabels($months, 'month');

        // Tahun-tahun yang punya data (maks. 6 terakhir), dari waktu input data
        $yearPoints = $akademik->pluck('created_at')
            ->concat($eksternal->pluck('created_at'))
            ->filter()
            ->map(fn ($d) => $d->year)
            ->unique()->sort()->values();
        if ($yearPoints->isEmpty()) {
            $yearPoints = collect([$now->year]);
        }
        $yearPoints = $yearPoints->slice(-6)->values();
        $yearlyLabels = $yearPoints->map(fn ($y) => (string) $y)->values();

        $buildTrend = function ($points, $labels, $startFn, $endFn) use ($akademik, $eksternal, $akademikLabel, $eksternalLabel) {
            return [
                'labels' => $labels,
                'series' => [
                    [
                        'label' => $akademikLabel,
                        'color' => 'var(--chart-1)',
                        'data'  => $points->map(fn ($p) => self::countBetween($akademik, $startFn($p), $endFn($p)))->values(),
                    ],
                    [
                        'label' => $eksternalLabel,
                        'color' => 'var(--chart-5)',
                        'data'  => $points->map(fn ($p) => self::countBetween($eksternal, $startFn($p), $endFn($p)))->values(),
                    ],
                ],
            ];
        };

        $trend = [
            'harian'   => $buildTrend($dailyPoints, $dailyLabels, fn ($d) => $d, fn ($d) => $d->copy()->endOfDay()),
            'mingguan' => $buildTrend($weeklyPoints, $weeklyLabels, fn ($w) => $w, fn ($w) => $w->copy()->endOfWeek()),
            'bulanan'  => $buildTrend($months, $monthlyLabels, fn ($m) => $m->copy()->startOfMonth(), fn ($m) => $m->copy()->endOfMonth()),
            'tahunan'  => $buildTrend(
                $yearPoints,
                $yearlyLabels,
                fn ($y) => Carbon::create($y, 1, 1)->startOfYear(),
                fn ($y) => Carbon::create($y, 1, 1)->endOfYear()
            ),
        ];

        // ---- Bar chart: total kegiatan (gabungan kedua kelompok) ----
        $all = $akademik->concat($eksternal);

        // Tahunan memakai tahun kegiatan (tahun / tanggal_mulai), bukan waktu input
        $years = $akademik->pluck('tahun')
            ->concat($eksternal->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->year))
            ->filter()->unique()->sort()->values();
        if ($years->isEmpty()) {
            $years = collect([$now->year]);
        }
        $years = $years->slice(-6)->values();

        $yearlyBar = $years->map(fn ($year) => [
            'label' => (string) $year,
            'value' => $akademik->where('tahun', $year)->count()
                + $eksternal->filter(fn ($i) => $i->tanggal_mulai && $i->tanggal_mulai->year === $year)->count(),
        ])->values();

        $bar = [
            'harian' => $dailyPoints->map(fn ($day, $i) => [
                'label' => $dailyLabels[$i],
                'value' => self::countBetween($all, $day, $day->copy()->endOfDay()),
            ])->values(),
            'mingguan' => $weeklyPoints->map(fn ($w, $i) => [
                'label' => $weeklyLabels[$i],
                'value' => self::countBetween($all, $w, $w->copy()->endOfWeek()),
            ])->values(),
            'bulanan' => $months->map(fn ($m, $i) => [
                'label' => $monthlyLabels[$i],
                'value' => self::countBetween($all, $m->copy()->startOfMonth(), $m->copy()->endOfMonth()),
            ])->values(),
            'tahunan' => $yearlyBar,
        ];

        return ['trend' => $trend, 'bar' => $bar];
    }

    private static function countBetween(Collection $items, $start, $end): int
    {
        return $items->filter(
            fn ($item) => $item->created_at && $item->created_at->between($start, $end)
        )->count();
    }

    private static function mapLabels(Collection $points, string $unit): Collection
    {
        $prev = null;

        return $points->values()->map(function ($p) use (&$prev, $unit) {
            $label = self::label($p, $prev, $unit);
            $prev = $p;

            return $label;
        })->values();
    }

    /**
     * Label sumbu bawah yang adaptif per granularitas:
     * - harian  : nama hari
     * - mingguan: tanggal; nama bulan muncul saat bulan berganti
     * - bulanan : nama bulan; tahun muncul saat tahun berganti
     */
    private static function label($point, $prev, string $unit): string
    {
        switch ($unit) {
            case 'day':
                return $point->translatedFormat('D');
            case 'week':
                $isNewMonth = !$prev || $point->month !== $prev->month || $point->year !== $prev->year;
                return $isNewMonth ? $point->translatedFormat('d M') : $point->translatedFormat('d');
            default:
                $isNewYear = !$prev || $point->year !== $prev->year;
                return $isNewYear ? $point->translatedFormat('M Y') : $point->translatedFormat('M');
        }
    }
}
