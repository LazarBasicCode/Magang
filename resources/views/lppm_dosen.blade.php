<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>LPPM Dosen &middot; SIDA</title>
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
                <a href="{{ url('/lppm/dosen') }}" aria-current="page" class="nav-link is-active">
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
                <a href="{{ url('/hak-akses') }}" class="nav-link">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
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
                        <span class="link">LPPM</span>
                        <span>/</span>
                        <span class="current">Data Dosen</span>
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

                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>LPPM</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Data Dosen</span>
                        </div>
                        <h1 class="page-title">Publikasi, HKI &amp; Buku Dosen</h1>
                        <p class="page-subtitle">Pendataan luaran Jurnal Q1-Q4, SINTA, HKI, dan Buku Dosen &middot; Tahun 2026</p>
                    </div>
                    <button type="button" class="btn-primary" id="btnTambahLppmDosen">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Data Dosen</span>
                    </button>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Karya</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary"><span class="material-symbols-outlined">library_books</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Jurnal Int. (Q1-Q4)</span>
                            <span class="stat-value">{{ $stats['jurnal'] }}</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">public</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">SINTA Nasional</span>
                            <span class="stat-value">{{ $stats['sinta'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">local_library</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">HKI &amp; Buku</span>
                            <span class="stat-value">{{ $stats['hki_buku'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">verified</span></div>
                    </div>
                </div>

                <!-- FILTER BAR (tampilan saja, belum disambung ke query) -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Jenis Karya</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="q_internasional">Jurnal Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="sinta_nasional">Jurnal Nasional (SINTA)</button>
                                    <button type="button" class="dropdown-option" data-value="hki">Hak Kekayaan Intelektual</button>
                                    <button type="button" class="dropdown-option" data-value="book">Buku</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari judul karya atau nama dosen..." />
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
                            <h2 class="table-card-title">Daftar Rekap LPPM Dosen</h2>
                            <p class="table-card-subtitle">Data luaran HKI, Buku, Jurnal Internasional dan Nasional 2026</p>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIDN</th>
                                    <th>Penulis (Dosen)</th>
                                    <th>Judul Publikasi / Karya</th>
                                    <th class="center">Jenis</th>
                                    <th class="center">Kategori / Peringkat</th>
                                    <th class="center">Tahun</th>
                                    <th class="center">Bukti / DOI</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="lppmDosenTableBody">
                                @php
                                $jenisLabel = [
                                'q_internasional' => 'Jurnal Int.',
                                'sinta_nasional' => 'Jurnal Nasional',
                                'hki' => 'HKI',
                                'book' => 'Buku',
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @forelse($items as $item)
                                @php
                                $nama = optional($item->dosen->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->dosen_id % count($colors)];
                                $kategori = $item->peringkat ?: ($item->jenis_hki ?: ($item->kategori_buku ?: '-'));
                                $buktiLabel = $item->link_doi ? 'Link DOI' : 'Lihat Bukti';
                                $buktiUrl = $item->link_doi ?: $item->bukti_kegiatan;
                                @endphp
                                <tr data-id="{{ $item->id }}">
                                    <td><span class="nim-code">{{ $item->dosen->nidn ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td><span class="activity-title" title="{{ $item->judul }}">{{ $item->judul }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $jenisLabel[$item->jenis] ?? $item->jenis }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $kategori }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $item->tahun }}</span></td>
                                    <td class="center">
                                        <a href="{{ $buktiUrl }}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                                            <span class="material-symbols-outlined">{{ $item->link_doi ? 'link' : 'cloud' }}</span>
                                            <span>{{ $buktiLabel }}</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-dosen_id="{{ $item->dosen_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-judul="{{ urlencode($item->judul) }}"
                                                data-penulis="{{ urlencode($item->penulis) }}"
                                                data-nama_jurnal="{{ urlencode($item->nama_jurnal ?? '') }}"
                                                data-peringkat="{{ urlencode($item->peringkat ?? '') }}"
                                                data-jenis_hki="{{ $item->jenis_hki ?? '' }}"
                                                data-kategori_buku="{{ $item->kategori_buku ?? '' }}"
                                                data-link_doi="{{ urlencode($item->link_doi ?? '') }}"
                                                data-bukti_kegiatan="{{ urlencode($item->bukti_kegiatan) }}"
                                                data-tahun="{{ $item->tahun }}">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="{{ $item->id }}">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="8" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data. Klik "Tambah Data Dosen" untuk mulai mengisi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>{{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }}</strong>
                            dari <strong>{{ $items->total() }}</strong> data publikasi LPPM dosen
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit LPPM Dosen ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="lppmDosenModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Data Dosen</h3>
                <p class="modal-subtitle">Isi detail publikasi/HKI/buku dosen</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="lppmDosenForm" class="modal-body">
            <input type="hidden" id="form-id" value="">

            <div class="field">
                <label class="field-label">Dosen</label>
                <div class="dropdown" data-dropdown id="dd-dosen">
                    <input type="hidden" id="form-dosen_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih dosen...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($dosenList as $d)
                        <button type="button" class="dropdown-option" data-value="{{ $d->id }}">{{ $d->nidn }} &mdash; {{ optional($d->user)->name ?? '-' }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Karya</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="q_internasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Jurnal Internasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="q_internasional">Jurnal Internasional</button>
                            <button type="button" class="dropdown-option" data-value="sinta_nasional">Jurnal Nasional (SINTA)</button>
                            <button type="button" class="dropdown-option" data-value="hki">Hak Kekayaan Intelektual</button>
                            <button type="button" class="dropdown-option" data-value="book">Buku</button>
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
                <label class="field-label" for="form-judul">Judul Karya</label>
                <div class="field-control">
                    <input id="form-judul" type="text" placeholder="Judul publikasi/karya" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-penulis">Penulis</label>
                <div class="field-control">
                    <input id="form-penulis" type="text" placeholder="Nama-nama penulis" required>
                </div>
            </div>

            <!-- Field khusus jurnal (q_internasional / sinta_nasional) -->
            <div class="field-row" id="field-jurnal-extra">
                <div class="field">
                    <label class="field-label" for="form-nama_jurnal">Nama Jurnal</label>
                    <div class="field-control">
                        <input id="form-nama_jurnal" type="text" placeholder="Nama jurnal">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-peringkat">Peringkat (Q1-Q4 / S1-S4)</label>
                    <div class="field-control">
                        <input id="form-peringkat" type="text" placeholder="Contoh: Q2 atau S3">
                    </div>
                </div>
            </div>

            <div class="field" id="field-link_doi">
                <label class="field-label" for="form-link_doi">Link DOI</label>
                <div class="field-control">
                    <input id="form-link_doi" type="url" placeholder="https://doi.org/...">
                </div>
            </div>

            <!-- Field khusus HKI -->
            <div class="field" id="field-jenis_hki">
                <label class="field-label">Jenis HKI</label>
                <div class="dropdown" data-dropdown id="dd-jenis_hki">
                    <input type="hidden" id="form-jenis_hki" value="hak_cipta" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Hak Cipta</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="hak_cipta">Hak Cipta</button>
                        <button type="button" class="dropdown-option" data-value="paten">Paten</button>
                        <button type="button" class="dropdown-option" data-value="merek">Merek</button>
                    </div>
                </div>
            </div>

            <!-- Field khusus Buku -->
            <div class="field" id="field-kategori_buku">
                <label class="field-label">Kategori Buku</label>
                <div class="dropdown" data-dropdown id="dd-kategori_buku">
                    <input type="hidden" id="form-kategori_buku" value="ajar" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Buku Ajar</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="ajar">Buku Ajar</button>
                        <button type="button" class="dropdown-option" data-value="referensi">Referensi</button>
                        <button type="button" class="dropdown-option" data-value="chapter">Chapter</button>
                    </div>
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
                    dropdown.classList.remove('is-open');
                });
            });
        });
        document.addEventListener('click', () => dropdowns.forEach((d) => d.classList.remove('is-open')));

        function selectDropdownValue(dropdownEl, value, placeholder) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll('.dropdown-option');
            const valueEl = dropdownEl.querySelector('.dropdown-value');
            const hiddenInput = dropdownEl.querySelector('input[type="hidden"]');
            let matched = false;
            options.forEach((o) => {
                const isMatch = o.dataset.value === String(value);
                o.classList.toggle('is-selected', isMatch);
                if (isMatch) {
                    valueEl.textContent = o.textContent.trim();
                    matched = true;
                }
            });
            if (hiddenInput) hiddenInput.value = matched ? value : '';
            if (!matched) valueEl.textContent = placeholder || (options[0] ? options[0].textContent.trim() : '');
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
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined">progress_activity</span><span>Memuat...</span>';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 400);
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
        // MODAL: buka/tutup, drag, field kondisional, dan CRUD via fetch
        // ================================================================
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('lppmDosenModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('lppmDosenForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahLppmDosen');
        const tableBody = document.getElementById('lppmDosenTableBody');
        const dragHandle = document.getElementById('modalDragHandle');

        const fieldJurnalExtra = document.getElementById('field-jurnal-extra');
        const fieldLinkDoi = document.getElementById('field-link_doi');
        const fieldJenisHki = document.getElementById('field-jenis_hki');
        const fieldKategoriBuku = document.getElementById('field-kategori_buku');

        function updateConditionalFields(jenis) {
            const isJurnal = jenis === 'q_internasional' || jenis === 'sinta_nasional';
            fieldJurnalExtra.classList.toggle('hidden', !isJurnal);
            fieldLinkDoi.classList.toggle('hidden', !isJurnal);
            fieldJenisHki.classList.toggle('hidden', jenis !== 'hki');
            fieldKategoriBuku.classList.toggle('hidden', jenis !== 'book');
        }
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((opt) => {
            opt.addEventListener('click', () => updateConditionalFields(opt.dataset.value));
        });

        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;
            modalCard.style.left = '';
            modalCard.style.top = '';
            modalCard.style.transform = '';

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Data Dosen' : 'Tambah Data Dosen';

            selectDropdownValue(document.getElementById('dd-dosen'), data.dosen_id || '', 'Pilih dosen...');
            const jenis = data.jenis || 'q_internasional';
            selectDropdownValue(document.getElementById('dd-jenis'), jenis);
            selectDropdownValue(document.getElementById('dd-jenis_hki'), data.jenis_hki || 'hak_cipta');
            selectDropdownValue(document.getElementById('dd-kategori_buku'), data.kategori_buku || 'ajar');
            updateConditionalFields(jenis);

            document.getElementById('form-tahun').value = data.tahun || 2026;
            document.getElementById('form-judul').value = data.judul ? decodeURIComponent(data.judul) : '';
            document.getElementById('form-penulis').value = data.penulis ? decodeURIComponent(data.penulis) : '';
            document.getElementById('form-nama_jurnal').value = data.nama_jurnal ? decodeURIComponent(data.nama_jurnal) : '';
            document.getElementById('form-peringkat').value = data.peringkat ? decodeURIComponent(data.peringkat) : '';
            document.getElementById('form-link_doi').value = data.link_doi ? decodeURIComponent(data.link_doi) : '';
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan ? decodeURIComponent(data.bukti_kegiatan) : '';

            modalBackdrop.classList.add('is-active');
            modalCard.classList.add('is-active');
            modalCard.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modalBackdrop.classList.remove('is-active');
            modalCard.classList.remove('is-active');
            modalCard.setAttribute('aria-hidden', 'true');
        }
        btnTambah?.addEventListener('click', () => openModal('create'));
        modalCloseBtn.addEventListener('click', closeModal);
        modalCancelBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalCard.classList.contains('is-active')) closeModal();
        });

        // ---- Drag ----
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
            const dx = e.clientX - dragState.startX,
                dy = e.clientY - dragState.startY;
            const maxLeft = window.innerWidth - modalCard.offsetWidth - 8,
                maxTop = window.innerHeight - modalCard.offsetHeight - 8;
            modalCard.style.left = Math.min(Math.max(8, dragState.originX + dx), Math.max(8, maxLeft)) + 'px';
            modalCard.style.top = Math.min(Math.max(8, dragState.originY + dy), Math.max(8, maxTop)) + 'px';
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

        // ---- Bangun/ganti/hapus baris tabel ----
        const jenisLabel = {
            q_internasional: 'Jurnal Int.',
            sinta_nasional: 'Jurnal Nasional',
            hki: 'HKI',
            book: 'Buku'
        };

        function initials(name) {
            return (name || '').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '-';
        }

        function avatarColor(id) {
            const c = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
            return c[Number(id) % c.length];
        }

        function esc(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function buildRowHTML(item) {
            const kategori = item.peringkat || item.jenis_hki || item.kategori_buku || '-';
            const buktiUrl = item.link_doi || item.bukti_kegiatan;
            const buktiLabel = item.link_doi ? 'Link DOI' : 'Lihat Bukti';
            const buktiIcon = item.link_doi ? 'link' : 'cloud';
            return `
            <tr data-id="${item.id}">
                <td><span class="nim-code">${esc(item.nidn)}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.dosen_id)}">${esc(initials(item.nama))}</div>
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td><span class="activity-title" title="${esc(item.judul)}">${esc(item.judul)}</span></td>
                <td class="center"><span class="plain-text">${esc(jenisLabel[item.jenis] || item.jenis)}</span></td>
                <td class="center"><span class="plain-text">${esc(kategori)}</span></td>
                <td class="center"><span class="year-chip">${esc(item.tahun)}</span></td>
                <td class="center">
                    <a href="${esc(buktiUrl)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">${buktiIcon}</span><span>${buktiLabel}</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-dosen_id="${item.dosen_id}" data-jenis="${item.jenis}"
                            data-judul="${encodeURIComponent(item.judul)}" data-penulis="${encodeURIComponent(item.penulis)}"
                            data-nama_jurnal="${encodeURIComponent(item.nama_jurnal || '')}"
                            data-peringkat="${encodeURIComponent(item.peringkat || '')}"
                            data-jenis_hki="${item.jenis_hki || ''}" data-kategori_buku="${item.kategori_buku || ''}"
                            data-link_doi="${encodeURIComponent(item.link_doi || '')}"
                            data-bukti_kegiatan="${encodeURIComponent(item.bukti_kegiatan)}" data-tahun="${item.tahun}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="${item.id}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </td>
            </tr>`.trim();
        }

        function insertRow(item) {
            document.getElementById('emptyRow')?.remove();
            const wrap = document.createElement('tbody');
            wrap.innerHTML = buildRowHTML(item);
            const row = wrap.firstElementChild;
            row.classList.add('is-new');
            tableBody.prepend(row);
        }

        function updateRow(item) {
            const existing = tableBody.querySelector(`tr[data-id="${item.id}"]`);
            if (!existing) return insertRow(item);
            const wrap = document.createElement('tbody');
            wrap.innerHTML = buildRowHTML(item);
            existing.replaceWith(wrap.firstElementChild);
        }

        function removeRow(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;
            row.classList.add('is-removing');
            row.addEventListener('transitionend', () => row.remove(), {
                once: true
            });
        }

        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const jenis = document.getElementById('form-jenis').value;
            const payload = {
                dosen_id: document.getElementById('form-dosen_id').value,
                jenis,
                judul: document.getElementById('form-judul').value,
                penulis: document.getElementById('form-penulis').value,
                nama_jurnal: document.getElementById('form-nama_jurnal').value || null,
                peringkat: document.getElementById('form-peringkat').value || null,
                jenis_hki: jenis === 'hki' ? document.getElementById('form-jenis_hki').value : null,
                kategori_buku: jenis === 'book' ? document.getElementById('form-kategori_buku').value : null,
                link_doi: document.getElementById('form-link_doi').value || null,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
                tahun: document.getElementById('form-tahun').value,
            };

            if (!payload.dosen_id) {
                modalError.textContent = 'Pilih dosen terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/lppm/dosen/${id}` : '/lppm/dosen';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
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
                closeModal();
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        async function handleDelete(id) {
            if (!confirm('Hapus data ini? Tindakan tidak bisa dibatalkan.')) return;
            try {
                const res = await fetch(`/lppm/dosen/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await res.json();
                if (res.ok && result.success) removeRow(result.id);
                else alert(result.message || 'Gagal menghapus data.');
            } catch (err) {
                alert('Gagal terhubung ke server.');
            }
        }

        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-row');
            if (editBtn) return openModal('edit', editBtn.dataset);
            const delBtn = e.target.closest('.btn-delete-row');
            if (delBtn) handleDelete(delBtn.dataset.id);
        });
    </script>
</body>

</html>