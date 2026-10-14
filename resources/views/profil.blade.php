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
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/delete-confirm.css') }}?v={{ @filemtime(public_path('css/delete-confirm.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ @filemtime(public_path('css/profile.css')) }}">
    <title>Profil &middot; SIDA</title>
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
                @if($__user->canAccessMenu('log'))
                <a href="{{ url('/login-audit') }}" class="nav-link">
                    <span class="material-symbols-outlined">history</span>
                    <span>Log Aktivitas</span>
                </a>
                @endif
                @if($__user->canAccessMenu('backup'))
                <a href="{{ url('/backup') }}" class="nav-link">
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
                <a href="{{ url('/profil') }}" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">account_circle</span>
                    <span>Profil Saya</span>
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
                        <span class="link">Akun</span>
                        <span>/</span>
                        <span class="current">Profil Saya</span>
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

                                <div class="notif-empty" id="notifEmpty">
                                    <span class="material-symbols-outlined notif-empty-icon">notifications</span>
                                    <p class="notif-empty-title">Belum ada notifikasi</p>
                                    <p class="notif-empty-desc">Pemberitahuan baru akan muncul di sini.</p>
                                </div>
                                <!-- <div class="notif-panel-footer">
                                    <a href="#" class="notif-view-all">Lihat semua notifikasi</a>
                                </div> -->
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
                                    <span class="header-profile-role">{{ ucfirst($__user->role) }}</span>
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
                                            <span class="header-profile-panel-role">{{ ucfirst($__user->role) }}</span>
                                        </div>
                                    </div>
                                    <div class="header-profile-menu">
                                        <a href="{{ url('/profil') }}" class="header-profile-menu-item">
                                            <span class="material-symbols-outlined">account_circle</span>
                                            <span>Profil Saya</span>
                                        </a>
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
            <div class="page-wrap pf-page is-loading" id="pageWrap" aria-busy="true">

                @php
                    $idNumber = $isDosen ? optional($profile)->nidn : optional($profile)->nim;
                    $idNumber = $idNumber ?: $user->nim_nidn;
                    $maxTahun = max(1, (int) $perTahun->max());
                    // Kelengkapan profil (hanya tampilan): email pemulihan, nomor induk, angkatan (mhs), status
                    $checks = [
                        'email'    => !empty($user->email),
                        'nomor'    => !empty($idNumber),
                        'angkatan' => $isDosen ? true : !empty(optional($profile)->angkatan),
                        'status'   => !empty(optional($profile)->status),
                    ];
                    $pct = (int) round(count(array_filter($checks)) / count($checks) * 100);
                @endphp

                {{-- ===== Hero: tanpa border, menyatu dengan latar ===== --}}
                <section class="pf-hero">
                    <div class="pf-palette">
                        <button type="button" class="pf-palette-btn" id="pfPaletteBtn" aria-expanded="false" aria-label="Ubah warna tampilan (sementara)" title="Ubah warna (sementara)">
                            <span class="material-symbols-outlined">format_paint</span>
                        </button>
                        <div class="pf-swatches" id="pfSwatches" hidden></div>
                    </div>

                    <div class="pf-hero-inner">
                        <div class="avatar pf-avatar {{ $user->avatarColorClass() }}">{{ $user->initials() }}</div>
                        <div class="pf-hero-text">
                            <h1 class="pf-name">{{ $user->name }}</h1>
                            <p class="pf-role">
                                {{ $isDosen ? 'Dosen' : 'Mahasiswa' }}@if(!$isDosen && optional($profile)->angkatan) &middot; Angkatan {{ $profile->angkatan }}@endif
                            </p>
                            <div class="pf-meta">
                                @if($idNumber)
                                    <span class="pf-chip is-locked"><span class="material-symbols-outlined">badge</span>{{ $idNumber }}<span class="material-symbols-outlined">lock</span></span>
                                @endif
                                @if($user->email)
                                    <span class="pf-chip"><span class="material-symbols-outlined">mail</span>{{ $user->email }}</span>
                                @endif
                                @if($profile && $profile->status)
                                    <span class="badge {{ $profile->statusBadge() }}">{{ $profile->statusLabel() }}</span>
                                @endif
                            </div>
                            <div class="pf-hero-total">
                                <span class="pf-total-num">{{ $total }}</span>
                                <span class="pf-total-label">data tercatat <br>atas nama Anda</span>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="pf-layout">

                    {{-- ===== Aside pengaturan: 1 tombol melebar per baris ===== --}}
                    <aside class="pf-aside" id="pfAside" aria-label="Pengaturan akun">
                        <h2 class="pf-sec-title">Kelengkapan Profil</h2>
                        <section class="pf-complete">
                            <div class="pf-ring" style="--p: {{ $pct }}"><span>{{ $pct }}%</span></div>
                            <div class="pf-complete-text">
                                @if(!$checks['email'])
                                    <small>Tambahkan email pemulihan agar akun mudah dipulihkan.</small>
                                    <button type="button" class="pf-btn" data-pf-click="btnEmailPemulihan" data-pf-open="1">Tambah email</button>
                                @else
                                    <small>{{ $pct === 100 ? 'Profil Anda sudah lengkap.' : 'Hubungi admin untuk melengkapi data induk.' }}</small>
                                @endif
                            </div>
                        </section>

                        <h2 class="pf-sec-title">Pengaturan Akun</h2>
                        <ul class="pf-setlist">
                            <li><button type="button" class="pf-set" data-pf-click="btnGantiPassword" data-pf-open="1">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">lock_reset</span></span>
                                <span class="pf-set-text"><b>Ganti Password</b><small>Perbarui kata sandi secara berkala agar akun tetap aman.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-click="btnEmailPemulihan" data-pf-open="1">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">alternate_email</span></span>
                                <span class="pf-set-text"><b>Email Pemulihan</b><small>Dipakai saat lupa password dan untuk menerima notifikasi.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-click="btnShowAccessInfo" data-pf-open="1">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">shield_person</span></span>
                                <span class="pf-set-text"><b>Informasi Akses</b><small>Lihat daftar menu yang boleh Anda buka dan kelola.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><div class="pf-set is-soon" aria-disabled="true">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">devices</span></span>
                                <span class="pf-set-text"><b>Riwayat Login</b><small>Pantau perangkat dan waktu masuk akun Anda.</small></span>
                                <span class="pf-soon">Segera</span>
                            </div></li>
                            <li><div class="pf-set is-soon" aria-disabled="true">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">notifications_active</span></span>
                                <span class="pf-set-text"><b>Preferensi Notifikasi</b><small>Atur jenis pemberitahuan yang ingin diterima.</small></span>
                                <span class="pf-soon">Segera</span>
                            </div></li>
                        </ul>
                    </aside>

                    <div class="pf-main">

                        {{-- ===== Identitas ===== --}}
                        <section>
                            <h2 class="pf-sec-title">Identitas</h2>
                            <div class="pf-panel">
                                <dl class="pf-fields">
                                    <div class="pf-field"><dt>Nama</dt><dd>{{ $user->name }}<span class="material-symbols-outlined" title="Dikunci: bersumber dari Data Master">lock</span></dd></div>
                                    <div class="pf-field"><dt>{{ $isDosen ? 'NIDN' : 'NIM' }}</dt><dd>{{ $idNumber ?: '—' }}<span class="material-symbols-outlined" title="Dikunci: bersumber dari Data Master">lock</span></dd></div>
                                    @unless($isDosen)
                                        <div class="pf-field"><dt>Angkatan</dt><dd>{{ optional($profile)->angkatan ?? '—' }}</dd></div>
                                    @endunless
                                    <div class="pf-field"><dt>Status</dt><dd>{{ ($profile && $profile->status) ? $profile->statusLabel() : '—' }}</dd></div>
                                    <div class="pf-field"><dt>Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
                                    <div class="pf-field"><dt>Peran</dt><dd>{{ ucfirst($user->role) }}</dd></div>
                                </dl>
                                <p class="pf-note">Nama dan {{ $isDosen ? 'NIDN' : 'NIM' }} dikunci karena bersumber dari Data Master. Hubungi admin bila ada yang keliru.</p>
                            </div>
                        </section>

                        {{-- ===== Rekap per kategori ===== --}}
                        <section>
                            <h2 class="pf-sec-title">Rekap per Kategori</h2>
                            <div class="pf-cats">
                                @foreach($categories as $cat)
                                    <div class="pf-cat">
                                        <span class="material-symbols-outlined pf-cat-icon">{{ $cat['icon'] }}</span>
                                        <b>{{ $cat['count'] }}</b>
                                        <span class="pf-cat-label">{{ $cat['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        {{-- ===== Rekap per tahun & notifikasi ===== --}}
                        <div class="pf-duo">
                            <section>
                                <h2 class="pf-sec-title">Rekap per Tahun</h2>
                                <div class="pf-panel">
                                    @if($perTahun->isEmpty())
                                        <div class="pf-empty">Belum ada data.</div>
                                    @else
                                        <div class="pf-ybars">
                                            @foreach($perTahun->reverse() as $tahun => $jumlah)
                                                <div class="pf-ybar">
                                                    <b>{{ $jumlah }}</b>
                                                    <div class="pf-ybar-track"><i style="height: {{ max(8, round($jumlah / $maxTahun * 100)) }}%"></i></div>
                                                    <span>{{ $tahun }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </section>
                            <section>
                                <h2 class="pf-sec-title">Notifikasi Terbaru</h2>
                                <div class="pf-panel">
                                    <ul class="pf-notes">
                                        @forelse($notifs as $n)
                                            <li class="pf-note-li">
                                                <button type="button" class="pf-note-item {{ $n['baca'] ? '' : 'is-unread' }}"
                                                    data-judul="{{ $n['judul'] }}" data-pesan="{{ $n['pesan'] }}"
                                                    data-waktu="{{ $n['waktu'] ?? $n['relatif'] }}" data-baca="{{ $n['baca'] ? '1' : '0' }}">
                                                    <span class="pf-dot"></span>
                                                    <span class="pf-note-body">
                                                        <span class="pf-note-title">{{ $n['judul'] }}</span>
                                                        @if($n['pesan'])<span class="pf-note-sub">{{ \Illuminate\Support\Str::limit($n['pesan'], 80) }}</span>@endif
                                                    </span>
                                                    <span class="pf-time">{{ $n['relatif'] }}</span>
                                                </button>
                                            </li>
                                        @empty
                                            <li class="pf-empty">Belum ada notifikasi.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </section>
                        </div>

                        {{-- ===== Upload terbaru ===== --}}
                        <section>
                            <h2 class="pf-sec-title">Upload Terbaru</h2>
                            @if($terbaru->isEmpty())
                                <div class="pf-panel"><div class="pf-empty">Belum ada data yang diunggah.</div></div>
                            @else
                                <div class="pf-hscroll">
                                    @foreach($terbaru as $t)
                                        <article class="pf-up">
                                            <span class="pf-up-ico"><span class="material-symbols-outlined">{{ $t['icon'] }}</span></span>
                                            <p class="pf-up-title" title="{{ $t['judul'] }}">{{ $t['judul'] }}</p>
                                            <div class="pf-up-foot"><span>{{ $t['label'] }}@if($t['tahun']) &middot; {{ $t['tahun'] }}@endif</span><span>{{ $t['relatif'] }}</span></div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>

                        {{-- ===== Riwayat gabungan (garis waktu) ===== --}}
                        <section>
                            <h2 class="pf-sec-title">Riwayat Data <small>Tombol Edit membuka menu asalnya</small></h2>
                            <div class="pf-toolbar">
                                <div class="dropdown pf-dd" data-dropdown>
                                    <input type="hidden" id="pfKat" value="semua" />
                                    <button type="button" class="dropdown-trigger">
                                        <span class="dropdown-value">Semua Kategori</span>
                                        <span class="material-symbols-outlined caret">expand_more</span>
                                    </button>
                                    <div class="dropdown-panel">
                                        <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Kategori</button>
                                        @foreach($categories as $cat)
                                            <button type="button" class="dropdown-option" data-value="{{ $cat['key'] }}">{{ $cat['label'] }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="dropdown pf-dd" data-dropdown>
                                    <input type="hidden" id="pfTahun" value="semua" />
                                    <button type="button" class="dropdown-trigger">
                                        <span class="dropdown-value">Semua Tahun</span>
                                        <span class="material-symbols-outlined caret">expand_more</span>
                                    </button>
                                    <div class="dropdown-panel">
                                        <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Tahun</button>
                                        @foreach($years as $y)
                                            <button type="button" class="dropdown-option" data-value="{{ $y }}">{{ $y }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div id="pfList" aria-live="polite" aria-busy="true">
                                @for($s = 0; $s < 3; $s++)
                                    <div class="pf-sk-item"><span class="pf-sk pf-sk-ico"></span><span class="pf-sk-lines"><span class="pf-sk"></span><span class="pf-sk"></span></span></div>
                                @endfor
                            </div>
                            <div class="pf-foot">
                                <div>Menampilkan <strong id="pfRange">0</strong> dari <strong id="pfTotal">0</strong> data</div>
                                <nav class="pager" id="pager" aria-label="Navigasi halaman" hidden></nav>
                            </div>
                        </section>

                    </div>
                </div>

            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <dialog class="pf-dialog" id="pfNotifDialog" aria-labelledby="pfNotifTitle">
        <div class="pf-dialog-head">
            <span class="pf-dialog-ico"><span class="material-symbols-outlined">notifications</span></span>
            <div>
                <h3 id="pfNotifTitle"></h3>
                <p id="pfNotifTime"></p>
            </div>
            <button type="button" class="pf-dialog-x" id="pfNotifClose" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
        </div>
        <p class="pf-dialog-body" id="pfNotifBody"></p>
        <div class="pf-dialog-foot"><span class="pf-chip" id="pfNotifState"></span><button type="button" class="pf-btn" id="pfNotifOk">Tutup</button></div>
    </dialog>

    <script>
        window.PROFIL = {
            perPage: 8,
            items: {!! json_encode($riwayat, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '[]' !!}
        };
    </script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}?v={{ @filemtime(public_path('js/profile.js')) }}"></script>
</body>

</html>