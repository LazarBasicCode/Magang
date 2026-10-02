<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/profile-account.css') }}?v={{ @filemtime(public_path('css/profile-account.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/skeleton.css') }}?v={{ @filemtime(public_path('css/skeleton.css')) }}">
    <title>Hak Akses &middot; SIDA</title>
</head>

<body>
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
                <a href="{{ url('/dashboard') }}" class="nav-link">
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
                <a href="{{ url('/hak-akses') }}" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">shield_person</span>
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
                        <span class="link">Administrasi</span>
                        <span>/</span>
                        <span class="current">Hak Akses</span>
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
                                    <span class="header-profile-role">{{ $__user->accessLabelFor('hak_akses') }}</span>
                                </div>
                                <span class="material-symbols-outlined header-profile-caret">expand_more</span>
                            </button>

                            <div class="header-profile-panel" id="profilePanel" role="menu" aria-hidden="true">
                                <div class="header-profile-view is-active" id="profileViewMain">
                                    <div class="header-profile-panel-header">
                                        <div class="header-profile-panel-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                        <div>
                                            <span class="header-profile-panel-name">{{ $__user->name }}</span>
                                            <span class="header-profile-panel-role">{{ $__user->accessLabelFor('hak_akses') }}</span>
                                        </div>
                                    </div>
                                    <div class="header-profile-menu">
                                        <button type="button" class="header-profile-menu-item" id="btnShowAccessInfo">
                                            <span class="material-symbols-outlined">shield_person</span>
                                            <span>Informasi Akses</span>
                                        </button>
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

                <!-- PAGE TITLE -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Manajemen Hak Akses</h1>
                        <p class="page-subtitle">Atur akses menu per pengguna &middot; Sistem Informasi Data Akademik 2026</p>
                    </div>
                </div>

                @unless($canManage)
                <div class="filter-card" style="margin-bottom: .3rem; display:flex; align-items:center; gap:10px; padding: 14px 18px;">
                    <span class="material-symbols-outlined" style="color: var(--warning);">visibility</span>
                    <span class="plain-text">Kamu hanya punya akses <strong>Read Only</strong> di menu ini &mdash; bisa melihat data, tapi tidak bisa mengubah hak akses siapa pun.</span>
                </div>
                @endunless

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Pengguna</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Akses Penuh</span>
                            <span class="stat-value">{{ $stats['penuh'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">verified_user</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Akses Biasa</span>
                            <span class="stat-value">{{ $stats['biasa'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">how_to_reg</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Read Only</span>
                            <span class="stat-value">{{ $stats['readonly'] }}</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">visibility</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR (aktif otomatis: filter & pencarian langsung jalan tanpa reload) -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Peran</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-role" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Peran</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Peran</button>
                                    <button type="button" class="dropdown-option" data-value="superadmin">Superadmin</button>
                                    <button type="button" class="dropdown-option" data-value="admin">Admin</button>
                                    <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                                    <button type="button" class="dropdown-option" data-value="mahasiswa">Mahasiswa</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label">Status</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-status" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Status</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Status</button>
                                    <button type="button" class="dropdown-option" data-value="aktif">Aktif</button>
                                    <button type="button" class="dropdown-option" data-value="read">Read</button>
                                    <button type="button" class="dropdown-option" data-value="nonaktif">Nonaktif</button>
                                </div>
                            </div>
                        </div>
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari nama atau NIM/NIDN pengguna..." />
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
                            <h2 class="table-card-title">Daftar Pengguna &amp; Hak Akses</h2>
                            <p class="table-card-subtitle">Klik ikon kunci pada kolom aksi untuk mengatur hak akses menu</p>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Pengguna</th>
                                    <th>NIM/NIDN</th>
                                    <th class="center">Peran</th>
                                    <th class="center">Status</th>
                                    <th class="center">Ringkasan Akses</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="hakAksesTableBody">
                                @php
                                $statusBadge = [
                                'aktif' => ['label' => 'Aktif', 'class' => 'badge-success'],
                                'read' => ['label' => 'Read', 'class' => 'badge-warning'],
                                'nonaktif' => ['label' => 'Nonaktif', 'class' => 'badge-neutral'],
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @forelse($rows as $row)
                                @php
                                $u = $row['user'];
                                $initials = collect(explode(' ', $u->name))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$u->id % count($colors)];
                                $sb = $statusBadge[$row['status']];
                                @endphp
                                <tr data-id="{{ $u->id }}" data-role="{{ $u->role }}" data-status="{{ $row['status'] }}">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $u->name }}</span></div>
                                        </div>
                                    </td>
                                    <td><span class="nim-code">{{ $u->nim_nidn ?? '-' }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $u->role }}</span></td>
                                    <td class="center"><span class="badge {{ $sb['class'] }}">{{ $sb['label'] }}</span></td>
                                    <td class="center">
                                        <div class="access-summary">
                                            <span class="access-chip penuh {{ $row['ringkasan']['penuh'] === 0 ? 'is-zero' : '' }}" title="Akses Penuh"><span class="access-dot penuh"></span> {{ $row['ringkasan']['penuh'] }}</span>
                                            <span class="access-chip biasa {{ $row['ringkasan']['biasa'] === 0 ? 'is-zero' : '' }}" title="Akses Biasa"><span class="access-dot biasa"></span> {{ $row['ringkasan']['biasa'] }}</span>
                                            <span class="access-chip readonly {{ $row['ringkasan']['readonly'] === 0 ? 'is-zero' : '' }}" title="Read Only"><span class="access-dot readonly"></span> {{ $row['ringkasan']['readonly'] }}</span>
                                            <span class="access-chip none {{ $row['ringkasan']['none'] === 0 ? 'is-zero' : '' }}" title="Tanpa Akses"><span class="access-dot none"></span> {{ $row['ringkasan']['none'] }}</span>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            @if($canManage && $u->role !== 'superadmin')
                                            <button type="button" title="Atur Hak Akses" class="row-action-btn btn-access-row"
                                                data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}"
                                                data-nim_nidn="{{ $u->nim_nidn ?? '-' }}"
                                                data-role="{{ $u->role }}"
                                                data-levels="{{ urlencode(json_encode($row['levels'])) }}">
                                                <span class="material-symbols-outlined">shield_person</span>
                                            </button>
                                            @else
                                            <button type="button"
                                                title="{{ $u->role === 'superadmin' ? 'Superadmin selalu memiliki akses penuh dan tidak bisa dibatasi' : 'Lihat Hak Akses' }}"
                                                class="row-action-btn btn-access-row"
                                                data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}"
                                                data-nim_nidn="{{ $u->nim_nidn ?? '-' }}"
                                                data-role="{{ $u->role }}"
                                                data-levels="{{ urlencode(json_encode($row['levels'])) }}"
                                                data-readonly="1"
                                                @if($u->role === 'superadmin') data-readonly-reason="Superadmin selalu memiliki akses penuh ke semua menu dan tidak bisa dibatasi lewat halaman ini." @endif>
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="6" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada pengguna terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                                <tr id="noResultsRow" hidden>
                                    <td colspan="6" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Tidak ada pengguna yang cocok dengan filter/pencarian.
                                    </td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                <tr><td colspan="7"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerVisibleCount">{{ $rows->count() }}</strong> dari <strong>{{ $stats['total'] }}</strong> pengguna
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Hak Akses ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="accessModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Atur Hak Akses</h3>
                <p class="modal-subtitle">Tentukan level akses untuk setiap menu</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="accessForm" class="modal-body">
            <input type="hidden" id="form-user_id" value="">

            <!-- User chip -->
            <div class="access-user-chip">
                <div class="avatar c-primary" id="accessUserAvatar">--</div>
                <div class="access-user-chip-text">
                    <span class="access-user-chip-name" id="accessUserName">-</span>
                    <div class="access-user-chip-meta">
                        <span class="badge badge-primary" id="accessUserRole">user</span>
                        <span class="plain-text" id="accessUserEmail">-</span>
                    </div>
                    <p class="access-readonly-note" id="modalReadonlyNote" hidden></p>
                </div>
            </div>

            <!-- Sidebar kiri -->
            <aside class="access-side">
                <div class="access-divider">Level Akses</div>
                <div class="access-level-list" id="accessLevelList">
                    <button type="button" class="access-level-item" data-value="penuh">
                        <span class="access-level-item-main">
                            <span class="access-dot penuh"></span>
                            <span>Akses Penuh</span>
                        </span>
                        <span class="access-level-apply material-symbols-outlined" title="Terapkan ke semua menu">arrow_forward</span>
                    </button>
                    <button type="button" class="access-level-item" data-value="biasa">
                        <span class="access-level-item-main">
                            <span class="access-dot biasa"></span>
                            <span>Akses Biasa</span>
                        </span>
                        <span class="access-level-apply material-symbols-outlined" title="Terapkan ke semua menu">arrow_forward</span>
                    </button>
                    <button type="button" class="access-level-item" data-value="readonly">
                        <span class="access-level-item-main">
                            <span class="access-dot readonly"></span>
                            <span>Read Only</span>
                        </span>
                        <span class="access-level-apply material-symbols-outlined" title="Terapkan ke semua menu">arrow_forward</span>
                    </button>
                    <button type="button" class="access-level-item" data-value="none">
                        <span class="access-level-item-main">
                            <span class="access-dot none"></span>
                            <span>Tidak Diberi Akses</span>
                        </span>
                        <span class="access-level-apply material-symbols-outlined" title="Terapkan ke semua menu">arrow_forward</span>
                    </button>
                </div>
                <p class="access-level-hint">Klik salah satu level untuk menerapkannya ke semua menu.</p>
            </aside>

            <!-- Area kanan: daftar menu -->
            <section class="access-main">
                <div class="access-divider">Daftar Menu</div>
                <div class="permission-list" id="permissionList">
                    @foreach($menus as $menu)
                    <div class="permission-row" data-menu="{{ $menu['key'] }}">
                        <div class="permission-menu">
                            <div class="permission-menu-icon">
                                <span class="material-symbols-outlined">{{ $menu['icon'] }}</span>
                            </div>
                            <span class="permission-menu-name">{{ $menu['label'] }}</span>
                        </div>
                        <div class="dropdown" data-dropdown data-permission-dropdown data-menu="{{ $menu['key'] }}">
                            <input type="hidden" class="permission-value" data-menu="{{ $menu['key'] }}" value="none" />
                            <button type="button" class="dropdown-trigger">
                                <span class="dropdown-value">Tidak Diberi Akses</span>
                                <span class="material-symbols-outlined caret">expand_more</span>
                            </button>
                            <div class="dropdown-panel">
                                <button type="button" class="dropdown-option is-selected" data-value="none"><span class="access-dot none"></span> Tidak Diberi Akses</button>
                                <button type="button" class="dropdown-option" data-value="readonly"><span class="access-dot readonly"></span> Read Only</button>
                                <button type="button" class="dropdown-option" data-value="biasa"><span class="access-dot biasa"></span> Akses Biasa</button>
                                <button type="button" class="dropdown-option" data-value="penuh"><span class="access-dot penuh"></span> Akses Penuh</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <div class="modal-error" id="modalError" hidden></div>

            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="modalCancelBtn">Batal</button>
                <button type="submit" class="btn-apply" id="modalSubmitBtn">
                    <span class="material-symbols-outlined">save</span>
                    <span>Simpan Hak Akses</span>
                </button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        // ================================================================
        // KHUSUS halaman Hak Akses.
        // Semua function khusus hak-akses dipertahankan 100%:
        //   - firstVisibleOption
        //   - selectDropdownValue (versi lokal, cek hidden)
        //   - resetDropdown        (versi lokal, cek hidden)
        //   - applyRoleAccessRules
        //   - applyLevelToAll
        //   - updateRowAfterSave + chip
        //   - esc (lokal, dipertahankan)
        //   - applyFilters
        //
        // Listener dropdown pakai capture phase + set nilai manual, supaya:
        //   - Guard read-only (canManage) bekerja
        //   - Guard opsi hidden bekerja
        //   - Filter baca nilai terbaru
        //   - row.dataset.state tetap di-set (untuk warna CSS)
        //
        // Generic (sidebar, dark mode, modal, util) dipindah ke script.js.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const canManage = @json($canManage);

        // ----------------------------------------------------------------
        // ATURAN BISNIS
        // ----------------------------------------------------------------
        const ROLES_WITHOUT_FULL_ACCESS = ['mahasiswa', 'dosen'];

        // Selaras dengan HakAkses::ADMIN_SINGLE_RESPONSIBILITY_MENUS &
        // ADMIN_READONLY_CEILING_MENUS di backend — dipakai untuk memberi
        // panduan visual di modal SEBELUM submit (validasi sebenarnya tetap
        // di server, ini cuma supaya UX-nya tidak perlu nunggu ditolak dulu).
        const ADMIN_SINGLE_RESPONSIBILITY_MENUS = ['kemahasiswaan', 'lppm_mahasiswa', 'lppm_dosen', 'rekognisi', 'kerja_sama'];
        const ADMIN_READONLY_CEILING_MENUS = ['data_master', 'hak_akses', 'log', 'backup'];

        function applyRoleAccessRules(role) {
            const roleLower = (role || '').toLowerCase();
            const restricted = ROLES_WITHOUT_FULL_ACCESS.includes(roleLower);
            document.querySelectorAll('#accessModal .dropdown-option[data-value="penuh"]').forEach((opt) => {
                opt.hidden = restricted;
            });
            const isAdmin = roleLower === 'admin';
            // Akses Cepat: mahasiswa/dosen tanpa "Penuh"; admin hanya
            // "Tidak Diberi Akses" & "Read Only" (tanpa Penuh/Biasa).
            const fullLevelItem = document.querySelector('#accessModal .access-level-item[data-value="penuh"]');
            if (fullLevelItem) fullLevelItem.hidden = restricted || isAdmin;
            const biasaLevelItem = document.querySelector('#accessModal .access-level-item[data-value="biasa"]');
            if (biasaLevelItem) biasaLevelItem.hidden = isAdmin;

            // Kebijakan khusus admin: Data Master & Hak Akses murni wewenang
            // superadmin — admin maksimal cuma boleh "Read Only", opsi
            // "Akses Biasa"/"Akses Penuh" disembunyikan total di 2 baris ini.
            ADMIN_READONLY_CEILING_MENUS.forEach((menu) => {
                const row = document.querySelector(`.permission-row[data-menu="${menu}"]`);
                row?.querySelectorAll('.dropdown-option[data-value="biasa"], .dropdown-option[data-value="penuh"]').forEach((opt) => {
                    opt.hidden = isAdmin;
                });
            });

            return restricted;
        }

        // Kebijakan "1 admin = 1 peran": kalau target modal ini seorang admin
        // dan salah satu menu operasional baru saja diaktifkan (biasa/penuh),
        // otomatis nonaktifkan menu operasional LAINNYA supaya tidak lolos
        // punya lebih dari satu peran sekaligus (validasi keras tetap di server).
        function enforceSingleResponsibility(changedMenu) {
            const targetRole = (document.getElementById('accessModal')?.dataset.targetRole || '').toLowerCase();
            if (targetRole !== 'admin') return;
            if (!ADMIN_SINGLE_RESPONSIBILITY_MENUS.includes(changedMenu)) return;

            ADMIN_SINGLE_RESPONSIBILITY_MENUS
                .filter((menu) => menu !== changedMenu)
                .forEach((menu) => {
                    const otherDropdown = document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`);
                    if (otherDropdown) selectDropdownValue(otherDropdown, 'none');
                });
        }

        // ----------------------------------------------------------------
        // DROPDOWN
        // ----------------------------------------------------------------
        const dropdowns = document.querySelectorAll('[data-dropdown]');

        function firstVisibleOption(dropdownEl) {
            return Array.from(dropdownEl.querySelectorAll('.dropdown-option')).find((o) => !o.hidden);
        }

        function selectDropdownValue(dropdownEl, value) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll('.dropdown-option');
            const valueEl = dropdownEl.querySelector('.dropdown-value');
            const hiddenInput = dropdownEl.querySelector('input[type="hidden"]');
            let target = Array.from(options).find((o) => !o.hidden && o.dataset.value === String(value));
            if (!target) target = firstVisibleOption(dropdownEl);
            options.forEach((o) => o.classList.toggle('is-selected', o === target));
            if (target) {
                valueEl.textContent = target.textContent.trim();
                if (hiddenInput) hiddenInput.value = target.dataset.value;
            }
            const row = dropdownEl.closest('.permission-row');
            if (row) row.dataset.state = target ? target.dataset.value : '';
        }

        function resetDropdown(dropdown) {
            if (!dropdown) return;
            const options = dropdown.querySelectorAll('.dropdown-option');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const target = firstVisibleOption(dropdown);
            options.forEach((o) => o.classList.toggle('is-selected', o === target));
            if (target) {
                valueEl.textContent = target.textContent.trim();
                if (hiddenInput) hiddenInput.value = target.dataset.value;
            }
        }

        // Listener dropdown: capture phase.
        //   - Guard read-only (canManage) di trigger.
        //   - Guard opsi hidden di option.
        //   - Set nilai manual di option (karena capture jalan SEBELUM script.js).
        //   - Set row.dataset.state (dipakai CSS untuk warna).
        //   - Callback filter untuk dropdown di filter-bar.
        dropdowns.forEach((dropdown) => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const options = dropdown.querySelectorAll('.dropdown-option');

            trigger?.addEventListener('click', (e) => {
                if (dropdown.closest('#accessModal') && !canManage) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                }
            }, true);

            options.forEach((option) => {
                option.addEventListener('click', (e) => {
                    if (option.hidden) {
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        return;
                    }
                    const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                    const valueEl = dropdown.querySelector('.dropdown-value');
                    if (hiddenInput) hiddenInput.value = option.dataset.value;
                    if (valueEl) valueEl.textContent = option.textContent.trim();
                    const row = dropdown.closest('.permission-row');
                    if (row) row.dataset.state = option.dataset.value;
                    if (dropdown.closest('.filter-grid')) applyFilters();
                    if (row && ['biasa', 'penuh'].includes(option.dataset.value)) {
                        enforceSingleResponsibility(row.dataset.menu);
                    }
                }, true);
            });
        });

        document.addEventListener('click', () => dropdowns.forEach((d) => d.classList.remove('is-open')));

        // ----------------------------------------------------------------
        // FILTER
        // ----------------------------------------------------------------
        const filterSearchInput = document.getElementById('filter-search');
        const filterRoleInput = document.getElementById('filter-role');
        const filterStatusInput = document.getElementById('filter-status');
        const tableBody = document.getElementById('hakAksesTableBody');
        const noResultsRow = document.getElementById('noResultsRow');
        const footerVisibleCount = document.getElementById('footerVisibleCount');

        function applyFilters() {
            if (!tableBody) return;
            const term = (filterSearchInput?.value || '').trim().toLowerCase();
            const role = filterRoleInput?.value || 'semua';
            const status = filterStatusInput?.value || 'semua';

            const rows = tableBody.querySelectorAll('tr[data-id]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const nama = (row.querySelector('.student-name .name')?.textContent || '').toLowerCase();
                const nim = (row.querySelector('.nim-code')?.textContent || '').toLowerCase();
                const rowRole = row.dataset.role || '';
                const rowStatus = row.dataset.status || '';

                const matchesSearch = !term || nama.includes(term) || nim.includes(term);
                const matchesRole = role === 'semua' || rowRole === role;
                const matchesStatus = status === 'semua' || rowStatus === status;
                const visible = matchesSearch && matchesRole && matchesStatus;

                row.hidden = !visible;
                if (visible) visibleCount++;
            });

            const emptyRow = document.getElementById('emptyRow');
            const hasData = rows.length > 0;
            if (noResultsRow) noResultsRow.hidden = !(hasData && visibleCount === 0);
            if (footerVisibleCount) footerVisibleCount.textContent = visibleCount;
            if (emptyRow) emptyRow.hidden = hasData;
        }

        let searchDebounceTimer = null;
        filterSearchInput?.addEventListener('input', () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(applyFilters, 150);
        });

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            document.querySelectorAll('.filter-grid [data-dropdown]').forEach(resetDropdown);
            if (filterSearchInput) filterSearchInput.value = '';
            applyFilters();
        });

        // ----------------------------------------------------------------
        // MODAL HAK AKSES
        // ----------------------------------------------------------------
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('accessModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('accessForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const dragHandle = document.getElementById('modalDragHandle');

        const accessUserAvatar = document.getElementById('accessUserAvatar');
        const accessUserName = document.getElementById('accessUserName');
        const modalReadonlyNote = document.getElementById('modalReadonlyNote');
        const accessUserRole = document.getElementById('accessUserRole');
        const accessUserEmail = document.getElementById('accessUserEmail');

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

        function openModal(data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-user_id').value = data.id || '';
            document.getElementById('accessModal').dataset.targetRole = (data.role || '').toLowerCase();
            modalTitle.textContent = data.readonly ? 'Lihat Hak Akses' : 'Atur Hak Akses';

            if (data.readonlyReason) {
                modalReadonlyNote.textContent = data.readonlyReason;
                modalReadonlyNote.hidden = false;
            } else {
                modalReadonlyNote.textContent = '';
                modalReadonlyNote.hidden = true;
            }

            accessUserName.textContent = data.name || '-';
            accessUserEmail.textContent = data.nim_nidn || '-';
            accessUserRole.textContent = data.role || 'user';
            accessUserAvatar.textContent = SIDA.util.initials(data.name);

            const restricted = applyRoleAccessRules(data.role);

            let levels = {};
            try {
                levels = JSON.parse(decodeURIComponent(data.levels || '{}'));
            } catch (_) {
                levels = {};
            }

            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                let level = levels[menu] || 'none';
                if (restricted && level === 'penuh') level = 'biasa';
                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    level
                );
            });

            modalSubmitBtn.hidden = !!data.readonly;
            document.getElementById('accessLevelList').classList.toggle('is-locked', !!data.readonly);
            document.querySelectorAll('.access-level-item').forEach((btn) => {
                btn.disabled = !!data.readonly;
            });

            openModalBase();
        }

        // ---- "Level Akses" gabungan = legenda + terapkan cepat ----
        function applyLevelToAll(value) {
            if (!canManage) return;
            const targetRole = (document.getElementById('accessModal')?.dataset.targetRole || '').toLowerCase();
            const isAdminActivating = targetRole === 'admin' && ['biasa', 'penuh'].includes(value);
            let pickedOneResponsibility = false;

            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                let effectiveValue = value;

                if (isAdminActivating && ADMIN_READONLY_CEILING_MENUS.includes(menu)) {
                    // Data Master & Hak Akses tidak ikut "Akses Biasa/Penuh" massal
                    // — mentok di Read Only sesuai kebijakan admin.
                    effectiveValue = 'readonly';
                } else if (isAdminActivating && ADMIN_SINGLE_RESPONSIBILITY_MENUS.includes(menu)) {
                    // "1 admin = 1 peran": cuma menu operasional PERTAMA yang ikut
                    // nilai massal ini, sisanya dikembalikan ke "none".
                    if (pickedOneResponsibility) {
                        effectiveValue = 'none';
                    } else {
                        pickedOneResponsibility = true;
                    }
                }

                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    effectiveValue
                );
            });

            if (isAdminActivating) {
                modalError.textContent = 'Catatan: untuk akun admin, "Terapkan ke semua" otomatis membatasi Data Master/Hak Akses ke Read Only dan hanya mengaktifkan satu menu peran.';
                modalError.hidden = false;
            }
        }
        document.getElementById('accessLevelList')?.addEventListener('click', (e) => {
            const btn = e.target.closest('.access-level-item');
            if (!btn || btn.disabled || btn.hidden) return;
            applyLevelToAll(btn.dataset.value);
        });

        // ---- Update baris setelah save ----
        const statusBadge = {
            aktif: {
                label: 'Aktif',
                cls: 'badge-success'
            },
            read: {
                label: 'Read',
                cls: 'badge-warning'
            },
            nonaktif: {
                label: 'Nonaktif',
                cls: 'badge-neutral'
            },
        };

        function esc(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        function chip(cls, label, count) {
            return `<span class="access-chip ${cls} ${count === 0 ? 'is-zero' : ''}" title="${label}"><span class="access-dot ${cls}"></span> ${count}</span>`;
        }

        function updateRowAfterSave(payload) {
            const row = tableBody.querySelector(`tr[data-id="${payload.id}"]`);
            if (!row) return;
            row.dataset.status = payload.status;

            const sb = statusBadge[payload.status] || statusBadge.nonaktif;
            const statusCell = row.querySelector('td:nth-child(4) .badge');
            if (statusCell) {
                statusCell.className = `badge ${sb.cls}`;
                statusCell.textContent = sb.label;
            }

            const summaryCell = row.querySelector('.access-summary');
            if (summaryCell) {
                summaryCell.innerHTML =
                    chip('penuh', 'Akses Penuh', payload.ringkasan.penuh) +
                    chip('biasa', 'Akses Biasa', payload.ringkasan.biasa) +
                    chip('readonly', 'Read Only', payload.ringkasan.readonly) +
                    chip('none', 'Tanpa Akses', payload.ringkasan.none);
            }

            const accessBtn = row.querySelector('.btn-access-row');
            if (accessBtn) {
                accessBtn.dataset.levels = encodeURIComponent(JSON.stringify(payload.levels));
            }

            applyFilters();
        }

        // ---- Submit: simpan ke database ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!canManage) return;
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const userId = document.getElementById('form-user_id').value;
            const levels = {};
            document.querySelectorAll('.permission-value').forEach((input) => {
                levels[input.dataset.menu] = input.value;
            });

            try {
                const res = await fetch(`/hak-akses/${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        levels
                    }),
                });
                const result = await res.json();
                if (!res.ok || !result.success) {
                    modalError.textContent = result.message || 'Gagal menyimpan hak akses.';
                    modalError.hidden = false;
                    return;
                }
                updateRowAfterSave(result.data);
                closeModal();
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Event delegation: tombol Atur/Lihat Hak Akses ----
        tableBody.addEventListener('click', (e) => {
            const accessBtn = e.target.closest('.btn-access-row');
            if (accessBtn) {
                return openModal({
                    id: accessBtn.dataset.id,
                    name: accessBtn.dataset.name,
                    nim_nidn: accessBtn.dataset.nim_nidn,
                    role: accessBtn.dataset.role,
                    levels: accessBtn.dataset.levels,
                    readonly: accessBtn.dataset.readonly === '1',
                    readonlyReason: accessBtn.dataset.readonlyReason || '',
                });
            }
        });

        // Inisialisasi filter saat halaman pertama kali dimuat
        applyFilters();
    </script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script>
        // Lepas skeleton begitu font siap (maks. 2,5 dtk) dan minimal tampil 350 ms
        // sejak halaman mulai dimuat, supaya tidak berkedip terlalu cepat.
        (function() {
            const wrap = document.getElementById('pageWrap');
            if (!wrap) return;
            const MIN_MS = 350;
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

</html>