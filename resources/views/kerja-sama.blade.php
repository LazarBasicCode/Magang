<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/lock-swap.css') }}?v={{ @filemtime(public_path('css/lock-swap.css')) }}">
    <script>
        (function() {
            try {
                var w = parseInt(localStorage.getItem('sida.sidebarW'), 10);
                if (w) document.documentElement.style.setProperty('--sidebar-w', (w < 140 ? 72 : Math.min(340, Math.max(200, w))) + 'px');
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/delete-confirm.css') }}?v={{ @filemtime(public_path('css/delete-confirm.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <title>Kerja Sama &middot; SIDA</title>
</head>

<body>
    <!-- Definisi dark mode -->
    <script> try { if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {} </script>
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

    @include('partials.sidebar', ['active' => 'kerja_sama'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'kerja_sama'])

        <main class="app-main">
            <div class="page-wrap is-loading" id="pageWrap" aria-busy="true">

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Kerja Sama &amp; Kemitraan</h1>
                        <p class="page-subtitle">Pendataan kerja sama mahasiswa, dosen, guest lecture, pengabdian &amp; research internasional &middot; Tahun {{ now()->year }}</p>
                    </div>
                    @php
                        // Menu titik tiga (Unduh Template / Upload / Download / Cetak Laporan): hanya admin & superadmin.
                        // Download butuh akses readonly ke atas; Template & Upload butuh akses penuh.
                        $bulkUser = auth()->user();
                        $bulkLevel = $bulkUser->menuLevel('kerja_sama');
                        $bulkRole = in_array($bulkUser->role, ['admin', 'superadmin'], true);
                        $showBulk = $bulkRole && in_array($bulkLevel, ['readonly', 'penuh'], true);
                        $bulkWrite = $bulkRole && $bulkLevel === 'penuh';
                    @endphp
                    <div class="title-actions">
                        <button type="button" class="btn-primary" id="btnTambahKerjaSama">
                            <span class="material-symbols-outlined">add</span>
                            <span>Tambah Kerja Sama</span>
                        </button>
                        @if($showBulk)
                            @include('partials.bulk-menu', [
                                'bulkLabel'    => 'Kerja Sama',
                                'bulkTemplate' => $bulkWrite ? route('kerja-sama.template') : null,
                                'bulkImport'   => $bulkWrite ? route('kerja-sama.import') : null,
                                'bulkExport'   => route('kerja-sama.export'),
                                'bulkPrint'    => route('cetak.show', 'kerja-sama'),
                            ])
                        @endif
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Kerja Sama</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">handshake</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Mahasiswa</span>
                            <span class="stat-value">{{ $stats['mahasiswa'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">school</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Dosen</span>
                            <span class="stat-value">{{ $stats['dosen'] }}</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">co_present</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Internasional</span>
                            <span class="stat-value">{{ $stats['internasional'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR (tampilan saja, belum disambung ke query) -->
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
                                    <button type="button" class="dropdown-option" data-value="lainnya">Lainnya</button>
                                </div>
                            </div>
                        </div>

                        <!-- Arah (untuk Guest Lecture) -->
                        <div class="field" id="field-filter-arah">
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
                                    <span class="field-locked-value">{{ now()->year }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari judul kegiatan atau mitra..." />
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
                            <h2 class="table-card-title">Daftar Rekap Kerja Sama</h2>
                            <p class="table-card-subtitle">Data kerja sama sesuai format Kemitraan 2026</p>
                        </div>
                    </div>

                    @if($showBulk)
                        {{-- Bar aksi data terpilih (Export Excel / Cetak PDF / Update / Hapus / Batal pilih); muncul saat ada baris dicentang --}}
                        @include('partials.row-select-bar')
                    @endif

                    <div class="table-scroll is-scrollable" id="tableScroll">
                        <table class="data-table"
                            @if($showBulk)
                                data-selectable="kerja-sama"
                                data-select-export="{{ route('pilihan.export', 'kerja-sama') }}"
                                data-select-print="{{ route('pilihan.cetak', 'kerja-sama') }}"
                                @if($bulkWrite) data-select-delete="{{ route('pilihan.hapus', 'kerja-sama') }}" @endif
                                @if($bulkWrite) data-select-update="{{ route('pilihan.ubah', 'kerja-sama') }}" @endif
                            @endif>
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
                            <tbody id="kerjaSamaTableBody">
                                @php
                                $jenisBadge = [
                                'conference_internasional' => ['label' => 'Conference Int.', 'class' => 'badge-info'],
                                'pkl' => ['label' => 'PKL', 'class' => 'badge-primary'],
                                'sharing_session' => ['label' => 'Sharing Session', 'class' => 'badge-warning'],
                                'keynote_session' => ['label' => 'Keynote Speaker', 'class' => 'badge-success'],
                                'guest_lecture' => ['label' => 'Guest Lecture', 'class' => 'badge-info'],
                                'pengabdian_internasional' => ['label' => 'Pengabdian Int.', 'class' => 'badge-danger'],
                                'research_internasional' => ['label' => 'Research Int.', 'class' => 'badge-neutral'],
                                'lainnya' => ['label' => 'Lainnya', 'class' => 'badge-neutral'],
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @foreach($items as $item)
                                @php
                                $nama = $item->user->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->user_id % count($colors)];
                                $jb = $jenisBadge[$item->jenis] ?? ['label' => $item->jenis, 'class' => 'badge-neutral'];
                                if ($item->jenis === 'lainnya' && $item->jenis_lainnya) {
                                $jb['label'] = $item->jenis_lainnya;
                                }
                                $periode = optional($item->tanggal_mulai)->translatedFormat('d M Y') . ' - ' . optional($item->tanggal_selesai)->translatedFormat('d M Y');
                                @endphp
                                <tr data-id="{{ $item->id }}" data-tipe_user="{{ $item->tipe_user }}" data-jenis="{{ $item->jenis }}" data-arah="{{ $item->arah }}">
                                    <td><span class="nim-code">{{ $item->user->nim_nidn ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title" title="{{ $item->judul_kegiatan }}">{{ $item->judul_kegiatan }}</span>
                                        @include('partials.approval-chip', ['item' => $item])
                                    </td>
                                    <td class="center"><span class="badge {{ $jb['class'] }}">{{ $jb['label'] }}</span></td>
                                    <td class="center"><span class="badge badge-neutral">{{ ucfirst($item->tipe_user) }}</span></td>
                                    <td class="center">
                                        @if($item->arah)
                                        <span class="badge badge-primary">{{ ucfirst($item->arah) }}</span>
                                        @else
                                        <span class="plain-text">-</span>
                                        @endif
                                    </td>
                                    <td class="center"><span class="plain-text">{{ $item->mitra }}</span></td>
                                    <td class="center"><span class="year-chip">{{ $periode }}</span></td>
                                    <td class="center">
                                        <a href="{{ $item->bukti_kegiatan }}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                                            <span class="material-symbols-outlined">link</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-user_id="{{ $item->user_id }}"
                                                data-tipe_user="{{ $item->tipe_user }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-jenis_lainnya="{{ $item->jenis_lainnya ?? '' }}"
                                                data-arah="{{ $item->arah }}"
                                                data-mitra="{{ $item->mitra }}"
                                                data-judul_kegiatan="{{ $item->judul_kegiatan }}"
                                                data-tanggal_mulai="{{ optional($item->tanggal_mulai)->format('Y-m-d') }}"
                                                data-tanggal_selesai="{{ optional($item->tanggal_selesai)->format('Y-m-d') }}"
                                                data-bukti_kegiatan="{{ $item->bukti_kegiatan }}">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row" data-id="{{ $item->id }}">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <tr id="emptyRow" {{ $items->total() === 0 ? '' : 'hidden' }}>
                                    <td colspan="10" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data kerja sama. Klik "Tambah Kerja Sama" untuk mulai mengisi.
                                    </td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                <tr><td colspan="11"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER -->
                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">{{ $items->firstItem() ?? 0 }}&ndash;{{ $items->lastItem() ?? 0 }}</strong>
                            dari <strong id="footerTotalCount">{{ $items->total() }}</strong> data kerja sama tahun <span class="highlight">{{ now()->year }}</span>
                        </div>
                        <nav class="pager" id="pager" aria-label="Navigasi halaman" hidden></nav>
                        @if($items->hasPages())
                        <noscript>{{ $items->links() }}</noscript>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Tambah / Edit Kerja Sama ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="kerjaSamaModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Kerja Sama</h3>
                <p class="modal-subtitle">Isi detail kerja sama &amp; kemitraan mahasiswa/dosen</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="kerjaSamaForm" class="modal-body">
            <input type="hidden" id="form-id" value="">
            <input type="hidden" id="form-tipe_user" value="">

            <div class="field">
                <label class="field-label">Mahasiswa / Dosen</label>
                <div class="dropdown" data-dropdown id="dd-user">
                    <input type="hidden" id="form-user_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih mahasiswa/dosen...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($userList as $u)
                        <button type="button" class="dropdown-option" data-value="{{ $u->id }}" data-role="{{ $u->role }}">{{ $u->nim_nidn ?? '-' }} &mdash; {{ $u->name }} ({{ ucfirst($u->role) }})</button>
                        @endforeach
                    </div>
                </div>
                <div class="field-locked" id="userLocked" style="display:none;">
                    <div class="field-locked-inner">
                        <span class="material-symbols-outlined">lock</span>
                        <span class="field-locked-value" id="userLockedValue"></span>
                    </div>
                </div>
            </div>

            {{-- Jenis Kerja Sama: selebar dropdown Mahasiswa/Dosen. Di bawahnya: "Lainnya" & "Arah" (selalu ada, terkunci sampai relevan). --}}
            <div class="field">
                <label class="field-label">Jenis Kerja Sama</label>
                <div class="dropdown" data-dropdown id="dd-jenis">
                    <input type="hidden" id="form-jenis" value="conference_internasional" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Conference Internasional</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="conference_internasional">Conference Internasional</button>
                        <button type="button" class="dropdown-option" data-value="pkl">PKL (Output)</button>
                        <button type="button" class="dropdown-option" data-value="sharing_session">Sharing Session</button>
                        <button type="button" class="dropdown-option" data-value="keynote_session">Keynote Speaker</button>
                        <button type="button" class="dropdown-option" data-value="guest_lecture">Guest Lecture</button>
                        <button type="button" class="dropdown-option" data-value="pengabdian_internasional">Pengabdian Internasional</button>
                        <button type="button" class="dropdown-option" data-value="research_internasional">Research Internasional</button>
                        <button type="button" class="dropdown-option" data-value="lainnya">Lainnya</button>
                    </div>
                </div>
            </div>

            {{-- Mahasiswa tidak punya Guest Lecture -> Arah menghilang & "Lainnya" melebar penuh (animasi: public/css/lock-swap.css, .field-row-swap) --}}
            <div class="field-row field-row-swap" id="rowLainnyaArah">
                <div class="field" id="field-lainnya">
                    <label class="field-label" for="form-jenis_lainnya">Jenis Kerja Sama Lainnya</label>
                    {{-- Selalu tampil: terkunci kecuali Jenis = Lainnya (animasi: public/js/lock-swap.js) --}}
                    <div class="lock-swap is-locked" id="lainnyaSwap">
                        <div class="field-control lock-swap-input">
                            <input id="form-jenis_lainnya" type="text" maxlength="255" placeholder="Tulis jenis kerja sama..." disabled>
                        </div>
                        <div class="field-locked lock-swap-locked" aria-live="polite">
                            <div class="field-locked-inner">
                                <span class="material-symbols-outlined lock-ic">lock</span>
                                <span class="field-locked-value">Tulis jenis kerja sama..."</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field field-collapsible" id="field-arah">
                    <label class="field-label">Arah (khusus Guest Lecture)</label>
                    {{-- Selalu tampil: terkunci kecuali Jenis = Guest Lecture (animasi: public/js/lock-swap.js) --}}
                    <div class="lock-swap is-locked" id="arahSwap">
                        <div class="lock-swap-input">
                            <div class="dropdown" data-dropdown id="dd-arah">
                                <input type="hidden" id="form-arah" value="inbound" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Inbound</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="inbound">Inbound</button>
                                    <button type="button" class="dropdown-option" data-value="outbound">Outbound</button>
                                </div>
                            </div>
                        </div>
                        <div class="field-locked lock-swap-locked" aria-live="polite">
                            <div class="field-locked-inner">
                                <span class="material-symbols-outlined lock-ic">lock</span>
                                <span class="field-locked-value">Kusus Guest Lecture</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-judul_kegiatan">Judul Kegiatan</label>
                <div class="field-control">
                    <input id="form-judul_kegiatan" type="text" placeholder="Judul kegiatan kerja sama" required>
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="form-mitra">Mitra (Institusi/Perusahaan/Negara)</label>
                <div class="field-control">
                    <input id="form-mitra" type="text" placeholder="Nama mitra kerja sama" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Tanggal Mulai</label>
                    <div class="datepicker" data-datepicker id="dp-tanggal_mulai">
                        <input type="hidden" id="form-tanggal_mulai" value="" required />
                        <button type="button" class="datepicker-trigger">
                            <span class="material-symbols-outlined dp-icon">calendar_month</span>
                            <span class="datepicker-value">Pilih tanggal</span>
                        </button>
                        <div class="datepicker-panel">
                            <div class="dp-header">
                                <button type="button" class="dp-nav dp-prev" aria-label="Bulan sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button>
                                <span class="dp-month-label"></span>
                                <button type="button" class="dp-nav dp-next" aria-label="Bulan berikutnya"><span class="material-symbols-outlined">chevron_right</span></button>
                            </div>
                            <div class="dp-weekdays"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
                            <div class="dp-days"></div>
                            <div class="dp-footer">
                                <button type="button" class="dp-today-btn">Hari ini</button>
                                <button type="button" class="dp-clear-btn">Bersihkan</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Tanggal Selesai</label>
                    <div class="datepicker" data-datepicker id="dp-tanggal_selesai">
                        <input type="hidden" id="form-tanggal_selesai" value="" required />
                        <button type="button" class="datepicker-trigger">
                            <span class="material-symbols-outlined dp-icon">calendar_month</span>
                            <span class="datepicker-value">Pilih tanggal</span>
                        </button>
                        <div class="datepicker-panel">
                            <div class="dp-header">
                                <button type="button" class="dp-nav dp-prev" aria-label="Bulan sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button>
                                <span class="dp-month-label"></span>
                                <button type="button" class="dp-nav dp-next" aria-label="Bulan berikutnya"><span class="material-symbols-outlined">chevron_right</span></button>
                            </div>
                            <div class="dp-weekdays"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
                            <div class="dp-days"></div>
                            <div class="dp-footer">
                                <button type="button" class="dp-today-btn">Hari ini</button>
                                <button type="button" class="dp-clear-btn">Bersihkan</button>
                            </div>
                        </div>
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

    @if($showBulk && $bulkWrite)
        @include('partials.bulk-import-modal', [
            'bulkLabel'    => 'Kerja Sama',
            'bulkImport'   => route('kerja-sama.import'),
            'bulkTemplate' => route('kerja-sama.template'),
        ])
    @endif

    @if($showBulk && $bulkWrite)
        {{-- Modal Update Massal (data terpilih). Kolom NIM/NIDN, Nama, Tipe User, Periode & Bukti sengaja tidak ada: khas per baris. --}}
        @include('partials.bulk-update-modal', [
            'updateLabel'  => 'Kerja Sama',
            'updateFields' => [
                ['name' => 'jenis',          'label' => 'Jenis Kerja Sama',  'type' => 'select', 'row' => 'a',
                    'optionsBy' => ['attr' => 'tipe_user', 'allowed' => $jenisByTipe],
                    'options' => [
                        'conference_internasional' => 'Conference Internasional',
                        'pkl'                      => 'PKL (Output)',
                        'sharing_session'          => 'Sharing Session',
                        'keynote_session'          => 'Keynote Speaker',
                        'guest_lecture'            => 'Guest Lecture',
                        'pengabdian_internasional' => 'Pengabdian Internasional',
                        'research_internasional'   => 'Research Internasional',
                        'lainnya'                  => 'Lainnya',
                    ]],
                ['name' => 'arah',           'label' => 'Arah (Guest Lecture)', 'type' => 'select', 'row' => 'a',
                    'showWhen' => ['jenis' => ['guest_lecture']],
                    'options' => ['inbound' => 'Inbound', 'outbound' => 'Outbound']],
                ['name' => 'jenis_lainnya',  'label' => 'Jenis Lainnya',      'type' => 'text',
                    'showWhen' => ['jenis' => ['lainnya']]],
                ['name' => 'judul_kegiatan', 'label' => 'Judul Kegiatan',     'type' => 'text'],
                ['name' => 'mitra',          'label' => 'Mitra',              'type' => 'text'],
            ],
        ])
    @endif

    <script src="{{ asset('js/approval-chip.js') }}?v={{ @filemtime(public_path('js/approval-chip.js')) }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/lock-swap.js') }}?v={{ @filemtime(public_path('js/lock-swap.js')) }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script>
        // ================================================================
        // KHUSUS halaman Kerja Sama.
        // Semua logika spesifik halaman ini dipertahankan 100%.
        // Yang generik (sidebar, dark mode, profile, modal attach,
        // util csrfToken/esc/initials/avatarColor) dipindah ke script.js.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const {
            esc,
            initials,
            avatarColor
        } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('kerjaSamaTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('kerjaSamaModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('kerjaSamaForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahKerjaSama');
        const dragHandle = document.getElementById('modalDragHandle');
        const ddUser = document.getElementById('dd-user');

        // Datepicker (Tanggal Mulai & Selesai) sudah ada di script.js: SIDA.datepicker
        const { start: dpMulai, end: dpSelesai } = SIDA.datepicker.pair(
            document.getElementById('dp-tanggal_mulai'),
            document.getElementById('dp-tanggal_selesai'),
        );

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            document.querySelectorAll('.filter-grid [data-dropdown]').forEach((d) => SIDA.dropdown.reset(d));
            const search = document.getElementById('filter-search');
            if (search) search.value = '';
            updateFilterArahVisibility();
            applyFilters();
        });

        // ----------------------------------------------------------------
        // FILTER KONDISIONAL ARAH
        // ----------------------------------------------------------------
        const fieldFilterArah = document.getElementById('field-filter-arah');

        function updateFilterArahVisibility() {
            const jenisVal = document.getElementById('filter-jenis')?.value || 'semua';
            const isGuestLecture = jenisVal === 'guest_lecture';
            fieldFilterArah.classList.toggle('hidden', !isGuestLecture);
            fieldFilterArah.style.display = isGuestLecture ? '' : 'none';
            if (!isGuestLecture) {
                SIDA.dropdown.reset(fieldFilterArah.querySelector('[data-dropdown]'));
            }
        }

        // ================================================================
        // FILTER, PENCARIAN & PAGINASI — diproses di SERVER (50 baris/halaman)
        // GET /kerja-sama?filter&q=&page= (JSON: items + meta) -> baris digambar buildRowHTML.
        // Tombol halaman digambar public/js/pager.js.
        // ================================================================
        const FILTERS = ['filter-tipe-user','filter-jenis','filter-arah'];
        const searchInput = document.getElementById('filter-search');
        const emptyRow = document.getElementById('emptyRow');
        const emptyDefault = emptyRow.firstElementChild.textContent.trim();
        const footerRange = document.getElementById('footerRange');
        const footerTotalCount = document.getElementById('footerTotalCount');
        let currentPage = {{ $items->currentPage() }};
        let requestSeq = 0;
        let searchTimer = null;

        const pager = Pager.create(document.getElementById('pager'), { onGo: (n) => { loadPage(n); document.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); } });
        pager.render({{ $items->currentPage() }}, {{ max(1, $items->lastPage()) }});

        async function loadPage(page = currentPage) {
            const seq = ++requestSeq;
            const term = (searchInput?.value || '').trim();
            const params = new URLSearchParams({ page: String(page) });
            let filtered = !!term;
            FILTERS.forEach((id) => {
                const v = document.getElementById(id)?.value || 'semua';
                if (v !== 'semua') { params.set(id.replace('filter-', '').replace(/-/g, '_'), v); filtered = true; }
            });
            if (term) params.set('q', term);

            tableBody.classList.add('is-fetching');
            try {
                const res = await fetch('/kerja-sama?' + params, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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
                    emptyRow.firstElementChild.textContent = filtered ? 'Tidak ada data yang cocok dengan filter.' : emptyDefault;
                }
                footerRange.textContent = data.meta.from + '–' + data.meta.to;
                footerTotalCount.textContent = data.meta.total;
                pager.render(data.meta.page, data.meta.last);
            } catch (err) {
                if (seq !== requestSeq) return;
                console.error('[Kerja Sama] Gagal memuat halaman:', err);
                window.Toast?.show?.({ type: 'error', title: 'Gagal memuat data', message: 'Tidak bisa memuat halaman ini. Coba lagi.' });
            } finally {
                if (seq === requestSeq) tableBody.classList.remove('is-fetching');
            }
        }

        // Update massal: muat ulang halaman aktif supaya data & kolom turunan konsisten dengan server.
        SIDA.bulkUpdateApply = () => loadPage(currentPage);

        // Dipanggil listener filter di bawah (dropdown/reset) -> server, mulai dari halaman 1
        function applyFilters() { loadPage(1); }

        // Listener filter — set nilai manual supaya tidak bergantung script.js
        document.querySelectorAll('#filter-tipe-user, #filter-jenis, #filter-arah').forEach((input) => {
            const dropdownEl = input.closest('[data-dropdown]');
            dropdownEl?.querySelectorAll('.dropdown-option').forEach((opt) => {
                opt.addEventListener('click', () => {
                    // Set nilai manual — supaya filter baca nilai terbaru
                    // tanpa bergantung pada listener script.js.
                    if (input) input.value = opt.dataset.value;
                    if (input.id === 'filter-jenis') updateFilterArahVisibility();
                    applyFilters();
                });
            });
        });

        // Set kondisi awal (default jenis = semua -> field arah disembunyikan)
        updateFilterArahVisibility();

        // ----------------------------------------------------------------
        // BATASI "JENIS" SESUAI "TIPE USER" DI FILTER (business rule)
        // ----------------------------------------------------------------
        const JENIS_BY_TIPE = @json($jenisByTipe); // sumber: KerjaSamaController::JENIS_BY_TIPE

        function syncFilterJenisOptions() {
            const tipe = document.getElementById('filter-tipe-user')?.value || 'semua';
            const allowed = JENIS_BY_TIPE[tipe];
            const jenisDropdown = document.getElementById('filter-jenis')?.closest('[data-dropdown]');
            if (!jenisDropdown) return;

            jenisDropdown.querySelectorAll('.dropdown-option').forEach((opt) => {
                const v = opt.dataset.value;
                const show = v === 'semua' || !allowed || allowed.includes(v);
                opt.style.display = show ? '' : 'none';
            });

            const current = document.getElementById('filter-jenis').value;
            if (current !== 'semua' && allowed && !allowed.includes(current)) {
                SIDA.dropdown.reset(jenisDropdown);
                updateFilterArahVisibility();
                applyFilters();
            }
        }

        document.getElementById('filter-tipe-user')?.closest('[data-dropdown]')
            ?.querySelectorAll('.dropdown-option')
            .forEach((opt) => opt.addEventListener('click', () => {
                const input = document.getElementById('filter-tipe-user');
                if (input) input.value = opt.dataset.value;
                setTimeout(syncFilterJenisOptions, 0);
            }));

        syncFilterJenisOptions();

        let searchDebounce;
        document.getElementById('filter-search')?.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(applyFilters, 300);
        });

        // ----------------------------------------------------------------
        // MODAL
        // ----------------------------------------------------------------
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

        // Kolom "Jenis Lainnya" & "Arah" selalu tampil, tapi terkunci kecuali jenisnya cocok.
        const lainnyaLock = LockSwap.create(document.getElementById('lainnyaSwap'));
        const arahLock = LockSwap.create(document.getElementById('arahSwap'), { focus: false });

        function updateConditionalFields(jenis, animate = false) {
            const isLainnya = jenis === 'lainnya';
            lainnyaLock.setLocked(!isLainnya, animate);
            if (!isLainnya) document.getElementById('form-jenis_lainnya').value = '';

            const isGuestLecture = jenis === 'guest_lecture';
            arahLock.setLocked(!isGuestLecture, animate);
            if (!isGuestLecture) {
                SIDA.dropdown.select(document.getElementById('dd-arah'), 'inbound', 'Inbound');
            }
        }
        document.querySelectorAll('#dd-jenis .dropdown-option').forEach((opt) => {
            opt.addEventListener('click', () => updateConditionalFields(opt.dataset.value, true));
        });

        // Mahasiswa tidak punya jenis Guest Lecture: kolom Arah dihilangkan dan "Lainnya" melebar penuh ke samping.
        // animate=false -> langsung (saat modal dibuka); true -> dengan animasi (saat user dipilih).
        const rowLainnyaArah = document.getElementById('rowLainnyaArah');
        function syncArahForRole(animate) {
            const solo = document.getElementById('form-tipe_user').value === 'mahasiswa';
            if (rowLainnyaArah.classList.contains('is-solo') === solo) return;
            if (!animate) rowLainnyaArah.classList.add('is-instant');
            rowLainnyaArah.classList.toggle('is-solo', solo);
            document.getElementById('field-arah').setAttribute('aria-hidden', String(solo));
            if (!animate) {
                void rowLainnyaArah.offsetWidth; // terapkan tanpa transisi
                rowLainnyaArah.classList.remove('is-instant');
            }
        }

        // Saat memilih mahasiswa/dosen → set tipe_user + sync jenis modal
        document.querySelectorAll('#dd-user .dropdown-option').forEach((opt) => {
            opt.addEventListener('click', () => {
                document.getElementById('form-tipe_user').value = opt.dataset.role || '';
                syncFormJenisOptions();
                syncArahForRole(true);
            });
        });

        // ----------------------------------------------------------------
        // BATASI "JENIS" DI MODAL SESUAI ROLE USER (business rule)
        // ----------------------------------------------------------------
        function syncFormJenisOptions() {
            const role = document.getElementById('form-tipe_user').value;
            const allowed = JENIS_BY_TIPE[role];
            const dd = document.getElementById('dd-jenis');
            dd.querySelectorAll('.dropdown-option').forEach((opt) => {
                opt.style.display = (!allowed || allowed.includes(opt.dataset.value)) ? '' : 'none';
            });
            const current = document.getElementById('form-jenis').value;
            if (allowed && !allowed.includes(current)) {
                SIDA.dropdown.select(dd, allowed[0]);
                updateConditionalFields(allowed[0], true);
            }
        }

        // Mode edit: dropdown user → tampilan terkunci
        function setUserLocked(locked, label = '') {
            document.getElementById('dd-user').style.display = locked ? 'none' : '';
            const box = document.getElementById('userLocked');
            box.style.display = locked ? '' : 'none';
            document.getElementById('userLockedValue').textContent = label;
        }

        function openModal(mode, data = {}) {
            modalForm.reset();
            modalError.hidden = true;

            document.getElementById('form-id').value = data.id || '';
            modalTitle.textContent = mode === 'edit' ? 'Edit Kerja Sama' : 'Tambah Kerja Sama';

            SIDA.dropdown.select(ddUser, data.user_id || '', 'Pilih mahasiswa/dosen...');
            document.getElementById('form-tipe_user').value = data.tipe_user || '';
            syncArahForRole(false);

            // Edit: kunci nama user. Tambah: dropdown normal.
            const selectedOpt = ddUser.querySelector('.dropdown-option.is-selected');
            setUserLocked(mode === 'edit', selectedOpt ? selectedOpt.textContent.trim() : '');

            const jenis = data.jenis || 'conference_internasional';
            SIDA.dropdown.select(document.getElementById('dd-jenis'), jenis);
            updateConditionalFields(jenis);
            syncFormJenisOptions();
            document.getElementById('form-jenis_lainnya').value = data.jenis_lainnya || '';

            SIDA.dropdown.select(document.getElementById('dd-arah'), data.arah || 'inbound', 'Inbound');

            document.getElementById('form-judul_kegiatan').value = data.judul_kegiatan || '';
            document.getElementById('form-mitra').value = data.mitra || '';
            dpMulai.setValue(data.tanggal_mulai || '');
            dpSelesai.setMinDate(data.tanggal_mulai || null);
            dpSelesai.setValue(data.tanggal_selesai || '');
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan || '';

            openModalBase();
        }

        btnTambah?.addEventListener('click', () => openModal('create'));

        // ----------------------------------------------------------------
        // BUILD / INSERT / UPDATE / REMOVE ROW
        // ----------------------------------------------------------------
        const jenisBadge = {
            lainnya: {
                label: 'Lainnya',
                cls: 'badge-neutral'
            },
            conference_internasional: {
                label: 'Conference Int.',
                cls: 'badge-info'
            },
            pkl: {
                label: 'PKL',
                cls: 'badge-primary'
            },
            sharing_session: {
                label: 'Sharing Session',
                cls: 'badge-warning'
            },
            keynote_session: {
                label: 'Keynote Speaker',
                cls: 'badge-success'
            },
            guest_lecture: {
                label: 'Guest Lecture',
                cls: 'badge-info'
            },
            pengabdian_internasional: {
                label: 'Pengabdian Int.',
                cls: 'badge-danger'
            },
            research_internasional: {
                label: 'Research Int.',
                cls: 'badge-neutral'
            },
        };

        function formatTanggal(iso) {
            if (!iso) return '-';
            const d = new Date(iso + 'T00:00:00');
            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function ucfirst(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        }

        function buildRowHTML(item) {
            const jb = {
                ...(jenisBadge[item.jenis] || {
                    label: item.jenis,
                    cls: 'badge-neutral'
                })
            };
            if (item.jenis === 'lainnya' && item.jenis_lainnya) jb.label = item.jenis_lainnya;
            const arahHTML = item.arah ? `<span class="badge badge-primary">${esc(ucfirst(item.arah))}</span>` : '<span class="plain-text">-</span>';
            const periode = `${formatTanggal(item.tanggal_mulai)} - ${formatTanggal(item.tanggal_selesai)}`;
            return `
            <tr data-id="${item.id}" data-tipe_user="${item.tipe_user}" data-jenis="${item.jenis}" data-arah="${item.arah || ''}">
                <td><span class="nim-code">${esc(item.nim_nidn)}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.user_id)}">${esc(initials(item.nama))}</div>
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td><span class="activity-title" title="${esc(item.judul_kegiatan)}">${esc(item.judul_kegiatan)}</span>${window.apprChip ? window.apprChip(item) : ''}</td>
                <td class="center"><span class="badge ${jb.cls}">${esc(jb.label)}</span></td>
                <td class="center"><span class="badge badge-neutral">${esc(ucfirst(item.tipe_user))}</span></td>
                <td class="center">${arahHTML}</td>
                <td class="center"><span class="plain-text">${esc(item.mitra)}</span></td>
                <td class="center"><span class="year-chip">${esc(periode)}</span></td>
                <td class="center">
                    <a href="${esc(item.bukti_kegiatan)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">link</span><span>Lihat Bukti</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-user_id="${item.user_id}" data-tipe_user="${item.tipe_user}"
                            data-jenis="${item.jenis}" data-jenis_lainnya="${esc(item.jenis_lainnya || '')}" data-arah="${item.arah || ''}"
                            data-mitra="${esc(item.mitra)}"
                            data-judul_kegiatan="${esc(item.judul_kegiatan)}"
                            data-tanggal_mulai="${item.tanggal_mulai}" data-tanggal_selesai="${item.tanggal_selesai}"
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


        // ----------------------------------------------------------------
        // SUBMIT
        // ----------------------------------------------------------------
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const jenis = document.getElementById('form-jenis').value;
            const payload = {
                user_id: document.getElementById('form-user_id').value,
                tipe_user: document.getElementById('form-tipe_user').value,
                jenis: jenis,
                jenis_lainnya: jenis === 'lainnya' ? document.getElementById('form-jenis_lainnya').value.trim() : null,
                arah: jenis === 'guest_lecture' ? document.getElementById('form-arah').value : null,
                judul_kegiatan: document.getElementById('form-judul_kegiatan').value,
                mitra: document.getElementById('form-mitra').value,
                tanggal_mulai: document.getElementById('form-tanggal_mulai').value,
                tanggal_selesai: document.getElementById('form-tanggal_selesai').value,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
            };

            if (jenis === 'lainnya' && !payload.jenis_lainnya) {
                modalError.textContent = 'Isi jenis kerja sama lainnya terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            if (!payload.user_id) {
                modalError.textContent = 'Pilih mahasiswa/dosen terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }
            if (!payload.tipe_user) {
                modalError.textContent = 'Tipe user tidak terdeteksi, pilih ulang mahasiswa/dosen.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/kerja-sama/${id}` : '/kerja-sama';
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
                closeModal();
                await loadPage(id ? currentPage : 1); // data baru tampil di halaman 1 (terbaru dulu)
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ----------------------------------------------------------------
        // DELETE
        // ----------------------------------------------------------------
        async function handleDelete(id, name) {
            const ok = await DeleteConfirm.ask({ name, entity: 'data' });
            if (!ok) return;
            try {
                const res = await fetch(`/kerja-sama/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await res.json();
                if (res.ok && result.success) loadPage(currentPage);
                else alert(result.message || 'Gagal menghapus data.');
            } catch (err) {
                alert('Gagal terhubung ke server.');
            }
        }

        // ----------------------------------------------------------------
        // EVENT DELEGATION
        // ----------------------------------------------------------------
        tableBody.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-row');
            if (editBtn) return openModal('edit', editBtn.dataset);
            const delBtn = e.target.closest('.btn-delete-row');
            if (delBtn) handleDelete(delBtn.dataset.id);
        });

        // Set kondisi awal field arah saat modal pertama kali di-load
        updateConditionalFields(document.getElementById('form-jenis').value);
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