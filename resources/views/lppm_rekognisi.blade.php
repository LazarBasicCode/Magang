<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>LPPM Rekognisi &middot; SIDA</title>
    <style>
        /* ================= Custom Datepicker (ganti input[type=date] bawaan browser) ================= */
        .datepicker {
            position: relative;
        }

        .datepicker-trigger {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--card);
            color: var(--ink);
            font-size: 13px;
            font-weight: 500;
            padding: 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
            font-family: 'Public Sans', sans-serif;
            transition: border-color .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }

        .datepicker-trigger:hover {
            border-color: var(--primary-border);
        }

        .datepicker-trigger .dp-icon {
            font-size: 18px;
            color: var(--ink-faint);
            flex-shrink: 0;
        }

        .datepicker-value {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--ink-faint);
        }

        .datepicker-value.has-value {
            color: var(--ink);
            font-weight: 600;
        }

        .datepicker.is-open .datepicker-trigger {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .datepicker.is-open .datepicker-trigger .dp-icon {
            color: var(--primary);
        }

        .datepicker-panel {
            position: fixed;
            z-index: 1000;
            width: 268px;
            background: var(--card);
            border: 1px solid var(--border-strong);
            border-radius: 12px;
            box-shadow: 0 16px 34px rgba(31, 41, 66, 0.28);
            padding: 12px;
            display: none;
        }

        .dp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .dp-month-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            text-transform: capitalize;
        }

        .dp-nav {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--ink-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }

        .dp-nav:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary-border);
        }

        .dp-nav .material-symbols-outlined {
            font-size: 18px;
        }

        .dp-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            margin-bottom: 4px;
        }

        .dp-weekdays span {
            text-align: center;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--ink-faint);
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 4px 0;
        }

        .dp-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .dp-day {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: var(--ink);
            font-size: 12.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .12s ease, color .12s ease;
            cursor: pointer;
        }

        .dp-day:hover:not(:disabled) {
            background: var(--canvas);
        }

        .dp-day.is-today {
            color: var(--primary);
            font-weight: 800;
            box-shadow: inset 0 0 0 1px var(--primary-border);
        }

        .dp-day.is-selected {
            background: var(--primary);
            color: #fff;
            font-weight: 700;
        }

        .dp-day.is-selected.is-today {
            box-shadow: none;
        }

        .dp-day:disabled {
            color: var(--ink-faint);
            opacity: .35;
            cursor: not-allowed;
        }

        .dp-day.is-empty {
            visibility: hidden;
            pointer-events: none;
        }

        .dp-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        .dp-today-btn {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--primary);
            background: transparent;
            border: none;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background-color .12s ease;
        }

        .dp-today-btn:hover {
            background: var(--primary-soft);
        }

        .dp-clear-btn {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--ink-faint);
            background: transparent;
            border: none;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background-color .12s ease, color .12s ease;
        }

        .dp-clear-btn:hover {
            background: var(--canvas);
            color: var(--ink-muted);
        }
    </style>
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
                <a href="{{ url('/lppm/rekognisi') }}" aria-current="page" class="nav-link is-active">
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
                            <span class="current">Rekognisi</span>
                        </div>
                        <h1 class="page-title">Rekognisi Nasional, Internasional &amp; Alumni</h1>
                        <p class="page-subtitle">Pendataan rekognisi dosen dan mahasiswa &middot; Tahun 2026</p>
                    </div>
                    <button type="button" class="btn-primary" id="btnTambahRekognisi">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Rekognisi</span>
                    </button>
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
                        <div class="field">
                            <label class="field-label">Jenis Rekognisi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="nasional">Nasional</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                                    <button type="button" class="dropdown-option" data-value="alumni">Alumni</button>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari nama, mitra, atau jabatan..." />
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
                            <h2 class="table-card-title">Daftar Rekap Rekognisi</h2>
                            <p class="table-card-subtitle">Data rekognisi nasional, internasional, dan alumni 2026</p>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIM/NIDN</th>
                                    <th>Nama</th>
                                    <th class="center">Tipe</th>
                                    <th>Mitra</th>
                                    <th class="center">Jenis</th>
                                    <th>Jabatan (Alumni)</th>
                                    <th class="center">Periode</th>
                                    <th class="center">Bukti</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rekognisiTableBody">
                                @php
                                $jenisLabel = [
                                'nasional' => 'Nasional',
                                'internasional' => 'Internasional',
                                'alumni' => 'Alumni',
                                ];
                                $jenisBadge = [
                                'nasional' => 'c-warning',
                                'internasional' => 'c-info',
                                'alumni' => 'c-success',
                                ];
                                $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
                                @endphp
                                @forelse($items as $item)
                                @php
                                $nama = optional($item->user)->name ?? 'Tanpa Nama';
                                $initials = collect(explode(' ', $nama))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $avatarColor = $colors[$item->user_id % count($colors)];
                                @endphp
                                <tr data-id="{{ $item->id }}" data-jenis="{{ $item->jenis }}">
                                    <td><span class="nim-code">{{ optional($item->user)->nim_nidn ?? '-' }}</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
                                            <div class="student-name"><span class="name">{{ $nama }}</span></div>
                                        </div>
                                    </td>
                                    <td class="center"><span class="plain-text">{{ ucfirst($item->tipe_user) }}</span></td>
                                    <td><span class="activity-title" title="{{ $item->mitra }}">{{ $item->mitra }}</span></td>
                                    <td class="center"><span class="plain-text">{{ $jenisLabel[$item->jenis] ?? $item->jenis }}</span></td>
                                    <td><span class="plain-text">{{ $item->jabatan ?? '-' }}</span></td>
                                    <td class="center"><span class="year-chip">{{ optional($item->tanggal_mulai)->format('d M Y') }} &ndash; {{ optional($item->tanggal_selesai)->format('d M Y') }}</span></td>
                                    <td class="center">
                                        <a href="{{ $item->bukti_kegiatan }}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                                                data-id="{{ $item->id }}"
                                                data-user_id="{{ $item->user_id }}"
                                                data-jenis="{{ $item->jenis }}"
                                                data-mitra="{{ urlencode($item->mitra) }}"
                                                data-jabatan="{{ urlencode($item->jabatan ?? '') }}"
                                                data-tanggal_mulai="{{ optional($item->tanggal_mulai)->format('Y-m-d') }}"
                                                data-tanggal_selesai="{{ optional($item->tanggal_selesai)->format('Y-m-d') }}"
                                                data-bukti_kegiatan="{{ urlencode($item->bukti_kegiatan) }}"
                                                data-bukti_tambahan="{{ urlencode($item->bukti_tambahan ?? '') }}">
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
                                    <td colspan="9" style="text-align:center; padding: 32px; color: var(--ink-faint);">
                                        Belum ada data. Klik "Tambah Rekognisi" untuk mulai mengisi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>{{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }}</strong>
                            dari <strong>{{ $items->total() }}</strong> data rekognisi
                        </div>
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
                <label class="field-label">Nama (Dosen/Mahasiswa)</label>
                <div class="dropdown" data-dropdown id="dd-user">
                    <input type="hidden" id="form-user_id" value="" />
                    <button type="button" class="dropdown-trigger">
                        <span class="dropdown-value">Pilih nama...</span>
                        <span class="material-symbols-outlined caret">expand_more</span>
                    </button>
                    <div class="dropdown-panel">
                        @foreach($userList as $u)
                        <button type="button" class="dropdown-option" data-value="{{ $u->id }}">{{ $u->nim_nidn ?? '-' }} &mdash; {{ $u->name }} ({{ ucfirst($u->role) }})</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label class="field-label">Jenis Rekognisi</label>
                    <div class="dropdown" data-dropdown id="dd-jenis">
                        <input type="hidden" id="form-jenis" value="nasional" />
                        <button type="button" class="dropdown-trigger">
                            <span class="dropdown-value">Nasional</span>
                            <span class="material-symbols-outlined caret">expand_more</span>
                        </button>
                        <div class="dropdown-panel">
                            <button type="button" class="dropdown-option is-selected" data-value="nasional">Nasional</button>
                            <button type="button" class="dropdown-option" data-value="internasional">Internasional</button>
                            <button type="button" class="dropdown-option" data-value="alumni">Alumni</button>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="form-mitra">Mitra / Instansi</label>
                    <div class="field-control">
                        <input id="form-mitra" type="text" placeholder="Nama perusahaan/instansi" required>
                    </div>
                </div>
            </div>

            <!-- Field khusus alumni -->
            <div class="field" id="field-jabatan">
                <label class="field-label" for="form-jabatan">Jabatan (khusus Alumni)</label>
                <div class="field-control">
                    <input id="form-jabatan" type="text" placeholder="Contoh: Manager Operasional">
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

            <div class="field">
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

        // ---------------- Custom Datepicker ----------------
        const MONTH_NAMES_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const DAY_MS = 24 * 60 * 60 * 1000;

        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function toISO(y, m, d) {
            return `${y}-${pad2(m + 1)}-${pad2(d)}`;
        }

        function parseISO(str) {
            if (!str) return null;
            const [y, m, d] = str.split('-').map(Number);
            if (!y || !m || !d) return null;
            return new Date(y, m - 1, d);
        }

        function formatDisplayDate(dateObj) {
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            return `${days[dateObj.getDay()]}, ${dateObj.getDate()} ${MONTH_NAMES_ID[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
        }

        function createDatepicker(rootEl) {
            const hiddenInput = rootEl.querySelector('input[type="hidden"]');
            const trigger = rootEl.querySelector('.datepicker-trigger');
            const valueEl = rootEl.querySelector('.datepicker-value');
            const panel = rootEl.querySelector('.datepicker-panel');
            const monthLabel = rootEl.querySelector('.dp-month-label');
            const daysGrid = rootEl.querySelector('.dp-days');
            const prevBtn = rootEl.querySelector('.dp-prev');
            const nextBtn = rootEl.querySelector('.dp-next');
            const todayBtn = rootEl.querySelector('.dp-today-btn');
            const clearBtn = rootEl.querySelector('.dp-clear-btn');

            // Pindahkan panel kalender ke <body> supaya posisinya lepas dari overflow modal
            // dan tidak pernah menggeser/mengubah ukuran modal saat dibuka.
            document.body.appendChild(panel);

            let selected = null;
            let minDate = null;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            let view = {
                year: today.getFullYear(),
                month: today.getMonth()
            };

            function render() {
                monthLabel.textContent = `${MONTH_NAMES_ID[view.month]} ${view.year}`;
                daysGrid.innerHTML = '';

                const firstDay = new Date(view.year, view.month, 1);
                const startWeekday = firstDay.getDay();
                const daysInMonth = new Date(view.year, view.month + 1, 0).getDate();

                for (let i = 0; i < startWeekday; i++) {
                    const blank = document.createElement('span');
                    blank.className = 'dp-day is-empty';
                    daysGrid.appendChild(blank);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const cellDate = new Date(view.year, view.month, d);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'dp-day';
                    btn.textContent = d;

                    if (cellDate.getTime() === today.getTime()) btn.classList.add('is-today');
                    if (selected && cellDate.getTime() === selected.getTime()) btn.classList.add('is-selected');
                    if (minDate && cellDate.getTime() < minDate.getTime()) btn.disabled = true;

                    btn.addEventListener('click', () => selectDate(cellDate));
                    daysGrid.appendChild(btn);
                }
            }

            function positionPanel() {
                const rect = trigger.getBoundingClientRect();
                const panelWidth = panel.offsetWidth || 268;
                let left = Math.min(rect.left, window.innerWidth - panelWidth - 8);
                left = Math.max(8, left);
                panel.style.left = left + 'px';

                const panelHeight = panel.offsetHeight;
                let top = rect.bottom + 6;
                if (top + panelHeight > window.innerHeight - 8) {
                    const above = rect.top - panelHeight - 6;
                    top = above > 8 ? above : Math.max(8, window.innerHeight - panelHeight - 8);
                }
                panel.style.top = top + 'px';
            }

            function selectDate(dateObj) {
                selected = dateObj;
                hiddenInput.value = toISO(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
                valueEl.textContent = formatDisplayDate(dateObj);
                valueEl.classList.add('has-value');
                closePanel();
                rootEl.dispatchEvent(new CustomEvent('datepicker:change', {
                    detail: {
                        date: dateObj,
                        iso: hiddenInput.value
                    }
                }));
            }

            function clear() {
                selected = null;
                hiddenInput.value = '';
                valueEl.textContent = 'Pilih tanggal';
                valueEl.classList.remove('has-value');
                rootEl.dispatchEvent(new CustomEvent('datepicker:change', {
                    detail: {
                        date: null,
                        iso: ''
                    }
                }));
            }

            function openPanel() {
                closeAllDatepickers();
                document.querySelectorAll('.dropdown.is-open').forEach((d) => d.classList.remove('is-open'));
                view = selected ? {
                    year: selected.getFullYear(),
                    month: selected.getMonth()
                } : {
                    year: today.getFullYear(),
                    month: today.getMonth()
                };
                render();
                panel.style.display = 'block';
                positionPanel();
                rootEl.classList.add('is-open');
                activeDatepickers.add(api);
            }

            function closePanel() {
                panel.style.display = 'none';
                rootEl.classList.remove('is-open');
                activeDatepickers.delete(api);
            }

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const wasOpen = rootEl.classList.contains('is-open');
                closeAllDatepickers();
                document.querySelectorAll('.dropdown.is-open').forEach((d) => d.classList.remove('is-open'));
                if (!wasOpen) openPanel();
            });
            panel.addEventListener('click', (e) => e.stopPropagation());
            prevBtn.addEventListener('click', () => {
                view.month--;
                if (view.month < 0) {
                    view.month = 11;
                    view.year--;
                }
                render();
            });
            nextBtn.addEventListener('click', () => {
                view.month++;
                if (view.month > 11) {
                    view.month = 0;
                    view.year++;
                }
                render();
            });
            todayBtn.addEventListener('click', () => selectDate(new Date(today)));
            clearBtn.addEventListener('click', clear);

            render();

            const api = {
                el: rootEl,
                panel,
                isOpen: () => rootEl.classList.contains('is-open'),
                close: closePanel,
                reposition: positionPanel,
                setValue(iso) {
                    const d = parseISO(iso);
                    if (d) {
                        selected = d;
                        valueEl.textContent = formatDisplayDate(d);
                        valueEl.classList.add('has-value');
                        hiddenInput.value = iso;
                    } else {
                        selected = null;
                        valueEl.textContent = 'Pilih tanggal';
                        valueEl.classList.remove('has-value');
                        hiddenInput.value = '';
                    }
                },
                setMinDate(iso) {
                    minDate = parseISO(iso);
                },
                clear,
            };
            return api;
        }

        const activeDatepickers = new Set();

        function closeAllDatepickers() {
            activeDatepickers.forEach((dp) => dp.close());
        }

        const dpMulai = createDatepicker(document.getElementById('dp-tanggal_mulai'));
        const dpSelesai = createDatepicker(document.getElementById('dp-tanggal_selesai'));

        // Tutup kalender saat klik di luar trigger maupun panel (panel kini ada di <body>)
        document.addEventListener('click', (e) => {
            [dpMulai, dpSelesai].forEach((dp) => {
                if (!dp.isOpen()) return;
                if (dp.el.contains(e.target) || dp.panel.contains(e.target)) return;
                dp.close();
            });
        });
        // Reposisi ulang kalender saat window di-resize atau ada scroll di mana pun (termasuk isi modal)
        window.addEventListener('resize', () => activeDatepickers.forEach((dp) => dp.reposition()));
        document.addEventListener('scroll', () => activeDatepickers.forEach((dp) => dp.reposition()), true);

        // Tanggal selesai tidak boleh sebelum tanggal mulai
        document.getElementById('dp-tanggal_mulai').addEventListener('datepicker:change', (e) => {
            dpSelesai.setMinDate(e.detail.iso || null);
            const selesaiVal = document.getElementById('form-tanggal_selesai').value;
            if (e.detail.iso && selesaiVal && selesaiVal < e.detail.iso) {
                dpSelesai.clear();
            }
        });

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
            applyFilters();
        });

        // ---------------- Live Filter (otomatis, tanpa tombol "Terapkan") ----------------
        function applyFilters() {
            const jenisVal = document.getElementById('filter-jenis')?.value || 'semua';
            const searchVal = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr[data-id]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const matchesJenis = jenisVal === 'semua' || row.dataset.jenis === jenisVal;
                const matchesSearch = !searchVal || row.textContent.toLowerCase().includes(searchVal);
                const visible = matchesJenis && matchesSearch;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            let noResultRow = document.getElementById('noResultRow');
            if (visibleCount === 0 && rows.length > 0) {
                if (!noResultRow) {
                    noResultRow = document.createElement('tr');
                    noResultRow.id = 'noResultRow';
                    const colCount = tableBody.closest('table')?.querySelectorAll('thead th').length || 9;
                    noResultRow.innerHTML = `<td colspan="${colCount}" style="text-align:center; padding: 32px; color: var(--ink-faint);">Tidak ada data yang cocok dengan filter.</td>`;
                    tableBody.appendChild(noResultRow);
                }
                noResultRow.style.display = '';
            } else if (noResultRow) {
                noResultRow.style.display = 'none';
            }
        }

        document.querySelectorAll('#filter-jenis').forEach((input) => {
            const dropdownEl = input.closest('[data-dropdown]');
            dropdownEl?.querySelectorAll('.dropdown-option').forEach((opt) => {
                opt.addEventListener('click', () => applyFilters());
            });
        });

        let searchDebounce;
        document.getElementById('filter-search')?.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(applyFilters, 150);
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
        const modalCard = document.getElementById('rekognisiModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('rekognisiForm');
        const modalError = document.getElementById('modalError');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');
        const btnTambah = document.getElementById('btnTambahRekognisi');
        const tableBody = document.getElementById('rekognisiTableBody');
        const dragHandle = document.getElementById('modalDragHandle');

        const fieldJabatan = document.getElementById('field-jabatan');

        function updateConditionalFields(jenis) {
            fieldJabatan.classList.toggle('hidden', jenis !== 'alumni');
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
            modalTitle.textContent = mode === 'edit' ? 'Edit Rekognisi' : 'Tambah Rekognisi';

            selectDropdownValue(document.getElementById('dd-user'), data.user_id || '', 'Pilih nama...');
            const jenis = data.jenis || 'nasional';
            selectDropdownValue(document.getElementById('dd-jenis'), jenis);
            updateConditionalFields(jenis);

            document.getElementById('form-mitra').value = data.mitra ? decodeURIComponent(data.mitra) : '';
            document.getElementById('form-jabatan').value = data.jabatan ? decodeURIComponent(data.jabatan) : '';
            dpMulai.setValue(data.tanggal_mulai || '');
            dpSelesai.setMinDate(data.tanggal_mulai || null);
            dpSelesai.setValue(data.tanggal_selesai || '');
            document.getElementById('form-bukti_kegiatan').value = data.bukti_kegiatan ? decodeURIComponent(data.bukti_kegiatan) : '';
            document.getElementById('form-bukti_tambahan').value = data.bukti_tambahan ? decodeURIComponent(data.bukti_tambahan) : '';

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
            nasional: 'Nasional',
            internasional: 'Internasional',
            alumni: 'Alumni'
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
            return `
            <tr data-id="${item.id}" data-jenis="${item.jenis}">
                <td><span class="nim-code">${esc(item.nim_nidn)}</span></td>
                <td>
                    <div class="student-cell">
                        <div class="avatar ${avatarColor(item.user_id)}">${esc(initials(item.nama))}</div>
                        <div class="student-name"><span class="name">${esc(item.nama)}</span></div>
                    </div>
                </td>
                <td class="center"><span class="plain-text">${esc(item.tipe_user.charAt(0).toUpperCase() + item.tipe_user.slice(1))}</span></td>
                <td><span class="activity-title" title="${esc(item.mitra)}">${esc(item.mitra)}</span></td>
                <td class="center"><span class="plain-text">${esc(jenisLabel[item.jenis] || item.jenis)}</span></td>
                <td><span class="plain-text">${esc(item.jabatan || '-')}</span></td>
                <td class="center"><span class="year-chip">${fmtDate(item.tanggal_mulai)} &ndash; ${fmtDate(item.tanggal_selesai)}</span></td>
                <td class="center">
                    <a href="${esc(item.bukti_kegiatan)}" target="_blank" rel="noopener noreferrer" class="evidence-link">
                        <span class="material-symbols-outlined">cloud</span><span>Lihat Bukti</span>
                    </a>
                </td>
                <td class="center">
                    <div class="row-actions">
                        <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                            data-id="${item.id}" data-user_id="${item.user_id}" data-jenis="${item.jenis}"
                            data-mitra="${encodeURIComponent(item.mitra)}"
                            data-jabatan="${encodeURIComponent(item.jabatan || '')}"
                            data-tanggal_mulai="${item.tanggal_mulai || ''}"
                            data-tanggal_selesai="${item.tanggal_selesai || ''}"
                            data-bukti_kegiatan="${encodeURIComponent(item.bukti_kegiatan)}"
                            data-bukti_tambahan="${encodeURIComponent(item.bukti_tambahan || '')}">
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
                user_id: document.getElementById('form-user_id').value,
                jenis,
                mitra: document.getElementById('form-mitra').value,
                jabatan: jenis === 'alumni' ? document.getElementById('form-jabatan').value : null,
                tanggal_mulai: document.getElementById('form-tanggal_mulai').value,
                tanggal_selesai: document.getElementById('form-tanggal_selesai').value,
                bukti_kegiatan: document.getElementById('form-bukti_kegiatan').value,
                bukti_tambahan: document.getElementById('form-bukti_tambahan').value || null,
            };

            if (!payload.user_id) {
                modalError.textContent = 'Pilih nama dosen/mahasiswa terlebih dahulu.';
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

        async function handleDelete(id) {
            if (!confirm('Hapus data ini? Tindakan tidak bisa dibatalkan.')) return;
            try {
                const res = await fetch(`/lppm/rekognisi/${id}`, {
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