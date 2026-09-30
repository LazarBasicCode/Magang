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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data untuk dashboard admin (resources/views/dashboard-admin.blade.php).
 *
 * Satu view dipakai semua admin. Yang berbeda hanya "mode"-nya, ditentukan dari
 * menu operasional yang levelnya 'penuh' (aturan sama dengan
 * User::adminRoleLabel()):
 *   - tepat SATU menu 'penuh' -> mode menu itu (kemahasiswaan, lppm_mahasiswa,
 *     lppm_dosen, rekognisi, kerja_sama)
 *   - selain itu, dan untuk superadmin -> mode 'sida' (ringkasan semua menu)
 *
 * Tiap mode mengembalikan struktur yang sama supaya view-nya cukup satu:
 *   meta, hero[3], cards[4], cards2[4], donuts[2], widgets[], groups[2], recent[]
 */
class AdminDashboardData
{
    public static function modeFor(User $user): string
    {
        if ($user->role === 'superadmin') {
            return 'sida';
        }

        $levels = $user->allMenuLevels();
        $full = array_values(array_filter(
            User::OPERATIONAL_MENUS,
            fn ($m) => ($levels[$m] ?? 'none') === 'penuh'
        ));

        return count($full) === 1 ? $full[0] : 'sida';
    }

    public static function build(User $user): array
    {
        $mode = self::modeFor($user);

        $data = match ($mode) {
            'kemahasiswaan'  => self::kemahasiswaan(),
            'lppm_mahasiswa' => self::lppmMahasiswa(),
            'lppm_dosen'     => self::lppmDosen(),
            'rekognisi'      => self::rekognisi(),
            'kerja_sama'     => self::kerjaSama(),
            default          => self::sida($user),
        };

        [$a, $b] = $data['groups'];
        $charts = DashboardCharts::build($a['items'], $b['items'], $a['label'], $b['label']);

        return $data + [
            'mode'          => $mode,
            'trendDatasets' => $charts['trend'],
            'barDatasets'   => $charts['bar'],
        ];
    }

