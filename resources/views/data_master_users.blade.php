<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Data Master Users &middot; SIDA</title>
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
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>

                <div class="nav-heading">LPPM</div>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">person</span>
                    <span>Mahasiswa</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">co_present</span>
                    <span>Dosen</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="nav-heading">Kemitraan</div>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>

                <div class="nav-heading">Administrasi</div>
                <a href="#" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">manage_accounts</span>
                    <span>Data Master</span>
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
                        <span class="current">Data Master</span>
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
                                <span class="header-profile-name">Admin LPPM</span>
                                <span class="header-profile-role">Institut Asia Malang</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>Administrasi</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Data Master</span>
                        </div>
                        <h1 class="page-title">Data Master Pengguna</h1>
                        <p class="page-subtitle">Kelola akun pengguna sistem: Superadmin, Admin, Dosen, dan Mahasiswa</p>
                    </div>
                    <button type="button" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Pengguna</span>
                    </button>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Pengguna</span>
                            <span class="stat-value">128</span>
                            <span class="stat-delta up">
                                <span class="material-symbols-outlined">arrow_upward</span>5% bulan ini
                            </span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Mahasiswa</span>
                            <span class="stat-value">96</span>
                            <span class="stat-delta neutral">75% dari total</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Dosen</span>
                            <span class="stat-value">24</span>
                            <span class="stat-delta neutral">19% dari total</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">co_present</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Admin & Superadmin</span>
                            <span class="stat-value">8</span>
                            <span class="stat-delta neutral">6% dari total</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <!-- Role -->
                        <div class="field">
                            <label class="field-label">Role Pengguna</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-role" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Role</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Role</button>
                                    <button type="button" class="dropdown-option" data-value="superadmin">Superadmin</button>
                                    <button type="button" class="dropdown-option" data-value="admin">Admin</button>
                                    <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                                    <button type="button" class="dropdown-option" data-value="mahasiswa">Mahasiswa</button>
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari nama, NIM, atau NIDN..." />
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
                            <h2 class="table-card-title">Daftar Pengguna Sistem</h2>
                            <p class="table-card-subtitle">Data akun Superadmin, Admin, Dosen, dan Mahasiswa</p>
                        </div>
                        <div class="table-card-tools">
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Pengguna</th>
                                    <th>Nama Lengkap</th>
                                    <th class="center">Role</th>
                                    <th>NIM / NIDN</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 (Superadmin) -->
                                <tr>
                                    <td><span class="nim-code">USR-001</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">SA</div>
                                            <div class="student-name">
                                                <span class="name">Sistem Administrator</span>
                                                <span class="prodi">superadmin@sida.ac.id</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-danger">Superadmin</span></td>
                                    <td>
                                        <span style="font-size: 12px; color: var(--ink-muted);">&mdash;</span>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 (Admin) -->
                                <tr>
                                    <td><span class="nim-code">USR-002</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-warning">RA</div>
                                            <div class="student-name">
                                                <span class="name">Rina Amelia</span>
                                                <span class="prodi">admin.lppm@sida.ac.id</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-warning">Admin</span></td>
                                    <td>
                                        <span style="font-size: 12px; color: var(--ink-muted);">&mdash;</span>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 3 (Dosen) -->
                                <tr>
                                    <td><span class="nim-code">USR-003</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-info">BS</div>
                                            <div class="student-name">
                                                <span class="name">Dr. Budi Santoso</span>
                                                <span class="prodi">Dosen - Fakultas Teknologi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-info">Dosen</span></td>
                                    <td>
                                        <span class="nim-code">0712048901</span>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 4 (Mahasiswa) -->
                                <tr>
                                    <td><span class="nim-code">USR-004</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-success">AR</div>
                                            <div class="student-name">
                                                <span class="name">Ahmad Rizal Fauzi</span>
                                                <span class="prodi">Mahasiswa - Teknik Mesin</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-success">Mahasiswa</span></td>
                                    <td>
                                        <span class="nim-code">222011005</span>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER: pagination -->
                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>1-4</strong> dari <strong>128</strong> data pengguna sistem
                        </div>
                        <div class="pagination">
                            <button type="button" class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                            <button type="button" class="page-btn is-active">1</button>
                            <button type="button" class="page-btn">2</button>
                            <button type="button" class="page-btn">3</button>
                            <span class="page-ellipsis">&hellip;</span>
                            <button type="button" class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Script fungsionalitas UI -->
    <script>
        // Custom dropdown behavior
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
                    dropdown.classList.remove('is-open');
                });
            });
        });

        // Reset Dropdown
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
            document.querySelectorAll('[data-dropdown]').forEach(resetDropdown);
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

        // SIDEBAR Drawer Toggle Logic
        const sidebar = document.querySelector('.app-sidebar');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('is-open');
            sidebarOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden'; // Mencegah scroll pada body saat sidebar terbuka
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            sidebarOverlay.classList.remove('is-active');
            document.body.style.overflow = '';
        }

        // Event Listeners
        sidebarToggleBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);

        // Otomatis menutup sidebar jika ukuran window diperbesar kembali ke mode desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        document.addEventListener('click', () => {
            dropdowns.forEach((d) => d.classList.remove('is-open'));
        });

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
    </script>
</body>

</html>