{{-- DASHBOARD DOSEN. Data dari DashboardController::dosen() (LPPM Dosen, Rekognisi, Kerja Sama). --}}
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
    'subtitle' => 'Ringkasan riset, publikasi & kerja samamu di Sistem Informasi Data Akademik',
    'action'   => null,
        'hero'     => [
            ['icon' => 'local_fire_department', 'label' => 'Total Kegiatan',        'value' => $stats['total'],             'unit' => 'kegiatan'],
            ['icon' => 'trending_up',           'label' => 'Rata-rata per Bulan',   'value' => $stats['avg_per_month'],     'unit' => 'kegiatan/bln'],
            ['icon' => 'public',                'label' => 'Capaian Internasional', 'value' => $stats['internasional_pct'], 'unit' => '%'],
        ],
    'cards'    => [
        ['label' => 'LPPM Dosen',             'value' => $stats['lppm_dosen'],  'color' => 'info',    'icon' => 'co_present'],
        ['label' => 'Jurnal Q Internasional', 'value' => $stats['publikasi_q'], 'color' => 'success', 'icon' => 'menu_book'],
        ['label' => 'Rekognisi',              'value' => $stats['rekognisi'],   'color' => 'warning', 'icon' => 'workspace_premium'],
        ['label' => 'Kerja Sama',             'value' => $stats['kerja_sama'],  'color' => 'primary', 'icon' => 'handshake'],
    ],
    'cards2'   => [
        ['label' => 'Jurnal Sinta Nasional', 'value' => $stats['sinta_nasional'], 'color' => 'info',    'icon' => 'article'],
        ['label' => 'HKI',                   'value' => $stats['hki'],            'color' => 'warning', 'icon' => 'verified'],
        ['label' => 'Buku',                  'value' => $stats['buku'],           'color' => 'success', 'icon' => 'auto_stories'],
        ['label' => 'Mitra Unik',            'value' => $stats['mitra_unik'],     'color' => 'primary', 'icon' => 'apartment'],
    ],
    'donuts'   => [
        ['title' => 'Distribusi Kegiatan per Kategori', 'sub' => 'Sebaran kegiatanmu di 3 menu', 'caption' => 'Kegiatan', 'data' => $kategoriDonut],
        ['title' => 'Distribusi Luaran LPPM', 'sub' => 'Jurnal, HKI, dan buku di LPPM Dosen', 'caption' => 'Luaran', 'data' => $luaranDonut],
    ],
    'widgets'       => $widgets,
    'trendSub'      => 'Riset & Publikasi vs Eksternal · bandingkan keseimbangan portofoliomu dari waktu ke waktu',
    'trendDatasets' => $trendDatasets,
    'barSub'        => 'Gabungan 3 kategori',
    'barDatasets'   => $barDatasets,
    'recentSub'     => 'Kegiatan terakhir yang kamu tambahkan',
    'recent'        => $recent,
    'showWho'       => false,
])
