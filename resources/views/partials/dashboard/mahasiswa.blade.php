{{-- DASHBOARD MAHASISWA. Data dari DashboardController::mahasiswa(). --}}
@include('partials.dashboard.content', [
    'brand'    => 'SIDA',
    'subtitle' => 'Ringkasan kegiatan dan rekognisimu di Sistem Informasi Data Akademik',
    'action'   => null,
        'hero'     => [
            ['icon' => 'local_fire_department', 'label' => 'Total Kegiatan',        'value' => $stats['total'],             'unit' => 'kegiatan'],
            ['icon' => 'trending_up',           'label' => 'Rata-rata per Bulan',   'value' => $stats['avg_per_month'],     'unit' => 'kegiatan/bln'],
            ['icon' => 'public',                'label' => 'Capaian Internasional', 'value' => $stats['internasional_pct'], 'unit' => '%'],
        ],
    'cards'    => [
        ['label' => 'Kemahasiswaan',  'value' => $stats['kemahasiswaan'],  'color' => 'info',    'icon' => 'school'],
        ['label' => 'Rekognisi',      'value' => $stats['rekognisi'],      'color' => 'warning', 'icon' => 'workspace_premium'],
        ['label' => 'Kerja Sama',     'value' => $stats['kerja_sama'],     'color' => 'primary', 'icon' => 'handshake'],
    ],
    'cards2'   => [],
    'donuts'   => [
        ['title' => 'Distribusi Kegiatan per Kategori', 'sub' => 'Sebaran kegiatanmu di 3 menu', 'caption' => 'Kegiatan', 'data' => $kategoriDonut],
        ['title' => 'Distribusi Tingkat Capaian', 'sub' => 'Berdasarkan data Kemahasiswaan', 'caption' => 'Capaian', 'data' => $tingkatDonut],
    ],
    'widgets'       => [],
    'trendSub'      => 'Akademik vs Eksternal · bandingkan keseimbangan portofoliomu dari waktu ke waktu',
    'trendDatasets' => $trendDatasets,
    'barSub'        => 'Gabungan 3 kategori',
    'barDatasets'   => $barDatasets,
    'recentSub'     => 'Kegiatan terakhir yang kamu tambahkan',
    'recent'        => $recent,
    'showWho'       => false,
])
