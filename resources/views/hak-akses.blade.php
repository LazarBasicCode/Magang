{{-- resources/views/hak_akses.blade.php --}}
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
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ url('/kemahasiswaan') }}" class="nav-link">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>

                <div class="nav-heading">LPPM</div>
                <a href="{{ url('/lppm/mahasiswa') }}" class="nav-link">
                    <span class="material-symbols-outlined">person</span>
                    <span>Mahasiswa</span>
                </a>
                <a href="{{ url('/lppm/dosen') }}" class="nav-link">
                    <span class="material-symbols-outlined">co_present</span>
                    <span>Dosen</span>
                </a>
                <a href="{{ url('/lppm/rekognisi') }}" class="nav-link">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="nav-heading">Kemitraan</div>
                <a href="{{ url('/kerja-sama') }}" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>

                <div class="nav-heading">Administrasi</div>
                <a href="{{ url('/data-master/users') }}" class="nav-link">
                    <span class="material-symbols-outlined">manage_accounts</span>
                    <span>Data Master</span>
                </a>
                <a href="{{ url('/hak-akses') }}" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">shield_person</span>
                    <span>Hak Akses</span>
                </a>
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
                                <span class="header-profile-name">Admin Sistem</span>
                                <span class="header-profile-role">Institut Asia Malang</span>
                            </div>
                        </div>
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

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Pengguna</span>
                            <span class="stat-value">24</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Akses Penuh</span>
                            <span class="stat-value">8</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">verified_user</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Akses Biasa</span>
                            <span class="stat-value">10</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">how_to_reg</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Read Only</span>
                            <span class="stat-value">6</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">visibility</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
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
                                    <button type="button" class="dropdown-option" data-value="nonaktif">Nonaktif</button>
                                </div>
                            </div>
                        </div>
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari nama atau email pengguna..." />
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="button" id="btn-reset-filter" class="btn-ghost">
                            <span class="material-symbols-outlined">restart_alt</span>
                            <span>Reset</span>
                        </button>
                        <button type="button" id="btn-apply-filter" class="btn-apply">
                            <span class="material-symbols-outlined">filter_alt</span>
                            <span>Terapkan Filter</span>
                        </button>
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
                                    <th>Email</th>
                                    <th class="center">Peran</th>
                                    <th class="center">Status</th>
                                    <th class="center">Ringkasan Akses</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="hakAksesTableBody">
                                {{-- Baris statis contoh (UI only) --}}
                                <tr data-id="1">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">AS</div>
                                            <div class="student-name"><span class="name">Admin Sistem</span></div>
                                        </div>
                                    </td>
                                    <td><span class="plain-text">admin@asia.ac.id</span></td>
                                    <td class="center"><span class="plain-text">admin</span></td>
                                    <td class="center"><span class="badge badge-success">Aktif</span></td>
                                    <td class="center">
                                        <div class="access-summary">
                                            <span class="access-chip penuh" title="Akses Penuh"><span class="access-dot penuh"></span> 8</span>
                                            <span class="access-chip biasa is-zero" title="Akses Biasa"><span class="access-dot biasa"></span> 0</span>
                                            <span class="access-chip readonly is-zero" title="Read Only"><span class="access-dot readonly"></span> 0</span>
                                            <span class="access-chip none is-zero" title="Tanpa Akses"><span class="access-dot none"></span> 0</span>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Atur Hak Akses" class="row-action-btn btn-access-row"
                                                data-id="1"
                                                data-name="Admin Sistem"
                                                data-email="admin@asia.ac.id"
                                                data-role="admin">
                                                <span class="material-symbols-outlined">shield_person</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-id="2">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-info">BS</div>
                                            <div class="student-name"><span class="name">Budi Santoso</span></div>
                                        </div>
                                    </td>
                                    <td><span class="plain-text">budi@asia.ac.id</span></td>
                                    <td class="center"><span class="plain-text">dosen</span></td>
                                    <td class="center"><span class="badge badge-success">Aktif</span></td>
                                    <td class="center">
                                        <div class="access-summary">
                                            <span class="access-chip penuh is-zero" title="Akses Penuh"><span class="access-dot penuh"></span> 0</span>
                                            <span class="access-chip biasa" title="Akses Biasa"><span class="access-dot biasa"></span> 3</span>
                                            <span class="access-chip readonly" title="Read Only"><span class="access-dot readonly"></span> 2</span>
                                            <span class="access-chip none" title="Tanpa Akses"><span class="access-dot none"></span> 3</span>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Atur Hak Akses" class="row-action-btn btn-access-row"
                                                data-id="2"
                                                data-name="Budi Santoso"
                                                data-email="budi@asia.ac.id"
                                                data-role="dosen">
                                                <span class="material-symbols-outlined">shield_person</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-id="3">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-warning">CW</div>
                                            <div class="student-name"><span class="name">Citra Wulandari</span></div>
                                        </div>
                                    </td>
                                    <td><span class="plain-text">citra@asia.ac.id</span></td>
                                    <td class="center"><span class="plain-text">operator</span></td>
                                    <td class="center"><span class="badge badge-success">Aktif</span></td>
                                    <td class="center">
                                        <div class="access-summary">
                                            <span class="access-chip penuh is-zero" title="Akses Penuh"><span class="access-dot penuh"></span> 0</span>
                                            <span class="access-chip biasa is-zero" title="Akses Biasa"><span class="access-dot biasa"></span> 0</span>
                                            <span class="access-chip readonly" title="Read Only"><span class="access-dot readonly"></span> 5</span>
                                            <span class="access-chip none" title="Tanpa Akses"><span class="access-dot none"></span> 3</span>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Atur Hak Akses" class="row-action-btn btn-access-row"
                                                data-id="3"
                                                data-name="Citra Wulandari"
                                                data-email="citra@asia.ac.id"
                                                data-role="operator">
                                                <span class="material-symbols-outlined">shield_person</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-id="4">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-success">DP</div>
                                            <div class="student-name"><span class="name">Dewi Puspita</span></div>
                                        </div>
                                    </td>
                                    <td><span class="plain-text">dewi@asia.ac.id</span></td>
                                    <td class="center"><span class="plain-text">mahasiswa</span></td>
                                    <td class="center"><span class="badge badge-neutral">Nonaktif</span></td>
                                    <td class="center">
                                        <div class="access-summary">
                                            <span class="access-chip penuh is-zero" title="Akses Penuh"><span class="access-dot penuh"></span> 0</span>
                                            <span class="access-chip biasa is-zero" title="Akses Biasa"><span class="access-dot biasa"></span> 0</span>
                                            <span class="access-chip readonly" title="Read Only"><span class="access-dot readonly"></span> 1</span>
                                            <span class="access-chip none" title="Tanpa Akses"><span class="access-dot none"></span> 7</span>
                                        </div>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Atur Hak Akses" class="row-action-btn btn-access-row"
                                                data-id="4"
                                                data-name="Dewi Puspita"
                                                data-email="dewi@asia.ac.id"
                                                data-role="mahasiswa">
                                                <span class="material-symbols-outlined">shield_person</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>1-4</strong> dari <strong>24</strong> pengguna
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
                </div>
            </div>

            <!-- Sidebar kiri -->
            <aside class="access-side">
                <div class="access-divider">Level Akses</div>
                <div class="access-legend">
                    <div class="access-legend-item"><span class="access-dot penuh"></span> Akses Penuh</div>
                    <div class="access-legend-item"><span class="access-dot biasa"></span> Akses Biasa</div>
                    <div class="access-legend-item"><span class="access-dot readonly"></span> Read Only</div>
                    <div class="access-legend-item"><span class="access-dot none"></span> Tidak Diberi Akses</div>
                </div>

                <div class="access-divider">Terapkan Cepat</div>
                <div class="access-bulk-row">
                    <div class="dropdown" data-dropdown id="dd-bulk">
                        <input type="hidden" id="bulk-value" value="" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Pilih level...</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option" data-value="penuh"><span class="access-dot penuh"></span> Akses Penuh</button>
                            <button type="button" class="dropdown-option" data-value="biasa"><span class="access-dot biasa"></span> Akses Biasa</button>
                            <button type="button" class="dropdown-option" data-value="readonly"><span class="access-dot readonly"></span> Read Only</button>
                            <button type="button" class="dropdown-option" data-value="none"><span class="access-dot none"></span> Tidak Diberi Akses</button>
                        </div>
                    </div>
                    <button type="button" class="btn-ghost" id="btnBulkApply">
                        <span class="material-symbols-outlined">done_all</span>
                        <span>Terapkan ke Semua</span>
                    </button>
                </div>
            </aside>

            <!-- Area kanan: daftar menu -->
            <section class="access-main">
                <div class="access-divider">Daftar Menu</div>
                <div class="permission-list" id="permissionList">
                    @php
                    $menus = [
                    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                    ['key' => 'kemahasiswaan', 'label' => 'Kemahasiswaan', 'icon' => 'school'],
                    ['key' => 'lppm_mahasiswa', 'label' => 'LPPM Mahasiswa', 'icon' => 'person'],
                    ['key' => 'lppm_dosen', 'label' => 'LPPM Dosen', 'icon' => 'co_present'],
                    ['key' => 'rekognisi', 'label' => 'Rekognisi', 'icon' => 'workspace_premium'],
                    ['key' => 'kerja_sama', 'label' => 'Kerja Sama', 'icon' => 'handshake'],
                    ['key' => 'data_master', 'label' => 'Data Master', 'icon' => 'manage_accounts'],
                    ['key' => 'hak_akses', 'label' => 'Hak Akses', 'icon' => 'shield_person'],
                    ];
                    @endphp

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

        // ---------------- Custom Dropdown ----------------
        const dropdowns = document.querySelectorAll('[data-dropdown]');
        dropdowns.forEach((dropdown) => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.dropdown-option');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
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
                });
            });
        });
        document.addEventListener('click', () => dropdowns.forEach((d) => d.classList.remove('is-open')));

        function selectDropdownValue(dropdownEl, value) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll('.dropdown-option');
            const valueEl = dropdownEl.querySelector('.dropdown-value');
            const hiddenInput = dropdownEl.querySelector('input[type="hidden"]');
            options.forEach((o) => {
                const isMatch = o.dataset.value === String(value);
                o.classList.toggle('is-selected', isMatch);
                if (isMatch && valueEl) valueEl.textContent = o.textContent.trim();
            });
            if (hiddenInput) hiddenInput.value = value;
            const row = dropdownEl.closest('.permission-row');
            if (row) row.dataset.state = value;
        }

        function resetDropdown(dropdown) {
            if (!dropdown) return;
            const options = dropdown.querySelectorAll('.dropdown-option');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            options.forEach((o, i) => {
                o.classList.toggle('is-selected', i === 0);
                if (i === 0) {
                    valueEl.textContent = o.textContent.trim();
                    if (hiddenInput) hiddenInput.value = o.dataset.value;
                }
            });
        }

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            document.querySelectorAll('.filter-grid [data-dropdown]').forEach(resetDropdown);
            const search = document.getElementById('filter-search');
            if (search) search.value = '';
        });

        document.getElementById('btn-apply-filter')?.addEventListener('click', () => {
            const btn = document.getElementById('btn-apply-filter');
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined">progress_activity</span><span>Memuat...</span>';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 400);
            }
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
        // MODAL HAK AKSES: buka/tutup, drag, bulk apply
        // ================================================================
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('accessModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('accessForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const tableBody = document.getElementById('hakAksesTableBody');
        const dragHandle = document.getElementById('modalDragHandle');

        const accessUserAvatar = document.getElementById('accessUserAvatar');
        const accessUserName = document.getElementById('accessUserName');
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
            modalTitle.textContent = 'Atur Hak Akses';

            accessUserName.textContent = data.name || '-';
            accessUserEmail.textContent = data.email || '-';
            accessUserRole.textContent = data.role || 'user';
            accessUserAvatar.textContent = initials(data.name);

            // Reset semua permission dropdown ke 'none'
            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    'none'
                );
            });

            resetDropdown(document.getElementById('dd-bulk'));
            document.querySelectorAll('#accessModal .permission-row').forEach(r => r.dataset.state = 'none');

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
            dragState = {
                startX: e.clientX,
                startY: e.clientY,
                originX: rect.left,
                originY: rect.top
            };
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
            const newLeft = Math.min(Math.max(8, dragState.originX + dx), Math.max(8, maxLeft));
            const newTop = Math.min(Math.max(8, dragState.originY + dy), Math.max(8, maxTop));
            modalCard.style.left = newLeft + 'px';
            modalCard.style.top = newTop + 'px';
        });

        function endDrag(e) {
            if (!dragState) return;
            dragState = null;
            modalCard.classList.remove('is-dragging');
            try {
                dragHandle.releasePointerCapture(e.pointerId);
            } catch (_) {}
        }
        dragHandle.addEventListener('pointerup', endDrag);
        dragHandle.addEventListener('pointercancel', endDrag);

        // ---- Bulk apply ----
        document.getElementById('btnBulkApply')?.addEventListener('click', () => {
            const bulkValue = document.getElementById('bulk-value').value;
            if (!bulkValue) return;
            document.querySelectorAll('.permission-value').forEach((input) => {
                const menu = input.dataset.menu;
                selectDropdownValue(
                    document.querySelector(`[data-permission-dropdown][data-menu="${menu}"]`),
                    bulkValue
                );
            });
        });

        // ---- Submit (UI only, tidak ada fetch) ----
        modalForm.addEventListener('submit', (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const userId = document.getElementById('form-user_id').value;
            const permissions = {};
            document.querySelectorAll('.permission-value').forEach((input) => {
                permissions[input.dataset.menu] = input.value;
            });

            console.log('Payload Hak Akses (UI only):', {
                user_id: userId,
                permissions
            });

            setTimeout(() => {
                modalSubmitBtn.disabled = false;
                closeModal();
            }, 400);
        });

        // ---- Event delegation: tombol Atur Hak Akses ----
        tableBody.addEventListener('click', (e) => {
            const accessBtn = e.target.closest('.btn-access-row');
            if (accessBtn) {
                return openModal({
                    id: accessBtn.dataset.id,
                    name: accessBtn.dataset.name,
                    email: accessBtn.dataset.email,
                    role: accessBtn.dataset.role,
                });
            }
        });
    </script>
</body>

</html>