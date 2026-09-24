{{-- resources/views/hak-akses.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Hak Akses &middot; SIDA</title>
</head>

<body>
    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <img alt="Logo Institut Asia Malang" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
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
                        <button type="button" class="icon-btn" aria-label="Notifikasi">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="dot"></span>
                        </button>
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">{{ $__user->name }}</span>
                                <span class="header-profile-role">{{ $__user->accessLabelFor('hak_akses') }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ url('/logout') }}" id="logoutForm">
                            @csrf
                            <button type="submit" class="icon-btn" id="logoutBtn" title="Keluar" aria-label="Keluar">
                                <span class="material-symbols-outlined">logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                <!-- PAGE TITLE -->
                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>Administrasi</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Hak Akses</span>
                        </div>
                        <h1 class="page-title">Manajemen Hak Akses</h1>
                        <p class="page-subtitle">Atur akses menu per pengguna &middot; Sistem Informasi Data Akademik 2026</p>
                    </div>
                </div>

                @unless($canManage)
                <div class="filter-card" style="margin-bottom: 20px; display:flex; align-items:center; gap:10px; padding: 14px 18px;">
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
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari nama atau NIM/NIDN pengguna..." />
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
                                        'aktif'    => ['label' => 'Aktif', 'class' => 'badge-success'],
                                        'read'     => ['label' => 'Read', 'class' => 'badge-warning'],
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

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const canManage = @json($canManage);

        // ---------------- Custom Dropdown ----------------
        const dropdowns = document.querySelectorAll('[data-dropdown]');
        dropdowns.forEach((dropdown) => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.dropdown-option');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (dropdown.closest('#accessModal') && !canManage) return; // read-only: kunci dropdown modal
                const wasOpen = dropdown.classList.contains('is-open');
                dropdowns.forEach((d) => d.classList.remove('is-open'));
                if (!wasOpen) dropdown.classList.add('is-open');
            });

            options.forEach((option) => {
                option.addEventListener('click', () => {
                    options.forEach((o) => o.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    valueEl.textContent = option.textContent.trim();
                    if (hiddenInput) hiddenInput.value = option.dataset.value;
                    const row = dropdown.closest('.permission-row');
                    if (row) row.dataset.state = option.dataset.value;
                    dropdown.classList.remove('is-open');
                    // Dropdown di filter bar langsung memicu pencarian otomatis
                    if (dropdown.closest('.filter-grid')) applyFilters();
                });
            });
        });
        document.addEventListener('click', () => dropdowns.forEach((d) => d.classList.remove('is-open')));

        // Helper: opsi pertama yang TIDAK disembunyikan (fallback saat sebuah
        // level, mis. "Akses Penuh", disembunyikan untuk role tertentu)
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

        // ----------------------------------------------------------------
        // ATURAN EMAS: role "mahasiswa" & "dosen" tidak boleh diberi
        // "Akses Penuh". Opsi itu disembunyikan total dari semua dropdown
        // level akses di modal (per-menu maupun "Terapkan Cepat"), dan dari
        // legenda, selama modal dibuka untuk pengguna dengan role tersebut.
        // ----------------------------------------------------------------
        const ROLES_WITHOUT_FULL_ACCESS = ['mahasiswa', 'dosen'];
        // Menu yang cuma relevan buat admin/superadmin — harus sinkron dengan
        // HakAkses::ADMIN_ONLY_MENUS di backend.
        const ADMIN_ONLY_MENUS = ['log', 'hak_akses', 'data_master'];

        function applyRoleAccessRules(role) {
            const restricted = ROLES_WITHOUT_FULL_ACCESS.includes((role || '').toLowerCase());
            document.querySelectorAll('#accessModal .dropdown-option[data-value="penuh"]').forEach((opt) => {
                opt.hidden = restricted;
            });
            const fullLevelItem = document.querySelector('#accessModal .access-level-item[data-value="penuh"]');
            if (fullLevelItem) fullLevelItem.hidden = restricted;

            // Sembunyikan total baris Log/Hak Akses/Data Master kalau target-nya
            // mahasiswa/dosen — mereka memang tidak pernah bisa mengaksesnya,
            // jadi opsinya tidak perlu ditampilkan sama sekali di daftar menu.
            ADMIN_ONLY_MENUS.forEach((menuKey) => {
                const row = document.querySelector(`#permissionList .permission-row[data-menu="${menuKey}"]`);
                if (row) row.hidden = restricted;
            });

            return restricted;
        }

        // ================================================================
        // FILTER & PENCARIAN: jalan otomatis (live) di sisi klien, tanpa reload.
        // Mencari di kolom Nama & NIM/NIDN; Peran dan Status difilter dari dropdown.
        // ================================================================
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

        // ================================================================
        // MODAL HAK AKSES: buka/tutup, drag, bulk apply, simpan ke database
        // ================================================================
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

        function initials(name) {
            return (name || '').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '-';
        }

        function openModal(data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            modalCard.style.left = '';
            modalCard.style.top = '';
            modalCard.style.transform = '';

            document.getElementById('form-user_id').value = data.id || '';
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
            accessUserAvatar.textContent = initials(data.name);

            // Terapkan aturan emas: sembunyikan "Akses Penuh" untuk role
            // mahasiswa/dosen, sebelum level dipasang ke tiap dropdown.
            const restricted = applyRoleAccessRules(data.role);

            let levels = {};
            try { levels = JSON.parse(decodeURIComponent(data.levels || '{}')); } catch (_) { levels = {}; }

            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                let level = levels[menu] || 'none';
                // Jaga-jaga: jika data lama menyimpan "penuh" untuk role yang
                // dibatasi, turunkan ke "biasa" supaya tidak macet di opsi tersembunyi.
                if (restricted && level === 'penuh') level = 'biasa';
                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    level
                );
            });

            // Mode lihat-saja: sembunyikan tombol simpan & kunci "Level Akses" (terapkan cepat)
            modalSubmitBtn.hidden = !!data.readonly;
            document.getElementById('accessLevelList').classList.toggle('is-locked', !!data.readonly);
            document.querySelectorAll('.access-level-item').forEach((btn) => {
                btn.disabled = !!data.readonly;
            });

            modalBackdrop.classList.add('is-active');
            modalCard.classList.add('is-active');
            modalCard.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modalBackdrop.classList.remove('is-active');
            modalCard.classList.remove('is-active');
            modalCard.setAttribute('aria-hidden', 'true');
        }

        modalCloseBtn.addEventListener('click', closeModal);
        modalCancelBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalCard.classList.contains('is-active')) closeModal();
        });

        // ---- Drag modal ----
        let dragState = null;
        dragHandle.addEventListener('pointerdown', (e) => {
            if (e.target.closest('.modal-close-btn')) return;
            const rect = modalCard.getBoundingClientRect();
            dragState = { startX: e.clientX, startY: e.clientY, originX: rect.left, originY: rect.top };
            modalCard.style.left = rect.left + 'px';
            modalCard.style.top = rect.top + 'px';
            modalCard.style.transform = 'none';
            modalCard.classList.add('is-dragging');
            dragHandle.setPointerCapture(e.pointerId);
        });
        dragHandle.addEventListener('pointermove', (e) => {
            if (!dragState) return;
            const dx = e.clientX - dragState.startX;
            const dy = e.clientY - dragState.startY;
            const maxLeft = window.innerWidth - modalCard.offsetWidth - 8;
            const maxTop = window.innerHeight - modalCard.offsetHeight - 8;
            modalCard.style.left = Math.min(Math.max(8, dragState.originX + dx), Math.max(8, maxLeft)) + 'px';
            modalCard.style.top = Math.min(Math.max(8, dragState.originY + dy), Math.max(8, maxTop)) + 'px';
        });
        function endDrag(e) {
            if (!dragState) return;
            dragState = null;
            modalCard.classList.remove('is-dragging');
            try { dragHandle.releasePointerCapture(e.pointerId); } catch (_) {}
        }
        dragHandle.addEventListener('pointerup', endDrag);
        dragHandle.addEventListener('pointercancel', endDrag);

        // ---- "Level Akses" gabungan = legenda + terapkan cepat ----
        // Klik salah satu level menerapkannya langsung ke semua menu.
        function applyLevelToAll(value) {
            if (!canManage) return;
            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    value
                );
            });
        }
        document.getElementById('accessLevelList')?.addEventListener('click', (e) => {
            const btn = e.target.closest('.access-level-item');
            if (!btn || btn.disabled || btn.hidden) return;
            applyLevelToAll(btn.dataset.value);
        });

        // ---- Bangun ulang isi baris (dipakai setelah simpan sukses) ----
        const statusBadge = {
            aktif:    { label: 'Aktif', cls: 'badge-success' },
            read:     { label: 'Read', cls: 'badge-warning' },
            nonaktif: { label: 'Nonaktif', cls: 'badge-neutral' },
        };
        function esc(str) { const d = document.createElement('div'); d.textContent = str ?? ''; return d.innerHTML; }
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
                    body: JSON.stringify({ levels }),
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

        // Inisialisasi tampilan filter saat halaman pertama kali dimuat
        applyFilters();
    </script>
</body>

</html>