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
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Dashboard Mahasiswa &middot; SIDA</title>

    <style>
        /* ==========================================================
           BACKGROUND PATTERN — beda untuk dark & light mode
           Diletakkan fixed di belakang seluruh konten (z-index rendah).
           ========================================================== */
        .bg-pattern {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            transition: opacity .4s ease;
        }

        /* ---- LIGHT MODE: soft dot-grid + gradient blob, lembut & bersih ---- */
        .bg-pattern.bg-light {
            opacity: 1;
            background-color: var(--canvas);
            background-image:
                radial-gradient(circle at 15% 20%, rgba(37, 99, 201, 0.10), transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(139, 95, 191, 0.08), transparent 45%),
                radial-gradient(rgba(37, 99, 201, 0.14) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 22px 22px;
            background-position: 0 0, 0 0, 0 0;
        }
        body.dark-mode .bg-pattern.bg-light { opacity: 0; }

        /* ---- DARK MODE: cyber grid ala Uiverse (disesuaikan warna aksen situs) ---- */
        .bg-pattern.bg-dark {
            opacity: 0;
            background-color: #05070c;
            background-image:
                radial-gradient(circle at center, transparent 30%, #000 92%),
                linear-gradient(rgba(96, 165, 250, 0.10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(96, 165, 250, 0.10) 1px, transparent 1px),
                linear-gradient(rgba(139, 95, 191, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(139, 95, 191, 0.06) 1px, transparent 1px);
            background-size: 100% 100%, 60px 60px, 60px 60px, 20px 20px, 20px 20px;
            animation: cyber-move 12s linear infinite;
        }
        body.dark-mode .bg-pattern.bg-dark { opacity: 1; }

        @keyframes cyber-move {
            0%   { background-position: 0 0, 0 0, 0 0, 0 0, 0 0; }
            100% { background-position: 0 0, 60px 60px, 60px 60px, 40px 40px, 40px 40px; }
        }

        .app-content, .app-sidebar, .sidebar-overlay { position: relative; z-index: 1; }

        /* ==========================================================
           SCROLL REVEAL — muncul dari blur+kecil+transparan ke normal,
           berulang setiap kali elemen masuk viewport (bukan sekali saja)
           ========================================================== */
        .reveal {
            opacity: 0;
            filter: blur(14px);
            transform: scale(0.82) translateY(18px);
            transition: opacity .7s cubic-bezier(.22,1,.36,1),
                        filter .7s cubic-bezier(.22,1,.36,1),
                        transform .7s cubic-bezier(.22,1,.36,1);
            will-change: opacity, filter, transform;
        }
        .reveal.is-visible {
            opacity: 1;
            filter: blur(0);
            transform: scale(1) translateY(0);
        }
        .reveal-delay-1 { transition-delay: .08s; }
        .reveal-delay-2 { transition-delay: .16s; }
        .reveal-delay-3 { transition-delay: .24s; }
        .reveal-delay-4 { transition-delay: .32s; }

        /* ==========================================================
           DONUT CHART + TOOLTIP TRIGGER (bulat-bulat)
           ========================================================== */
        .donut-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .donut-card {
            background: var(--card); border: 1px solid var(--border-strong); border-radius: 16px;
            padding: 24px; display: flex; flex-direction: column; align-items: center; gap: 18px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .donut-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(16, 24, 40, .10);
            border-color: var(--primary-border);
        }
        .donut-card-title { font-size: 14px; font-weight: 700; color: var(--ink); text-align: center; }
        .donut-card-sub { font-size: 11.5px; color: var(--ink-faint); margin-top: -12px; }

        .donut-wrap { position: relative; width: 190px; height: 190px; flex-shrink: 0; }
        .donut-ring {
            width: 100%; height: 100%; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: filter .25s ease;
        }
        .donut-hole {
            width: 62%; height: 62%; border-radius: 50%; background: var(--card);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            box-shadow: inset 0 0 0 1px var(--border);
        }
        .donut-hole .num { font-size: 24px; font-weight: 800; color: var(--ink); line-height: 1; }
        .donut-hole .lbl { font-size: 10.5px; color: var(--ink-faint); margin-top: 3px; text-transform: uppercase; letter-spacing: .04em; }

        .donut-marker {
            position: absolute; width: 15px; height: 15px; border-radius: 50%;
            background: var(--card); border: 3px solid var(--marker-color, var(--primary));
            transform: translate(-50%, -50%); cursor: pointer;
            box-shadow: 0 2px 6px rgba(16, 24, 40, .18);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .donut-marker:hover, .donut-marker:focus-visible, .donut-marker.is-tapped {
            transform: translate(-50%, -50%) scale(1.35);
            box-shadow: 0 4px 12px rgba(16, 24, 40, .28);
        }

        .donut-tooltip {
            position: absolute; bottom: calc(100% + 10px); left: 50%; transform: translateX(-50%) translateY(4px);
            background: #111827; color: #fff; font-size: 12px; font-weight: 700;
            padding: 6px 12px; border-radius: 8px; white-space: nowrap;
            opacity: 0; pointer-events: none; transition: opacity .18s ease, transform .18s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,.25);
        }
        .donut-tooltip::after {
            content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
            border: 6px solid transparent; border-top-color: #111827;
        }
        .donut-tooltip .tt-label { display: block; font-weight: 500; font-size: 10.5px; color: #cbd5e1; margin-top: 1px; }
        .donut-marker:hover .donut-tooltip,
        .donut-marker:focus-visible .donut-tooltip,
        .donut-marker.is-tapped .donut-tooltip {
            opacity: 1; transform: translateX(-50%) translateY(0);
        }

        .donut-legend { width: 100%; display: flex; flex-direction: column; gap: 8px; }
        .legend-row { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--ink-muted); }
        .legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .legend-label { flex: 1; }
        .legend-value { font-weight: 700; color: var(--ink); }
        .legend-pct { font-size: 11px; color: var(--ink-faint); min-width: 38px; text-align: right; }

        @media (max-width: 480px) {
            .donut-wrap { width: 160px; height: 160px; }
        }
    </style>
</head>

<body>
    <div class="bg-pattern bg-light"></div>
    <div class="bg-pattern bg-dark"></div>

    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <img alt="Logo Institut Asia Malang"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
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
                @if($__user->canAccessMenu('dashboard'))
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                @endif

                @if($__user->canAccessMenu('kemahasiswaan'))
                <a href="{{ url('/kemahasiswaan') }}" class="nav-link">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>
                <a href="{{ url('/kemahasiswaan/dashboard') }}" aria-current="page" class="nav-link is-active" style="padding-left: 44px; font-size: 13px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">donut_large</span>
                    <span>Dashboard Visual</span>
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
                <a href="{{ url('/kerja-sama') }}" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>
                @endif

                @if($__user->canAccessMenu('data_master') || $__user->canAccessMenu('hak_akses'))
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
                        <span class="link">Kemahasiswaan</span>
                        <span>/</span>
                        <span class="current">Dashboard Visual</span>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="icon-btn" aria-label="Notifikasi">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="dot"></span>
                        </button>
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="header-profile-role">Institut Asia Malang</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                <div class="title-bar reveal">
                    <div>
                        <div class="breadcrumb">
                            <span>Kemahasiswaan</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Dashboard Visual</span>
                        </div>
                        <h1 class="page-title">Dashboard Mahasiswa</h1>
                        <p class="page-subtitle">Rekap visual data prestasi &amp; kegiatan kemahasiswaan</p>
                    </div>
                    <a href="{{ url('/kemahasiswaan') }}" class="btn-primary">
                        <span class="material-symbols-outlined">table_rows</span>
                        <span>Lihat Tabel Data</span>
                    </a>
                </div>

                <!-- KPI Row -->
                <div class="stat-grid">
                    <div class="stat-card reveal reveal-delay-1">
                        <div class="stat-info">
                            <span class="stat-label">Total Kegiatan</span>
                            <span class="stat-value">{{ $stats['total_kegiatan'] }}</span>
                        </div>
                        <div class="stat-icon primary"><span class="material-symbols-outlined">event_note</span></div>
                    </div>
                    <div class="stat-card reveal reveal-delay-2">
                        <div class="stat-info">
                            <span class="stat-label">Total Mahasiswa</span>
                            <span class="stat-value">{{ $stats['total_mahasiswa'] }}</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">groups</span></div>
                    </div>
                    <div class="stat-card reveal reveal-delay-3">
                        <div class="stat-info">
                            <span class="stat-label">Mahasiswa Aktif</span>
                            <span class="stat-value">{{ $stats['mahasiswa_aktif'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">verified</span></div>
                    </div>
                    <div class="stat-card reveal reveal-delay-4">
                        <div class="stat-info">
                            <span class="stat-label">Nasional + Internasional</span>
                            <span class="stat-value">{{ $stats['nasional_plus'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">public</span></div>
                    </div>
                </div>

                <!-- Donut Charts -->
                <div class="donut-grid">
                    @foreach($donuts as $key => $donut)
                        <div class="donut-card reveal reveal-delay-{{ $loop->iteration }}">
                            <div class="donut-card-title">{{ $donut['title'] }}</div>
                            <div class="donut-card-sub">Total {{ $donut['total'] }} data</div>

                            <div class="donut-wrap">
                                <div class="donut-ring" style="background: {{ $donut['gradient'] }};">
                                    <div class="donut-hole">
                                        <span class="num">{{ $donut['total'] }}</span>
                                        <span class="lbl">Total</span>
                                    </div>
                                </div>

                                @foreach($donut['segments'] as $seg)
                                    @if($seg['value'] > 0)
                                    <button type="button" class="donut-marker"
                                        style="left: {{ $seg['x'] }}%; top: {{ $seg['y'] }}%; --marker-color: {{ $seg['color'] }};"
                                        aria-label="{{ $seg['label'] }}: {{ $seg['value'] }}">
                                        <span class="donut-tooltip">
                                            {{ $seg['value'] }}
                                            <span class="tt-label">{{ $seg['label'] }} ({{ $seg['pct'] }}%)</span>
                                        </span>
                                    </button>
                                    @endif
                                @endforeach
                            </div>

                            <div class="donut-legend">
                                @foreach($donut['segments'] as $seg)
                                    <div class="legend-row">
                                        <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                                        <span class="legend-label">{{ $seg['label'] }}</span>
                                        <span class="legend-value">{{ $seg['value'] }}</span>
                                        <span class="legend-pct">{{ $seg['pct'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        // ---------------- Sidebar Drawer ----------------
        const sidebar = document.querySelector('.app-sidebar');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('is-open');
            sidebarOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('is-open');
            sidebarOverlay.classList.remove('is-active');
            document.body.style.overflow = '';
        }
        sidebarToggleBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeSidebar();
        });

        // ---------------- Dark Mode ----------------
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeIcon) themeIcon.textContent = 'light_mode';
        }
        themeToggleBtn?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            if (themeIcon) themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // ---------------- Scroll Reveal (berulang setiap masuk/keluar viewport) ----------------
        const revealEls = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                entry.target.classList.toggle('is-visible', entry.isIntersecting);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach((el) => revealObserver.observe(el));

        // ---------------- Donut Tooltip: dukungan tap di layar sentuh ----------------
        document.querySelectorAll('.donut-marker').forEach((marker) => {
            marker.addEventListener('click', (e) => {
                e.stopPropagation();
                const alreadyTapped = marker.classList.contains('is-tapped');
                document.querySelectorAll('.donut-marker.is-tapped').forEach((m) => m.classList.remove('is-tapped'));
                if (!alreadyTapped) marker.classList.add('is-tapped');
            });
        });
        document.addEventListener('click', () => {
            document.querySelectorAll('.donut-marker.is-tapped').forEach((m) => m.classList.remove('is-tapped'));
        });
    </script>
</body>

</html>
