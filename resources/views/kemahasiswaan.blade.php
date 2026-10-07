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
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/delete-confirm.css') }}?v={{ @filemtime(public_path('css/delete-confirm.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <script>
        (function() {
            try {
                var w = parseInt(localStorage.getItem('sida.sidebarW'), 10);
                if (w) document.documentElement.style.setProperty('--sidebar-w', (w < 140 ? 72 : Math.min(340, Math.max(200, w))) + 'px');
            } catch (e) {}
        })();
    </script>
    <title>Kemahasiswaan &middot; SIDA</title>
</head>

<body>
    <!-- Definisi ikon SIDA (dipakai di logo sidebar) -->
    <svg class="svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="sida-mark" viewBox="0 0 48 48">
                <clipPath id="sidaClip">
                    <circle cx="24" cy="24" r="22.5" />
                </clipPath>
                <image href="{{ asset('img/logo-prodi.png') }}" x="1.5" y="1.5" width="45" height="45" preserveAspectRatio="xMidYMid slice" clip-path="url(#sidaClip)" />
                <circle cx="24" cy="24" r="22.5" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5" />
            </symbol>
        </defs>
    </svg>

    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <svg class="sidebar-brand-mark" aria-hidden="true">
                <use href="#sida-mark" />
            </svg>
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
                <a href="{{ url('/dashboard') }}" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>

                @if($__user->canAccessMenu('kemahasiswaan'))
                <a href="{{ url('/kemahasiswaan') }}" aria-current="page" class="nav-link is-active">
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
                        <span class="link">Kemahasiswaan</span>
                        <span>/</span>
                        <span class="current">Data Prestasi &amp; Kegiatan</span>
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
                                    <span class="header-profile-role">{{ $__user->accessLabelFor('kemahasiswaan') }}</span>
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
                                            <span class="header-profile-panel-role">{{ $__user->accessLabelFor('kemahasiswaan') }}</span>
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
            <div class="page-wrap is-loading" id="pageWrap" aria-busy="true">

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Prestasi &amp; Kegiatan Mahasiswa</h1>
                        <p class="page-subtitle">Pendataan kegiatan akademik, non-akademik, inbis, dan kompetisi
                            &middot; Tahun Akademik 2025/2026 (Genap)</p>
                    </div>
                    @php
                    // Menu titik tiga (Unduh Template / Upload / Download / Cetak Laporan): hanya admin & superadmin.
                    // Download butuh akses readonly ke atas; Template & Upload butuh akses penuh.
                    $bulkUser = auth()->user();
                    $bulkLevel = $bulkUser->menuLevel('kemahasiswaan');
                    $bulkRole = in_array($bulkUser->role, ['admin', 'superadmin'], true);
                    $showBulk = $bulkRole && in_array($bulkLevel, ['readonly', 'penuh'], true);
                    $bulkWrite = $bulkRole && $bulkLevel === 'penuh';
                    @endphp
                    <div class="title-actions">
                        <button type="button" class="btn-primary" id="btnTambahKegiatan">
                            <span class="material-symbols-outlined">add</span>
                            <span>Tambah Kegiatan</span>
                        </button>
                        @if($showBulk)
                        @include('partials.bulk-menu', [
                        'bulkLabel' => 'Kemahasiswaan',
                        'bulkTemplate' => $bulkWrite ? route('kemahasiswaan.template') : null,
                        'bulkImport' => $bulkWrite ? route('kemahasiswaan.import') : null,
                        'bulkExport' => route('kemahasiswaan.export'),
                        'bulkPrint' => route('cetak.show', 'kemahasiswaan'),
                        ])
                        @endif
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Kegiatan</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">emoji_events</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Nasional</span>
                            <span class="stat-value">{{ $stats['nasional'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">flag</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Internasional</span>
                            <span class="stat-value">{{ $stats['internasional'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Unit Inbis</span>
                            <span class="stat-value">{{ $stats['inbis'] }}</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">storefront</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Jenis Divisi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator
                                        Bisnis)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="kemahasiswaan">Kemahasiswaan</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label">Kategori Tab</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tab" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tab</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Tab</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="akademik">Akademik</button>
                                    <button type="button" class="dropdown-option" data-value="non_akademik">Non
                                        Akademik</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label">Tingkat Capaian</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tingkat" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tingkat</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Tingkat</button>
                                    <button type="button" class="dropdown-option" data-value="lokal">Lokal
                                        (Kota/Wilayah)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="nasional">Nasional (RI)</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional
                                        (Global)</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            @php $tsYear = now()->year; @endphp
                            <label class="field-label">Tahun Akademik</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tahun" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tahun</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Tahun</button>
                                    <button type="button" class="dropdown-option" data-value="{{ $tsYear }}">TS ({{ $tsYear }})</button>
                                    <button type="button" class="dropdown-option" data-value="{{ $tsYear - 1 }}">TS-1 ({{ $tsYear - 1 }})</button>
                                    <button type="button" class="dropdown-option" data-value="{{ $tsYear - 2 }}">TS-2 ({{ $tsYear - 2 }})</button>
                                </div>
                            </div>
                        </div>
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari NIM/nama mahasiswa..." />
                                </div>
                            </div>
                            <div class="field field-reset">
                                <button type="button" id="btn-reset-filter" class="btn-rst" title="Reset Filter">
                                    <span class="material-symbols-outlined">restart_alt</span>
                                    <span class="btn-rst-text">Reset Filter</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATA TABLE CARD -->
                <div class="table-card">
                    <div class="table-card-header">
                        <div>
                            <h2 class="table-card-title">Daftar Rekap Prestasi Mahasiswa</h2>
                            <p class="table-card-subtitle">Data kegiatan terverifikasi sesuai format resmi SIM
                                Kemahasiswaan 2026</p>
                        </div>
                    </div>

                    @if($showBulk)
                        {{-- Bar aksi data terpilih (Export Excel / Cetak PDF / Batal pilih); muncul saat ada baris dicentang --}}
                        @include('partials.row-select-bar')
                    @endif

                    <div class="table-scroll">
                        <table class="data-table"
                            @if($showBulk)
                                data-selectable="kemahasiswaan"
                                data-select-export="{{ route('pilihan.export', 'kemahasiswaan') }}"
                                data-select-print="{{ route('pilihan.cetak', 'kemahasiswaan') }}"
                                @if($bulkWrite) data-select-delete="{{ route('pilihan.hapus', 'kemahasiswaan') }}" @endif
                                @if($bulkWrite) data-select-update="{{ route('pilihan.ubah', 'kemahasiswaan') }}" @endif
                            @endif>
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Mahasiswa</th>
                                    <th>Nama Kegiatan</th>
                                    <th class="center">Jenis</th>
                                    <th class="center">Tab</th>
                                    <th class="center">Tingkat</th>
                                    <th class="center">Tahun</th>
                                    <th class="center">Bukti Kegiatan</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kegiatanTableBody">
                                @forelse($kegiatan as $item)
                                @php
                                $nama = optional($item->mahasiswa->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                $avatarColor = $colors[$item->mahasiswa_id % count($colors)];
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}" data-tab="{{ $item->tab }}" data-tingkat="{{ $item->tingkat }}" data-tahun="{{ $item->tahun }}">
                                    <td><span class="nim-code">{{ $item->mahasiswa->nim ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name">
                                                <span class="name">{{ $nama }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="{{ $item->nama_kegiatan }}">{{ $item->nama_kegiatan }}</span>
                                    </td>
                                    <td class="center"><span class="plain-text">{{ $item->jenis }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $item->tab }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $item->tingkat }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $item->tahun }}</span></td>
                                    <td class="center">
                                        <a href="{{ $item->bukti_kegiatan }}" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-mahasiswa_id="{{ $item->mahasiswa_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-tab="{{ $item->tab }}"
                                                data-tingkat="{{ $item->tingkat }}"
                                                data-tahun="{{ $item->tahun }}"
                                                data-nama_kegiatan="{{ $item->nama_kegiatan }}"
                                                data-bukti_kegiatan="{{ $item->bukti_kegiatan }}">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row"
                                                data-id="{{ $item->id }}">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="9" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data kegiatan. Klik "Tambah Kegiatan" untuk mulai mengisi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                    <tr>
                                    <td colspan="9"><span class="sk-bar"></span></td>
                                    </tr>
                                    @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>{{ $kegiatan->firstItem() ?? 0 }}-{{ $kegiatan->lastItem() ?? 0 }}</strong>
                            dari <strong>{{ $kegiatan->total() }}</strong> data kegiatan mahasiswa
                        </div>
                        {{-- Pagination bawaan Laravel bisa ditambahkan di sini via {{ $kegiatan->links() }}
                        setelah view paginator kamu disesuaikan dengan desain ini. --}}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit Kegiatan ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="kegiatanModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Kegiatan</h3>
                <p class="modal-subtitle">Isi detail prestasi/kegiatan mahasiswa</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="kegiatanForm" class="modal-body">
            <input type="hidden" id="form-id" value="">

            <div class="field">
                <label class="field-label">Mahasiswa</label>
                <div class="dropdown" data-dropdown id="dd-mahasiswa">
                    <input type="hidden" id="form-mahasiswa_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih mahasiswa...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($mahasiswaList as $m)
                        <button type="button" class="dropdown-option" data-value="{{ $m->id }}">{{ $m->nim }} &mdash; {{ optional($m->user)->name ?? '-' }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Kegiatan</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="kemahasiswaan" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Kemahasiswaan</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="kemahasiswaan">Kemahasiswaan</button>
                            <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator Bisnis)</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Kategori Tab</label>
                    <div class="dropdown" data-dropdown id="dd-tab">
                        <input type="hidden" id="form-tab" value="akademik" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Akademik</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="akademik">Akademik</button>
                            <button type="button" class="dropdown-option" data-value="non_akademik">Non Akademik</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Tingkat Capaian</label>
                    <div class="dropdown" data-dropdown id="dd-tingkat">
                        <input type="hidden" id="form-tingkat" value="lokal" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Lokal (Kota/Wilayah)</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="lokal">Lokal (Kota/Wilayah)</button>
                            <button type="button" class="dropdown-option" data-value="nasional">Nasional (RI)</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional (Global)</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-tahun">Tahun</label>
                    <div class="field-control">
                        <input id="form-tahun" type="number" min="2000" max="2100" value="2026" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-nama_kegiatan">Nama Kegiatan</label>
                <div class="field-control">
                    <input id="form-nama_kegiatan" type="text" placeholder="Contoh: Juara 1 Kompetisi UI/UX Nasional" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti Kegiatan</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
            </div>

            <div class="modal-error" id="modalError" hidden></div>

            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="modalCancelBtn">Batal</button>
                <button type="submit" class="btn-apply" id="modalSubmitBtn">
                    <span class="material-symbols-outlined">save</span>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>

    @if($showBulk && $bulkWrite)
    @include('partials.bulk-import-modal', [
    'bulkLabel' => 'Kemahasiswaan',
    'bulkImport' => route('kemahasiswaan.import'),
    'bulkTemplate' => route('kemahasiswaan.template'),
    ])
    @endif

    @if($showBulk && $bulkWrite)
        {{-- Modal Update Massal (data terpilih). Kolom NIM, Mahasiswa, Tahun & Bukti sengaja tidak ada: khas per baris. --}}
        @include('partials.bulk-update-modal', [
            'updateLabel'  => 'Kemahasiswaan',
            'updateFields' => [
                ['name' => 'nama_kegiatan', 'label' => 'Nama Kegiatan', 'type' => 'text', 'placeholder' => 'Kosongkan = nama kegiatan tidak diubah', 'col' => 2, 'target' => '.activity-title'],
                ['name' => 'jenis',   'label' => 'Jenis Kegiatan',  'type' => 'select', 'row' => 'a', 'col' => 3, 'target' => '.plain-text',
                    'options' => ['kemahasiswaan' => 'Kemahasiswaan', 'inbis' => 'Inbis (Inkubator Bisnis)']],
                ['name' => 'tab',     'label' => 'Kategori Tab',    'type' => 'select', 'row' => 'a', 'col' => 4, 'target' => '.plain-text',
                    'options' => ['akademik' => 'Akademik', 'non_akademik' => 'Non Akademik']],
                ['name' => 'tingkat', 'label' => 'Tingkat Capaian', 'type' => 'select', 'col' => 5, 'target' => '.plain-text',
                    'options' => ['lokal' => 'Lokal (Kota/Wilayah)', 'nasional' => 'Nasional (RI)', 'internasional' => 'Internasional (Global)']],
            ],
        ])
    @endif

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        // ================================================================
        // Bagian ini KHUSUS halaman Kemahasiswaan:
        // endpoint API, bentuk baris tabel, field form, dan filter tambahan.
        // Semua yang generik (dropdown, sidebar, dark mode, notifikasi,
        // modal buka/tutup/drag, live search) sudah ditangani oleh
        // public/js/script.js lewat objek global SIDA.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const {
            esc,
            initials,
            avatarColor
        } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('kegiatanTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('kegiatanModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('kegiatanForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahKegiatan');
        const dragHandle = document.getElementById('modalDragHandle');

        // ---- Bangun HTML baris tabel dari data JSON (khusus halaman ini) ----
        function buildRowHTML(item) {
            return `
            <tr data-id="${item.id}" data-jenis="${item.jenis}" data-tab="${item.tab}" data-tingkat="${item.tingkat}" data-tahun="${item.tahun}">
                <td><span class="nim-code">${esc(item.nim)}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.mahasiswa_id)}">${esc(initials(item.nama))}</div>
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td><span class="activity-title" title="${esc(item.nama_kegiatan)}">${esc(item.nama_kegiatan)}</span></td>
                <td class="center"><span class="plain-text">${esc(item.jenis)}</span></td>
                <td class="center"><span class="plain-text">${esc(item.tab)}</span></td>
                <td class="center"><span class="plain-text">${esc(item.tingkat)}</span></td>
                <td class="center"><span class="year-chip">${esc(item.tahun)}</span></td>
                <td class="center">
                    <a href="${esc(item.bukti_kegiatan)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">cloud</span><span>Lihat Bukti</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-mahasiswa_id="${item.mahasiswa_id}" data-jenis="${item.jenis}"
                            data-tab="${item.tab}" data-tingkat="${item.tingkat}" data-tahun="${item.tahun}"
                            data-nama_kegiatan="${esc(item.nama_kegiatan)}"
                            data-bukti_kegiatan="${esc(item.bukti_kegiatan)}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="${item.id}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </td>
            </tr>`.trim();
        }
        const {
            insertRow,
            updateRow,
            removeRow
        } = SIDA.table.create(tableBody, buildRowHTML);

        // ---- Live filter (khusus halaman ini: 3 dropdown + search) ----
        const applyFilters = SIDA.filter.setup({
            tableBody,
            dropdownFilterIds: ['filter-jenis', 'filter-tab', 'filter-tingkat', 'filter-tahun'],
            searchInputId: 'filter-search',
            matches: (row) => {
                const jenisVal = document.getElementById('filter-jenis')?.value || 'semua';
                const tabVal = document.getElementById('filter-tab')?.value || 'semua';
                const tingkatVal = document.getElementById('filter-tingkat')?.value || 'semua';
                const tahunVal = document.getElementById('filter-tahun')?.value || 'semua';
                const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
                const matchesJenis = jenisVal === 'semua' || row.dataset.jenis === jenisVal;
                const matchesTab = tabVal === 'semua' || row.dataset.tab === tabVal;
                const matchesTingkat = tingkatVal === 'semua' || row.dataset.tingkat === tingkatVal;
                const matchesTahun = tahunVal === 'semua' || row.dataset.tahun === tahunVal;
                const matchesSearch = !searchVal || row.textContent.toLowerCase().includes(searchVal);
                return matchesJenis && matchesTab && matchesTingkat && matchesTahun && matchesSearch;
            },
            emptyMessage: 'Tidak ada data yang cocok dengan filter.',
        });

        // ---- Modal: mekanisme buka/tutup/drag dari script.js ----
        const {
            open: openModalBase,
            close: closeModal
        } = SIDA.modal.attach({
            backdrop: modalBackdrop,
            card: modalCard,
            closeBtn: modalCloseBtn,
            cancelBtn: modalCancelBtn,
            dragHandle: dragHandle,
        });

        // ---- Isi form modal (khusus halaman ini) ----
        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Kegiatan' : 'Tambah Kegiatan';

            SIDA.dropdown.select(document.getElementById('dd-mahasiswa'), data.mahasiswa_id || '', 'Pilih mahasiswa...');
            SIDA.dropdown.select(document.getElementById('dd-jenis'), data.jenis || 'kemahasiswaan');
            SIDA.dropdown.select(document.getElementById('dd-tab'), data.tab || 'akademik');
            SIDA.dropdown.select(document.getElementById('dd-tingkat'), data.tingkat || 'lokal');

            document.getElementById('form-tahun').value = data.tahun || 2026;
            document.getElementById('form-nama_kegiatan').value = data.nama_kegiatan || '';
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan || '';

            openModalBase();
        }

        btnTambah?.addEventListener('click', () => openModal('create'));

        // ---- Submit form (create / update) — endpoint khusus halaman ini ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const payload = {
                mahasiswa_id: document.getElementById('form-mahasiswa_id').value,
                jenis: document.getElementById('form-jenis').value,
                tab: document.getElementById('form-tab').value,
                tingkat: document.getElementById('form-tingkat').value,
                tahun: document.getElementById('form-tahun').value,
                nama_kegiatan: document.getElementById('form-nama_kegiatan').value,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
            };

            if (!payload.mahasiswa_id) {
                modalError.textContent = 'Pilih mahasiswa terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/kemahasiswaan/${id}` : '/kemahasiswaan';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                const result = await res.json();

                if (!res.ok) {
                    const firstError = result.errors ? Object.values(result.errors)[0][0] : (result.message || 'Terjadi kesalahan, coba lagi.');
                    modalError.textContent = firstError;
                    modalError.hidden = false;
                    return;
                }

                if (id) updateRow(result.data);
                else insertRow(result.data);
                applyFilters();
                closeModal();
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Delete — endpoint khusus halaman ini ----
        async function handleDelete(id, name) {
            const ok = await DeleteConfirm.ask({
                name,
                entity: 'data'
            });
            if (!ok) return;
            try {
                const res = await fetch(`/kemahasiswaan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                });
                const result = await res.json();
                if (res.ok && result.success) removeRow(result.id);
                else alert(result.message || 'Gagal menghapus data.');
            } catch (err) {
                alert('Gagal terhubung ke server.');
            }
        }

        // ---- Event delegation: tombol Edit & Hapus di tiap baris (termasuk baris baru) ----
        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-row');
            if (editBtn) return openModal('edit', editBtn.dataset);
            const delBtn = e.target.closest('.btn-delete-row');
            if (delBtn) handleDelete(delBtn.dataset.id);
        });
    </script>
    <script src="{{ asset('js/delete-confirm.js') }}?v={{ @filemtime(public_path('js/delete-confirm.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @if($showBulk)
        <script src="{{ asset('js/bulk-import.js') }}?v={{ @filemtime(public_path('js/bulk-import.js')) }}"></script>
        <script src="{{ asset('js/row-select.js') }}?v={{ @filemtime(public_path('js/row-select.js')) }}"></script>
    @endif
    @if($showBulk && $bulkWrite)
        <script src="{{ asset('js/bulk-update.js') }}?v={{ @filemtime(public_path('js/bulk-update.js')) }}"></script>
    @endif
    <script>
        // Lepas skeleton begitu font siap (maks. 2,5 dtk) dan minimal tampil 350 ms
        // sejak halaman mulai dimuat, supaya tidak berkedip terlalu cepat.
        (function() {
            const wrap = document.getElementById('pageWrap');
            if (!wrap) return;
            const MIN_MS = 500;
            const fontsReady = (document.fonts && document.fonts.ready) || Promise.resolve();
            Promise.race([fontsReady, new Promise((r) => setTimeout(r, 2500))]).then(() => {
                setTimeout(() => {
                    wrap.classList.remove('is-loading');
                    wrap.classList.add('is-loaded');
                    wrap.removeAttribute('aria-busy');
                }, Math.max(0, MIN_MS - performance.now()));
            });
        })();
    </script>
</body>

</html>