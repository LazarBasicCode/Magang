{{-- DASHBOARD DOSEN. Data dari DashboardController::dosen() (Rekognisi, Kerja Sama). --}}
@php
    // Widget khusus dosen, memakai format yang sama dengan widget admin
    // (lihat partials/dashboard/widgets.blade.php).
    $widgets = [
        [
            'type' => 'bars', 'title' => 'Kualitas Publikasi',
            'sub' => 'Sebaran peringkat jurnal (Q1–Q4 & Sinta S1–S6)',
            'wide' => false, 'empty' => 'Belum ada jurnal dengan peringkat tercatat.',
            'fill' => 'is-s',
            'rows' => collect($kualitasPublikasi)->map(fn ($q) => [
                'label' => $q['label'],
                'value' => $q['value'],
                'width' => max(6, round($q['value'] / $kualitasMax * 100)),
                'fill'  => str_starts_with($q['label'], 'Q') ? 'is-q' : 'is-s',
            ])->all(),
        ],
        [
            'type' => 'attention', 'title' => 'Kelengkapan Data Jurnal',
            'sub' => 'Jurnal yang sudah dilengkapi link DOI',
            'pct' => $kelengkapan['pct'], 'icon' => 'link_off',
            'note' => $kelengkapan['lengkap'] . ' dari ' . $kelengkapan['total'] . ' jurnal punya DOI',
            'rows' => $kelengkapan['perlu']->map(fn ($p) => [
                'title' => $p['title'], 'meta' => $p['jenis'] . ' · belum ada link DOI',
            ])->all(),
            'ok' => $kelengkapan['total'] > 0 ? 'Semua jurnal sudah punya DOI. Mantap!' : null,
        ],
        [
            'type' => 'donut', 'title' => 'Ragam Kerja Sama',
            'sub' => 'Keynote, guest lecture, pengabdian, riset, dan lainnya',
            'caption' => 'Kerja Sama', 'data' => $ragamKerjaSama,
        ],
        [
            'type' => 'upcoming', 'title' => 'Berlangsung & Akan Datang',
            'sub' => $stats['kegiatan_aktif'] . ' kerja sama/rekognisi yang belum selesai',
            'rows' => collect($berjalan)->map(fn ($b) => ['who' => $b['menu']] + $b)->all(), // baris dosen menampilkan nama menu
        ],
    ];
@endphp

@include('partials.dashboard.content', [
    'brand'    => 'SIDA',
    'subtitle' => 'Ringkasan publikasi, rekognisi & kerja samamu di Sistem Informasi Data Akademik',
    'action'   => null,
        'hero'     => [
            ['icon' => 'local_fire_department', 'label' => 'Total Kegiatan',        'value' => $stats['total'],             'unit' => 'kegiatan'],
            ['icon' => 'trending_up',           'label' => 'Rata-rata per Bulan',   'value' => $stats['avg_per_month'],     'unit' => 'kegiatan/bln'],
            ['icon' => 'public',                'label' => 'Capaian Internasional', 'value' => $stats['internasional_pct'], 'unit' => '%'],
        ],
    'cards'    => [
        ['label' => 'Rekognisi',              'value' => $stats['rekognisi'],             'color' => 'warning', 'icon' => 'workspace_premium'],
        ['label' => 'Jurnal Internasional',   'value' => $stats['jurnal_internasional'],  'color' => 'success', 'icon' => 'menu_book'],
        ['label' => 'Kerja Sama',             'value' => $stats['kerja_sama'],  'color' => 'primary', 'icon' => 'handshake'],
        ['label' => 'Mitra Unik',              'value' => $stats['mitra_unik'], 'color' => 'info',    'icon' => 'apartment'],
    ],
    'cards2'   => [
        ['label' => 'Jurnal Nasional',       'value' => $stats['jurnal_nasional'], 'color' => 'info',    'icon' => 'article'],
        ['label' => 'HKI',                   'value' => $stats['hki'],            'color' => 'warning', 'icon' => 'verified'],
        ['label' => 'Buku',                  'value' => $stats['buku'],           'color' => 'success', 'icon' => 'auto_stories'],
    ],
    'donuts'   => [
        ['title' => 'Distribusi Kegiatan per Kategori', 'sub' => 'Sebaran kegiatanmu di 2 menu', 'caption' => 'Kegiatan', 'data' => $kategoriDonut],
        ['title' => 'Distribusi Publikasi', 'sub' => 'Jurnal, HKI, dan buku di Rekognisi', 'caption' => 'Publikasi', 'data' => $luaranDonut],
    ],
    'widgets'       => $widgets,
    'trendSub'      => 'Riset & Publikasi vs Eksternal · bandingkan keseimbangan portofoliomu dari waktu ke waktu',
    'trendDatasets' => $trendDatasets,
    'barSub'        => 'Gabungan 2 kategori',
    'barDatasets'   => $barDatasets,
    'recentSub'     => 'Kegiatan terakhir yang kamu tambahkan',
    'recent'        => $recent,
    'showWho'       => false,
])
