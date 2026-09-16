<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Kerja Sama &middot; SIDA</title>
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
                <a href="#" aria-current="page" class="nav-link is-active">
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
                        <span class="link">Kemitraan</span>
                        <span>/</span>
                        <span class="current">Kerja Sama</span>
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
                                <span class="header-profile-name">Admin Kemitraan</span>
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
                            <span>Kemitraan</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Kerja Sama</span>
                        </div>
                        <h1 class="page-title">Kerja Sama &amp; Kemitraan</h1>
                        <p class="page-subtitle">Pendataan kerja sama mahasiswa, dosen, guest lecture, pengabdian &amp; research internasional &middot; Tahun 2026</p>
                    </div>
                    <button type="button" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Kerja Sama</span>
                    </button>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Kerja Sama</span>
                            <span class="stat-value">36</span>
                            <span class="stat-delta up">
                                <span class="material-symbols-outlined">arrow_upward</span>8% bulan ini
                            </span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">handshake</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Mahasiswa</span>
                            <span class="stat-value">14</span>
                            <span class="stat-delta neutral">39% dari total</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">school</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Dosen</span>
                            <span class="stat-value">9</span>
                            <span class="stat-delta neutral">25% dari total</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">co_present</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Internasional</span>
                            <span class="stat-value">13</span>
                            <span class="stat-delta neutral">36% dari total</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <!-- Tipe User -->
                        <div class="field">
                            <label class="field-label">Tipe User</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tipe-user" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tipe</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Tipe</button>
                                    <button type="button" class="dropdown-option" data-value="mahasiswa">Mahasiswa</button>
                                    <button type="button" class="dropdown-option" data-value="dosen">Dosen</button>
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Kerja Sama -->
                        <div class="field">
                            <label class="field-label">Jenis Kerja Sama</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="conference_internasional">Conference Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="pkl">PKL (Output)</button>
                                    <button type="button" class="dropdown-option" data-value="sharing_session">Sharing Session</button>
                                    <button type="button" class="dropdown-option" data-value="keynote_session">Keynote Speaker</button>
                                    <button type="button" class="dropdown-option" data-value="guest_lecture">Guest Lecture</button>
                                    <button type="button" class="dropdown-option" data-value="pengabdian_internasional">Pengabdian Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="research_internasional">Research Internasional</button>
                                </div>
                            </div>
                        </div>

                        <!-- Arah (untuk Guest Lecture) -->
                        <div class="field">
                            <label class="field-label">Arah (Guest Lecture)</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-arah" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Arah</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Arah</button>
                                    <button type="button" class="dropdown-option" data-value="inbound">Inbound</button>
                                    <button type="button" class="dropdown-option" data-value="outbound">Outbound</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tahun (locked) -->
                        <div class="field">
                            <label class="field-label">Tahun</label>
                            <div class="field-locked">
                                <div class="field-locked-inner">
                                    <span class="material-symbols-outlined">lock</span>
                                    <span class="field-locked-value">2026</span>
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari judul kegiatan atau mitra..." />
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
                            <h2 class="table-card-title">Daftar Rekap Kerja Sama</h2>
                            <p class="table-card-subtitle">Data kerja sama sesuai format Kemitraan 2026</p>
                        </div>
                        <div class="table-card-tools">
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">view_column</span>
                                <span>Kolom</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIM/NIDN</th>
                                    <th>Nama</th>
                                    <th>Judul Kegiatan</th>
                                    <th class="center">Jenis</th>
                                    <th class="center">Tipe User</th>
                                    <th class="center">Arah</th>
                                    <th class="center">Mitra</th>
                                    <th class="center">Periode</th>
                                    <th class="center">Bukti</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1: Conference Internasional Mahasiswa -->
                                <tr>
                                    <td><span class="nim-code">222011005</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">AR</div>
                                            <div class="student-name">
                                                <span class="name">Ahmad Rizal Fauzi</span>
                                                <span class="prodi">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="International Conference on Artificial Intelligence 2026">International Conference on Artificial Intelligence 2026</span>
                                    </td>
                                    <td class="center"><span class="badge badge-info">Conference Int.</span></td>
                                    <td class="center"><span class="badge badge-neutral">Mahasiswa</span></td>
                                    <td class="center"><span class="plain-text">-</span></td>
                                    <td class="center"><span class="plain-text">IEEE Indonesia</span></td>
                                    <td class="center"><span class="year-chip">12-15 Feb 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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

                                <!-- Row 2: PKL Mahasiswa -->
                                <tr>
                                    <td><span class="nim-code">232012014</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-info">SN</div>
                                            <div class="student-name">
                                                <span class="name">Siti Nurhaliza Putri</span>
                                                <span class="prodi">Sistem Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="Praktik Kerja Lapangan di PT Telkom Indonesia">Praktik Kerja Lapangan di PT Telkom Indonesia</span>
                                    </td>
                                    <td class="center"><span class="badge badge-primary">PKL</span></td>
                                    <td class="center"><span class="badge badge-neutral">Mahasiswa</span></td>
                                    <td class="center"><span class="plain-text">-</span></td>
                                    <td class="center"><span class="plain-text">PT Telkom Indonesia</span></td>
                                    <td class="center"><span class="year-chip">1 Jan - 31 Mar 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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

                                <!-- Row 3: Sharing Session Mahasiswa -->
                                <tr>
                                    <td><span class="nim-code">211009088</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-warning">KA</div>
                                            <div class="student-name">
                                                <span class="name">Kevin Ardiansyah</span>
                                                <span class="prodi">Desain Komunikasi Visual</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="Sharing Session UI/UX Design bersama Google Indonesia">Sharing Session UI/UX Design bersama Google Indonesia</span>
                                    </td>
                                    <td class="center"><span class="badge badge-warning">Sharing Session</span></td>
                                    <td class="center"><span class="badge badge-neutral">Mahasiswa</span></td>
                                    <td class="center"><span class="plain-text">-</span></td>
                                    <td class="center"><span class="plain-text">Google Indonesia</span></td>
                                    <td class="center"><span class="year-chip">20 Mar 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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

                                <!-- Row 4: Keynote Speaker Dosen -->
                                <tr>
                                    <td><span class="nim-code">0712345601</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-danger">BS</div>
                                            <div class="student-name">
                                                <span class="name">Dr. Budi Santoso</span>
                                                <span class="prodi">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="Keynote Speaker di International Seminar on Cybersecurity">Keynote Speaker di International Seminar on Cybersecurity</span>
                                    </td>
                                    <td class="center"><span class="badge badge-success">Keynote Speaker</span></td>
                                    <td class="center"><span class="badge badge-neutral">Dosen</span></td>
                                    <td class="center"><span class="plain-text">-</span></td>
                                    <td class="center"><span class="plain-text">NUS Singapore</span></td>
                                    <td class="center"><span class="year-chip">5 Apr 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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

                                <!-- Row 5: Guest Lecture Inbound -->
                                <tr>
                                    <td><span class="nim-code">0712345602</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-success">RW</div>
                                            <div class="student-name">
                                                <span class="name">Prof. Rina Wijaya</span>
                                                <span class="prodi">Sistem Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="Guest Lecture: AI in Healthcare dari Universitas Melbourne">Guest Lecture: AI in Healthcare dari Universitas Melbourne</span>
                                    </td>
                                    <td class="center"><span class="badge badge-info">Guest Lecture</span></td>
                                    <td class="center"><span class="badge badge-neutral">Dosen</span></td>
                                    <td class="center"><span class="badge badge-primary">Inbound</span></td>
                                    <td class="center"><span class="plain-text">Universitas Melbourne</span></td>
                                    <td class="center"><span class="year-chip">10 Mei 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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

                                <!-- Row 6: Pengabdian Internasional Dosen -->
                                <tr>
                                    <td><span class="nim-code">0712345603</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">AH</div>
                                            <div class="student-name">
                                                <span class="name">Dr. Ahmad Hidayat</span>
                                                <span class="prodi">Teknik Elektro</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="Pengabdian Internasional: Instalasi Panel Surya di Desa Terpencil Timor Leste">Pengabdian Internasional: Instalasi Panel Surya di Desa Terpencil Timor Leste</span>
                                    </td>
                                    <td class="center"><span class="badge badge-danger">Pengabdian Int.</span></td>
                                    <td class="center"><span class="badge badge-neutral">Dosen</span></td>
                                    <td class="center"><span class="plain-text">-</span></td>
                                    <td class="center"><span class="plain-text">Universidade Nacional Timor Lorosa'e</span></td>
                                    <td class="center"><span class="year-chip">1-14 Jun 2026</span></td>
                                    <td class="center">
                                        <a href="#" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
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
                            Menampilkan <strong>1-6</strong> dari <strong>36</strong> data kerja sama tahun <span class="highlight">2026</span>
                        </div>
                        <div class="pagination">
                            <button type="button" class="page-btn" disabled>
                                <span class="material-symbols-outlined">chevron_left</span>
                            </button>
                            <button type="button" class="page-btn is-active">1</button>
                            <button type="button" class="page-btn">2</button>
                            <button type="button" class="page-btn">3</button>
                            <span class="page-ellipsis">&hellip;</span>
                            <button type="button" class="page-btn">6</button>
                            <button type="button" class="page-btn">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        // Custom Dropdown
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

        document.addEventListener('click', () => {
            dropdowns.forEach((d) => d.classList.remove('is-open'));
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
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        // Dark Mode Toggle Logic
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
            if (themeIcon) {
                themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>

</html>