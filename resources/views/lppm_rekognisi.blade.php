<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
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
    <link rel="stylesheet" href="{{ asset('css/skeleton.css') }}?v={{ @filemtime(public_path('css/skeleton.css')) }}">
    <title>LPPM Rekognisi &middot; SIDA</title>
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

    @include('partials.sidebar', ['active' => 'rekognisi'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'rekognisi'])

        <main class="app-main">
            <div class="page-wrap is-loading" id="pageWrap" aria-busy="true">

                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Rekognisi Nasional, Internasional &amp; Alumni</h1>
                        <p class="page-subtitle">Pendataan rekognisi dosen, mahasiswa, dan alumni &middot; Tahun {{ now()->year }}</p>
                    </div>
                    @php
                        // Menu titik tiga (Unduh Template / Upload / Download / Cetak Laporan): hanya admin & superadmin.
                        // Download butuh akses readonly ke atas; Template & Upload butuh akses penuh.
                        $bulkUser = auth()->user();
                        $bulkLevel = $bulkUser->menuLevel('rekognisi');
                        $bulkRole = in_array($bulkUser->role, ['admin', 'superadmin'], true);
                        $showBulk = $bulkRole && in_array($bulkLevel, ['readonly', 'penuh'], true);
                        $bulkWrite = $bulkRole && $bulkLevel === 'penuh';
                    @endphp
                    <div class="title-actions">
                        <button type="button" class="btn-primary" id="btnTambahRekognisi">
                            <span class="material-symbols-outlined">add</span>
                            <span>Tambah Rekognisi</span>
                        </button>
                        @if($showBulk)
                            @include('partials.bulk-menu', [
                                'bulkLabel'    => 'Rekognisi',
                                'bulkTemplate' => $bulkWrite ? route('lppm.rekognisi.template') : null,
                                'bulkImport'   => $bulkWrite ? route('lppm.rekognisi.import') : null,
                                'bulkExport'   => route('lppm.rekognisi.export'),
                                'bulkPrint'    => route('cetak.show', 'rekognisi'),
                            ])
                        @endif
                    </div>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Rekognisi</span>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                        </div>
                        <div class="stat-icon primary"><span class="material-symbols-outlined">workspace_premium</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Nasional</span>
                            <span class="stat-value">{{ $stats['nasional'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">flag</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Internasional</span>
                            <span class="stat-value">{{ $stats['internasional'] }}</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">public</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Alumni</span>
                            <span class="stat-value">{{ $stats['alumni'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">school</span></div>
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
                                    <button type="button" class="dropdown-option" data-value="alumni">Alumni</button>
                                </div>
                            </div>
                        </div>
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
                                    <button type="button" class="dropdown-option" data-value="jurnal">Jurnal</button>
                                    <button type="button" class="dropdown-option" data-value="hki">HKI</button>
                                    <button type="button" class="dropdown-option" data-value="book">Buku</button>
                                    <button type="button" class="dropdown-option" data-value="sinta">Publikasi SINTA</button>
                                    <button type="button" class="dropdown-option" data-value="conference">Conference</button>
                                    <button type="button" class="dropdown-option" data-value="rekognisi">Rekognisi</button>
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
                                    <button type="button" class="dropdown-option" data-value="nasional">Nasional</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                                </div>
                            </div>
                        </div>
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari nama, judul, penulis, mitra..." />
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
                            <h2 class="table-card-title">Daftar Rekap Rekognisi</h2>
                            <p class="table-card-subtitle">Data rekognisi nasional, internasional, dan alumni {{ now()->year }}</p>
                        </div>
                    </div>

                    @if($showBulk)
                        {{-- Bar aksi data terpilih (Export Excel / Cetak PDF / Update / Hapus / Batal pilih); muncul saat ada baris dicentang --}}
                        @include('partials.row-select-bar')
                    @endif

                    <div class="table-scroll is-scrollable" id="tableScroll">
                        <table class="data-table"
                            @if($showBulk)
                                data-selectable="rekognisi"
                                data-select-export="{{ route('pilihan.export', 'rekognisi') }}"
                                data-select-print="{{ route('pilihan.cetak', 'rekognisi') }}"
                                @if($bulkWrite) data-select-delete="{{ route('pilihan.hapus', 'rekognisi') }}" @endif
                                @if($bulkWrite) data-select-update="{{ route('pilihan.ubah', 'rekognisi') }}" @endif
                            @endif>
                            <thead>
                                <tr>
                                    <th>NIM/NIDN</th>
                                    <th>Nama</th>
                                    <th class="center">Tipe</th>
                                    <th>Judul Karya</th>
                                    <th>Penulis</th>
                                    <th>Mitra</th>
                                    <th class="center">Jenis</th>
                                    <th>Detail Karya</th>
                                    <th class="center">Skala</th>
                                    <th>Jabatan (Alumni)</th>
                                    <th class="center">Periode</th>
                                    <th class="center">Bukti</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rekognisiTableBody">
                                @php
                                $jenisLabel = [
                                'jurnal' => 'Jurnal',
                                'hki' => 'HKI',
                                'book' => 'Buku',
                                'sinta' => 'Publikasi SINTA',
                                'conference' => 'Conference',
                                'nasional' => 'Nasional',
                                'internasional' => 'Internasional',
                                'alumni' => 'Alumni',
                                'rekognisi' => 'Rekognisi',
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @foreach($items as $item)
                                @php
                                $nama = optional($item->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->user_id % count($colors)];
                                $detailKarya = match ($item->jenis) {
                                    'jurnal', 'sinta' => trim(($item->nama_jurnal ?? '') . ($item->peringkat ? ' · ' . $item->peringkat : '')) ?: '-',
                                    'hki' => \Illuminate\Support\Str::headline((string) $item->jenis_hki),
                                    'book' => \Illuminate\Support\Str::headline((string) $item->kategori_buku),
                                    default => '-',
                                };
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}" data-tipe_user="{{ $item->tipe_user_efektif }}">
                                    <td><span class="nim-code">{{ optional($item->user)->nim_nidn ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            @include('partials.avatar', ['initials' => $initials, 'color' => $avatarColor, 'url' => optional($item->user)->avatar_url])
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="plain-text">{{ ucfirst($item->tipe_user_efektif) }}</span></td>
                                    <td><span class="activity-title" title="{{ $item->judul }}">{{ $item->judul ?: '-' }}</span></td>
                                    <td><span class="plain-text">{{ $item->penulis ?: '-' }}</span></td>
                                    <td><span class="activity-title" title="{{ $item->mitra }}">{{ $item->mitra ?: '-' }}</span>
                                        @include('partials.approval-chip', ['item' => $item])</td>
                                    <td class="center"><span class="plain-text">{{ $jenisLabel[$item->jenis] ?? $item->jenis }}</span></td>
                                    <td>
                                        <span class="plain-text">{{ $detailKarya }}</span>
                                        @if($item->link_doi)
                                            <a href="{{ $item->link_doi }}" target="_blank" rel="noopener noreferrer" class="evidence-link"><span>DOI</span></a>
                                        @endif
                                    </td>
                                    <td class="center"><span class="plain-text">{{ $item->skala ? ucfirst($item->skala) : '-' }}</span></td>
                                    <td><span class="plain-text">{{ $item->jabatan_efektif ?? '-' }}</span></td>
                                    <td class="center"><span class="year-chip">{{ optional($item->tanggal_mulai)->format('d M Y') }} &ndash; {{ optional($item->tanggal_selesai)->format('d M Y') }}</span></td>
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
                                                data-tipe_user="{{ $item->tipe_user_efektif }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-skala="{{ $item->skala ?? '' }}"
                                                data-judul="{{ $item->judul ?? '' }}"
                                                data-penulis="{{ $item->penulis ?? '' }}"
                                                data-mitra="{{ $item->mitra }}"
                                                data-nama_jurnal="{{ $item->nama_jurnal ?? '' }}"
                                                data-peringkat="{{ $item->peringkat ?? '' }}"
                                                data-jenis_hki="{{ $item->jenis_hki ?? '' }}"
                                                data-kategori_buku="{{ $item->kategori_buku ?? '' }}"
                                                data-link_doi="{{ $item->link_doi ?? '' }}"
                                                data-jabatan="{{ $item->jabatan ?? '' }}"
                                                data-tahun="{{ $item->tahun ?? '' }}"
                                                data-tanggal_mulai="{{ optional($item->tanggal_mulai)->format('Y-m-d') }}"
                                                data-tanggal_selesai="{{ optional($item->tanggal_selesai)->format('Y-m-d') }}"
                                                data-bukti_kegiatan="{{ $item->bukti_kegiatan }}"
                                                data-bukti_tambahan="{{ $item->bukti_tambahan ?? '' }}">
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
                                    <td colspan="13" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data. Klik "Tambah Rekognisi" untuk mulai mengisi.
                                    </td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                <tr><td colspan="13"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">{{ $items->firstItem() ?? 0 }}&ndash;{{ $items->lastItem() ?? 0 }}</strong>
                            dari <strong id="footerTotalCount">{{ $items->total() }}</strong> data rekognisi
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

    <!-- ============ MODAL: Tambah / Edit Rekognisi ============ -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    <div class="modal-card" id="rekognisiModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="modalDragHandle">
            <div>
                <h3 class="modal-title" id="modalTitle">Tambah Rekognisi</h3>
                <p class="modal-subtitle">Isi detail rekognisi dosen/mahasiswa</p>
            </div>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="rekognisiForm" class="modal-body">
            <input type="hidden" id="form-id" value="">

            <div class="field">
                <label class="field-label">Nama (Dosen/Mahasiswa/Alumni)</label>
                <div class="dropdown" data-dropdown id="dd-user">
                    <input type="hidden" id="form-user_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih nama...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($userList as $u)
                        <button type="button" class="dropdown-option" data-value="{{ $u->id }}" data-role="{{ $u->role }}" data-status="{{ $u->mahasiswa?->status ?? '' }}">{{ $u->nim_nidn ?? '-' }} &mdash; {{ $u->name }} (@if($u->role === 'mahasiswa' && $u->mahasiswa?->status === 'lulus') Alumni @else {{ ucfirst($u->role) }} @endif)</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field-row hidden" id="row-jenis-skala">
                <div class="field">
                    <label class="field-label">Jenis Karya / Publikasi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="jurnal" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Jurnal</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="jurnal">Jurnal</button>
                            <button type="button" class="dropdown-option" data-value="hki">Hak Kekayaan Intelektual</button>
                            <button type="button" class="dropdown-option" data-value="book">Buku</button>
                            <button type="button" class="dropdown-option" data-value="sinta">Publikasi SINTA</button>
                            <button type="button" class="dropdown-option" data-value="conference">Conference</button>
                            <button type="button" class="dropdown-option" data-value="rekognisi">Rekognisi</button>
                        </div>
                    </div>
                </div>
                <div class="field" id="field-skala">
                    <label class="field-label">Skala</label>
                    <div class="dropdown" data-dropdown id="dd-skala">
                        <input type="hidden" id="form-skala" value="nasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Nasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="nasional">Nasional</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Layout Mahasiswa: judul lebar di atas, lalu tahun & penulis berdampingan.
                 Field judul/penulis/tahun dipindahkan ke dua baris ini oleh JS (applyLayout)
                 saat nama yang dipilih adalah mahasiswa; untuk dosen/alumni kembali ke baris semula. --}}
            <div class="hidden" id="row-judul-wide"></div>
            <div class="field-row hidden" id="row-tahun-penulis"></div>

            <div class="field-row hidden" id="row-mitra-tahun">
                <div class="field" id="field-mitra">
                    <label class="field-label" for="form-mitra">Mitra / Instansi</label>
                    <div class="field-control">
                        <input id="form-mitra" type="text" placeholder="Nama perusahaan/instansi">
                    </div>
                </div>
                <div class="field hidden" id="field-tahun">
                    <label class="field-label" for="form-tahun">Tahun</label>
                    <div class="field-control">
                        <input id="form-tahun" type="number" min="2000" max="2100" value="{{ now()->year }}">
                    </div>
                </div>
            </div>

            <div class="field-row hidden" id="row-judul-penulis">
                <div class="field" id="field-judul">
                    <label class="field-label" for="form-judul">Judul Karya / Publikasi</label>
                    <div class="field-control">
                        <input id="form-judul" type="text" maxlength="255" placeholder="Judul karya atau publikasi">
                    </div>
                </div>
                <div class="field" id="field-penulis">
                    <label class="field-label" for="form-penulis">Penulis</label>
                    <div class="field-control">
                        <input id="form-penulis" type="text" maxlength="255" placeholder="Nama-nama penulis">
                    </div>
                </div>
            </div>

            <div class="field-row hidden" id="row-jurnal">
                <div class="field">
                    <label class="field-label" for="form-nama_jurnal">Nama Jurnal</label>
                    <div class="field-control">
                        <input id="form-nama_jurnal" type="text" maxlength="255" placeholder="Nama jurnal">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-peringkat">Peringkat</label>
                    <div class="field-control">
                        <input id="form-peringkat" type="text" maxlength="100" placeholder="Contoh: Q2 atau S3">
                    </div>
                </div>
            </div>

            <div class="field hidden" id="field-link_doi">
                <label class="field-label" for="form-link_doi">Link DOI</label>
                <div class="field-control">
                    <input id="form-link_doi" type="url" placeholder="https://doi.org/...">
                </div>
            </div>

            <div class="field hidden" id="field-jenis_hki">
                <label class="field-label">Jenis HKI</label>
                <div class="dropdown" data-dropdown id="dd-jenis_hki">
                    <input type="hidden" id="form-jenis_hki" value="hak_cipta" />
                    <button type="button" class="dropdown-trigger"><span class="dropdown-value">Hak Cipta</span><span class="material-symbols-outlined caret">expand_more</span></button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="hak_cipta">Hak Cipta</button>
                        <button type="button" class="dropdown-option" data-value="paten">Paten</button>
                        <button type="button" class="dropdown-option" data-value="merek">Merek</button>
                    </div>
                </div>
            </div>

            <div class="field hidden" id="field-kategori_buku">
                <label class="field-label">Kategori Buku</label>
                <div class="dropdown" data-dropdown id="dd-kategori_buku">
                    <input type="hidden" id="form-kategori_buku" value="ajar" />
                    <button type="button" class="dropdown-trigger"><span class="dropdown-value">Buku Ajar</span><span class="material-symbols-outlined caret">expand_more</span></button>
                    <div class="dropdown-panel">
                        <button type="button" class="dropdown-option is-selected" data-value="ajar">Buku Ajar</button>
                        <button type="button" class="dropdown-option" data-value="referensi">Referensi</button>
                        <button type="button" class="dropdown-option" data-value="chapter">Chapter</button>
                    </div>
                </div>
            </div>

            <div class="field" id="field-jabatan">
                <label class="field-label" for="form-jabatan">Jabatan (khusus Alumni)</label>
                <div class="field-control">
                    <input id="form-jabatan" type="text" placeholder="Contoh: Manager Operasional">
                </div>
            </div>

            <div class="field-row hidden" id="row-tanggal">
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

            <div class="field hidden" id="field-bukti">
                <label class="field-label" for="form-bukti_kegiatan">Link Bukti Kegiatan</label>
                <div class="field-control">
                    <input id="form-bukti_kegiatan" type="url" placeholder="https://drive.google.com/..." required>
                </div>
            </div>

            <div class="field hidden" id="field-bukti_tambahan">
                <label class="field-label" for="form-bukti_tambahan">Link Bukti Tambahan (opsional)</label>
                <div class="field-control">
                    <input id="form-bukti_tambahan" type="url" placeholder="https://drive.google.com/...">
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
            'bulkLabel'    => 'Rekognisi',
            'bulkImport'   => route('lppm.rekognisi.import'),
            'bulkTemplate' => route('lppm.rekognisi.template'),
        ])
    @endif

    @if($showBulk && $bulkWrite)
        {{-- Modal Update Massal (data terpilih). Kolom NIM/NIDN, Nama, Tipe, Tanggal & Bukti sengaja tidak ada: khas per baris. --}}
        @include('partials.bulk-update-modal', [
            'updateLabel'  => 'Rekognisi',
            'updateFields' => [
                ['name' => 'jenis',   'label' => 'Jenis Karya', 'type' => 'select', 'row' => 'a', 'col' => 4, 'target' => '.plain-text',
                    'options' => ['jurnal' => 'Jurnal', 'hki' => 'HKI', 'book' => 'Buku', 'sinta' => 'Publikasi SINTA', 'conference' => 'Conference', 'rekognisi' => 'Rekognisi']],
                ['name' => 'skala',   'label' => 'Skala', 'type' => 'select', 'row' => 'a', 'col' => 3, 'target' => '.plain-text',
                    'options' => ['nasional' => 'Nasional', 'internasional' => 'Internasional']],
                ['name' => 'mitra',   'label' => 'Mitra / Instansi', 'type' => 'text',   'row' => 'a', 'col' => 3, 'target' => '.activity-title'],
                ['name' => 'jabatan', 'label' => 'Jabatan (Alumni)', 'type' => 'text',   'col' => 2, 'target' => '.plain-text'],
            ],
        ])
    @endif

    <script src="{{ asset('js/approval-chip.js') }}?v={{ @filemtime(public_path('js/approval-chip.js')) }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script>
        // ================================================================
        // Bagian ini KHUSUS halaman LPPM Rekognisi:
        // endpoint API, bentuk baris tabel, field form, field kondisional,
        // dan filter tambahan.
        // Semua yang generik (dropdown, sidebar, dark mode, notifikasi,
        // modal buka/tutup/drag, live search, profile dropdown) sudah
        // ditangani public/js/script.js via SIDA.
        // ================================================================
        const csrfToken = SIDA.util.csrfToken();
        const {
            esc,
            initials,
            avatarColor
        } = SIDA.util;

        // ---- Elemen tabel & modal ----
        const tableBody = document.getElementById('rekognisiTableBody');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalCard = document.getElementById('rekognisiModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('rekognisiForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahRekognisi');
        const dragHandle = document.getElementById('modalDragHandle');

        // ---- Field kondisional (khusus halaman ini) ----
        const userDropdown = document.getElementById('dd-user');
        const kindOptions = {
            dosen: ['jurnal', 'hki', 'book', 'rekognisi'],
            mahasiswa: ['sinta', 'conference', 'jurnal'],
            alumni: ['sinta', 'conference', 'jurnal', 'rekognisi'],
        };
        const defaultKindByType = { dosen: 'jurnal', mahasiswa: 'sinta', alumni: 'sinta' };
        const scalableKinds = ['jurnal', 'sinta', 'conference'];
        const userOptionButtons = Array.from(userDropdown.querySelectorAll('.dropdown-option'));
        const kindOptionButtons = Array.from(document.querySelectorAll('#dd-jenis .dropdown-option'));
        let lastSelectedType = null;
        // Data lama mahasiswa yang jenisnya "rekognisi" tetap bisa diedit tanpa berubah diam-diam jadi jenis lain.
        let legacyKind = null;

        function selectedType() {
            const userId = document.getElementById('form-user_id').value;
            const selectedUser = userOptionButtons.find((option) => option.dataset.value === userId);
            if (!selectedUser) return null;
            if (selectedUser.dataset.role === 'mahasiswa' && selectedUser.dataset.status === 'lulus') return 'alumni';
            return selectedUser.dataset.role;
        }

        function updateKindOptions() {
            const type = selectedType();
            const allowed = (kindOptions[type] || []).concat(type === 'mahasiswa' && legacyKind ? [legacyKind] : []);
            kindOptionButtons.forEach((option) => {
                const visible = allowed.includes(option.dataset.value);
                option.hidden = !visible;
                option.style.display = visible ? '' : 'none';
            });

            const current = document.getElementById('form-jenis').value;
            if (allowed.length && (type !== lastSelectedType || !allowed.includes(current))) {
                SIDA.dropdown.select(document.getElementById('dd-jenis'), defaultKindByType[type] || allowed[0]);
            }
            lastSelectedType = type;
            updateConditionalFields();
        }

        // Letak field per tipe. Mahasiswa: [Jenis | Skala] -> [Judul (lebar)] -> [Tahun | Penulis] -> [Nama Jurnal | Peringkat].
        // Dosen/Alumni: susunan semula ([Mitra | Tahun] -> [Judul | Penulis]).
        function applyLayout(isMahasiswa) {
            const el = (id) => document.getElementById(id);
            const judul = el('field-judul'), penulis = el('field-penulis'), tahun = el('field-tahun'), mitra = el('field-mitra');
            if (isMahasiswa) {
                if (judul.parentElement.id !== 'row-judul-wide') el('row-judul-wide').appendChild(judul);
                if (tahun.parentElement.id !== 'row-tahun-penulis') el('row-tahun-penulis').appendChild(tahun);
                if (penulis.parentElement.id !== 'row-tahun-penulis') el('row-tahun-penulis').appendChild(penulis);
            } else {
                if (mitra.parentElement.id !== 'row-mitra-tahun') el('row-mitra-tahun').insertBefore(mitra, el('row-mitra-tahun').firstChild);
                if (tahun.parentElement.id !== 'row-mitra-tahun') el('row-mitra-tahun').appendChild(tahun);
                if (judul.parentElement.id !== 'row-judul-penulis') el('row-judul-penulis').insertBefore(judul, el('row-judul-penulis').firstChild);
                if (penulis.parentElement.id !== 'row-judul-penulis') el('row-judul-penulis').appendChild(penulis);
            }
        }

        function updateConditionalFields() {
            const hasUser = Boolean(document.getElementById('form-user_id').value);
            const type = selectedType();
            const kind = document.getElementById('form-jenis').value;
            const isPublication = hasUser && kind !== 'rekognisi';
            const hasJournalFields = hasUser && ['jurnal', 'sinta'].includes(kind);
            const hasScale = hasUser && (scalableKinds.includes(kind) || (kind === 'rekognisi' && type !== 'alumni'));
            const isAlumni = hasUser && type === 'alumni';
            const isMahasiswa = hasUser && type === 'mahasiswa';
            applyLayout(isMahasiswa);

            document.getElementById('row-jenis-skala').classList.toggle('hidden', !hasUser);
            // Mahasiswa tidak mengisi Mitra / Instansi.
            document.getElementById('row-mitra-tahun').classList.toggle('hidden', !hasUser || isMahasiswa);
            document.getElementById('field-mitra').classList.toggle('hidden', isMahasiswa);
            document.getElementById('row-tanggal').classList.toggle('hidden', !hasUser);
            document.getElementById('field-bukti').classList.toggle('hidden', !hasUser);
            document.getElementById('field-bukti_tambahan').classList.toggle('hidden', !hasUser);
            document.getElementById('field-skala').classList.toggle('hidden', !hasScale);
            document.getElementById('row-judul-penulis').classList.toggle('hidden', !isPublication || isMahasiswa);
            document.getElementById('row-judul-wide').classList.toggle('hidden', !(isPublication && isMahasiswa));
            document.getElementById('row-tahun-penulis').classList.toggle('hidden', !(isPublication && isMahasiswa));
            document.getElementById('field-tahun').classList.toggle('hidden', !isPublication);
            document.getElementById('row-jurnal').classList.toggle('hidden', !hasJournalFields);
            document.getElementById('field-link_doi').classList.toggle('hidden', !hasJournalFields);
            document.getElementById('field-jenis_hki').classList.toggle('hidden', kind !== 'hki');
            document.getElementById('field-kategori_buku').classList.toggle('hidden', kind !== 'book');
            document.getElementById('field-jabatan').classList.toggle('hidden', !isAlumni);

            document.getElementById('form-mitra').required = type === 'dosen' || isAlumni;
            document.getElementById('form-jabatan').required = isAlumni;
            document.getElementById('form-judul').required = isPublication;
            document.getElementById('form-penulis').required = isPublication;
            document.getElementById('form-tahun').required = isPublication;
            document.getElementById('form-nama_jurnal').required = hasJournalFields;
            document.getElementById('form-peringkat').required = hasJournalFields;
            document.getElementById('form-tanggal_mulai').required = hasUser;
            document.getElementById('form-tanggal_selesai').required = hasUser;
            document.getElementById('form-bukti_kegiatan').required = hasUser;
        }

        userOptionButtons.forEach((option) => {
            option.addEventListener('click', () => setTimeout(updateKindOptions, 0));
        });
        kindOptionButtons.forEach((option) => option.addEventListener('click', () => setTimeout(updateConditionalFields, 0)));
        updateKindOptions();

        // Datepicker (Tanggal Mulai & Selesai) sudah ada di script.js: SIDA.datepicker
        const { start: dpMulai, end: dpSelesai } = SIDA.datepicker.pair(
            document.getElementById('dp-tanggal_mulai'),
            document.getElementById('dp-tanggal_selesai'),
        );

        // ---- Label jenis (khusus halaman ini) ----
        const jenisLabel = {
            jurnal: 'Jurnal',
            hki: 'HKI',
            book: 'Buku',
            sinta: 'Publikasi SINTA',
            conference: 'Conference',
            nasional: 'Nasional',
            internasional: 'Internasional',
            alumni: 'Alumni',
            rekognisi: 'Rekognisi'
        };

        // ---- Tampilkan hasil Update Massal di baris tabel (khusus halaman ini) ----
        // Beda dari default bulk-update.js: jenis tampil sebagai label, dan jabatan hanya tampil untuk alumni.
        SIDA.bulkUpdateApply = function(rows) {
            const body = document.getElementById('rekognisiTableBody') || document.querySelector('table[data-selectable] tbody');
            (rows || []).forEach((r) => {
                const tr = body && body.querySelector(`tr[data-id="${r.id}"]`);
                if (!tr) return;
                const cells = Array.from(tr.children).filter((td) => !td.classList.contains('sel-col'));
                const mitraEl = cells[5] && cells[5].querySelector('.activity-title');
                if (mitraEl) { mitraEl.textContent = r.mitra; mitraEl.title = r.mitra; }
                const jenisEl = cells[6] && cells[6].querySelector('.plain-text');
                if (jenisEl) jenisEl.textContent = jenisLabel[r.jenis] || r.jenis;
                const jabEl = cells[9] && cells[9].querySelector('.plain-text');
                if (jabEl) jabEl.textContent = r.jabatan_tampil || '-';

                const edit = tr.querySelector('.btn-edit-row');
                ['jenis', 'mitra', 'jabatan'].forEach((k) => {
                    const v = r[k] == null ? '' : String(r[k]);
                    if (tr.hasAttribute('data-' + k)) tr.setAttribute('data-' + k, v);
                    if (edit && edit.hasAttribute('data-' + k)) edit.setAttribute('data-' + k, v);
                });

                tr.classList.remove('is-updated-flash');
                void tr.offsetWidth;
                tr.classList.add('is-updated-flash');
                setTimeout(() => tr.classList.remove('is-updated-flash'), 1800);
            });
        };

        // ---- Bangun HTML baris tabel dari data JSON (khusus halaman ini) ----
        function fmtDate(d) {
            if (!d) return '-';
            const dt = new Date(d + 'T00:00:00');
            if (isNaN(dt)) return d;
            return dt.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function buildRowHTML(item) {
            const detail = ['jurnal', 'sinta'].includes(item.jenis)
                ? [item.nama_jurnal, item.peringkat].filter(Boolean).join(' · ') || '-'
                : item.jenis === 'hki'
                    ? (item.jenis_hki || '-').replaceAll('_', ' ')
                    : item.jenis === 'book'
                        ? (item.kategori_buku || '-').replaceAll('_', ' ')
                        : '-';
            return `
            <tr data-id="${item.id}" data-jenis="${item.jenis}" data-tipe_user="${item.tipe_user}">
                <td><span class="nim-code">${esc(item.nim_nidn)}</span></td>
                <td>
                    <div class="student-cell">
                        ${SIDA.util.avatarHtml(item.nama, item.user_id, item.avatar_url)}
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td class="center"><span class="plain-text">${esc(item.tipe_user.charAt(0).toUpperCase() + item.tipe_user.slice(1))}</span></td>
                <td><span class="activity-title" title="${esc(item.judul || '')}">${esc(item.judul || '-')}</span></td>
                <td><span class="plain-text">${esc(item.penulis || '-')}</span></td>
                <td><span class="activity-title" title="${esc(item.mitra || '')}">${esc(item.mitra || '-')}</span>${window.apprChip ? window.apprChip(item) : ''}</td>
                <td class="center"><span class="plain-text">${esc(jenisLabel[item.jenis] || item.jenis)}</span></td>
                <td><span class="plain-text">${esc(detail)}</span>${item.link_doi ? ` <a href="${esc(item.link_doi)}" target="_blank" rel="noopener noreferrer" class="evidence-link"><span>DOI</span></a>` : ''}</td>
                <td class="center"><span class="plain-text">${esc(item.skala ? item.skala.charAt(0).toUpperCase() + item.skala.slice(1) : '-')}</span></td>
                <td><span class="plain-text">${esc(item.jabatan_tampil || '-')}</span></td>
                <td class="center"><span class="year-chip">${fmtDate(item.tanggal_mulai)} &ndash; ${fmtDate(item.tanggal_selesai)}</span></td>
                <td class="center">
                    <a href="${esc(item.bukti_kegiatan)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">link</span><span>Lihat Bukti</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-user_id="${item.user_id}" data-tipe_user="${item.tipe_user}"
                            data-jenis="${item.jenis}" data-skala="${item.skala || ''}"
                            data-judul="${esc(item.judul || '')}" data-penulis="${esc(item.penulis || '')}"
                            data-mitra="${esc(item.mitra || '')}"
                            data-nama_jurnal="${esc(item.nama_jurnal || '')}" data-peringkat="${esc(item.peringkat || '')}"
                            data-jenis_hki="${item.jenis_hki || ''}" data-kategori_buku="${item.kategori_buku || ''}"
                            data-link_doi="${esc(item.link_doi || '')}" data-tahun="${item.tahun || ''}"
                            data-jabatan="${esc(item.jabatan || '')}"
                            data-tanggal_mulai="${item.tanggal_mulai || ''}"
                            data-tanggal_selesai="${item.tanggal_selesai || ''}"
                            data-bukti_kegiatan="${esc(item.bukti_kegiatan)}"
                            data-bukti_tambahan="${esc(item.bukti_tambahan || '')}">
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
        // GET /lppm/rekognisi?filter&q=&page= (JSON: items + meta) -> baris digambar buildRowHTML.
        // Tombol halaman digambar public/js/pager.js.
        // ================================================================
        const FILTERS = ['filter-tipe-user','filter-jenis','filter-skala'];
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
                const res = await fetch('/lppm/rekognisi?' + params, {
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
                console.error('[Rekognisi] Gagal memuat halaman:', err);
                window.Toast?.show?.({ type: 'error', title: 'Gagal memuat data', message: 'Tidak bisa memuat halaman ini. Coba lagi.' });
            } finally {
                if (seq === requestSeq) tableBody.classList.remove('is-fetching');
            }
        }

        // Cari (tunda 300ms), pilih dropdown, reset -> kembali ke halaman 1
        searchInput?.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => loadPage(1), 300); });
        // setTimeout 0: tunggu script.js selesai mengisi nilai dropdown tersembunyi
        document.querySelectorAll('.filter-card .dropdown-option').forEach((opt) => opt.addEventListener('click', () => setTimeout(() => loadPage(1), 0)));
        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            FILTERS.forEach((id) => SIDA.dropdown.reset(document.getElementById(id).closest('[data-dropdown]')));
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
            modalTitle.textContent = mode === 'edit' ? 'Edit Rekognisi' : 'Tambah Rekognisi';

            legacyKind = mode === 'edit' && data.jenis === 'rekognisi' ? 'rekognisi' : null;
            SIDA.dropdown.select(document.getElementById('dd-user'), data.user_id || '', 'Pilih nama...');
            const type = selectedType();
            updateKindOptions();
            const defaultKind = defaultKindByType[type] || 'jurnal';
            const jenis = data.jenis || defaultKind;
            SIDA.dropdown.select(document.getElementById('dd-jenis'), jenis);
            SIDA.dropdown.select(document.getElementById('dd-skala'), data.skala || 'nasional');
            updateConditionalFields();

            document.getElementById('form-mitra').value = data.mitra || '';
            document.getElementById('form-jabatan').value = data.jabatan || '';
            document.getElementById('form-judul').value = data.judul || '';
            document.getElementById('form-penulis').value = data.penulis || '';
            document.getElementById('form-nama_jurnal').value = data.nama_jurnal || '';
            document.getElementById('form-peringkat').value = data.peringkat || '';
            document.getElementById('form-link_doi').value = data.link_doi || '';
            document.getElementById('form-tahun').value = data.tahun || new Date().getFullYear();
            SIDA.dropdown.select(document.getElementById('dd-jenis_hki'), data.jenis_hki || 'hak_cipta');
            SIDA.dropdown.select(document.getElementById('dd-kategori_buku'), data.kategori_buku || 'ajar');
            dpMulai.setValue(data.tanggal_mulai || '');
            dpSelesai.setMinDate(data.tanggal_mulai || null);
            dpSelesai.setValue(data.tanggal_selesai || '');
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan || '';
            document.getElementById('form-bukti_tambahan').value = data.bukti_tambahan || '';

            openModalBase();
        }

        btnTambah?.addEventListener('click', () => openModal('create'));

        // ---- Submit form (create / update) — endpoint khusus halaman ini ----
        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            modalError.hidden = true;
            modalSubmitBtn.disabled = true;

            const id = document.getElementById('form-id').value;
            const tipeUser = selectedType();
            const payload = {
                user_id: document.getElementById('form-user_id').value,
                jenis: document.getElementById('form-jenis').value,
                skala: document.getElementById('form-skala').value || null,
                mitra: document.getElementById('form-mitra').value,
                jabatan: tipeUser === 'alumni' ? document.getElementById('form-jabatan').value : null,
                judul: document.getElementById('form-judul').value || null,
                penulis: document.getElementById('form-penulis').value || null,
                nama_jurnal: document.getElementById('form-nama_jurnal').value || null,
                peringkat: document.getElementById('form-peringkat').value || null,
                jenis_hki: document.getElementById('form-jenis_hki').value || null,
                kategori_buku: document.getElementById('form-kategori_buku').value || null,
                link_doi: document.getElementById('form-link_doi').value || null,
                tahun: document.getElementById('form-tahun').value || null,
                tanggal_mulai: document.getElementById('form-tanggal_mulai').value,
                tanggal_selesai: document.getElementById('form-tanggal_selesai').value,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
                bukti_tambahan: document.getElementById('form-bukti_tambahan').value || null,
            };

            if (!payload.user_id) {
                modalError.textContent = 'Pilih nama sesuai tipe pengguna terlebih dahulu.';
                modalError.hidden = false;
                modalSubmitBtn.disabled = false;
                return;
            }

            const url = id ? `/lppm/rekognisi/${id}` : '/lppm/rekognisi';
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
                    Toast.show({
                        type: 'error',
                        title: 'Gagal menyimpan',
                        message: firstError
                    });
                    return;
                }
                closeModal();
                await loadPage(id ? currentPage : 1); // data baru tampil di halaman 1 (terbaru dulu)
                Toast.show({
                    type: 'success',
                    message: id ? 'Data rekognisi berhasil diperbarui.' : 'Data rekognisi baru berhasil ditambahkan.',
                });
            } catch (err) {
                modalError.textContent = 'Gagal terhubung ke server.';
                modalError.hidden = false;
                Toast.show({
                    type: 'error',
                    title: 'Gagal menyimpan',
                    message: 'Tidak bisa terhubung ke server. Coba lagi.'
                });
            } finally {
                modalSubmitBtn.disabled = false;
            }
        });

        // ---- Delete — endpoint khusus halaman ini ----
        async function handleDelete(id, name) {
            const ok = await DeleteConfirm.ask({ name, entity: 'data' });
            if (!ok) return;
            try {
                const res = await fetch(`/lppm/rekognisi/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    loadPage(currentPage);
                    Toast.show({
                        type: 'success',
                        message: 'Data rekognisi berhasil dihapus.'
                    });
                } else {
                    Toast.show({
                        type: 'error',
                        title: 'Gagal menghapus',
                        message: result.message || 'Gagal menghapus data.'
                    });
                }
            } catch (err) {
                Toast.show({
                    type: 'error',
                    title: 'Gagal menghapus',
                    message: 'Tidak bisa terhubung ke server.'
                });
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