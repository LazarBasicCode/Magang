<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
    <script>
        (function() {
            try {
                var w = parseInt(localStorage.getItem('sida.sidebarW'), 10);
                if (w) document.documentElement.style.setProperty('--sidebar-w', (w < 140 ? 72 : Math.min(340, Math.max(200, w))) + 'px');
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}?v={{ @filemtime(public_path('css/laporan.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ @filemtime(public_path('css/dashboard.css')) }}">
    <title>Dashboard {{ $meta['title'] }} &middot; SIDA</title>
</head>

<body>
    <!-- Definisi dark mode -->
    <script> try { if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {} </script>
    <!-- Definisi ikon SIDA (dipakai di logo sidebar) -->
    <svg class="svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="sida-mark" viewBox="0 0 48 48">
                <clipPath id="sidaClip"><circle cx="24" cy="24" r="22.5"/></clipPath>
                <image href="{{ asset('img/logo-prodi.png') }}" x="1.5" y="1.5" width="45" height="45" preserveAspectRatio="xMidYMid slice" clip-path="url(#sidaClip)"/>
                <circle cx="24" cy="24" r="22.5" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5"/>
            </symbol>
        </defs>
    </svg>

    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <svg class="sidebar-brand-mark" aria-hidden="true"><use href="#sida-mark"/></svg>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-title">SIDA</span>
                <span class="sidebar-brand-sub">Institut Asia Malang</span>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup Menu">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <a href="{{ url('/dashboard') }}" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>

                @if($__user->canAccessMenu('kemahasiswaan'))
                <a href="{{ url('/kemahasiswaan') }}" class="nav-link">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>
                @endif

                @if($__user->canAccessMenu('lppm_mahasiswa') || $__user->canAccessMenu('lppm_dosen') || $__user->canAccessMenu('rekognisi'))
                <div class="nav-heading">LPPM</div>
                @endif
                @if($__user->canAccessMenu('lppm_mahasiswa'))
                <a href="{{ url('/lppm/mahasiswa') }}" class="nav-link">
                    <span class="material-symbols-outlined">person</span>
                    <span>Mahasiswa</span>
                </a>
                @endif
                @if($__user->canAccessMenu('lppm_dosen'))
                <a href="{{ url('/lppm/dosen') }}" class="nav-link">
                    <span class="material-symbols-outlined">co_present</span>
                    <span>Dosen</span>
                </a>
                @endif
                @if($__user->canAccessMenu('rekognisi'))
                <a href="{{ url('/lppm/rekognisi') }}" class="nav-link">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>
                @endif

                @if($__user->canAccessMenu('kerja_sama'))
                <div class="nav-heading">Kemitraan</div>
                @endif
                @if($__user->canAccessMenu('kerja_sama'))
                <a href="{{ url('/kerja-sama') }}" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>
                @endif

                @if($__user->canAccessMenu('data_master') || $__user->canAccessMenu('hak_akses') || $__user->canAccessMenu('log'))
                <div class="nav-heading">Administrasi</div>
                @endif
                @if($__user->canAccessMenu('data_master'))
                <a href="{{ url('/data-master/users') }}" class="nav-link">
                    <span class="material-symbols-outlined">manage_accounts</span>
                    <span>Data Master</span>
                </a>
                @endif
                @if($__user->canAccessMenu('hak_akses'))
                <a href="{{ url('/hak-akses') }}" class="nav-link">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    <span>Hak Akses</span>
                </a>
                @endif
                @if($__user->canAccessMenu('log'))
                <a href="{{ url('/login-audit') }}" class="nav-link">
                    <span class="material-symbols-outlined">history</span>
                    <span>Log Aktivitas</span>
                </a>
                @endif
                @if($__user->canAccessMenu('backup'))
                <a href="{{ url('/backup') }}" class="nav-link">
                    <span class="material-symbols-outlined">backup</span>
                    <span>Backup &amp; Restore</span>
                </a>
                @endif
                @if($__user->role === 'superadmin')
                <a href="{{ url('/laporan') }}" class="nav-link">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Laporan</span>
                </a>
                @endif
            </div>
        </nav>

        <div class="sidebar-help">
            <div class="help-card">
                <p class="help-card-text">© Prodi IT - Institut Asia Malang</p>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        <header class="app-header">
            <div class="header-capsule">
                <div class="header-inner">
                    <button type="button" class="icon-btn sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Buka Menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="header-crumb">
                        <span class="link">Dashboard</span>
                        <span>/</span>
                        <span class="current">{{ $meta['title'] }}</span>
                    </div>
                    <div class="header-actions">
                        <!-- ============ NOTIFIKASI ============ -->
                        <div class="notif-dropdown" id="notifDropdown">
                            <button type="button" class="icon-btn" id="notifToggleBtn" aria-label="Notifikasi" aria-haspopup="true" aria-expanded="false">
                                <span class="material-symbols-outlined">notifications</span>
                            </button>

                            <div class="notif-panel" id="notifPanel" role="menu" aria-hidden="true">
                                <div class="notif-panel-header">
                                    <h3 class="notif-panel-title">Notifikasi</h3>
                                    <button type="button" class="notif-mark-all" id="notifMarkAllBtn">Tandai semua dibaca</button>
                                </div>

                                <div class="notif-panel-body" id="notifListWrap" hidden></div>

                                {{-- Tampilan saat tidak ada notifikasi --}}
                                <div class="notif-empty" id="notifEmpty">
                                    <span class="material-symbols-outlined notif-empty-icon">notifications</span>
                                    <p class="notif-empty-title">Belum ada notifikasi</p>
                                    <p class="notif-empty-desc">Pemberitahuan baru akan muncul di sini.</p>
                                </div>
                            </div>
                        </div>
                        <!-- ============ /NOTIFIKASI ============ -->
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>

                        {{-- ============ PROFILE DROPDOWN ============ --}}
                        @php
                        $__initials = $__user->initials();
                        $__avatarColor = $__user->avatarColorClass();
                        $__accessRows = $__user->accessBreakdown();
                        @endphp
                        <div class="header-profile-dropdown" id="profileDropdown">
                            <button type="button" class="header-profile-trigger" id="profileToggleBtn"
                                aria-haspopup="true" aria-expanded="false">
                                <div class="header-profile-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                <div class="header-profile-text">
                                    <span class="header-profile-name">{{ $__user->name }}</span>
                                    <span class="header-profile-role">{{ $__user->accessLabelFor() }}</span>
                                </div>
                                <span class="material-symbols-outlined header-profile-caret">expand_more</span>
                            </button>

                            <div class="header-profile-panel" id="profilePanel" role="menu" aria-hidden="true">
                                {{-- ---- View 1: menu utama ---- --}}
                                <div class="header-profile-view is-active" id="profileViewMain">
                                    <div class="header-profile-panel-header">
                                        <div class="header-profile-panel-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                        <div>
                                            <span class="header-profile-panel-name">{{ $__user->name }}</span>
                                            <span class="header-profile-panel-role">{{ $__user->accessLabelFor() }}</span>
                                        </div>
                                    </div>
                                    <div class="header-profile-menu">
                                        <button type="button" class="header-profile-menu-item" id="btnShowAccessInfo">
                                            <span class="material-symbols-outlined">shield_person</span>
                                            <span>Informasi Akses</span>
                                        </button>
                                        {{-- UI saja untuk saat ini, belum ada endpoint di baliknya --}}
                                        <button type="button" class="header-profile-menu-item" id="btnGantiPassword">
                                            <span class="material-symbols-outlined">key</span>
                                            <span>Ganti Password</span>
                                        </button>
                                        <button type="button" class="header-profile-menu-item" id="btnEmailPemulihan">
                                            <span class="material-symbols-outlined">mark_email_unread</span>
                                            <span>Email Pemulihan</span>
                                        </button>
                                        <div class="header-profile-menu-divider"></div>
                                        <form method="POST" action="{{ url('/logout') }}" id="logoutForm">
                                            @csrf
                                            <button type="submit" class="header-profile-menu-item is-danger">
                                                <span class="material-symbols-outlined">logout</span>
                                                <span>Keluar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- ---- View 2: rincian hak akses per menu ---- --}}
                                <div class="header-profile-view" id="profileViewAccess">
                                    <button type="button" class="header-profile-panel-back" id="btnBackToMain">
                                        <span class="material-symbols-outlined">arrow_back</span>
                                        <span>Informasi Akses</span>
                                    </button>
                                    <div class="access-info-list">
                                        @foreach($__accessRows as $row)
                                        <div class="access-info-row {{ $row['level'] === 'none' ? 'is-zero' : '' }}">
                                            <span class="material-symbols-outlined">{{ $row['icon'] }}</span>
                                            <span class="access-info-row-label">{{ $row['label'] }}</span>
                                            <span class="access-chip {{ $row['level'] }}">
                                                <span class="access-dot {{ $row['level'] }}"></span>
                                                {{ $row['level_label'] }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ============ /PROFILE DROPDOWN ============ --}}
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                {{-- =====================================================================
                     DASHBOARD ADMIN — satu file untuk semua admin.
                     Isinya menyesuaikan $mode (lihat App\Support\AdminDashboardData):
                     kemahasiswaan | lppm_mahasiswa | lppm_dosen | rekognisi | kerja_sama | sida
                     ===================================================================== --}}
                @php
                    $jamSekarang = (int) now()->format('G');
                    if ($jamSekarang >= 4 && $jamSekarang < 11) {
                        $sapaan = 'Selamat Pagi';
                    } elseif ($jamSekarang >= 11 && $jamSekarang < 15) {
                        $sapaan = 'Selamat Siang';
                    } elseif ($jamSekarang >= 15 && $jamSekarang < 18) {
                        $sapaan = 'Selamat Sore';
                    } else {
                        $sapaan = 'Selamat Malam';
                    }
                @endphp

                <!-- WELCOME + HERO STAT -->
                <div class="dash-panel reveal">
                    <div class="dash-stars" aria-hidden="true">
                        <div class="dash-stars-rot">
                            <div class="ds ds1"></div>
                            <div class="ds ds2"></div>
                            <div class="ds ds3"></div>
                        </div>
                    </div>
                    <div class="dash-welcome">
                        <div class="dash-welcome-main">
                            <p class="dash-welcome-brand">SIDA &middot; {{ $meta['title'] }}</p>
                            <p class="dash-welcome-title">{{ $sapaan }}, {{ auth()->user()->name }} 👋</p>
                            <p class="dash-welcome-sub">{{ $meta['subtitle'] }} &middot; {{ now()->year }}</p>
                            @if(!empty($meta['menu_url']))
                                <a href="{{ $meta['menu_url'] }}" class="btn-primary admin-quick-link">
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                    <span>{{ $meta['menu_label'] }}</span>
                                </a>
                            @endif
                        </div>
                        <div class="dash-welcome-clock">
                            <div class="dash-welcome-clock-time" id="dashWelcomeClock">{{ now()->format('H:i') }}</div>
                            <div class="dash-welcome-clock-date">{{ now()->translatedFormat('l, d F Y') }}</div>
                        </div>
                    </div>

                    <div class="dash-panel-divider"></div>

                    <div class="stat-hero-grid">
                        <div class="stat-hero-card grad-1"></div>
                        @foreach($hero as $i => $h)
                            <div class="stat-hero-card grad-{{ $i + 2 }}">
                                <span class="material-symbols-outlined stat-hero-icon">{{ $h['icon'] }}</span>
                                <p class="stat-hero-label">{{ $h['label'] }}</p>
                                <div class="stat-hero-value">{{ $h['value'] }} <span class="stat-hero-unit">{{ $h['unit'] }}</span></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS (2 baris x 4) -->
                @foreach([$cards, $cards2] as $row)
                    <div class="stat-grid" @if($loop->last) style="margin-top:18px;" @endif>
                        @foreach($row as $card)
                            <div class="stat-card">
                                <div class="stat-info">
                                    <span class="stat-label">{{ $card['label'] }}</span>
                                    <span class="stat-value">{{ $card['value'] }}</span>
                                </div>
                                <div class="stat-icon {{ $card['color'] }}">
                                    <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <!-- DASHBOARD GRID -->
                <div class="dash-grid">

                    {{-- 2 donut khusus mode ini --}}
                    @foreach($donuts as $d)
                        <div class="dash-card donut-card reveal" @if(!$loop->first) style="transition-delay:80ms" @endif>
                            <h2 class="dash-card-title">{{ $d['title'] }}</h2>
                            <p class="dash-card-sub">{{ $d['sub'] }}</p>
                            <div class="donut-widget" data-donut data-caption="{{ $d['caption'] }}" data-chart='@json($d['data'])'>
                                <div class="donut-svg-holder">
                                    <svg viewBox="0 0 200 200"></svg>
                                    <div class="donut-center-label">
                                        <span class="donut-center-value">0</span>
                                        <span class="donut-center-caption">{{ $d['caption'] }}</span>
                                    </div>
                                </div>
                                <div class="donut-legend"></div>
                            </div>
                        </div>
                    @endforeach

                    <!-- KURVA TREN -->
                    <div class="dash-card trend-card reveal">
                        <div class="trend-card-top">
                            <div>
                                <h2 class="dash-card-title">Kurva Tren Kegiatan</h2>
                                <p class="dash-card-sub">{{ $groups[0]['label'] }} vs {{ $groups[1]['label'] }} &middot; seluruh data yang masuk ke sistem</p>
                            </div>
                            <span class="trend-hint">
                                <span class="material-symbols-outlined">info</span>
                                Arahkan kursor ke titik untuk detail
                            </span>
                        </div>
                        <div class="period-switcher" id="trendPeriodSwitcher" role="tablist">
                            <button type="button" class="period-btn" data-period="harian">Harian</button>
                            <button type="button" class="period-btn" data-period="mingguan">Mingguan</button>
                            <button type="button" class="period-btn is-active" data-period="bulanan">Bulanan</button>
                            <button type="button" class="period-btn" data-period="tahunan">Tahunan</button>
                        </div>
                        <div class="trend-legend-row" id="trendLegend"></div>
                        <div class="trend-chart-holder" data-trendchart data-active-period="bulanan" data-chart-sets='@json($trendDatasets)'></div>
                    </div>

                    <!-- BAR CHART + AKTIVITAS TERBARU -->
                    <div class="dash-card trend-card reveal">
                        <div class="periode-recent-split">
                            <div class="periode-recent-col periode-recent-col-chart">
                                <div class="trend-card-top">
                                    <div>
                                        <h2 class="dash-card-title">Kegiatan per Periode</h2>
                                        <p class="dash-card-sub">Total gabungan</p>
                                    </div>
                                    <span class="trend-hint">
                                        <span class="material-symbols-outlined">info</span>
                                        Arahkan kursor ke batang untuk detail
                                    </span>
                                </div>
                                <div class="period-switcher" id="periodSwitcher" role="tablist">
                                    <button type="button" class="period-btn" data-period="harian">Harian</button>
                                    <button type="button" class="period-btn" data-period="mingguan">Mingguan</button>
                                    <button type="button" class="period-btn is-active" data-period="bulanan">Bulanan</button>
                                    <button type="button" class="period-btn" data-period="tahunan">Tahunan</button>
                                </div>
                                <div class="bar-chart-holder" data-barchart data-active-period="bulanan" data-chart-sets='@json($barDatasets)'></div>
                            </div>

                            <div class="periode-recent-divider"></div>

                            <div class="periode-recent-col periode-recent-col-recent">
                                <h2 class="dash-card-title">Aktivitas Terbaru</h2>
                                <p class="dash-card-sub">Data terakhir yang masuk, lengkap dengan pemiliknya</p>
                                <div class="recent-list">
                                    @forelse($recent as $item)
                                        <div class="recent-item">
                                            <div class="recent-item-icon">
                                                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                                            </div>
                                            <div class="recent-item-text">
                                                <span class="recent-item-title">{{ $item['title'] ?? '(tanpa judul)' }}</span>
                                                <span class="recent-item-meta">{{ $item['menu'] }} &middot; {{ $item['who'] ?? '-' }} &middot; {{ $item['date']?->translatedFormat('d M Y') }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="recent-empty">Belum ada aktivitas yang tercatat.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WIDGET KHUSUS MODE (jumlahnya berbeda per mode; kalau ganjil, yang terakhir melebar) -->
                <div class="dash-grid">
                    @foreach($widgets as $w)
                        @php $wide = ($loop->last && $loop->count % 2 === 1) ? 'trend-card' : ''; @endphp

                        @if($w['type'] === 'bars')
                            <div class="dash-card reveal {{ $wide }}">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <div class="quality-list">
                                    @forelse($w['rows'] as $r)
                                        <div class="quality-row">
                                            <span class="quality-label is-wide" title="{{ $r['label'] }}">{{ $r['label'] }}</span>
                                            <div class="quality-track">
                                                <div class="quality-fill {{ $w['fill'] }}" style="width: {{ $r['width'] }}%"></div>
                                            </div>
                                            <span class="quality-value">{{ $r['value'] }}</span>
                                        </div>
                                    @empty
                                        <div class="recent-empty">Belum ada data untuk ditampilkan.</div>
                                    @endforelse
                                </div>
                            </div>

                        @elseif($w['type'] === 'attention')
                            <div class="dash-card reveal {{ $wide }}" style="transition-delay:80ms">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <div class="complete-head">
                                    <span class="complete-pct">{{ $w['pct'] }}%</span>
                                    <span class="complete-note">{{ $w['note'] }}</span>
                                </div>
                                <div class="quality-track complete-track">
                                    <div class="quality-fill is-ok" style="width: {{ $w['pct'] }}%"></div>
                                </div>
                                @if(!empty($w['rows']))
                                    <p class="complete-sub">Perlu dilengkapi</p>
                                    <div class="recent-list">
                                        @foreach($w['rows'] as $p)
                                            <div class="recent-item">
                                                <div class="recent-item-icon"><span class="material-symbols-outlined">error</span></div>
                                                <div class="recent-item-text">
                                                    <span class="recent-item-title">{{ $p['title'] }}</span>
                                                    <span class="recent-item-meta">{{ $p['meta'] }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="complete-sub">{{ $w['ok'] }}</p>
                                @endif
                                @if(!empty($w['extra']))
                                    <p class="complete-sub admin-extra-note">{{ $w['extra'] }}</p>
                                @endif
                            </div>

                        @elseif($w['type'] === 'ranking')
                            <div class="dash-card reveal {{ $wide }}">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <div class="recent-list">
                                    @forelse($w['rows'] as $n => $r)
                                        <div class="recent-item">
                                            <div class="recent-item-icon">
                                                <span class="material-symbols-outlined">{{ $n === 0 ? 'emoji_events' : 'military_tech' }}</span>
                                            </div>
                                            <div class="recent-item-text">
                                                <span class="recent-item-title">{{ $n + 1 }}. {{ $r['name'] }}</span>
                                                <span class="recent-item-meta">{{ $r['value'] }} {{ $w['unit'] }}</span>
                                            </div>
                                            <span class="status-chip is-live">{{ $r['value'] }}</span>
                                        </div>
                                    @empty
                                        <div class="recent-empty">Belum ada data untuk diperingkat.</div>
                                    @endforelse
                                </div>
                            </div>

                        @elseif($w['type'] === 'upcoming')
                            <div class="dash-card reveal {{ $wide }}" style="transition-delay:80ms">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <div class="recent-list">
                                    @forelse($w['rows'] as $b)
                                        <div class="recent-item">
                                            <div class="recent-item-icon"><span class="material-symbols-outlined">{{ $b['icon'] }}</span></div>
                                            <div class="recent-item-text">
                                                <span class="recent-item-title">{{ $b['title'] }}</span>
                                                <span class="recent-item-meta">{{ $b['who'] ?? '-' }} &middot; {{ $b['start']->translatedFormat('d M Y') }} &ndash; {{ $b['end']->translatedFormat('d M Y') }}</span>
                                            </div>
                                            <span class="status-chip {{ $b['status'] === 'Berlangsung' ? 'is-live' : 'is-soon' }}">{{ $b['status'] }}</span>
                                        </div>
                                    @empty
                                        <div class="recent-empty">Tidak ada kegiatan yang sedang berjalan.</div>
                                    @endforelse
                                </div>
                            </div>

                        @elseif($w['type'] === 'laporan')
                            <div class="dash-card reveal {{ $wide }}">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <ul class="laporan-mini-list">
                                    @foreach($w['items'] as $item)
                                        <li><span class="material-symbols-outlined">check_circle</span>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                <a href="{{ $w['url'] }}" class="admin-log-link">
                                    <span class="material-symbols-outlined">assignment</span>
                                    <span>Buka Laporan</span>
                                </a>
                            </div>
                        @elseif($w['type'] === 'log')
                            <div class="dash-card reveal {{ $wide }}">
                                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                                <div class="log-mini-grid">
                                    <div class="log-mini is-ok"><span class="log-mini-value">{{ $w['success'] }}</span><span class="log-mini-label">Berhasil</span></div>
                                    <div class="log-mini is-warn"><span class="log-mini-value">{{ $w['failed'] }}</span><span class="log-mini-label">Gagal</span></div>
                                    <div class="log-mini is-bad"><span class="log-mini-value">{{ $w['locked'] }}</span><span class="log-mini-label">Terkunci</span></div>
                                </div>
                                <a href="{{ $w['url'] }}" class="admin-log-link">
                                    <span class="material-symbols-outlined">history</span>
                                    <span>Buka Log Aktivitas</span>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="chart-tooltip" id="chartTooltip"></div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        // Jam di panel sapaan
        (function () {
            var el = document.getElementById('dashWelcomeClock');
            if (!el) return;
            function tick() {
                var d = new Date();
                el.textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            }
            tick();
            setInterval(tick, 1000 * 30);
        })();
    </script>
    <script src="{{ asset('js/dashboard-charts.js') }}?v={{ @filemtime(public_path('js/dashboard-charts.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>
