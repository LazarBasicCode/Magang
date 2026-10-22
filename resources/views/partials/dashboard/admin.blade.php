{{--
    DASHBOARD ADMIN & SUPERADMIN.
    Data lengkap sudah disusun App\Support\AdminDashboardData (isinya menyesuaikan $mode);
    di sini hanya diteruskan ke kartu-kartu di partials/dashboard/content.
--}}
@include('partials.dashboard.content', [
    'brand'         => 'SIDA · ' . $meta['title'],
    'subtitle'      => $meta['subtitle'],
    'action'        => empty($meta['menu_url']) ? null : ['url' => $meta['menu_url'], 'label' => $meta['menu_label']],
    'hero'          => $hero,
    'cards'         => $cards,
    'cards2'        => $cards2,
    'donuts'        => $donuts,
    'widgets'       => $widgets,
    'trendSub'      => $groups[0]['label'] . ' vs ' . $groups[1]['label'] . ' · seluruh data yang masuk ke sistem',
    'trendDatasets' => $trendDatasets,
    'barSub'        => 'Total gabungan',
    'barDatasets'   => $barDatasets,
    'recentSub'     => 'Data terakhir yang masuk, lengkap dengan pemiliknya',
    'recent'        => $recent,
    'showWho'       => true,
])
