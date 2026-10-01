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
    <title>Data Master Pengguna &middot; SIDA</title>
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
                @if($__user->canAccessMenu('dashboard'))
                <a href="{{ url('/dashboard') }}" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                @endif

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
                    <button type="button" class="btn-primary" id="btnTambahUser">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Pengguna</span>
                    </button>
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

                <!-- FILTER BAR (aktif: filter & pencarian jalan otomatis tanpa reload) -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Role Pengguna</label>
                            <div class="dropdown" data-dropdown id="dd-filter-role">
                                <input type="hidden" id="filter-role" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Role</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Role</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="superadmin">Superadmin</button>
                                    <button type="button" class="dropdown-option" data-value="admin">Admin</button>
                                    <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="mahasiswa">Mahasiswa</button>
                                </div>
                            </div>
                        </div>
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari ID, nama, NIM, atau NIDN..." />
                            </div>
                        </div>
                        <div class="field field-reset" style="grid-column: -1; justify-self: end; align-self: center;">
                            <button type="button" id="btn-reset-filter" class="btn-rst" title="Reset Filter">
                                <span class="material-symbols-outlined">restart_alt</span>
                            </button>
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
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th class="center">Role</th>
                                    <th class="center">NIM / NIDN</th>
                                    <th class="center">Email</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            @php
                            $isPaginator = method_exists($users, 'total');
                            $serverTotal = $isPaginator ? $users->total() : $users->count();
                            $nextUrl = $isPaginator ? ($users->nextPageUrl() ?? '') : '';
                            @endphp
                            <tbody id="userTableBody" data-total="{{ $serverTotal }}" data-next-url="{{ $nextUrl }}">
                                @forelse($users as $item)
                                @php
                                $initials = collect(explode(' ', $item->name))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                $avatarColor = $colors[$item->id % count($colors)];
                                $roleBadge = [
                                'superadmin' => 'badge-danger',
                                'admin' => 'badge-warning',
                                'dosen' => 'badge-info',
                                'mahasiswa' => 'badge-success',
                                ][$item->role] ?? 'badge-neutral';
                                $roleLabel = ucfirst($item->role);
                                $identifier = $item->role === 'mahasiswa'
                                ? optional($item->mahasiswa)->nim
                                : ($item->role === 'dosen' ? optional($item->dosen)->nidn : null);
                                @endphp
                                <tr data-id="{{ $item->id }}" data-role="{{ $item->role }}">
                                    <td><span class="nim-code">USR-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name">
                                                <span class="name">{{ $item->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
                                    <td class="center">
                                        <span class="plain-text">{{ $identifier ?? '—' }}</span>
                                    </td>
                                    <td class="center">
                                        <span class="plain-text">{{ $item->email ?? '—' }}</span>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ urlencode($item->name) }}"
                                                data-role="{{ $item->role }}"
                                                data-identifier="{{ urlencode($identifier ?? '') }}"
                                                data-email="{{ urlencode($item->email ?? '') }}">
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
                                    <td colspan="6" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data pengguna. Klik "Tambah Pengguna" untuk mulai mengisi.
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
                                <tr><td colspan="6"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerVisibleCount">{{ $users->count() }}</strong>
                            dari <strong id="footerTotalCount">{{ $serverTotal }}</strong> data pengguna
                            <span id="footerLoading" hidden style="margin-left:8px; color: var(--ink-faint);">· memuat sisa data…</span>
                        </div>
                        {{-- Fallback tanpa JS: pagination bawaan Laravel. Dengan JS, semua halaman
                             dimuat otomatis ke tabel sehingga filter & pencarian mencakup semua data. --}}
                        @if($isPaginator && $users->hasPages())
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

    <script src="{{ asset('js/script.js') }}"></script>
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
                    if (dropdown.closest('.filter-grid')) applyFilters();
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
        // FILTER & PENCARIAN (khusus halaman ini)
        // ----------------------------------------------------------------
        // Beda dari halaman lain: filter di sini baca kolom by POSISI
        // (td:nth-child), bukan row.dataset. Karena kolom identifier
        // (NIM/NIDN) dan email tidak ada di data-* pada <tr>.
        // ================================================================
        const filterSearchInput = document.getElementById('filter-search');
        const filterRoleInput = document.getElementById('filter-role');
        const noResultsRow = document.getElementById('noResultsRow');
        const footerVisibleCount = document.getElementById('footerVisibleCount');
        const footerTotalCount = document.getElementById('footerTotalCount');
        const footerLoading = document.getElementById('footerLoading');

        // Total data menurut server; disesuaikan saat tambah/hapus, dan
        // disamakan dengan jumlah baris nyata setelah semua halaman termuat.
        let totalCount = Number(tableBody?.dataset.total) || 0;

        function applyFilters() {
            if (!tableBody) return;
            const term = (filterSearchInput?.value || '').trim().toLowerCase();
            const role = filterRoleInput?.value || 'semua';

            const rows = tableBody.querySelectorAll('tr[data-id]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const idText = (row.querySelector('.nim-code')?.textContent || '').toLowerCase();
                const nameText = (row.querySelector('.student-name .name')?.textContent || '').toLowerCase();
                const identifierText = (row.querySelector('td:nth-child(4) .plain-text')?.textContent || '').toLowerCase();
                const emailText = (row.querySelector('td:nth-child(5) .plain-text')?.textContent || '').toLowerCase();
                const roleValue = (row.dataset.role || '').toLowerCase();

                const matchesSearch = !term ||
                    idText.includes(term) ||
                    nameText.includes(term) ||
                    identifierText.includes(term) ||
                    emailText.includes(term);
                const matchesRole = role === 'semua' || roleValue === role;
                const visible = matchesSearch && matchesRole;

                row.hidden = !visible;
                if (visible) visibleCount++;
            });

            const emptyRow = document.getElementById('emptyRow');
            const hasData = rows.length > 0;
            if (noResultsRow) {
                noResultsRow.hidden = !(hasData && visibleCount === 0);
            }
            if (footerVisibleCount) footerVisibleCount.textContent = visibleCount;
            if (footerTotalCount) footerTotalCount.textContent = Math.max(totalCount, rows.length);
            if (emptyRow) {
                emptyRow.hidden = hasData;
            }
        }

        // ---- Muat SEMUA halaman paginasi ke tabel ----
        // Server mengirim data per halaman (paginate). Supaya filter & pencarian
        // mencakup semua pengguna, halaman berikutnya diambil di belakang layar
        // lalu barisnya ditambahkan ke tabel. (Alternatif lebih ringan: ubah
        // controller dari ->paginate(n) menjadi ->get(); blok ini otomatis
        // tidak jalan karena data-next-url kosong.)
        async function loadRemainingPages() {
            let nextUrl = tableBody?.dataset.nextUrl || '';
            if (!nextUrl) return;
            if (footerLoading) footerLoading.hidden = false;
            const seen = new Set(Array.from(tableBody.querySelectorAll('tr[data-id]')).map((r) => r.dataset.id));

            try {
                while (nextUrl) {
                    // Pakai path relatif supaya aman walau APP_URL beda dengan host yang dipakai
                    const u = new URL(nextUrl, window.location.href);
                    const res = await fetch(u.pathname + u.search, {
                        headers: { 'Accept': 'text/html' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                    const body = doc.getElementById('userTableBody');
                    if (!body) break;

                    body.querySelectorAll('tr[data-id]').forEach((row) => {
                        if (seen.has(row.dataset.id)) return;
                        seen.add(row.dataset.id);
                        tableBody.insertBefore(document.importNode(row, true), noResultsRow);
                    });
                    nextUrl = body.dataset.nextUrl || '';
                    applyFilters();
                }
                // Semua halaman sudah termuat: total = jumlah baris nyata
                totalCount = tableBody.querySelectorAll('tr[data-id]').length;
            } catch (err) {
                console.error('[Data Master] Gagal memuat sisa halaman:', err);
                window.Toast?.show?.({ type: 'error', title: 'Data belum lengkap', message: 'Sebagian data pengguna gagal dimuat. Muat ulang halaman.' });
            } finally {
                if (footerLoading) footerLoading.hidden = true;
                applyFilters();
            }
        }

        // Debounce kecil supaya tidak query ulang di tiap keystroke terlalu agresif
        let searchDebounceTimer = null;
        filterSearchInput?.addEventListener('input', () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(applyFilters, 150);
        });

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            document.querySelectorAll('.filter-grid [data-dropdown]').forEach((d) => SIDA.dropdown.reset(d));
            if (filterSearchInput) filterSearchInput.value = '';
            applyFilters();
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
                inputIdentifier.value = '';
                inputIdentifier.closest('.field').hidden = true;
            }
        }

        document.querySelectorAll('#dd-role .dropdown-option').forEach((option) => {
            option.addEventListener('click', () => syncIdentifierField(option.dataset.value));
        });

        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna';

            document.getElementById('form-name').value = data.name ? decodeURIComponent(data.name) : '';

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
            inputIdentifier.value = data.identifier ? decodeURIComponent(data.identifier) : '';
            document.getElementById('form-email').value = data.email ? decodeURIComponent(data.email) : '';

            document.getElementById('form-password').value = '';
            document.getElementById('form-password').required = mode !== 'edit';

            openModalBase();
        }

        btnTambah?.addEventListener('click', () => openModal('create'));

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

        function buildRowHTML(item) {
            return `
            <tr data-id="${item.id}" data-role="${item.role}">
                <td><span class="nim-code">USR-${String(item.id).padStart(3, '0')}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.id)}">${esc(initials(item.name))}</div>
                        <div class="student-name"><span class="name">${esc(item.name)}</span></div>
                    </div>
                </td>
                <td class="center"><span class="badge ${roleBadgeClass(item.role)}">${esc(roleLabel(item.role))}</span></td>
                <td class="center"><span class="plain-text">${esc(item.identifier || '—')}</span></td>
                <td class="center"><span class="plain-text">${esc(item.email || '—')}</span></td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-name="${encodeURIComponent(item.name)}" data-role="${item.role}"
                            data-identifier="${encodeURIComponent(item.identifier || '')}"
                            data-email="${encodeURIComponent(item.email || '')}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="${item.id}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </td>
            </tr>`.trim();
        }

        const { insertRow, updateRow } = SIDA.table.create(tableBody, buildRowHTML);
        // removeRow di halaman ini BEDA dari SIDA.table.removeRow:
        // versi asli memanggil applyFilters() setelah remove, supaya footer
        // count & noResultsRow ter-update. Jadi kita tulis sendiri.
        function removeRow(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);
            if (!row || row.dataset.removing) return;
            row.dataset.removing = '1';
            row.classList.add('is-removing');
            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                row.remove();
                totalCount = Math.max(0, totalCount - 1);
                applyFilters();
            };
            row.addEventListener('transitionend', finish, { once: true });
            setTimeout(finish, 400); // fallback kalau transitionend tidak terpicu
        }

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

            if (!payload.name) {
                modalError.textContent = 'Nama lengkap wajib diisi.';
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

                if (id) updateRow(result.data);
                else {
                    insertRow(result.data);
                    totalCount++;
                }
                applyFilters();
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
                    removeRow(result.id);
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
                let name = '';
                try {
                    const raw = delBtn.closest('tr')?.querySelector('.btn-edit-row')?.dataset.name || '';
                    name = decodeURIComponent(raw.replace(/\+/g, ' '));
                } catch (_) {}
                handleDelete(delBtn.dataset.id, name);
            }
        });

        // Inisialisasi tampilan filter saat halaman pertama kali dimuat,
        // lalu muat sisa halaman paginasi di belakang layar.
        applyFilters();
        loadRemainingPages();
    </script>
    <script src="{{ asset('js/delete-confirm.js') }}?v={{ @filemtime(public_path('js/delete-confirm.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
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