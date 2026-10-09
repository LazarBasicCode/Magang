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
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/delete-confirm.css') }}?v={{ @filemtime(public_path('css/delete-confirm.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <title>Data Master Pengguna &middot; SIDA</title>
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
                <a href="{{ url('/data-master/users') }}" aria-current="page" class="nav-link is-active">
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
                        <span class="link">Administrasi</span>
                        <span>/</span>
                        <span class="current">Data Master Pengguna</span>
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
                                    <span class="header-profile-role">{{ $__user->accessLabelFor('data_master') }}</span>
                                </div>
                                <span class="material-symbols-outlined header-profile-caret">expand_more</span>
                            </button>

                            <div class="header-profile-panel" id="profilePanel" role="menu" aria-hidden="true">
                                <div class="header-profile-view is-active" id="profileViewMain">
                                    <div class="header-profile-panel-header">
                                        <div class="header-profile-panel-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                        <div>
                                            <span class="header-profile-panel-name">{{ $__user->name }}</span>
                                            <span class="header-profile-panel-role">{{ $__user->accessLabelFor('data_master') }}</span>
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

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Data Master Pengguna</h1>
                        <p class="page-subtitle">Kelola akun pengguna sistem: Superadmin, Admin, Dosen, dan Mahasiswa</p>
                    </div>
                    @php
                        // Menu titik tiga (Unduh Template / Upload / Download / Cetak Laporan): hanya admin & superadmin.
                        // Download butuh akses readonly ke atas; Template & Upload butuh akses penuh.
                        $bulkUser = auth()->user();
                        $bulkLevel = $bulkUser->menuLevel('data_master');
                        $bulkRole = in_array($bulkUser->role, ['admin', 'superadmin'], true);
                        $showBulk = $bulkRole && in_array($bulkLevel, ['readonly', 'penuh'], true);
                        $bulkWrite = $bulkRole && $bulkLevel === 'penuh';
                    @endphp
                    <div class="title-actions">
                        <button type="button" class="btn-primary" id="btnTambahUser">
                            <span class="material-symbols-outlined">add</span>
                            <span>Tambah Pengguna</span>
                        </button>
                        @if($showBulk)
                            @include('partials.bulk-menu', [
                                'bulkLabel'    => 'Data Master',
                                'bulkTemplate' => $bulkWrite ? route('data-master.users.template') : null,
                                'bulkImport'   => $bulkWrite ? route('data-master.users.import') : null,
                                'bulkExport'   => route('data-master.users.export'),
                                'bulkPrint'    => route('cetak.show', 'data-master'),
                            ])
                        @endif
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Pengguna</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Mahasiswa</span>
                            <span class="stat-value">{{ $stats['mahasiswa'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Dosen</span>
                            <span class="stat-value">{{ $stats['dosen'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">co_present</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Admin &amp; Superadmin</span>
                            <span class="stat-value">{{ $stats['admin'] }}</span>
                        </div>
                        <div class="stat-icon danger">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER CARD: tab kelompok pengguna + pencarian cepat -->
                <div class="filter-card">
                    <div class="view-tabs" id="viewTabs" role="tablist" aria-label="Kelompok pengguna">
                        <button type="button" class="view-tab {{ $view === 'mahasiswa' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $view === 'mahasiswa' ? 'true' : 'false' }}" tabindex="{{ $view === 'mahasiswa' ? 0 : -1 }}"
                            data-view="mahasiswa" data-total="{{ $stats['mahasiswa'] }}">Mahasiswa</button>
                        <button type="button" class="view-tab {{ $view === 'dosen' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $view === 'dosen' ? 'true' : 'false' }}" tabindex="{{ $view === 'dosen' ? 0 : -1 }}"
                            data-view="dosen" data-total="{{ $stats['dosen'] }}">Dosen</button>
                        <button type="button" class="view-tab {{ $view === 'admin' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $view === 'admin' ? 'true' : 'false' }}" tabindex="{{ $view === 'admin' ? 0 : -1 }}"
                            data-view="admin" data-total="{{ $stats['admin'] }}">Administrator</button>
                    </div>

                    <div class="filter-grid">
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" value="{{ $term }}" placeholder="Cari ID, nama, NIM, angkatan, status, atau email..." />
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
                            <h2 class="table-card-title">Daftar Pengguna Sistem</h2>
                            <p class="table-card-subtitle">Data akun Superadmin, Admin, Dosen, dan Mahasiswa</p>
                        </div>
                        <!-- <div class="table-card-tools">
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                        </div> -->
                    </div>

                    <div class="table-scroll">
                        <table class="data-table" id="userTable" data-view="{{ $view }}">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th class="center" data-v="admin">Role</th>
                                    {{-- Label kolom ini berganti per tab (NIM / NIDN / Username), diisi JS lewat data-text --}}
                                    <th class="center th-dyn" id="thIdentifier" data-text="NIM" aria-label="NIM"></th>
                                    <th class="center" data-v="mahasiswa">Angkatan</th>
                                    <th class="center" data-v="mahasiswa dosen">Status</th>
                                    <th class="center">Email</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                @include('partials.user-rows', ['users' => $users])
                                <tr id="noResultsRow" {{ $users->total() === 0 ? '' : 'hidden' }}>
                                    <td colspan="8" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        {{ $users->total() === 0 ? ($term !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data pada kelompok ini.') : '' }}
                                    </td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                <tr><td colspan="8"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">{{ $users->firstItem() ?? 0 }}&ndash;{{ $users->lastItem() ?? 0 }}</strong>
                            dari <strong id="footerTotalCount">{{ $users->total() }}</strong>
                            <span id="footerNoun">{{ ['mahasiswa' => 'mahasiswa', 'dosen' => 'dosen', 'admin' => 'administrator'][$view] }}</span>
                        </div>
                        {{-- Tombol halaman digambar public/js/pager.js (tersembunyi bila data <= 50). Tanpa JS: pagination bawaan Laravel. --}}
                        <nav class="pager" id="pager" aria-label="Navigasi halaman" hidden></nav>
                        @if($users->hasPages())
                        <noscript>{{ $users->links() }}</noscript>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit Pengguna ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="userModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Pengguna</h3>
                <p class="modal-subtitle">Isi detail akun pengguna sistem</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="userForm" class="modal-body">
            <input type="hidden" id="form-id" value="">

            <div class="field">
                <label class="field-label" for="form-name">Nama Lengkap</label>
                <div class="field-control">
                    <input id="form-name" type="text" placeholder="Contoh: Budi Santoso" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Role</label>
                    <div class="dropdown" data-dropdown id="dd-role">
                        <input type="hidden" id="form-role" value="mahasiswa" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Mahasiswa</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option" data-value="superadmin" hidden>Superadmin</button>
                            <button type="button" class="dropdown-option" data-value="admin">Admin</button>
                            <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                            <button type="button" class="dropdown-option is-selected" data-value="mahasiswa">Mahasiswa</button>
                        </div>
                    </div>
                </div>
                <div class="field" id="field-identifier">
                    <label class="field-label" for="form-identifier" id="label-identifier">NIM</label>
                    <div class="field-control">
                        <input id="form-identifier" type="text" placeholder="Contoh: 222011005">
                    </div>
                </div>
            </div>

            {{-- Angkatan & Status: tampil sesuai role (mahasiswa: angkatan + status; dosen: status; admin: disembunyikan) --}}
            <div class="field-row" id="row-profile">
                <div class="field" id="field-angkatan">
                    <label class="field-label" for="form-angkatan">Angkatan</label>
                    <div class="field-control">
                        <input id="form-angkatan" type="number" inputmode="numeric" min="1990" max="{{ now()->year + 1 }}" step="1" placeholder="Contoh: {{ now()->year - 3 }}">
                    </div>
                </div>
                <div class="field" id="field-status-mahasiswa">
                    <label class="field-label">Status Mahasiswa</label>
                    <div class="dropdown" data-dropdown id="dd-status-mahasiswa">
                        <input type="hidden" id="form-status-mahasiswa" value="aktif" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">{{ \App\Models\Mahasiswa::STATUS['aktif'] }}</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            @foreach(\App\Models\Mahasiswa::STATUS as $value => $label)
                            <button type="button" class="dropdown-option {{ $value === 'aktif' ? 'is-selected' : '' }}" data-value="{{ $value }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="field" id="field-status-dosen">
                    <label class="field-label">Status Dosen</label>
                    <div class="dropdown" data-dropdown id="dd-status-dosen">
                        <input type="hidden" id="form-status-dosen" value="aktif" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">{{ \App\Models\Dosen::STATUS['aktif'] }}</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            @foreach(\App\Models\Dosen::STATUS as $value => $label)
                            <button type="button" class="dropdown-option {{ $value === 'aktif' ? 'is-selected' : '' }}" data-value="{{ $value }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-email">Email</label>
                <div class="field-control">
                    <input id="form-email" type="email" placeholder="Contoh: budi@kampus.ac.id">
                </div>
                <p class="field-hint">Opsional. Dipakai untuk mengirim tautan reset password ke pengguna ini.</p>
            </div>

            <div class="field">
                <label class="field-label" for="form-password">Password</label>
                <div class="field-control">
                    <input id="form-password" type="password" placeholder="Kosongkan jika tidak diubah (saat edit)">
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
            'bulkLabel'    => 'Data Master',
            'bulkImport'   => route('data-master.users.import'),
            'bulkTemplate' => route('data-master.users.template'),
        ])
    @endif

    <script id="dmInit" type="application/json">{!! json_encode(['view' => $view, 'q' => $term, 'page' => $users->currentPage(), 'last' => max(1, $users->lastPage())], JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script>
        // ================================================================
        // Bagian ini KHUSUS halaman Data Master Pengguna:
        // endpoint API, bentuk baris tabel, filter (baca kolom by posisi),
        // syncIdentifierField (NIM/NIDN), dan dropdown portal di modal.
        // Semua yang generik (dropdown dasar, sidebar, dark mode,
        // notifikasi, modal buka/tutup/drag, profile dropdown) sudah
        // ditangani public/js/script.js via SIDA.
        //
        // CATATAN: Dropdown Portal & syncIdentifierField TIDAK ada di
        // SIDA.*, jadi tetap ditulis lengkap di sini (khusus halaman ini).
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const { esc, initials, avatarColor } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('userTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('userModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('userForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahUser');
        const dragHandle = document.getElementById('modalDragHandle');
        const labelIdentifier = document.getElementById('label-identifier');
        const inputIdentifier = document.getElementById('form-identifier');
        // Angkatan & status (hanya relevan untuk mahasiswa/dosen)
        const rowProfile = document.getElementById('row-profile');
        const fieldAngkatan = document.getElementById('field-angkatan');
        const fieldStatusMhs = document.getElementById('field-status-mahasiswa');
        const fieldStatusDosen = document.getElementById('field-status-dosen');
        const ddStatusMhs = document.getElementById('dd-status-mahasiswa');
        const ddStatusDosen = document.getElementById('dd-status-dosen');
        const inputAngkatan = document.getElementById('form-angkatan');
        const inputStatusMhs = document.getElementById('form-status-mahasiswa');
        const inputStatusDosen = document.getElementById('form-status-dosen');

        // ================================================================
        // DROPDOWN PORTAL (khusus halaman ini)
        // ----------------------------------------------------------------
        // Kenapa perlu: panel dropdown di dalam modal akan terpotong /
        // ikut ter-scroll oleh .modal-body (overflow-y: auto). Solusinya:
        // saat dropdown di dalam modal dibuka, panel-nya "dipindah" ke
        // <body> (fixed positioning), lalu dikembalikan ke induknya saat
        // ditutup. Khusus halaman ini.
        // ================================================================
        const dropdowns = document.querySelectorAll('[data-dropdown]');
        const dropdownPanelHome = new Map(); // dropdown -> { panel, parent }
        dropdowns.forEach((dropdown) => {
            const panel = dropdown.querySelector('.dropdown-panel');
            if (panel) dropdownPanelHome.set(dropdown, {
                panel,
                parent: dropdown
            });
        });

        function isDropdownInModal(dropdown) {
            return !!dropdown.closest('.modal-card');
        }

        function openDropdownPortal(dropdown) {
            const entry = dropdownPanelHome.get(dropdown);
            const trigger = dropdown.querySelector('.dropdown-trigger');
            if (!entry || !trigger || !isDropdownInModal(dropdown)) return;
            const rect = trigger.getBoundingClientRect();
            const panel = entry.panel;
            panel.classList.add('dropdown-panel--portal');
            panel.style.display = 'flex';
            panel.style.position = 'fixed';
            panel.style.left = rect.left + 'px';
            panel.style.top = (rect.bottom + 6) + 'px';
            panel.style.width = rect.width + 'px';
            panel.style.right = 'auto';
            panel.style.zIndex = 9999;
            document.body.appendChild(panel);
        }

        function closeDropdownPortal(dropdown) {
            const entry = dropdownPanelHome.get(dropdown);
            if (!entry) return;
            const panel = entry.panel;
            if (!panel.classList.contains('dropdown-panel--portal')) return;
            panel.classList.remove('dropdown-panel--portal');
            panel.style.display = '';
            panel.style.position = '';
            panel.style.left = '';
            panel.style.top = '';
            panel.style.width = '';
            panel.style.right = '';
            panel.style.zIndex = '';
            entry.parent.appendChild(panel);
        }

        function closeAllDropdowns() {
            dropdowns.forEach((d) => {
                d.classList.remove('is-open');
                closeDropdownPortal(d);
            });
        }

        // Listener dropdown (khusus halaman ini karena ada portal + callback filter)
        dropdowns.forEach((dropdown) => {
            // Tandai supaya SIDA.dropdown.init() (script.js) tidak memasang
            // listener kedua di trigger yang sama (penyebab dropdown buka-lalu-tutup).
            dropdown.dataset.bound = '1';
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.dropdown-option');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const wasOpen = dropdown.classList.contains('is-open');
                closeAllDropdowns();
                if (!wasOpen) {
                    dropdown.classList.add('is-open');
                    openDropdownPortal(dropdown);
                }
            });

            options.forEach((option) => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    options.forEach((o) => o.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    valueEl.textContent = option.textContent.trim();
                    if (hiddenInput) hiddenInput.value = option.dataset.value;
                    dropdown.classList.remove('is-open');
                    closeDropdownPortal(dropdown);
                    // Dropdown filter (Role Pengguna) langsung memicu pencarian otomatis
                    if (dropdown.closest('.filter-grid')) goPage(1);
                });
            });
        });

        document.addEventListener('click', () => {
            closeAllDropdowns();
        });
        // Esc: tutup dropdown (termasuk panel portal) sebelum modal ditutup SIDA.modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeAllDropdowns();
        }, true);
        // Tutup dropdown yang lagi terbuka kalau halaman di-scroll, supaya
        // panel yang di-portal tidak "nyangkut" di posisi lama.
        window.addEventListener('scroll', () => {
            dropdowns.forEach((d) => {
                if (d.classList.contains('is-open')) {
                    d.classList.remove('is-open');
                    closeDropdownPortal(d);
                }
            });
        }, true);

        // ================================================================
        // TAB, PENCARIAN & PAGINASI (khusus halaman ini) — diproses di SERVER
        // ----------------------------------------------------------------
        // Tabel hanya memuat 50 baris per halaman. Ganti tab / ketik pencarian /
        // klik tombol halaman -> GET /data-master/users?view=&q=&page= (JSON),
        // lalu isi tabel diganti. Tombol halaman digambar public/js/pager.js.
        // ================================================================
        const filterSearchInput = document.getElementById('filter-search');
        const noResultsRow = document.getElementById('noResultsRow');
        const footerRange = document.getElementById('footerRange');
        const footerTotalCount = document.getElementById('footerTotalCount');
        const footerNoun = document.getElementById('footerNoun');
        const pagerEl = document.getElementById('pager');
        const userTable = document.getElementById('userTable');
        const thIdentifier = document.getElementById('thIdentifier');
        const viewTabs = Array.from(document.querySelectorAll('.view-tab'));
        const DEFAULT_VIEW = 'mahasiswa';
        const VIEW_META = {
            mahasiswa: { identifier: 'NIM', noun: 'mahasiswa', createRole: 'mahasiswa', search: 'Cari ID, nama, NIM, angkatan, status, atau email...' },
            dosen: { identifier: 'NIDN', noun: 'dosen', createRole: 'dosen', search: 'Cari ID, nama, NIDN, status, atau email...' },
            admin: { identifier: 'Username', noun: 'administrator', createRole: 'admin', search: 'Cari ID, nama, role, username, atau email...' },
        };
        const init = JSON.parse(document.getElementById('dmInit').textContent);
        let activeView = VIEW_META[init.view] ? init.view : DEFAULT_VIEW;
        let currentPage = init.page || 1;
        let lastPage = init.last || 1;
        let requestSeq = 0;

        // superadmin & admin -> kelompok 'admin'
        const viewOf = (role) => (role === 'mahasiswa' || role === 'dosen' ? role : 'admin');

        const pager = Pager.create(pagerEl, { onGo: (n) => goPage(n) });
        pager.render(currentPage, lastPage);

        // Label kolom, placeholder & label kartu HP mengikuti tab aktif
        function syncViewUI() {
            const meta = VIEW_META[activeView];
            userTable.dataset.view = activeView;
            thIdentifier.dataset.text = meta.identifier;
            thIdentifier.setAttribute('aria-label', meta.identifier);
            if (filterSearchInput) filterSearchInput.placeholder = meta.search;
            if (footerNoun) footerNoun.textContent = meta.noun;
            viewTabs.forEach((t) => {
                const on = t.dataset.view === activeView;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', String(on));
                t.tabIndex = on ? 0 : -1;
            });
            tableBody.querySelectorAll('tr[data-id] .col-identifier').forEach((c) => { c.dataset.label = meta.identifier; });
        }

        function updateStats(stats) {
            if (!stats) return;
            const vals = document.querySelectorAll('.stat-grid .stat-value');
            [stats.total, stats.mahasiswa, stats.dosen, stats.admin].forEach((n, i) => { if (vals[i]) vals[i].textContent = n; });
        }

        // Ambil satu halaman dari server dan ganti isi tabel.
        async function loadPage(page = currentPage) {
            const seq = ++requestSeq;
            const term = (filterSearchInput?.value || '').trim();
            const params = new URLSearchParams({ view: activeView, page: String(page) });
            if (term) params.set('q', term);

            tableBody.classList.add('is-fetching');
            try {
                const res = await fetch('/data-master/users?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (seq !== requestSeq) return; // ada permintaan yang lebih baru, abaikan yang ini

                tableBody.querySelectorAll('tr[data-id]').forEach((r) => r.remove());
                tableBody.insertAdjacentHTML('afterbegin', data.html);

                currentPage = data.meta.page;
                lastPage = data.meta.last;
                const empty = data.meta.total === 0;
                noResultsRow.hidden = !empty;
                if (empty) {
                    noResultsRow.firstElementChild.textContent = term
                        ? 'Tidak ada ' + VIEW_META[activeView].noun + ' yang cocok dengan pencarian.'
                        : 'Belum ada data ' + VIEW_META[activeView].noun + '.';
                }
                footerRange.textContent = data.meta.from + '–' + data.meta.to;
                footerTotalCount.textContent = data.meta.total;
                pager.render(currentPage, lastPage);
                updateStats(data.stats);
                syncViewUI();

                // Simpan posisi di URL supaya refresh / tombol back tetap di tempat yang sama
                const qs = new URLSearchParams();
                if (activeView !== DEFAULT_VIEW) qs.set('view', activeView);
                if (term) qs.set('q', term);
                if (currentPage > 1) qs.set('page', String(currentPage));
                history.replaceState(null, '', location.pathname + (qs.toString() ? '?' + qs : ''));
            } catch (err) {
                if (seq !== requestSeq) return;
                console.error('[Data Master] Gagal memuat halaman:', err);
                window.Toast?.show?.({ type: 'error', title: 'Gagal memuat data', message: 'Tidak bisa memuat halaman ini. Coba lagi.' });
            } finally {
                if (seq === requestSeq) tableBody.classList.remove('is-fetching');
            }
        }

        function goPage(n) {
            loadPage(n);
            // Setelah pindah halaman, tampilkan lagi bagian atas tabel
            document.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function setView(view) {
            activeView = VIEW_META[view] ? view : DEFAULT_VIEW;
            syncViewUI();
            loadPage(1);
        }

        viewTabs.forEach((tab, i) => {
            tab.addEventListener('click', () => { if (tab.dataset.view !== activeView) setView(tab.dataset.view); });
            // Panah kiri/kanan pindah tab
            tab.addEventListener('keydown', (e) => {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                e.preventDefault();
                const step = e.key === 'ArrowRight' ? 1 : -1;
                const next = viewTabs[(i + step + viewTabs.length) % viewTabs.length];
                next.focus();
                setView(next.dataset.view);
            });
        });

        // Debounce supaya tidak query server di tiap ketikan
        let searchDebounceTimer = null;
        filterSearchInput?.addEventListener('input', () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => loadPage(1), 300);
        });

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            if (filterSearchInput) filterSearchInput.value = '';
            setView(DEFAULT_VIEW);
        });

        // ================================================================
        // MODAL (khusus halaman ini: syncIdentifierField + dropdown portal)
        // ================================================================
        const { open: openModalBase, close: closeModalBase } = SIDA.modal.attach({
            backdrop: modalBackdrop,
            card: modalCard,
            closeBtn: modalCloseBtn,
            cancelBtn: modalCancelBtn,
            dragHandle: dragHandle,
        });

        // Wrapper closeModal: tutup dropdown portal dulu, baru tutup modal
        function closeModal() {
            closeAllDropdowns();
            closeModalBase();
        }

        // Sesuaikan label & placeholder NIM/NIDN berdasarkan role yang dipilih
        function syncIdentifierField(role) {
            if (role === 'mahasiswa') {
                labelIdentifier.textContent = 'NIM';
                inputIdentifier.placeholder = 'Contoh: 222011005';
                inputIdentifier.closest('.field').hidden = false;
            } else if (role === 'dosen') {
                labelIdentifier.textContent = 'NIDN';
                inputIdentifier.placeholder = 'Contoh: 0712048901';
                inputIdentifier.closest('.field').hidden = false;
            } else {
                // Admin/superadmin juga WAJIB punya username untuk login
                // (form login mencocokkan kolom nim_nidn).
                labelIdentifier.textContent = 'Username';
                inputIdentifier.placeholder = 'Contoh: admin.kemahasiswaan';
                inputIdentifier.closest('.field').hidden = false;
            }
        }

        // Tampilkan/sembunyikan angkatan & status sesuai role.
        // Pakai hidden + style.display supaya aman walau CSS .field/.field-row
        // mengatur display sendiri.
        function setVisible(el, visible) {
            el.hidden = !visible;
            el.style.display = visible ? '' : 'none';
        }

        function syncProfileFields(role) {
            const isMhs = role === 'mahasiswa';
            const isDosen = role === 'dosen';
            setVisible(rowProfile, isMhs || isDosen);
            setVisible(fieldAngkatan, isMhs);
            setVisible(fieldStatusMhs, isMhs);
            setVisible(fieldStatusDosen, isDosen);
        }

        // Nilai status yang sedang dipilih untuk role tertentu ('' kalau role tidak punya status).
        function currentStatus(role) {
            if (role === 'mahasiswa') return inputStatusMhs.value;
            if (role === 'dosen') return inputStatusDosen.value;
            return '';
        }

        document.querySelectorAll('#dd-role .dropdown-option').forEach((option) => {
            option.addEventListener('click', () => {
                syncIdentifierField(option.dataset.value);
                syncProfileFields(option.dataset.value);
            });
        });

        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna';

            document.getElementById('form-name').value = data.name || '';

            const role = data.role || 'mahasiswa';
            // Superadmin: opsi role ditampilkan & dikunci supaya role-nya tidak
            // terkirim kosong / tidak sengaja turun jadi Admin saat disimpan.
            const ddRole = document.getElementById('dd-role');
            const isSuper = role === 'superadmin';
            const superOpt = ddRole.querySelector('.dropdown-option[data-value="superadmin"]');
            if (superOpt) superOpt.hidden = !isSuper;
            const roleTrigger = ddRole.querySelector('.dropdown-trigger');
            roleTrigger.disabled = isSuper;
            roleTrigger.style.opacity = isSuper ? '.6' : '';
            roleTrigger.style.cursor = isSuper ? 'not-allowed' : '';
            SIDA.dropdown.select(ddRole, role);
            syncIdentifierField(role);
            syncProfileFields(role);
            inputIdentifier.value = data.identifier || '';
            inputAngkatan.value = data.angkatan || '';
            // Status: edit -> nilai tersimpan; tambah / role berbeda -> default 'aktif'
            SIDA.dropdown.select(ddStatusMhs, role === 'mahasiswa' && data.status ? data.status : 'aktif');
            SIDA.dropdown.select(ddStatusDosen, role === 'dosen' && data.status ? data.status : 'aktif');
            document.getElementById('form-email').value = data.email || '';

            document.getElementById('form-password').value = '';
            document.getElementById('form-password').required = mode !== 'edit';

            openModalBase();
        }

        // Default role di modal mengikuti tab yang sedang dibuka
        btnTambah?.addEventListener('click', () => openModal('create', { role: VIEW_META[activeView].createRole }));

        // Drag: tambahkan closeAllDropdowns sebelum drag mulai (khusus halaman ini)
        dragHandle.addEventListener('pointerdown', (e) => {
            if (e.target.closest('.modal-close-btn')) return;
            closeAllDropdowns();
        }, true);

        // ---- Bangun/ganti/hapus baris tabel ----
        function roleBadgeClass(role) {
            return {
                superadmin: 'badge-danger',
                admin: 'badge-warning',
                dosen: 'badge-info',
                mahasiswa: 'badge-success'
            } [role] || 'badge-neutral';
        }

        function roleLabel(role) {
            return role ? role.charAt(0).toUpperCase() + role.slice(1) : '-';
        }

        // Baris tabel dirender SERVER (partials/user-rows.blade.php). Setelah tambah / ubah / hapus,
        // halaman aktif cukup dimuat ulang lewat loadPage() sehingga tabel, total, dan tombol halaman selalu sinkron.

        // ---- Submit form (create / update) — endpoint khusus halaman ini ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const payload = {
                name: document.getElementById('form-name').value,
                role: document.getElementById('form-role').value,
                identifier: document.getElementById('form-identifier').value,
                email: document.getElementById('form-email').value,
                password: document.getElementById('form-password').value,
            };
            // Angkatan & status hanya dikirim untuk role yang memilikinya.
            payload.angkatan = payload.role === 'mahasiswa' ? inputAngkatan.value.trim() : null;
            payload.status = currentStatus(payload.role) || null;

            if (!payload.name) {
                modalError.textContent = 'Nama lengkap wajib diisi.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }
            if (!payload.identifier.trim()) {
                modalError.textContent = 'NIM / NIDN / Username wajib diisi karena dipakai untuk login.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }
            payload.identifier = payload.identifier.trim();
            if (payload.role === 'mahasiswa' && !/^\d{4}$/.test(payload.angkatan || '')) {
                modalError.textContent = 'Angkatan wajib diisi dengan tahun 4 digit (contoh: 2022).';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }
            if (!id && !payload.password) {
                modalError.textContent = 'Password wajib diisi untuk pengguna baru.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/data-master/users/${id}` : '/data-master/users';
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
                    Toast.show({ type: 'error', title: 'Gagal menyimpan', message: firstError });
                    return;
                }

                const newView = viewOf(result.data.role);
                if (newView !== activeView) {
                    // Pindah ke tab tempat data itu berada, mulai dari halaman 1 (data baru ada di paling atas)
                    activeView = newView;
                    syncViewUI();
                    loadPage(1);
                } else {
                    loadPage(id ? currentPage : 1);
                }
                closeModal();
                Toast.show({
                    type: 'success',
                    message: id ? 'Data pengguna berhasil diperbarui.' : 'Pengguna baru berhasil ditambahkan.',
                });
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
                Toast.show({ type: 'error', title: 'Gagal menyimpan', message: 'Tidak bisa terhubung ke server. Coba lagi.' });
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Delete — endpoint khusus halaman ini ----
        async function handleDelete(id, name) {
            const ok = await DeleteConfirm.ask({ name, entity: 'user' });
            if (!ok) return;
            try {
                const res = await fetch(`/data-master/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    const gone = tableBody.querySelector(`tr[data-id="${result.id}"]`);
                    if (gone) gone.classList.add('is-removing');
                    setTimeout(() => loadPage(currentPage), 250);
                    Toast.show({ type: 'success', message: 'Data pengguna berhasil dihapus.' });
                } else {
                    Toast.show({ type: 'error', title: 'Gagal menghapus', message: result.message || 'Gagal menghapus data.' });
                }
            } catch (err) {
                Toast.show({ type: 'error', title: 'Gagal menghapus', message: 'Tidak bisa terhubung ke server.' });
            }
        }

        // ---- Event delegation: tombol Edit & Hapus di tiap baris (termasuk baris baru) ----
        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-row');
            if (editBtn) return openModal('edit', editBtn.dataset);
            const delBtn = e.target.closest('.btn-delete-row');
            if (delBtn) {
                const name = delBtn.closest('tr')?.querySelector('.btn-edit-row')?.dataset.name || '';
                handleDelete(delBtn.dataset.id, name);
            }
        });

        // Inisialisasi tampilan tab/kolom saat halaman pertama kali dimuat.
        syncViewUI();
    </script>
    <script src="{{ asset('js/delete-confirm.js') }}?v={{ @filemtime(public_path('js/delete-confirm.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @if($showBulk)
        <script src="{{ asset('js/bulk-import.js') }}?v={{ @filemtime(public_path('js/bulk-import.js')) }}"></script>
    @endif
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