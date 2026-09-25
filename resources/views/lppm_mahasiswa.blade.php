<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>LPPM Mahasiswa &middot; SIDA</title>
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
                <a href="{{ url('/lppm/mahasiswa') }}" aria-current="page" class="nav-link is-active">
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
                        <span class="link">LPPM</span>
                        <span>/</span>
                        <span class="current">Data Mahasiswa</span>
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

                                <div class="notif-panel-body" id="notifListWrap" hidden>
                                    {{--
                                        STRUKTUR ITEM NOTIFIKASI (referensi utk nanti, saat sudah connect ke controller):

                                        <div class="notif-group-label">Hari Ini</div>
                                        <a href="#" class="notif-item is-unread" data-id="1">
                                            <span class="notif-item-icon c-primary"><span class="material-symbols-outlined">workspace_premium</span></span>
                                            <span class="notif-item-body">
                                                <span class="notif-item-title">Judul notifikasi</span>
                                                <span class="notif-item-desc">Deskripsi singkat notifikasi.</span>
                                                <span class="notif-item-time">10 menit lalu</span>
                                            </span>
                                            <span class="notif-item-dot" aria-hidden="true"></span>
                                        </a>

                                        Ganti @forelse($notifications as $n) ... @endforelse di sini,
                                        lalu hapus atribut "hidden" pada div ini dan pada #notifEmpty di bawah
                                        (di-toggle sesuai $notifications->isEmpty()).
                                    --}}
                                </div>

                                {{-- Tampilan saat tidak ada notifikasi --}}
                                <div class="notif-empty" id="notifEmpty">
                                    <span class="material-symbols-outlined notif-empty-icon">notifications</span>
                                    <p class="notif-empty-title">Belum ada notifikasi</p>
                                    <p class="notif-empty-desc">Pemberitahuan baru akan muncul di sini.</p>
                                </div>

                                <div class="notif-panel-footer">
                                    <a href="#" class="notif-view-all">Lihat semua notifikasi</a>
                                </div>
                            </div>
                        </div>
                        <!-- ============ /NOTIFIKASI ============ -->
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">{{ $__user->name }}</span>
                                <span class="header-profile-role">{{ $__user->accessLabelFor('lppm_mahasiswa') }}</span>
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

                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>LPPM</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Data Mahasiswa</span>
                        </div>
                        <h1 class="page-title">Publikasi &amp; Conference Mahasiswa</h1>
                        <p class="page-subtitle">Pendataan luaran publikasi SINTA Nasional, Conference, dan Jurnal Internasional &middot; Tahun 2026</p>
                    </div>
                    <button type="button" class="btn-primary" id="btnTambahLppmMahasiswa">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Data Publikasi</span>
                    </button>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Publikasi</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary"><span class="material-symbols-outlined">library_books</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">SINTA Nasional</span>
                            <span class="stat-value">{{ $stats['sinta'] }}</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">local_library</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Conference Int.</span>
                            <span class="stat-value">{{ $stats['conference'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">groups</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Jurnal Internasional</span>
                            <span class="stat-value">{{ $stats['jurnal'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">public</span></div>
                    </div>
                </div>

                <!-- FILTER BAR (tampilan saja, belum disambung ke query) -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Jenis Publikasi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="sinta_nasional">SINTA Nasional</button>
                                    <button type="button" class="dropdown-option" data-value="conference_internasional">Conference Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="jurnal_internasional">Jurnal Internasional</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari judul penelitian atau nama penulis..." />
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
                            <h2 class="table-card-title">Daftar Rekap LPPM Mahasiswa</h2>
                            <p class="table-card-subtitle">Data luaran publikasi sesuai format LPPM 2026</p>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Penulis (Mahasiswa)</th>
                                    <th>Judul Publikasi</th>
                                    <th class="center">Jenis</th>
                                    <th class="center">Peringkat / Jurnal</th>
                                    <th class="center">Tahun</th>
                                    <th class="center">Bukti / DOI</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="lppmMahasiswaTableBody">
                                @php
                                $jenisBadge = [
                                'sinta_nasional' => ['label' => 'SINTA Nasional', 'class' => 'badge-info'],
                                'conference_internasional' => ['label' => 'Conference Int.', 'class' => 'badge-warning'],
                                'jurnal_internasional' => ['label' => 'Jurnal Int.', 'class' => 'badge-success'],
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @forelse($items as $item)
                                @php
                                $nama = optional($item->mahasiswa->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->mahasiswa_id % count($colors)];
                                $jb = $jenisBadge[$item->jenis] ?? ['label' => $item->jenis, 'class' => 'badge-neutral'];
                                $buktiLabel = $item->link_doi ? 'Link DOI' : 'Lihat Bukti';
                                $buktiIcon = $item->link_doi ? 'link' : 'cloud';
                                $buktiUrl = $item->link_doi ?: $item->bukti_kegiatan;
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}">
                                    <td><span class="nim-code">{{ $item->mahasiswa->nim ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td><span class="activity-title" title="{{ $item->judul }}">{{ $item->judul }}</span></td>
                                    <td class="center"><span class="badge {{ $jb['class'] }}">{{ $jb['label'] }}</span></td>
                                    <td class="center"><span class="badge badge-primary">{{ $item->peringkat ?: '-' }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $item->tahun }}</span></td>
                                    <td class="center">
                                        <a href="{{ $buktiUrl }}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                                            <span class="material-symbols-outlined">{{ $buktiIcon }}</span>
                                            <span>{{ $buktiLabel }}</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-mahasiswa_id="{{ $item->mahasiswa_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-judul="{{ urlencode($item->judul) }}"
                                                data-penulis="{{ urlencode($item->penulis) }}"
                                                data-nama_jurnal="{{ urlencode($item->nama_jurnal ?? '') }}"
                                                data-peringkat="{{ urlencode($item->peringkat ?? '') }}"
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
                                        Belum ada data. Klik "Tambah Data Publikasi" untuk mulai mengisi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>{{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }}</strong>
                            dari <strong>{{ $items->total() }}</strong> data publikasi LPPM mahasiswa
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit LPPM Mahasiswa ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="lppmMahasiswaModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Data Publikasi</h3>
                <p class="modal-subtitle">Isi detail publikasi ilmiah mahasiswa</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="lppmMahasiswaForm" class="modal-body">
            <input type="hidden" id="form-id" value="">

            <div class="field">
                <label class="field-label">Mahasiswa</label>
                <div class="dropdown" data-dropdown id="dd-mahasiswa">
                    <input type="hidden" id="form-mahasiswa_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih mahasiswa...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($mahasiswaList as $m)
                        <button type="button" class="dropdown-option" data-value="{{ $m->id }}">{{ $m->nim }} &mdash; {{ optional($m->user)->name ?? '-' }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Publikasi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="sinta_nasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">SINTA Nasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="sinta_nasional">SINTA Nasional</button>
                            <button type="button" class="dropdown-option" data-value="conference_internasional">Conference Internasional</button>
                            <button type="button" class="dropdown-option" data-value="jurnal_internasional">Jurnal Internasional</button>
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
                <label class="field-label" for="form-judul">Judul Publikasi</label>
                <div class="field-control">
                    <input id="form-judul" type="text" placeholder="Judul penelitian/publikasi" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-penulis">Penulis</label>
                <div class="field-control">
                    <input id="form-penulis" type="text" placeholder="Nama-nama penulis" required>
                </div>
            </div>

            <!-- Field khusus jurnal (sinta_nasional / jurnal_internasional) — tidak berlaku untuk conference -->
            <div class="field-row" id="field-jurnal-extra">
                <div class="field">
                    <label class="field-label" for="form-nama_jurnal">Nama Jurnal</label>
                    <div class="field-control">
                        <input id="form-nama_jurnal" type="text" placeholder="Nama jurnal">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-peringkat">Peringkat (S1-S4 / Q1-Q4)</label>
                    <div class="field-control">
                        <input id="form-peringkat" type="text" placeholder="Contoh: S2 atau Q3">
                    </div>
                </div>
            </div>

            <div class="field" id="field-link_doi">
                <label class="field-label" for="form-link_doi">Link DOI</label>
                <div class="field-control">
                    <input id="form-link_doi" type="url" placeholder="https://doi.org/...">
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

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        // ================================================================
        // Bagian ini KHUSUS halaman LPPM Mahasiswa:
        // endpoint API, bentuk baris tabel, field form, dan field kondisional.
        // Semua yang generik (dropdown, sidebar, dark mode, notifikasi,
        // modal buka/tutup/drag, live search) sudah ditangani oleh
        // public/js/script.js lewat objek global SIDA.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const { esc, initials, avatarColor } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('lppmMahasiswaTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('lppmMahasiswaModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('lppmMahasiswaForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahLppmMahasiswa');
        const dragHandle = document.getElementById('modalDragHandle');

        const fieldJurnalExtra = document.getElementById('field-jurnal-extra');
        const fieldLinkDoi = document.getElementById('field-link_doi');

        // ---- Field kondisional: hanya tampil untuk jenis publikasi jurnal ----
        function updateConditionalFields(jenis) {
            const isJurnal = jenis === 'sinta_nasional' || jenis === 'jurnal_internasional';
            fieldJurnalExtra.classList.toggle('hidden', !isJurnal);
            fieldLinkDoi.classList.toggle('hidden', !isJurnal);
        }
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((opt) => {
            opt.addEventListener('click', () => updateConditionalFields(opt.dataset.value));
        });

        // ---- Badge jenis publikasi (khusus halaman ini) ----
        const jenisBadge = {
            sinta_nasional: { label: 'SINTA Nasional', cls: 'badge-info' },
            conference_internasional: { label: 'Conference Int.', cls: 'badge-warning' },
            jurnal_internasional: { label: 'Jurnal Int.', cls: 'badge-success' },
        };

        // ---- Bangun HTML baris tabel dari data JSON (khusus halaman ini) ----
        function buildRowHTML(item) {
            const jb = jenisBadge[item.jenis] || { label: item.jenis, cls: 'badge-neutral' };
            const buktiUrl = item.link_doi || item.bukti_kegiatan;
            const buktiLabel = item.link_doi ? 'Link DOI' : 'Lihat Bukti';
            const buktiIcon = item.link_doi ? 'link' : 'cloud';
            return `
            <tr data-id="${item.id}" data-jenis="${item.jenis}">
                <td><span class="nim-code">${esc(item.nim)}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.mahasiswa_id)}">${esc(initials(item.nama))}</div>
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td><span class="activity-title" title="${esc(item.judul)}">${esc(item.judul)}</span></td>
                <td class="center"><span class="badge ${jb.cls}">${esc(jb.label)}</span></td>
                <td class="center"><span class="badge badge-primary">${esc(item.peringkat || '-')}</span></td>
                <td class="center"><span class="year-chip">${esc(item.tahun)}</span></td>
                <td class="center">
                    <a href="${esc(buktiUrl)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">${buktiIcon}</span><span>${buktiLabel}</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-mahasiswa_id="${item.mahasiswa_id}" data-jenis="${item.jenis}"
                            data-judul="${encodeURIComponent(item.judul)}" data-penulis="${encodeURIComponent(item.penulis)}"
                            data-nama_jurnal="${encodeURIComponent(item.nama_jurnal || '')}"
                            data-peringkat="${encodeURIComponent(item.peringkat || '')}"
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
        const { insertRow, updateRow, removeRow } = SIDA.table.create(tableBody, buildRowHTML);

        // ---- Live filter (khusus halaman ini: 1 dropdown + search) ----
        const applyFilters = SIDA.filter.setup({
            tableBody,
            dropdownFilterIds: ['filter-jenis'],
            searchInputId: 'filter-search',
            matches: (row) => {
                const jenisVal = document.getElementById('filter-jenis')?.value || 'semua';
                const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
                const matchesJenis = jenisVal === 'semua' || row.dataset.jenis === jenisVal;
                const matchesSearch = !searchVal || row.textContent.toLowerCase().includes(searchVal);
                return matchesJenis && matchesSearch;
            },
            emptyMessage: 'Tidak ada data yang cocok dengan filter.',
        });

        // ---- Modal: mekanisme buka/tutup/drag dari script.js ----
        const { open: openModalBase, close: closeModal } = SIDA.modal.attach({
            backdrop: modalBackdrop,
            card: modalCard,
            closeBtn: modalCloseBtn,
            cancelBtn: modalCancelBtn,
            dragHandle: dragHandle,
        });

        // ---- Isi form modal (khusus halaman ini) ----
        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Data Publikasi' : 'Tambah Data Publikasi';

            SIDA.dropdown.select(document.getElementById('dd-mahasiswa'), data.mahasiswa_id || '', 'Pilih mahasiswa...');
            const jenis = data.jenis || 'sinta_nasional';
            SIDA.dropdown.select(document.getElementById('dd-jenis'), jenis);
            updateConditionalFields(jenis);

            document.getElementById('form-tahun').value = data.tahun || 2026;
            document.getElementById('form-judul').value = data.judul ? decodeURIComponent(data.judul) : '';
            document.getElementById('form-penulis').value = data.penulis ? decodeURIComponent(data.penulis) : '';
            document.getElementById('form-nama_jurnal').value = data.nama_jurnal ? decodeURIComponent(data.nama_jurnal) : '';
            document.getElementById('form-peringkat').value = data.peringkat ? decodeURIComponent(data.peringkat) : '';
            document.getElementById('form-link_doi').value = data.link_doi ? decodeURIComponent(data.link_doi) : '';
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan ? decodeURIComponent(data.bukti_kegiatan) : '';

            openModalBase();
        }

        btnTambah?.addEventListener('click', () => openModal('create'));

        // ---- Submit form (create / update) — endpoint khusus halaman ini ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const payload = {
                mahasiswa_id: document.getElementById('form-mahasiswa_id').value,
                jenis: document.getElementById('form-jenis').value,
                judul: document.getElementById('form-judul').value,
                penulis: document.getElementById('form-penulis').value,
                nama_jurnal: document.getElementById('form-nama_jurnal').value || null,
                peringkat: document.getElementById('form-peringkat').value || null,
                link_doi: document.getElementById('form-link_doi').value || null,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
                tahun: document.getElementById('form-tahun').value,
            };

            if (!payload.mahasiswa_id) {
                modalError.textContent = 'Pilih mahasiswa terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/lppm/mahasiswa/${id}` : '/lppm/mahasiswa';
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
                applyFilters();
                closeModal();
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Delete — endpoint khusus halaman ini ----
        async function handleDelete(id) {
            if (!confirm('Hapus data ini? Tindakan tidak bisa dibatalkan.')) return;
            try {
                const res = await fetch(`/lppm/mahasiswa/${id}`, {
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

        // ---- Event delegation: tombol Edit & Hapus di tiap baris (termasuk baris baru) ----
        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-row');
            if (editBtn) return openModal('edit', editBtn.dataset);
            const delBtn = e.target.closest('.btn-delete-row');
            if (delBtn) handleDelete(delBtn.dataset.id);
        });
    </script>

</body>

</html>