    // =====================================================================
    // MODE: KEMAHASISWAAN
    // =====================================================================
    private static function kemahasiswaan(): array
    {
        $items = Kemahasiswaan::with('mahasiswa.user')->get();
        $total = $items->count();
        $mhsTotal = Mahasiswa::count();
        $terlibat = $items->pluck('mahasiswa_id')->unique()->count();
        $intl = $items->where('tingkat', 'internasional')->count();
        $tanpaBukti = $items->filter(fn ($i) => blank($i->bukti_kegiatan));
        $owner = fn ($i) => $i->mahasiswa?->user?->name;

        return [
            'meta' => [
                'title'      => 'Kemahasiswaan',
                'subtitle'   => 'Pantauan prestasi & kegiatan seluruh mahasiswa',
                'menu_label' => 'Kelola Kemahasiswaan',
                'menu_url'   => url('/kemahasiswaan'),
            ],
            'hero' => [
                self::h('Total Kegiatan', $total, 'kegiatan', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($items), 'kegiatan/bln', 'trending_up'),
                self::h('Capaian Internasional', self::pct($intl, $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Total Kegiatan', $total, 'emoji_events', 'info'),
                self::c('Tingkat Nasional', $items->where('tingkat', 'nasional')->count(), 'flag', 'primary'),
                self::c('Tingkat Internasional', $intl, 'public', 'success'),
                self::c('Mahasiswa Terlibat', $terlibat, 'groups', 'warning'),
            ],
            'cards2' => [
                self::c('Akademik', $items->where('tab', 'akademik')->count(), 'school', 'info'),
                self::c('Non-Akademik', $items->where('tab', 'non_akademik')->count(), 'sports_esports', 'primary'),
                self::c('Inbis', $items->where('jenis', 'inbis')->count(), 'rocket_launch', 'success'),
                self::c('Partisipasi Mahasiswa', self::pct($terlibat, $mhsTotal) . '%', 'donut_large', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Distribusi Tingkat', 'Lokal, nasional, dan internasional', 'Kegiatan',
                    self::donut($items, 'tingkat', [
                        'lokal'         => ['Lokal', 'var(--chart-2)'],
                        'nasional'      => ['Nasional', 'var(--chart-3)'],
                        'internasional' => ['Internasional', 'var(--chart-5)'],
                    ])),
                self::donutCard('Akademik vs Non-Akademik', 'Sebaran tab kegiatan', 'Kegiatan',
                    self::donut($items, 'tab', [
                        'akademik'     => ['Akademik', 'var(--chart-1)'],
                        'non_akademik' => ['Non-Akademik', 'var(--chart-4)'],
                    ])),
            ],
            'widgets' => [
                self::barsWidget('Jenis Kegiatan', 'Inbis dan kemahasiswaan', collect([
                    'Inbis'         => $items->where('jenis', 'inbis')->count(),
                    'Kemahasiswaan' => $items->where('jenis', 'kemahasiswaan')->count(),
                ]), 'is-q', false),
                self::attentionWidget(
                    'Perlu Ditindaklanjuti', 'Kelengkapan bukti & keterlibatan mahasiswa',
                    self::pct($total - $tanpaBukti->count(), $total),
                    ($total - $tanpaBukti->count()) . ' dari ' . $total . ' kegiatan punya bukti',
                    $tanpaBukti->sortByDesc('created_at')->take(3)->map(fn ($i) => [
                        'title' => $i->nama_kegiatan,
                        'meta'  => ($owner($i) ?? '-') . ' · belum ada bukti',
                    ])->values()->all(),
                    'Semua kegiatan sudah punya bukti.',
                    max(0, $mhsTotal - $terlibat) . ' dari ' . $mhsTotal . ' mahasiswa belum punya kegiatan tercatat.'
                ),
                self::rankingWidget('Mahasiswa Paling Aktif', 'Jumlah kegiatan terbanyak', 'kegiatan', self::top($items, $owner)),
            ],
            'groups' => [
                ['label' => 'Akademik', 'items' => $items->where('tab', 'akademik')->values()],
                ['label' => 'Non-Akademik', 'items' => $items->where('tab', 'non_akademik')->values()],
            ],
            'recent' => self::recent([[$items, fn ($i) => $i->nama_kegiatan, 'Kemahasiswaan', 'school', $owner]]),
        ];
    }

    // =====================================================================
    // MODE: LPPM MAHASISWA
    // =====================================================================
    private static function lppmMahasiswa(): array
    {
        $items = LppmMahasiswa::with('mahasiswa.user')->get();
        $total = $items->count();
        $mhsTotal = Mahasiswa::count();
        $terlibat = $items->pluck('mahasiswa_id')->unique()->count();
        $jurnal = $items->whereIn('jenis', ['sinta_nasional', 'jurnal_internasional']);
        $conf = $items->where('jenis', 'conference_internasional');
        $intl = $items->whereIn('jenis', ['jurnal_internasional', 'conference_internasional'])->count();
        $owner = fn ($i) => $i->mahasiswa?->user?->name;
        $tanpaDoi = $jurnal->filter(fn ($i) => blank($i->link_doi));

        return [
            'meta' => [
                'title'      => 'LPPM Mahasiswa',
                'subtitle'   => 'Pantauan publikasi & conference seluruh mahasiswa',
                'menu_label' => 'Kelola LPPM Mahasiswa',
                'menu_url'   => url('/lppm/mahasiswa'),
            ],
            'hero' => [
                self::h('Total Luaran', $total, 'luaran', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($items), 'luaran/bln', 'trending_up'),
                self::h('Capaian Internasional', self::pct($intl, $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Total Luaran', $total, 'menu_book', 'info'),
                self::c('Sinta Nasional', $items->where('jenis', 'sinta_nasional')->count(), 'article', 'primary'),
                self::c('Jurnal Internasional', $items->where('jenis', 'jurnal_internasional')->count(), 'public', 'success'),
                self::c('Conference Intl.', $conf->count(), 'co_present', 'warning'),
            ],
            'cards2' => [
                self::c('Mahasiswa Terlibat', $terlibat, 'groups', 'info'),
                self::c('Partisipasi Mahasiswa', self::pct($terlibat, $mhsTotal) . '%', 'donut_large', 'primary'),
                self::c('Jurnal Punya DOI', self::pct($jurnal->count() - $tanpaDoi->count(), $jurnal->count()) . '%', 'link', 'success'),
                self::c('Luaran Tahun Ini', $items->where('tahun', now()->year)->count(), 'event', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Distribusi Jenis Luaran', 'Sinta, jurnal internasional, dan conference', 'Luaran',
                    self::donut($items, 'jenis', [
                        'sinta_nasional'           => ['Sinta Nasional', 'var(--chart-3)'],
                        'jurnal_internasional'     => ['Jurnal Internasional', 'var(--chart-5)'],
                        'conference_internasional' => ['Conference Intl.', 'var(--chart-2)'],
                    ])),
                self::participationDonut('Keterlibatan Mahasiswa', $terlibat, $mhsTotal, 'Mahasiswa'),
            ],
            'widgets' => [
                self::barsWidget('Peringkat Jurnal', 'Sebaran peringkat (S1–S6 / Q1–Q4)',
                    $jurnal->map(fn ($i) => strtoupper(trim((string) $i->peringkat)) ?: 'Tanpa peringkat')->countBy()->sortKeys()),
                self::attentionWidget(
                    'Perlu Ditindaklanjuti', 'Jurnal yang belum dilengkapi link DOI',
                    self::pct($jurnal->count() - $tanpaDoi->count(), $jurnal->count()),
                    ($jurnal->count() - $tanpaDoi->count()) . ' dari ' . $jurnal->count() . ' jurnal punya DOI',
                    $tanpaDoi->sortByDesc('created_at')->take(3)->map(fn ($i) => [
                        'title' => $i->judul,
                        'meta'  => ($owner($i) ?? '-') . ' · belum ada link DOI',
                    ])->values()->all(),
                    'Semua jurnal sudah punya DOI.',
                    max(0, $mhsTotal - $terlibat) . ' dari ' . $mhsTotal . ' mahasiswa belum punya luaran LPPM.'
                ),
                self::rankingWidget('Mahasiswa Paling Produktif', 'Jumlah luaran terbanyak', 'luaran', self::top($items, $owner)),
            ],
            'groups' => [
                ['label' => 'Jurnal (Sinta & Internasional)', 'items' => $jurnal->values()],
                ['label' => 'Conference Internasional', 'items' => $conf->values()],
            ],
            'recent' => self::recent([[$items, fn ($i) => $i->judul, 'LPPM Mahasiswa', 'person', $owner]]),
        ];
    }

    // =====================================================================
    // MODE: LPPM DOSEN
    // =====================================================================
    private static function lppmDosen(): array
    {
        $items = LppmDosen::with('dosen.user')->get();
        $total = $items->count();
        $dosenTotal = Dosen::count();
        $terlibat = $items->pluck('dosen_id')->unique()->count();
        $jurnal = $items->whereIn('jenis', ['q_internasional', 'sinta_nasional']);
        $lain = $items->whereIn('jenis', ['hki', 'book']);
        $owner = fn ($i) => $i->dosen?->user?->name;
        $tanpaDoi = $jurnal->filter(fn ($i) => blank($i->link_doi));

        return [
            'meta' => [
                'title'      => 'LPPM Dosen',
                'subtitle'   => 'Pantauan riset, publikasi, HKI & buku seluruh dosen',
                'menu_label' => 'Kelola LPPM Dosen',
                'menu_url'   => url('/lppm/dosen'),
            ],
            'hero' => [
                self::h('Total Luaran', $total, 'luaran', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($items), 'luaran/bln', 'trending_up'),
                self::h('Jurnal Q Internasional', self::pct($items->where('jenis', 'q_internasional')->count(), $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Total Luaran', $total, 'co_present', 'info'),
                self::c('Jurnal Q Internasional', $items->where('jenis', 'q_internasional')->count(), 'menu_book', 'success'),
                self::c('Jurnal Sinta Nasional', $items->where('jenis', 'sinta_nasional')->count(), 'article', 'primary'),
                self::c('HKI', $items->where('jenis', 'hki')->count(), 'verified', 'warning'),
            ],
            'cards2' => [
                self::c('Buku', $items->where('jenis', 'book')->count(), 'auto_stories', 'success'),
                self::c('Dosen Terlibat', $terlibat, 'groups', 'info'),
                self::c('Partisipasi Dosen', self::pct($terlibat, $dosenTotal) . '%', 'donut_large', 'primary'),
                self::c('Luaran Tahun Ini', $items->where('tahun', now()->year)->count(), 'event', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Distribusi Jenis Luaran', 'Jurnal, HKI, dan buku', 'Luaran',
                    self::donut($items, 'jenis', [
                        'q_internasional' => ['Jurnal Q Internasional', 'var(--chart-5)'],
                        'sinta_nasional'  => ['Jurnal Sinta Nasional', 'var(--chart-3)'],
                        'hki'             => ['HKI', 'var(--chart-2)'],
                        'book'            => ['Buku', 'var(--chart-4)'],
                    ])),
                self::participationDonut('Keterlibatan Dosen', $terlibat, $dosenTotal, 'Dosen'),
            ],
            'widgets' => [
                self::barsWidget('Peringkat Jurnal', 'Sebaran peringkat (Q1–Q4 / S1–S6)',
                    $jurnal->map(fn ($i) => strtoupper(trim((string) $i->peringkat)) ?: 'Tanpa peringkat')->countBy()->sortKeys()),
                self::attentionWidget(
                    'Perlu Ditindaklanjuti', 'Jurnal yang belum dilengkapi link DOI',
                    self::pct($jurnal->count() - $tanpaDoi->count(), $jurnal->count()),
                    ($jurnal->count() - $tanpaDoi->count()) . ' dari ' . $jurnal->count() . ' jurnal punya DOI',
                    $tanpaDoi->sortByDesc('created_at')->take(3)->map(fn ($i) => [
                        'title' => $i->judul,
                        'meta'  => ($owner($i) ?? '-') . ' · belum ada link DOI',
                    ])->values()->all(),
                    'Semua jurnal sudah punya DOI.',
                    max(0, $dosenTotal - $terlibat) . ' dari ' . $dosenTotal . ' dosen belum punya luaran LPPM.'
                ),
                self::rankingWidget('Dosen Paling Produktif', 'Jumlah luaran terbanyak', 'luaran', self::top($items, $owner)),
            ],
            'groups' => [
                ['label' => 'Jurnal (Q & Sinta)', 'items' => $jurnal->values()],
                ['label' => 'HKI & Buku', 'items' => $lain->values()],
            ],
            'recent' => self::recent([[$items, fn ($i) => $i->judul, 'LPPM Dosen', 'co_present', $owner]]),
        ];
    }

    // =====================================================================
    // MODE: REKOGNISI
    // =====================================================================
    private static function rekognisi(): array
    {
        $items = Rekognisi::with('user')->get();
        $total = $items->count();
        $owner = fn ($i) => $i->user?->name;
        $intl = $items->where('jenis', 'internasional')->count();
        $upcoming = self::upcoming([[$items, fn ($i) => $i->jabatan ?: $i->mitra, 'Rekognisi', 'workspace_premium', $owner]]);
        $alumniTanpaJabatan = $items->where('jenis', 'alumni')->filter(fn ($i) => blank($i->jabatan));
        $alumni = $items->where('jenis', 'alumni')->count();

        return [
            'meta' => [
                'title'      => 'Rekognisi',
                'subtitle'   => 'Pantauan rekognisi mahasiswa & dosen dari mitra',
                'menu_label' => 'Kelola Rekognisi',
                'menu_url'   => url('/lppm/rekognisi'),
            ],
            'hero' => [
                self::h('Total Rekognisi', $total, 'rekognisi', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($items), 'rekognisi/bln', 'trending_up'),
                self::h('Capaian Internasional', self::pct($intl, $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Total Rekognisi', $total, 'workspace_premium', 'info'),
                self::c('Nasional', $items->where('jenis', 'nasional')->count(), 'flag', 'primary'),
                self::c('Internasional', $intl, 'public', 'success'),
                self::c('Alumni', $alumni, 'school', 'warning'),
            ],
            'cards2' => [
                self::c('Mahasiswa', $items->where('tipe_user', 'mahasiswa')->count(), 'person', 'info'),
                self::c('Dosen', $items->where('tipe_user', 'dosen')->count(), 'co_present', 'primary'),
                self::c('Mitra Unik', self::uniqueCount($items, 'mitra'), 'apartment', 'success'),
                self::c('Sedang Berlangsung', $upcoming['total'], 'schedule', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Distribusi Jenis Rekognisi', 'Nasional, internasional, dan alumni', 'Rekognisi',
                    self::donut($items, 'jenis', [
                        'nasional'      => ['Nasional', 'var(--chart-3)'],
                        'internasional' => ['Internasional', 'var(--chart-5)'],
                        'alumni'        => ['Alumni', 'var(--chart-2)'],
                    ])),
                self::donutCard('Penerima Rekognisi', 'Mahasiswa vs dosen', 'Rekognisi',
                    self::donut($items, 'tipe_user', [
                        'mahasiswa' => ['Mahasiswa', 'var(--chart-1)'],
                        'dosen'     => ['Dosen', 'var(--chart-4)'],
                    ])),
            ],
            'widgets' => [
                self::barsWidget('Mitra Terbanyak', 'Mitra dengan rekognisi paling banyak',
                    $items->pluck('mitra')->map(fn ($m) => trim((string) $m))->filter()->countBy()->sortDesc()->take(6), 'is-q', false),
                self::upcomingWidget('Berlangsung & Akan Datang', $upcoming),
                self::attentionWidget(
                    'Perlu Ditindaklanjuti', 'Data rekognisi alumni yang belum lengkap',
                    self::pct($alumni - $alumniTanpaJabatan->count(), $alumni),
                    ($alumni - $alumniTanpaJabatan->count()) . ' dari ' . $alumni . ' alumni punya jabatan',
                    $alumniTanpaJabatan->sortByDesc('created_at')->take(3)->map(fn ($i) => [
                        'title' => $i->mitra,
                        'meta'  => ($owner($i) ?? '-') . ' · belum ada jabatan',
                    ])->values()->all(),
                    'Semua rekognisi alumni sudah punya jabatan.'
                ),
                self::rankingWidget('Penerima Rekognisi Terbanyak', 'Jumlah rekognisi terbanyak', 'rekognisi', self::top($items, $owner)),
            ],
            'groups' => [
                ['label' => 'Mahasiswa', 'items' => $items->where('tipe_user', 'mahasiswa')->values()],
                ['label' => 'Dosen', 'items' => $items->where('tipe_user', 'dosen')->values()],
            ],
            'recent' => self::recent([[$items, fn ($i) => $i->jabatan ?: $i->mitra, 'Rekognisi', 'workspace_premium', $owner]]),
        ];
    }

    // =====================================================================
    // MODE: KERJA SAMA
    // =====================================================================
    private static function kerjaSama(): array
    {
        $items = KerjaSama::with('user')->get();
        $total = $items->count();
        $owner = fn ($i) => $i->user?->name;
        $intlJenis = ['conference_internasional', 'pengabdian_internasional', 'research_internasional'];
        $intl = $items->whereIn('jenis', $intlJenis)->count();
        $upcoming = self::upcoming([[$items, fn ($i) => $i->judul_kegiatan, 'Kerja Sama', 'handshake', $owner]]);
        $guest = $items->where('jenis', 'guest_lecture');
        $guestTanpaArah = $guest->filter(fn ($i) => blank($i->arah));

        return [
            'meta' => [
                'title'      => 'Kerja Sama',
                'subtitle'   => 'Pantauan kemitraan & kerja sama mahasiswa dan dosen',
                'menu_label' => 'Kelola Kerja Sama',
                'menu_url'   => url('/kerja-sama'),
            ],
            'hero' => [
                self::h('Total Kerja Sama', $total, 'kerja sama', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($items), 'kegiatan/bln', 'trending_up'),
                self::h('Capaian Internasional', self::pct($intl, $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Total Kerja Sama', $total, 'handshake', 'info'),
                self::c('Mahasiswa', $items->where('tipe_user', 'mahasiswa')->count(), 'person', 'primary'),
                self::c('Dosen', $items->where('tipe_user', 'dosen')->count(), 'co_present', 'success'),
                self::c('Mitra Unik', self::uniqueCount($items, 'mitra'), 'apartment', 'warning'),
            ],
            'cards2' => [
                self::c('Berlangsung / Akan Datang', $upcoming['total'], 'schedule', 'info'),
                self::c('Guest Lecture Inbound', $guest->where('arah', 'inbound')->count(), 'south_west', 'primary'),
                self::c('Guest Lecture Outbound', $guest->where('arah', 'outbound')->count(), 'north_east', 'success'),
                self::c('PKL', $items->where('jenis', 'pkl')->count(), 'work', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Ragam Kerja Sama', 'Sebaran jenis kegiatan', 'Kerja Sama',
                    self::donut($items, 'jenis', [
                        'conference_internasional' => ['Conference Intl.', 'var(--chart-5)'],
                        'pkl'                      => ['PKL', 'var(--chart-1)'],
                        'sharing_session'          => ['Sharing Session', 'var(--chart-2)'],
                        'keynote_session'          => ['Keynote Session', 'var(--chart-3)'],
                        'guest_lecture'            => ['Guest Lecture', 'var(--chart-4)'],
                        'pengabdian_internasional' => ['Pengabdian Intl.', 'var(--chart-1)'],
                        'research_internasional'   => ['Research Intl.', 'var(--chart-2)'],
                        'lainnya'                  => ['Lainnya', 'var(--border)'],
                    ])),
                self::donutCard('Pelaku Kerja Sama', 'Mahasiswa vs dosen', 'Kerja Sama',
                    self::donut($items, 'tipe_user', [
                        'mahasiswa' => ['Mahasiswa', 'var(--chart-1)'],
                        'dosen'     => ['Dosen', 'var(--chart-4)'],
                    ])),
            ],
            'widgets' => [
                self::barsWidget('Mitra Terbanyak', 'Mitra dengan kerja sama paling banyak',
                    $items->pluck('mitra')->map(fn ($m) => trim((string) $m))->filter()->countBy()->sortDesc()->take(6), 'is-q', false),
                self::upcomingWidget('Berlangsung & Akan Datang', $upcoming),
                self::attentionWidget(
                    'Perlu Ditindaklanjuti', 'Guest lecture yang belum ditandai inbound/outbound',
                    self::pct($guest->count() - $guestTanpaArah->count(), $guest->count()),
                    ($guest->count() - $guestTanpaArah->count()) . ' dari ' . $guest->count() . ' guest lecture punya arah',
                    $guestTanpaArah->sortByDesc('created_at')->take(3)->map(fn ($i) => [
                        'title' => $i->judul_kegiatan,
                        'meta'  => ($owner($i) ?? '-') . ' · arah belum diisi',
                    ])->values()->all(),
                    'Semua guest lecture sudah punya arah.'
                ),
                self::rankingWidget('Pelaku Kerja Sama Teraktif', 'Jumlah kerja sama terbanyak', 'kerja sama', self::top($items, $owner)),
            ],
            'groups' => [
                ['label' => 'Mahasiswa', 'items' => $items->where('tipe_user', 'mahasiswa')->values()],
                ['label' => 'Dosen', 'items' => $items->where('tipe_user', 'dosen')->values()],
            ],
            'recent' => self::recent([[$items, fn ($i) => $i->judul_kegiatan, 'Kerja Sama', 'handshake', $owner]]),
        ];
    }

    // =====================================================================
    // MODE: SIDA (ringkasan semua menu) — superadmin & admin multi-menu
    // =====================================================================
    private static function sida(User $viewer): array
    {
        $kem = Kemahasiswaan::with('mahasiswa.user')->get();
        $lm = LppmMahasiswa::with('mahasiswa.user')->get();
        $ld = LppmDosen::with('dosen.user')->get();
        $rek = Rekognisi::with('user')->get();
        $ks = KerjaSama::with('user')->get();

        $all = $kem->concat($lm)->concat($ld)->concat($rek)->concat($ks);
        $total = $all->count();
        $intl = $kem->where('tingkat', 'internasional')->count()
            + $lm->whereIn('jenis', ['jurnal_internasional', 'conference_internasional'])->count()
            + $ld->where('jenis', 'q_internasional')->count()
            + $rek->where('jenis', 'internasional')->count()
            + $ks->whereIn('jenis', ['conference_internasional', 'pengabdian_internasional', 'research_internasional'])->count();

        $ownerMhs = fn ($i) => $i->mahasiswa?->user?->name;
        $ownerDsn = fn ($i) => $i->dosen?->user?->name;
        $ownerUsr = fn ($i) => $i->user?->name;

        $upcoming = self::upcoming([
            [$rek, fn ($i) => $i->jabatan ?: $i->mitra, 'Rekognisi', 'workspace_premium', $ownerUsr],
            [$ks, fn ($i) => $i->judul_kegiatan, 'Kerja Sama', 'handshake', $ownerUsr],
        ]);

        $counts = User::selectRaw('role, count(*) as n')->groupBy('role')->pluck('n', 'role');
        $mhsCount = Mahasiswa::count();
        $dsnCount = Dosen::count();

        $topOwners = $kem->map($ownerMhs)
            ->concat($lm->map($ownerMhs))
            ->concat($ld->map($ownerDsn))
            ->concat($rek->map($ownerUsr))
            ->concat($ks->map($ownerUsr))
            ->filter()->countBy()->sortDesc()->take(5)
            ->map(fn ($v, $k) => ['name' => $k, 'value' => $v])->values()->all();

        $widgets = [
            self::barsWidget('Kontribusi per Menu', 'Jumlah data di tiap menu operasional', collect([
                'Kemahasiswaan'  => $kem->count(),
                'LPPM Mahasiswa' => $lm->count(),
                'LPPM Dosen'     => $ld->count(),
                'Rekognisi'      => $rek->count(),
                'Kerja Sama'     => $ks->count(),
            ]), 'is-q', false),
            self::upcomingWidget('Berlangsung & Akan Datang', $upcoming),
            self::rankingWidget('Kontributor Teraktif', 'Total data terbanyak lintas semua menu', 'data', $topOwners),
        ];

        // Ringkasan keamanan login hanya untuk yang punya akses menu "log".
        if ($viewer->canAccessMenu('log', 'readonly')) {
            $since = now()->subDay();
            $attempts = LoginAttempt::where('created_at', '>=', $since)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
            $widgets[] = [
                'type'    => 'log',
                'title'   => 'Keamanan Login (24 Jam)',
                'sub'     => 'Ringkasan percobaan login terakhir',
                'success' => (int) ($attempts['success'] ?? 0),
                'failed'  => (int) ($attempts['failed'] ?? 0),
                'locked'  => (int) ($attempts['locked'] ?? 0),
                'url'     => url('/login-audit'),
            ];
        }

        // Superadmin: tautan ke halaman Laporan (versi lengkap dari ringkasan ini).
        if ($viewer->role === 'superadmin') {
            $widgets[] = [
                'type'  => 'laporan',
                'title' => 'Laporan Lengkap',
                'sub'   => 'Rincian semua menu, tren tahunan, kontributor, dan bisa dicetak',
                'items' => ['Rekap & rincian per menu', 'Tren per tahun', 'Kontributor & partisipasi', 'Kualitas data', 'Keamanan login'],
                'url'   => url('/laporan'),
            ];
        }

        return [
            'meta' => [
                'title'      => $viewer->role === 'superadmin' ? 'Super Admin' : 'Admin SIDA',
                'subtitle'   => 'Ringkasan seluruh kegiatan di Sistem Informasi Data Akademik',
                'menu_label' => null,
                'menu_url'   => null,
            ],
            'hero' => [
                self::h('Total Data', $total, 'data', 'local_fire_department'),
                self::h('Rata-rata per Bulan', self::avgPerMonth($all), 'data/bln', 'trending_up'),
                self::h('Capaian Internasional', self::pct($intl, $total), '%', 'public'),
            ],
            'cards' => [
                self::c('Kemahasiswaan', $kem->count(), 'school', 'info'),
                self::c('LPPM Mahasiswa', $lm->count(), 'person', 'primary'),
                self::c('LPPM Dosen', $ld->count(), 'co_present', 'success'),
                self::c('Rekognisi', $rek->count(), 'workspace_premium', 'warning'),
            ],
            'cards2' => [
                self::c('Kerja Sama', $ks->count(), 'handshake', 'info'),
                self::c('Mahasiswa Terdaftar', $mhsCount, 'groups', 'primary'),
                self::c('Dosen Terdaftar', $dsnCount, 'badge', 'success'),
                self::c('Admin & Superadmin', (int) ($counts['admin'] ?? 0) + (int) ($counts['superadmin'] ?? 0), 'admin_panel_settings', 'warning'),
            ],
            'donuts' => [
                self::donutCard('Distribusi per Menu', 'Sebaran data di 5 menu operasional', 'Data', [
                    ['label' => 'Kemahasiswaan', 'value' => $kem->count(), 'color' => 'var(--chart-1)'],
                    ['label' => 'LPPM Mahasiswa', 'value' => $lm->count(), 'color' => 'var(--chart-2)'],
                    ['label' => 'LPPM Dosen', 'value' => $ld->count(), 'color' => 'var(--chart-3)'],
                    ['label' => 'Rekognisi', 'value' => $rek->count(), 'color' => 'var(--chart-4)'],
                    ['label' => 'Kerja Sama', 'value' => $ks->count(), 'color' => 'var(--chart-5)'],
                ]),
                self::donutCard('Sebaran Akun', 'Jumlah akun per peran', 'Akun', [
                    ['label' => 'Mahasiswa', 'value' => (int) ($counts['mahasiswa'] ?? 0), 'color' => 'var(--chart-1)'],
                    ['label' => 'Dosen', 'value' => (int) ($counts['dosen'] ?? 0), 'color' => 'var(--chart-2)'],
                    ['label' => 'Admin', 'value' => (int) ($counts['admin'] ?? 0), 'color' => 'var(--chart-3)'],
                    ['label' => 'Superadmin', 'value' => (int) ($counts['superadmin'] ?? 0), 'color' => 'var(--chart-5)'],
                ]),
            ],
            'widgets' => $widgets,
            'groups' => [
                ['label' => 'Akademik (Kemahasiswaan + LPPM)', 'items' => $kem->concat($lm)->concat($ld)->values()],
                ['label' => 'Eksternal (Rekognisi + Kerja Sama)', 'items' => $rek->concat($ks)->values()],
            ],
            'recent' => self::recent([
                [$kem, fn ($i) => $i->nama_kegiatan, 'Kemahasiswaan', 'school', $ownerMhs],
                [$lm, fn ($i) => $i->judul, 'LPPM Mahasiswa', 'person', $ownerMhs],
                [$ld, fn ($i) => $i->judul, 'LPPM Dosen', 'co_present', $ownerDsn],
                [$rek, fn ($i) => $i->jabatan ?: $i->mitra, 'Rekognisi', 'workspace_premium', $ownerUsr],
                [$ks, fn ($i) => $i->judul_kegiatan, 'Kerja Sama', 'handshake', $ownerUsr],
            ]),
        ];
    }

    // =====================================================================
    // Helper kecil
    // =====================================================================
    private static function h(string $label, $value, string $unit, string $icon): array
    {
        return compact('label', 'value', 'unit', 'icon');
    }

    private static function c(string $label, $value, string $icon, string $color): array
    {
        return compact('label', 'value', 'icon', 'color');
    }

    private static function pct(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round($part / $whole * 100) : 0;
    }

    private static function avgPerMonth(Collection $items): float
    {
        $earliest = $items->pluck('created_at')->filter()->min();
        $months = $earliest ? max(1, Carbon::parse($earliest)->diffInMonths(now()) + 1) : 1;

        return round($items->count() / $months, 1);
    }

    private static function uniqueCount(Collection $items, string $field): int
    {
        return $items->pluck($field)
            ->map(fn ($v) => mb_strtolower(trim((string) $v)))
            ->filter()->unique()->count();
    }

    private static function donut(Collection $items, string $field, array $defs): array
    {
        $counts = $items->countBy($field);

        return collect($defs)->map(fn ($d, $key) => [
            'label' => $d[0],
            'value' => $counts->get($key, 0),
            'color' => $d[1],
        ])->values()->all();
    }

    private static function donutCard(string $title, string $sub, string $caption, array $data): array
    {
        return compact('title', 'sub', 'caption', 'data');
    }

    /** Donut "terlibat vs belum terlibat" untuk mode LPPM. */
    private static function participationDonut(string $title, int $terlibat, int $total, string $caption): array
    {
        return self::donutCard($title, 'Yang sudah punya luaran vs belum', $caption, [
            ['label' => 'Sudah terlibat', 'value' => $terlibat, 'color' => 'var(--chart-3)'],
            ['label' => 'Belum terlibat', 'value' => max(0, $total - $terlibat), 'color' => 'var(--border)'],
        ]);
    }

    /** Daftar peringkat pemilik data (nama => jumlah), maks. $limit baris. */
    private static function top(Collection $items, callable $nameFn, int $limit = 5): array
    {
        return $items->map($nameFn)->filter()->countBy()->sortDesc()->take($limit)
            ->map(fn ($v, $k) => ['name' => $k, 'value' => $v])->values()->all();
    }

    private static function barsWidget(string $title, string $sub, Collection $counts, string $fill = 'is-s', bool $sortDesc = false): array
    {
        if ($sortDesc) {
            $counts = $counts->sortDesc();
        }
        $max = max(1, (int) $counts->max());

        return [
            'type'  => 'bars',
            'title' => $title,
            'sub'   => $sub,
            'fill'  => $fill,
            'rows'  => $counts->map(fn ($v, $k) => [
                'label' => (string) $k,
                'value' => (int) $v,
                'width' => max(6, (int) round($v / $max * 100)),
            ])->values()->all(),
        ];
    }

    private static function attentionWidget(string $title, string $sub, int $pct, string $note, array $rows, string $okMessage, ?string $extra = null): array
    {
        return compact('title', 'sub', 'pct', 'note', 'rows', 'extra') + ['type' => 'attention', 'ok' => $okMessage];
    }

    private static function rankingWidget(string $title, string $sub, string $unit, array $rows): array
    {
        return compact('title', 'sub', 'unit', 'rows') + ['type' => 'ranking'];
    }

    private static function upcomingWidget(string $title, array $upcoming): array
    {
        return [
            'type'  => 'upcoming',
            'title' => $title,
            'sub'   => $upcoming['total'] . ' kegiatan yang belum selesai',
            'rows'  => $upcoming['rows'],
        ];
    }

    /**
     * Kegiatan yang belum selesai (tanggal_selesai >= hari ini), diurutkan dari
     * yang paling awal mulai. $sources: [items, titleFn, menu, icon, ownerFn].
     */
    private static function upcoming(array $sources): array
    {
        $today = now()->startOfDay();
        $rows = collect();

        foreach ($sources as [$items, $title, $menu, $icon, $owner]) {
            $rows = $rows->concat($items->map(fn ($i) => [
                'title' => $title($i),
                'menu'  => $menu,
                'icon'  => $icon,
                'who'   => $owner($i),
                'start' => $i->tanggal_mulai,
                'end'   => $i->tanggal_selesai,
            ]));
        }

        $rows = $rows
            ->filter(fn ($r) => $r['start'] && $r['end'] && $r['end']->greaterThanOrEqualTo($today))
            ->map(function ($r) use ($today) {
                $r['status'] = $r['start']->greaterThan($today) ? 'Akan Datang' : 'Berlangsung';

                return $r;
            })
            ->sortBy('start')->values();

        return ['total' => $rows->count(), 'rows' => $rows->take(5)->all()];
    }

    /** Aktivitas terbaru gabungan. $sources: [items, titleFn, menu, icon, ownerFn]. */
    private static function recent(array $sources): array
    {
        $rows = collect();

        foreach ($sources as [$items, $title, $menu, $icon, $owner]) {
            $rows = $rows->concat($items->map(fn ($i) => [
                'title' => $title($i),
                'menu'  => $menu,
                'icon'  => $icon,
                'who'   => $owner($i),
                'date'  => $i->created_at,
            ]));
        }

        return $rows->filter(fn ($r) => $r['date'])->sortByDesc('date')->take(10)->values()->all();
    }
}
