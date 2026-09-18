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
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Data Master Pengguna &middot; SIDA</title>
</head>

<body>
    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <img alt="Logo Institut Asia Malang"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
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
                <a href="#" class="nav-link">
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
                        <button type="button" class="icon-btn" aria-label="Notifikasi">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="dot"></span>
                        </button>
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">Superadmin SIDA</span>
                                <span class="header-profile-role">Institut Asia Malang</span>
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

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>Administrasi</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Data Master Pengguna</span>
                        </div>
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
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Dosen</span>
                            <span class="stat-value">{{ $stats['dosen'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">co_present</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Admin &amp; Superadmin</span>
                            <span class="stat-value">{{ $stats['admin'] }}</span>
                        </div>
                        <div class="stat-icon primary">
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
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th class="center">Role</th>
                                    <th class="center">NIM / NIDN</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                @forelse($users as $item)
                                @php
                                $initials = collect(explode(' ', $item->name))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
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
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ urlencode($item->name) }}"
                                                data-role="{{ $item->role }}"
                                                data-identifier="{{ urlencode($identifier ?? '') }}">
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
                                    <td colspan="5" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data pengguna. Klik "Tambah Pengguna" untuk mulai mengisi.
                                    </td>
                                </tr>
                                @endforelse
                                <tr id="noResultsRow" hidden>
                                    <td colspan="5" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Tidak ada pengguna yang cocok dengan filter/pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerVisibleCount">{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }}</strong>
                            dari <strong>{{ $users->total() }}</strong> data pengguna
                        </div>
                        {{-- Pagination bawaan Laravel bisa ditambahkan di sini via {{ $users->links() }}
                        setelah view paginator kamu disesuaikan dengan desain ini. --}}
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

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ---------------- Custom Dropdown (dipakai di filter bar & modal) ----------------
        const dropdowns = document.querySelectorAll('[data-dropdown]');

        // Dropdown yang letaknya di dalam modal akan "dipindah" (portal) ke <body>
        // saat dibuka, supaya panel-nya tidak terpotong/ikut ter-scroll oleh
        // modal-body (overflow-y: auto) dan tidak merusak tampilan modal.
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

        dropdowns.forEach((dropdown) => {
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

        function selectDropdownValue(dropdownEl, value) {
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
            if (!matched && options.length) {
                valueEl.textContent = options[0].textContent.trim();
            }
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

        // ================================================================
        // FILTER & PENCARIAN: jalan otomatis (live) di sisi klien, tanpa reload.
        // Mencari di kolom ID, Nama, dan NIM/NIDN; role difilter dari dropdown.
        // ================================================================
        const filterSearchInput = document.getElementById('filter-search');
        const filterRoleInput = document.getElementById('filter-role');
        const userTableBodyForFilter = document.getElementById('userTableBody');
        const noResultsRow = document.getElementById('noResultsRow');
        const footerVisibleCount = document.getElementById('footerVisibleCount');

        function applyFilters() {
            if (!userTableBodyForFilter) return;
            const term = (filterSearchInput?.value || '').trim().toLowerCase();
            const role = filterRoleInput?.value || 'semua';

            const rows = userTableBodyForFilter.querySelectorAll('tr[data-id]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const idText = (row.querySelector('.nim-code')?.textContent || '').toLowerCase();
                const nameText = (row.querySelector('.student-name .name')?.textContent || '').toLowerCase();
                const identifierText = (row.querySelector('td:nth-child(4) .plain-text')?.textContent || '').toLowerCase();
                const roleText = (row.querySelector('td:nth-child(3) .badge')?.textContent || '').trim().toLowerCase();

                const matchesSearch = !term ||
                    idText.includes(term) ||
                    nameText.includes(term) ||
                    identifierText.includes(term);
                const matchesRole = role === 'semua' || roleText === role;
                const visible = matchesSearch && matchesRole;

                row.hidden = !visible;
                if (visible) visibleCount++;
            });

            const emptyRow = document.getElementById('emptyRow');
            const hasData = rows.length > 0;
            if (noResultsRow) {
                noResultsRow.hidden = !(hasData && visibleCount === 0);
            }
            if (footerVisibleCount) {
                footerVisibleCount.textContent = visibleCount;
            }
            if (emptyRow) {
                // Baris "belum ada data" cuma relevan kalau memang tidak ada data sama sekali
                emptyRow.hidden = hasData;
            }
        }

        // Debounce kecil supaya tidak query ulang di tiap keystroke terlalu agresif
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
        // MODAL: buka/tutup, drag, dan CRUD via fetch (tanpa reload halaman)
        // ================================================================
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('userModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('userForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahUser');
        const tableBody = document.getElementById('userTableBody');
        const dragHandle = document.getElementById('modalDragHandle');
        const labelIdentifier = document.getElementById('label-identifier');
        const inputIdentifier = document.getElementById('form-identifier');

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

            // pastikan posisi drag sebelumnya tidak terbawa; kembali ke tengah layar
            modalCard.style.left = '';
            modalCard.style.top = '';
            modalCard.style.transform = '';

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna';

            document.getElementById('form-name').value = data.name ? decodeURIComponent(data.name) : '';

            const role = data.role || 'mahasiswa';
            selectDropdownValue(document.getElementById('dd-role'), role);
            syncIdentifierField(role);
            inputIdentifier.value = data.identifier ? decodeURIComponent(data.identifier) : '';

            document.getElementById('form-password').value = '';
            document.getElementById('form-password').required = mode !== 'edit';

            modalBackdrop.classList.add('is-active');
            modalCard.classList.add('is-active');
            modalCard.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            closeAllDropdowns();
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

        // ---- Drag modal lewat header (pointer events -> jalan di mouse & touch) ----
        let dragState = null;

        dragHandle.addEventListener('pointerdown', (e) => {
            if (e.target.closest('.modal-close-btn')) return;
            closeAllDropdowns();
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

        // ---- Bangun/ganti/hapus baris tabel dari data JSON (tanpa reload) ----
        function initials(name) {
            return (name || '').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '-';
        }

        function avatarColor(userId) {
            const colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
            return colors[Number(userId) % colors.length];
        }

        function esc(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

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
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-name="${encodeURIComponent(item.name)}" data-role="${item.role}"
                            data-identifier="${encodeURIComponent(item.identifier || '')}">
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
            row.addEventListener('transitionend', () => {
                row.remove();
                applyFilters();
            }, {
                once: true
            });
        }

        // ---- Submit form (create / update) ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const payload = {
                name: document.getElementById('form-name').value,
                role: document.getElementById('form-role').value,
                identifier: document.getElementById('form-identifier').value,
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

        // ---- Delete ----
        async function handleDelete(id) {
            if (!confirm('Hapus data pengguna ini? Tindakan tidak bisa dibatalkan.')) return;
            try {
                const res = await fetch(`/data-master/users/${id}`, {
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

        // Inisialisasi tampilan filter saat halaman pertama kali dimuat
        applyFilters();
    </script>
</body>

</html>