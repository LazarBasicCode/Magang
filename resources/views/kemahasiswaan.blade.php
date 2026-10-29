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
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/delete-confirm.css') }}?v={{ @filemtime(public_path('css/delete-confirm.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <title>Kemahasiswaan &middot; SIDA</title>
</head>

<body>
    <!-- Definisi dark mode -->
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
        } catch (e) {}
    </script>
    <!-- Definisi ikon SIDA (dipakai di logo sidebar) -->
    <svg class="svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="sida-mark" viewBox="0 0 48 48">
                <clipPath id="sidaClip">
                    <circle cx="24" cy="24" r="22.5" />
                </clipPath>
                <image href="{{ asset('img/logo-prodi.png') }}" x="1.5" y="1.5" width="45" height="45" preserveAspectRatio="xMidYMid slice" clip-path="url(#sidaClip)" />
                <circle cx="24" cy="24" r="22.5" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5" />
            </symbol>
        </defs>
    </svg>

    @include('partials.sidebar', ['active' => 'kemahasiswaan'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'kemahasiswaan'])

        <main class="app-main">
            <div class="page-wrap is-loading" id="pageWrap" aria-busy="true" data-skeleton-min="500">

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Prestasi &amp; Kegiatan Mahasiswa</h1>
                        <p class="page-subtitle">Pendataan kegiatan akademik, non-akademik, inbis, dan kompetisi
                            &middot; Tahun Akademik 2025/2026 (Genap)</p>
                    </div>
                    @php
                    // Menu titik tiga (Unduh Template / Upload / Download / Cetak Laporan): hanya admin & superadmin.
                    // Download butuh akses readonly ke atas; Template & Upload butuh akses penuh.
                    $bulkUser = auth()->user();
                    $bulkLevel = $bulkUser->menuLevel('kemahasiswaan');
                    $bulkRole = in_array($bulkUser->role, ['admin', 'superadmin'], true);
                    $showBulk = $bulkRole && in_array($bulkLevel, ['readonly', 'penuh'], true);
                    $bulkWrite = $bulkRole && $bulkLevel === 'penuh';
                    @endphp
                    <div class="title-actions">
                        <button type="button" class="btn-primary" id="btnTambahKegiatan">
                            <span class="material-symbols-outlined">add</span>
                            <span>Tambah Kegiatan</span>
                        </button>
                        @if($showBulk)
                        @include('partials.bulk-menu', [
                        'bulkLabel' => 'Kemahasiswaan',
                        'bulkTemplate' => $bulkWrite ? route('kemahasiswaan.template') : null,
                        'bulkImport' => $bulkWrite ? route('kemahasiswaan.import') : null,
                        'bulkExport' => route('kemahasiswaan.export'),
                        'bulkPrint' => route('cetak.show', 'kemahasiswaan'),
                        ])
                        @endif
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Kegiatan</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">emoji_events</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Nasional</span>
                            <span class="stat-value">{{ $stats['nasional'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">flag</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Internasional</span>
                            <span class="stat-value">{{ $stats['internasional'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Unit Inbis</span>
                            <span class="stat-value">{{ $stats['inbis'] }}</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">storefront</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Jenis Prestasi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis_divisi" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis Prestasi</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis Prestasi</button>
                                    <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator
                                        Bisnis)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="kemahasiswaan">Kemahasiswaan</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label">Jenis Prestasi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis_prestasi" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis Prestasi</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis Prestasi</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="akademik">Akademik</button>
                                    <button type="button" class="dropdown-option" data-value="non_akademik">Non
                                        Akademik</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label">Skala</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-skala" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Skala</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Skala</button>
                                    <button type="button" class="dropdown-option" data-value="lokal">Lokal
                                        (Kota/Wilayah)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="nasional">Nasional (RI)</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional
                                        (Global)</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            @php $currentYear = now()->year; @endphp
                            <label class="field-label">Tahun Akademik</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tahun" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tahun</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Tahun</button>
                                    @for($year = $currentYear; $year >= 2023; $year--)
                                    <button type="button" class="dropdown-option" data-value="{{ $year }}">{{ $year }}</button>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari NIM/nama mahasiswa..." />
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
                            <h2 class="table-card-title">Daftar Rekap Prestasi Mahasiswa</h2>
                            <p class="table-card-subtitle">Data kegiatan terverifikasi sesuai format resmi SIM
                                Kemahasiswaan 2026</p>
                        </div>
                    </div>

                    @if($showBulk)
                    {{-- Bar aksi data terpilih (Export Excel / Cetak PDF / Batal pilih); muncul saat ada baris dicentang --}}
                    @include('partials.row-select-bar')
                    @endif

                    <div class="table-scroll is-scrollable" id="tableScroll">
                        <table class="data-table"
                            @if($showBulk)
                            data-selectable="kemahasiswaan"
                            data-select-export="{{ route('pilihan.export', 'kemahasiswaan') }}"
                            data-select-print="{{ route('pilihan.cetak', 'kemahasiswaan') }}"
                            @if($bulkWrite) data-select-delete="{{ route('pilihan.hapus', 'kemahasiswaan') }}" @endif
                            @if($bulkWrite) data-select-update="{{ route('pilihan.ubah', 'kemahasiswaan') }}" @endif
                            @endif>
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Mahasiswa</th>
                                    <th>Nama Kegiatan</th>
                                    <th class="center">Jenis Prestasi</th>
                                    <th class="center">Jenis Prestasi</th>
                                    <th class="center">Skala</th>
                                    <th class="center">Tahun</th>
                                    <th class="center">Bukti Kegiatan</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kegiatanTableBody">
                                @foreach($kegiatan as $item)
                                @php
                                $nama = optional($item->mahasiswa->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                $avatarColor = $colors[$item->mahasiswa_id % count($colors)];
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}" data-tab="{{ $item->tab }}" data-tingkat="{{ $item->tingkat }}" data-tahun="{{ $item->tahun }}">
                                    <td><span class="nim-code">{{ $item->mahasiswa->nim ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            @include('partials.avatar', ['initials' => $initials, 'color' => $avatarColor, 'url' => optional(optional($item->mahasiswa)->user)->avatar_url])
                                            <div class="student-name">
                                                <span class="name">{{ $nama }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="{{ $item->nama_kegiatan }}">{{ $item->nama_kegiatan }}</span>
                                        @include('partials.approval-chip', ['item' => $item])
                                    </td>
                                    <td class="center"><span class="plain-text">{{ $item->jenis }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $item->tab }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $item->tingkat }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $item->tahun }}</span></td>
                                    <td class="center">
                                        <a href="{{ $item->bukti_kegiatan }}" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">link</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-mahasiswa_id="{{ $item->mahasiswa_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-tab="{{ $item->tab }}"
                                                data-tingkat="{{ $item->tingkat }}"
                                                data-tahun="{{ $item->tahun }}"
                                                data-nama_kegiatan="{{ $item->nama_kegiatan }}"
                                                data-bukti_kegiatan="{{ $item->bukti_kegiatan }}">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row"
                                                data-id="{{ $item->id }}">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <tr id="emptyRow" {{ $kegiatan->total() === 0 ? '' : 'hidden' }}>
                                    <td colspan="9" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data kegiatan. Klik "Tambah Kegiatan" untuk mulai mengisi.
                                    </td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                    <tr>
                                    <td colspan="9"><span class="sk-bar"></span></td>
                                    </tr>
                                    @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">{{ $kegiatan->firstItem() ?? 0 }}&ndash;{{ $kegiatan->lastItem() ?? 0 }}</strong>
                            dari <strong id="footerTotalCount">{{ $kegiatan->total() }}</strong> data kegiatan mahasiswa
                        </div>
                        <nav class="pager" id="pager" aria-label="Navigasi halaman" hidden></nav>
                        @if($kegiatan->hasPages())
                        <noscript>{{ $kegiatan->links() }}</noscript>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit Kegiatan ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="kegiatanModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Kegiatan</h3>
                <p class="modal-subtitle">Isi detail prestasi/kegiatan mahasiswa</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="kegiatanForm" class="modal-body">
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
                    <label class="field-label">Jenis Prestasi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="kemahasiswaan" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Kemahasiswaan</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="kemahasiswaan">Kemahasiswaan</button>
                            <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator Bisnis)</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Jenis Prestasi</label>
                    <div class="dropdown" data-dropdown id="dd-tab">
                        <input type="hidden" id="form-tab" value="akademik" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Akademik</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="akademik">Akademik</button>
                            <button type="button" class="dropdown-option" data-value="non_akademik">Non Akademik</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Skala</label>
                    <div class="dropdown" data-dropdown id="dd-tingkat">
                        <input type="hidden" id="form-tingkat" value="lokal" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Lokal (Kota/Wilayah)</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="lokal">Lokal (Kota/Wilayah)</button>
                            <button type="button" class="dropdown-option" data-value="nasional">Nasional (RI)</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional (Global)</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-tahun">Tahun</label>
                    <div class="field-control">
                        <input id="form-tahun" type="number" min="2000" max="2100" value="{{ now()->year }}" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-nama_kegiatan">Nama Kegiatan</label>
                <div class="field-control">
                    <input id="form-nama_kegiatan" type="text" placeholder="Contoh: Juara 1 Kompetisi UI/UX Nasional" required>
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

    @if($showBulk && $bulkWrite)
    @include('partials.bulk-import-modal', [
    'bulkLabel' => 'Kemahasiswaan',
    'bulkImport' => route('kemahasiswaan.import'),
    'bulkTemplate' => route('kemahasiswaan.template'),
    ])
    @endif

    @if($showBulk && $bulkWrite)
    {{-- Modal Update Massal (data terpilih). Kolom NIM, Mahasiswa, Tahun & Bukti sengaja tidak ada: khas per baris. --}}
    @include('partials.bulk-update-modal', [
    'updateLabel' => 'Kemahasiswaan',
    'updateFields' => [
    ['name' => 'nama_kegiatan', 'label' => 'Nama Kegiatan', 'type' => 'text', 'placeholder' => 'Kosongkan = nama kegiatan tidak diubah', 'col' => 2, 'target' => '.activity-title'],
    ['name' => 'jenis', 'label' => 'Jenis Prestasi', 'type' => 'select', 'row' => 'a', 'col' => 3, 'target' => '.plain-text',
    'options' => ['kemahasiswaan' => 'Kemahasiswaan', 'inbis' => 'Inbis (Inkubator Bisnis)']],
    ['name' => 'tab', 'label' => 'Jenis Prestasi', 'type' => 'select', 'row' => 'a', 'col' => 4, 'target' => '.plain-text',
    'options' => ['akademik' => 'Akademik', 'non_akademik' => 'Non Akademik']],
    ['name' => 'tingkat', 'label' => 'Skala', 'type' => 'select', 'col' => 5, 'target' => '.plain-text',
    'options' => ['lokal' => 'Lokal (Kota/Wilayah)', 'nasional' => 'Nasional (RI)', 'internasional' => 'Internasional (Global)']],
    ],
    ])
    @endif

    <script src="{{ asset('js/approval-chip.js') }}?v={{ @filemtime(public_path('js/approval-chip.js')) }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script>
        // ================================================================
        // Bagian ini KHUSUS halaman Kemahasiswaan:
        // endpoint API, bentuk baris tabel, field form, dan filter tambahan.
        // Semua yang generik (dropdown, sidebar, dark mode, notifikasi,
        // modal buka/tutup/drag, live search) sudah ditangani oleh
        // public/js/script.js lewat objek global SIDA.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const {
            esc,
            initials,
            avatarColor
        } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('kegiatanTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('kegiatanModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('kegiatanForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahKegiatan');
        const dragHandle = document.getElementById('modalDragHandle');

        // ---- Bangun HTML baris tabel dari data JSON (khusus halaman ini) ----
        function buildRowHTML(item) {
            return `
            <tr data-id="${item.id}" data-jenis="${item.jenis}" data-tab="${item.tab}" data-tingkat="${item.tingkat}" data-tahun="${item.tahun}">
                <td><span class="nim-code">${esc(item.nim)}</span></td>
                <td>
                    <div class="student-cell">
                        ${SIDA.util.avatarHtml(item.nama, item.mahasiswa_id, item.avatar_url)}
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td><span class="activity-title" title="${esc(item.nama_kegiatan)}">${esc(item.nama_kegiatan)}</span>${window.apprChip ? window.apprChip(item) : ''}</td>
                <td class="center"><span class="plain-text">${esc(item.jenis)}</span></td>
                <td class="center"><span class="plain-text">${esc(item.tab)}</span></td>
                <td class="center"><span class="plain-text">${esc(item.tingkat)}</span></td>
                <td class="center"><span class="year-chip">${esc(item.tahun)}</span></td>
                <td class="center">
                    <a href="${esc(item.bukti_kegiatan)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">link</span><span>Lihat Bukti</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-mahasiswa_id="${item.mahasiswa_id}" data-jenis="${item.jenis}"
                            data-tab="${item.tab}" data-tingkat="${item.tingkat}" data-tahun="${item.tahun}"
                            data-nama_kegiatan="${esc(item.nama_kegiatan)}"
                            data-bukti_kegiatan="${esc(item.bukti_kegiatan)}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="${item.id}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </td>
            </tr>`.trim();
        }
        const {
            insertRow,
            updateRow,
            removeRow
        } = SIDA.table.create(tableBody, buildRowHTML);

        // ================================================================
        // FILTER, PENCARIAN & PAGINASI — diproses di SERVER (50 baris/halaman)
        // GET /kemahasiswaan?filter&q=&page= (JSON: items + meta) -> baris digambar buildRowHTML.
        // Tombol halaman digambar public/js/pager.js.
        // ================================================================
        const FILTERS = ['filter-jenis_divisi', 'filter-jenis_prestasi', 'filter-skala', 'filter-tahun'];
        const searchInput = document.getElementById('filter-search');
        const emptyRow = document.getElementById('emptyRow');
        const footerRange = document.getElementById('footerRange');
        const footerTotalCount = document.getElementById('footerTotalCount');
        let currentPage = {{ $kegiatan->currentPage() }};
        let requestSeq = 0;
        let searchTimer = null;

        const pager = Pager.create(document.getElementById('pager'), {
            onGo: (n) => {
                loadPage(n);
                document.querySelector('.table-card')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
        pager.render({{ $kegiatan->currentPage() }}, {{ max(1, $kegiatan->lastPage()) }});

        async function loadPage(page = currentPage) {
            const seq = ++requestSeq;
            const term = (searchInput?.value || '').trim();
            const params = new URLSearchParams({
                page: String(page)
            });
            let filtered = !!term;
            FILTERS.forEach((id) => {
                const v = document.getElementById(id)?.value || 'semua';
                if (v !== 'semua') {
                    params.set(id.replace('filter-', ''), v);
                    filtered = true;
                }
            });
            if (term) params.set('q', term);

            tableBody.classList.add('is-fetching');
            try {
                const res = await fetch('/kemahasiswaan?' + params, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (seq !== requestSeq) return;

                tableBody.querySelectorAll('tr[data-id]').forEach((r) => r.remove());
                tableBody.insertAdjacentHTML('afterbegin', data.items.map(buildRowHTML).join(''));
                currentPage = data.meta.page;
                emptyRow.hidden = data.meta.total !== 0;
                if (data.meta.total === 0) {
                    emptyRow.firstElementChild.textContent = filtered ?
                        'Tidak ada data yang cocok dengan filter.' :
                        'Belum ada data kegiatan. Klik "Tambah Kegiatan" untuk mulai mengisi.';
                }
                footerRange.textContent = data.meta.from + '–' + data.meta.to;
                footerTotalCount.textContent = data.meta.total;
                pager.render(data.meta.page, data.meta.last);
            } catch (err) {
                if (seq !== requestSeq) return;
                console.error('[Kemahasiswaan] Gagal memuat halaman:', err);
                window.Toast?.show?.({
                    type: 'error',
                    title: 'Gagal memuat data',
                    message: 'Tidak bisa memuat halaman ini. Coba lagi.'
                });
            } finally {
                if (seq === requestSeq) tableBody.classList.remove('is-fetching');
            }
        }

        // Cari (tunda 300ms), pilih dropdown, reset -> kembali ke halaman 1
        searchInput?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadPage(1), 300);
        });
        // setTimeout 0: tunggu script.js selesai mengisi nilai dropdown tersembunyi
        document.querySelectorAll('.filter-card .dropdown-option').forEach((opt) => opt.addEventListener('click', () => setTimeout(() => loadPage(1), 0)));
        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            FILTERS.forEach((id) => SIDA.dropdown.select(document.getElementById(id).closest('[data-dropdown]'), 'semua'));
            loadPage(1);
        });

        // Update massal: muat ulang halaman aktif supaya data & kolom turunan konsisten dengan server.
        SIDA.bulkUpdateApply = () => loadPage(currentPage);

        // ---- Modal: mekanisme buka/tutup/drag dari script.js ----
        const {
            open: openModalBase,
            close: closeModal
        } = SIDA.modal.attach({
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
            modalTitle.textContent = mode === 'edit' ? 'Edit Kegiatan' : 'Tambah Kegiatan';

            SIDA.dropdown.select(document.getElementById('dd-mahasiswa'), data.mahasiswa_id || '', 'Pilih mahasiswa...');
            SIDA.dropdown.select(document.getElementById('dd-jenis'), data.jenis || 'kemahasiswaan');
            SIDA.dropdown.select(document.getElementById('dd-tab'), data.tab || 'akademik');
            SIDA.dropdown.select(document.getElementById('dd-tingkat'), data.tingkat || 'lokal');

            document.getElementById('form-tahun').value = data.tahun || new Date().getFullYear();
            document.getElementById('form-nama_kegiatan').value = data.nama_kegiatan || '';
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan || '';

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
                tab: document.getElementById('form-tab').value,
                tingkat: document.getElementById('form-tingkat').value,
                tahun: document.getElementById('form-tahun').value,
                nama_kegiatan: document.getElementById('form-nama_kegiatan').value,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
            };

            if (!payload.mahasiswa_id) {
                modalError.textContent = 'Pilih mahasiswa terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/kemahasiswaan/${id}` : '/kemahasiswaan';
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

                closeModal();
                await loadPage(id ? currentPage : 1); // data baru tampil di halaman 1 (terbaru dulu)
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Delete — endpoint khusus halaman ini ----
        async function handleDelete(id, name) {
            const ok = await DeleteConfirm.ask({
                name,
                entity: 'data'
            });
            if (!ok) return;
            try {
                const res = await fetch(`/kemahasiswaan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                });
                const result = await res.json();
                if (res.ok && result.success) loadPage(currentPage);
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
    <script src="{{ asset('js/delete-confirm.js') }}?v={{ @filemtime(public_path('js/delete-confirm.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @if($showBulk)
    <script src="{{ asset('js/bulk-import.js') }}?v={{ @filemtime(public_path('js/bulk-import.js')) }}"></script>
    <script src="{{ asset('js/row-select.js') }}?v={{ @filemtime(public_path('js/row-select.js')) }}"></script>
    @endif
    @if($showBulk && $bulkWrite)
    <script src="{{ asset('js/bulk-update.js') }}?v={{ @filemtime(public_path('js/bulk-update.js')) }}"></script>
    @endif
</body>

</html>