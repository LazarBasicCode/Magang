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
    <script>
        (function() {
            try {
                var w = parseInt(localStorage.getItem('sida.sidebarW'), 10);
                if (w) document.documentElement.style.setProperty('--sidebar-w', (w < 140 ? 72 : Math.min(340, Math.max(200, w))) + 'px');
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}?v={{ @filemtime(public_path('css/laporan.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/backup.css') }}?v={{ @filemtime(public_path('css/backup.css')) }}">
    <title>Backup &amp; Restore &middot; SIDA</title>
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

    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <svg class="sidebar-brand-mark" aria-hidden="true"><use href="#sida-mark"/></svg>
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
                <a href="{{ url('/dashboard') }}" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>

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

                @if($__user->canAccessMenu('data_master') || $__user->canAccessMenu('hak_akses') || $__user->canAccessMenu('log'))
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
                @if($__user->canAccessMenu('log'))
                <a href="{{ url('/login-audit') }}" class="nav-link">
                    <span class="material-symbols-outlined">history</span>
                    <span>Log Aktivitas</span>
                </a>
                @endif
                @if($__user->canAccessMenu('backup'))
                <a href="{{ url('/backup') }}" class="nav-link is-active">
                    <span class="material-symbols-outlined">backup</span>
                    <span>Backup &amp; Restore</span>
                </a>
                @endif
                @if($__user->role === 'superadmin')
                <a href="{{ url('/laporan') }}" class="nav-link">
                    <span class="material-symbols-outlined">assignment</span>
                    <span>Laporan</span>
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
                        <a class="link" href="{{ url('/dashboard') }}">Dashboard</a>
                        <span>/</span>
                        <span class="current">Backup &amp; Restore</span>
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

                                <div class="notif-panel-body" id="notifListWrap" hidden></div>

                                {{-- Tampilan saat tidak ada notifikasi --}}
                                <div class="notif-empty" id="notifEmpty">
                                    <span class="material-symbols-outlined notif-empty-icon">notifications</span>
                                    <p class="notif-empty-title">Belum ada notifikasi</p>
                                    <p class="notif-empty-desc">Pemberitahuan baru akan muncul di sini.</p>
                                </div>
                            </div>
                        </div>
                        <!-- ============ /NOTIFIKASI ============ -->
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>

                        {{-- ============ PROFILE DROPDOWN ============ --}}
                        @php
                        $__initials = $__user->initials();
                        $__avatarColor = $__user->avatarColorClass();
                        $__accessRows = $__user->accessBreakdown();
                        @endphp
                        <div class="header-profile-dropdown" id="profileDropdown">
                            <button type="button" class="header-profile-trigger" id="profileToggleBtn"
                                aria-haspopup="true" aria-expanded="false">
                                <div class="header-profile-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                <div class="header-profile-text">
                                    <span class="header-profile-name">{{ $__user->name }}</span>
                                    <span class="header-profile-role">{{ $__user->accessLabelFor() }}</span>
                                </div>
                                <span class="material-symbols-outlined header-profile-caret">expand_more</span>
                            </button>

                            <div class="header-profile-panel" id="profilePanel" role="menu" aria-hidden="true">
                                {{-- ---- View 1: menu utama ---- --}}
                                <div class="header-profile-view is-active" id="profileViewMain">
                                    <div class="header-profile-panel-header">
                                        <div class="header-profile-panel-avatar {{ $__avatarColor }}">{{ $__initials }}</div>
                                        <div>
                                            <span class="header-profile-panel-name">{{ $__user->name }}</span>
                                            <span class="header-profile-panel-role">{{ $__user->accessLabelFor() }}</span>
                                        </div>
                                    </div>
                                    <div class="header-profile-menu">
                                        <button type="button" class="header-profile-menu-item" id="btnShowAccessInfo">
                                            <span class="material-symbols-outlined">shield_person</span>
                                            <span>Informasi Akses</span>
                                        </button>
                                        {{-- UI saja untuk saat ini, belum ada endpoint di baliknya --}}
                                        <button type="button" class="header-profile-menu-item" id="btnGantiPassword">
                                            <span class="material-symbols-outlined">key</span>
                                            <span>Ganti Password</span>
                                        </button>
                                        <button type="button" class="header-profile-menu-item" id="btnEmailPemulihan">
                                            <span class="material-symbols-outlined">mark_email_unread</span>
                                            <span>Email Pemulihan</span>
                                        </button>
                                        <div class="header-profile-menu-divider"></div>
                                        <form method="POST" action="{{ url('/logout') }}" id="logoutForm">
                                            @csrf
                                            <button type="submit" class="header-profile-menu-item is-danger">
                                                <span class="material-symbols-outlined">logout</span>
                                                <span>Keluar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- ---- View 2: rincian hak akses per menu ---- --}}
                                <div class="header-profile-view" id="profileViewAccess">
                                    <button type="button" class="header-profile-panel-back" id="btnBackToMain">
                                        <span class="material-symbols-outlined">arrow_back</span>
                                        <span>Informasi Akses</span>
                                    </button>
                                    <div class="access-info-list">
                                        @foreach($__accessRows as $row)
                                        <div class="access-info-row {{ $row['level'] === 'none' ? 'is-zero' : '' }}">
                                            <span class="material-symbols-outlined">{{ $row['icon'] }}</span>
                                            <span class="access-info-row-label">{{ $row['label'] }}</span>
                                            <span class="access-chip {{ $row['level'] }}">
                                                <span class="access-dot {{ $row['level'] }}"></span>
                                                {{ $row['level_label'] }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ============ /PROFILE DROPDOWN ============ --}}
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap rp-page bk-page">
                @php
                    $totalNow = array_sum($current);
                    $last = $backups[0] ?? null;
                    $fmtSize = fn ($b) => $b >= 1048576 ? number_format($b / 1048576, 2, ',', '.') . ' MB' : number_format(max($b, 1) / 1024, 1, ',', '.') . ' KB';
                @endphp

                <div class="rp-toolbar">
                    <div>
                        <h1 class="rp-title">Backup &amp; Restore</h1>
                        <p class="rp-sub">Unduh seluruh data SIDA sebagai satu file ZIP, dan pulihkan kembali bila diperlukan.</p>
                    </div>
                </div>

                @unless($zipOk)
                    <div class="bk-alert is-danger">
                        <span class="material-symbols-outlined">error</span>
                        <div><strong>Ekstensi PHP "zip" belum aktif.</strong> Aktifkan <code>extension=zip</code> di php.ini lalu restart server, baru fitur ini bisa dipakai.</div>
                    </div>
                @endunless

                <div class="rp-kpi-grid">
                    <div class="rp-kpi"><span class="rp-kpi-label">Total Data Saat Ini</span><span class="rp-kpi-value">{{ number_format($totalNow, 0, ',', '.') }}</span><span class="rp-kpi-note">baris di {{ count($current) }} tabel</span></div>
                    <div class="rp-kpi"><span class="rp-kpi-label">Backup Tersimpan</span><span class="rp-kpi-value">{{ count($backups) }}</span><span class="rp-kpi-note">file di server</span></div>
                    <div class="rp-kpi"><span class="rp-kpi-label">Backup Terakhir</span><span class="rp-kpi-value bk-kpi-small">{{ $last ? \Carbon\Carbon::createFromTimestamp($last['mtime'])->diffForHumans() : 'Belum ada' }}</span><span class="rp-kpi-note">{{ $last ? \Carbon\Carbon::createFromTimestamp($last['mtime'])->translatedFormat('d M Y, H:i') : 'Buat backup pertama Anda' }}</span></div>
                </div>

                <!-- ===== BUAT BACKUP ===== -->
                <section class="rp-card">
                    <div class="bk-head">
                        <h2>Buat Backup</h2>
                        <p>Satu file ZIP berisi semua data di bawah ini, lengkap dengan checksum untuk memastikan file tidak rusak.</p>
                    </div>
                    <div class="bk-chips">
                        @foreach($tables as $key => $label)
                            @if(isset($current[$key]))
                                <span class="bk-chip"><strong>{{ number_format($current[$key], 0, ',', '.') }}</strong> {{ $label }}</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="bk-alert is-warn">
                        <span class="material-symbols-outlined">lock</span>
                        <div>File backup berisi data pribadi dan <em>hash</em> password seluruh pengguna. Simpan di tempat aman dan jangan dibagikan.</div>
                    </div>
                    <div class="bk-actions">
                        <form method="POST" action="{{ url('/backup/create') }}" id="bkCreateDownload">
                            @csrf <input type="hidden" name="mode" value="download">
                            <button type="submit" class="rp-btn rp-btn-primary" @disabled(!$zipOk)><span class="material-symbols-outlined">download</span> Buat &amp; Unduh</button>
                        </form>
                        <form method="POST" action="{{ url('/backup/create') }}">
                            @csrf <input type="hidden" name="mode" value="store">
                            <button type="submit" class="rp-btn" @disabled(!$zipOk)><span class="material-symbols-outlined">save</span> Simpan di Server</button>
                        </form>
                    </div>
                </section>

                <!-- ===== RIWAYAT ===== -->
                <section class="rp-card">
                    <div class="bk-head">
                        <h2>Backup di Server</h2>
                        <p>Disimpan di <code>storage/app/backups</code>. File bernama <em>pre-restore</em> dibuat otomatis sebelum setiap pemulihan.</p>
                    </div>
                    @if(count($backups))
                        <div class="rp-table-wrap">
                            <table class="rp-table">
                                <thead><tr><th>File</th><th>Dibuat</th><th>Oleh</th><th class="num">Ukuran</th><th class="num">Baris</th><th class="num">Aksi</th></tr></thead>
                                <tbody>
                                    @foreach($backups as $b)
                                        <tr>
                                            <td>
                                                <span class="bk-file">{{ $b['name'] }}</span>
                                                @if($b['label'])<span class="bk-tag">{{ $b['label'] }}</span>@endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::createFromTimestamp($b['mtime'])->translatedFormat('d M Y, H:i') }}</td>
                                            <td>{{ $b['created_by'] ?? '—' }}</td>
                                            <td class="num">{{ $fmtSize($b['size']) }}</td>
                                            <td class="num">{{ $b['rows'] !== null ? number_format($b['rows'], 0, ',', '.') : '—' }}</td>
                                            <td class="num">
                                                <div class="bk-row-actions">
                                                    <a class="bk-icon-btn" href="{{ url('/backup/download/' . $b['name']) }}" title="Unduh"><span class="material-symbols-outlined">download</span></a>
                                                    @if($canRestore)
                                                        <button type="button" class="bk-icon-btn" data-restore-stored="{{ $b['name'] }}" title="Pulihkan dari backup ini"><span class="material-symbols-outlined">restore</span></button>
                                                        <form method="POST" action="{{ url('/backup/' . $b['name']) }}" onsubmit="return confirm('Hapus file backup ini dari server?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="bk-icon-btn is-danger" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rp-empty">Belum ada backup tersimpan. Gunakan "Simpan di Server" di atas.</p>
                    @endif
                </section>

                <!-- ===== PULIHKAN DARI FILE ===== -->
                @if($canRestore)
                    <section class="rp-card">
                        <div class="bk-head">
                            <h2>Pulihkan dari File</h2>
                            <p>Unggah file backup (.zip) dari komputer Anda. File diperiksa lebih dulu, tidak ada data yang berubah sebelum Anda mengonfirmasi.</p>
                        </div>
                        <label class="bk-drop" id="bkDrop">
                            <span class="material-symbols-outlined">upload_file</span>
                            <strong>Pilih atau tarik file backup ke sini</strong>
                            <small>Format .zip dari menu Backup SIDA, maksimal 100 MB</small>
                            <input type="file" id="bkFile" accept=".zip,application/zip" hidden>
                        </label>
                    </section>
                @endif
            </div>
        </main>
    </div>

    <!-- ===== MODAL KONFIRMASI RESTORE ===== -->
    <div class="bk-overlay" id="bkOverlay" aria-hidden="true">
        <div class="bk-modal" role="dialog" aria-modal="true" aria-labelledby="bkModalTitle">
            <div class="bk-modal-head">
                <div class="bk-modal-icon"><span class="material-symbols-outlined">warning</span></div>
                <div>
                    <h3 id="bkModalTitle">Pulihkan data dari backup?</h3>
                    <p id="bkModalMeta"></p>
                </div>
            </div>
            <div class="bk-modal-body">
                <div class="bk-alert is-danger">
                    <span class="material-symbols-outlined">error</span>
                    <div><strong>Semua data saat ini akan diganti</strong> dengan isi backup. Sebelum itu sistem otomatis membuat backup <em>pre-restore</em> dari kondisi sekarang, dan semua pengguna (termasuk Anda) akan diminta login ulang.</div>
                </div>
                <div class="rp-table-wrap">
                    <table class="rp-table bk-compare">
                        <thead><tr><th>Tabel</th><th class="num">Saat ini</th><th class="num">Di backup</th></tr></thead>
                        <tbody id="bkCompare"></tbody>
                    </table>
                </div>
                <ul class="bk-warnings" id="bkWarnings"></ul>
                <label class="bk-confirm-label" for="bkConfirm">Ketik <strong>PULIHKAN</strong> untuk melanjutkan</label>
                <input type="text" id="bkConfirm" class="bk-confirm" autocomplete="off" placeholder="PULIHKAN">
            </div>
            <div class="bk-modal-foot">
                <button type="button" class="rp-btn" id="bkCancel">Batal</button>
                <button type="button" class="rp-btn bk-btn-danger" id="bkRun" disabled><span class="material-symbols-outlined">restore</span> Pulihkan Sekarang</button>
            </div>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script>
        @if(session('backup_status')) Toast.show({ type: 'success', message: @json(session('backup_status')) }); @endif
        @if(session('backup_error')) Toast.show({ type: 'error', title: 'Backup gagal', message: @json(session('backup_error')) }); @endif
        @if($errors->any()) Toast.show({ type: 'error', message: @json($errors->first()) }); @endif
    </script>
    <script src="{{ asset('js/backup.js') }}?v={{ @filemtime(public_path('js/backup.js')) }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>
