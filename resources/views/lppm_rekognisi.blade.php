<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>LPPM Rekognisi &middot; SIDA</title>
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
                <a href="#" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="nav-heading">Kemitraan</div>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
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
                        <span class="link">LPPM</span>
                        <span>/</span>
                        <span class="current">Rekognisi</span>
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
                            <span>LPPM</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Rekognisi</span>
                        </div>
                        <h1 class="page-title">Data Rekognisi & Karir Alumni</h1>
                        <p class="page-subtitle">Pendataan rekognisi tingkat Nasional, Internasional, dan jejak karir Alumni</p>
                    </div>
                    <button type="button" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Data Rekognisi</span>
                    </button>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Rekognisi</span>
                            <span class="stat-value">84</span>
                            <span class="stat-delta up">
                                <span class="material-symbols-outlined">arrow_upward</span>3% bulan ini
                            </span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">stars</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Nasional</span>
                            <span class="stat-value">42</span>
                            <span class="stat-delta neutral">50% dari total</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">map</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Internasional</span>
                            <span class="stat-value">16</span>
                            <span class="stat-delta neutral">19% dari total</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Karir Alumni</span>
                            <span class="stat-value">26</span>
                            <span class="stat-delta neutral">31% dari total</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">work_history</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <!-- Tipe User -->
                        <div class="field">
                            <label class="field-label">Entitas Pengguna</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-user" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Entitas</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Entitas</button>
                                    <button type="button" class="dropdown-option" data-value="mahasiswa">Mahasiswa / Alumni</button>
                                    <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Rekognisi -->
                        <div class="field">
                            <label class="field-label">Tingkat Rekognisi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Kategori</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Kategori</button>
                                    <button type="button" class="dropdown-option" data-value="nasional">Nasional</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="alumni">Karir Alumni</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tahun Akademik -->
                        <div class="field">
                            <label class="field-label">Rentang Periode</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tahun" value="2026" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Tahun 2026</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="2026">Tahun 2026</button>
                                    <button type="button" class="dropdown-option" data-value="2025">Tahun 2025</button>
                                    <button type="button" class="dropdown-option" data-value="2024">Tahun 2024</button>
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari nama, mitra, atau jabatan..." />
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
                            <h2 class="table-card-title">Daftar Rekap Rekognisi</h2>
                            <p class="table-card-subtitle">Data kegiatan pengakuan Nasional, Internasional, dan Alumni</p>
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
                                    <th class="center">Kategori</th>
                                    <th>Instansi Mitra & Jabatan</th>
                                    <th>Periode Tanggal</th>
                                    <th class="center">Bukti Validasi</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 (Mahasiswa - Nasional) -->
                                <tr>
                                    <td><span class="nim-code">222011005</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-info">AR</div>
                                            <div class="student-name">
                                                <span class="name">Ahmad Rizal Fauzi</span>
                                                <span class="prodi">Mahasiswa - Teknik Mesin</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-info">Nasional</span></td>
                                    <td>
                                        <span class="activity-title" title="Universitas Gadjah Mada">Universitas Gadjah Mada</span>
                                        <div style="font-size: 11.5px; color: var(--ink-muted); margin-top: 2px;">Peserta Pertukaran Mahasiswa</div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 500; color: var(--ink);">01 Jan 2026</div>
                                        <div style="font-size: 11px; color: var(--ink-muted);">s/d 30 Jun 2026</div>
                                    </td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud_download</span>
                                            <span>Sertifikat</span>
                                        </a>
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
                                <!-- Row 2 (Dosen - Internasional) -->
                                <tr>
                                    <td><span class="nim-code">0712048901</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">BS</div>
                                            <div class="student-name">
                                                <span class="name">Dr. Budi Santoso</span>
                                                <span class="prodi">Dosen - Fakultas Teknologi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-warning">Internasional</span></td>
                                    <td>
                                        <span class="activity-title" title="National University of Singapore">National University of Singapore</span>
                                        <div style="font-size: 11.5px; color: var(--ink-muted); margin-top: 2px;">Visiting Researcher</div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 500; color: var(--ink);">10 Mar 2026</div>
                                        <div style="font-size: 11px; color: var(--ink-muted);">s/d 10 Mei 2026</div>
                                    </td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud_download</span>
                                            <span>SK Tugas</span>
                                        </a>
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
                                <!-- Row 3 (Alumni - Karir) -->
                                <tr>
                                    <td><span class="nim-code">192011044</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-success">SN</div>
                                            <div class="student-name">
                                                <span class="name">Sarah Novita, S.Kom</span>
                                                <span class="prodi">Alumni - Sistem Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="badge badge-success">Alumni</span></td>
                                    <td>
                                        <span class="activity-title" title="PT Telkom Indonesia (Persero) Tbk">PT Telkom Indonesia (Persero) Tbk</span>
                                        <div style="font-size: 11.5px; color: var(--primary); font-weight: 600; margin-top: 2px;">Senior Data Analyst</div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 500; color: var(--ink);">01 Ags 2025</div>
                                        <div style="font-size: 11px; color: var(--ink-muted);">s/d Sekarang</div>
                                    </td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">work</span>
                                            <span>Bukti Kerja</span>
                                        </a>
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
                            Menampilkan <strong>1-3</strong> dari <strong>84</strong> data rekognisi LPPM <span class="highlight">2026</span>
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