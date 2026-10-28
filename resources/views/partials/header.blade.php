{{--
    Header global SIDA (hamburger + breadcrumb + notifikasi + tema + profil).

    Dipakai oleh semua halaman utama via (kunci sama dengan sidebar):
        @include('partials.header', ['active' => '<kunci menu>'])

    Teks breadcrumb diambil dari $crumbs di bawah (kunci => [induk, aktif, url induk]).
    Opsional untuk kasus khusus (mis. judul dinamis):
        crumbParent / crumbCurrent / crumbParentUrl   menimpa isi $crumbs

    Label peran di profil mengikuti hak akses menu ($active) bila menu itu
    ada di HakAkses::MENUS; selain itu label peran biasa.

    $__user disuntikkan oleh View::composer di AppServiceProvider.
--}}
@php
    $active = $active ?? null;

    // kunci menu => [induk, teks aktif, url induk (opsional)]
    $crumbs = [
        'dashboard'      => [null, 'Dashboard'],
        'kemahasiswaan'  => ['Kemahasiswaan', 'Data Prestasi & Kegiatan'],
        'rekognisi'      => ['LPPM', 'Rekognisi'],
        'kerja_sama'     => ['Kemitraan', 'Kerja Sama'],
        'data_master'    => ['Administrasi', 'Data Master Pengguna'],
        'hak_akses'      => ['Administrasi', 'Hak Akses'],
        'log'            => ['Administrasi', 'Log Aktivitas'],
        'backup'         => ['Dashboard', 'Backup & Restore', '/dashboard'],
        'laporan'        => ['Dashboard', 'Laporan', '/dashboard'],
        'persetujuan'    => ['Dashboard', 'Persetujuan', '/dashboard'],
        'profil'         => ['Akun', 'Profil Saya'],
    ];
    $__crumb   = $crumbs[$active] ?? [null, ''];
    $crumbParent    = $crumbParent ?? $__crumb[0];
    $crumbCurrent   = $crumbCurrent ?? $__crumb[1];
    $crumbParentUrl = $crumbParentUrl ?? (isset($__crumb[2]) ? url($__crumb[2]) : null);
    $menu      = isset(\App\Models\HakAkses::MENUS[$active]) ? $active : null;
    $isProfil  = $active === 'profil';
@endphp
<header class="app-header">
    <div class="header-capsule">
        <div class="header-inner">
            <button type="button" class="icon-btn sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Buka Menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="header-crumb">
                @if(!empty($crumbParent))
                    @if(!empty($crumbParentUrl))
                        <a class="link" href="{{ $crumbParentUrl }}">{{ $crumbParent }}</a>
                    @else
                        <span class="link">{{ $crumbParent }}</span>
                    @endif
                    <span>/</span>
                @endif
                <span class="current">{{ $crumbCurrent }}</span>
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
$__roleLabel = !empty($isProfil) ? ucfirst($__user->role) : $__user->accessLabelFor($menu ?? null);
                @endphp
                <div class="header-profile-dropdown" id="profileDropdown">
                    <button type="button" class="header-profile-trigger" id="profileToggleBtn"
                        aria-haspopup="true" aria-expanded="false">
                        @include('partials.avatar', ['class' => 'header-profile-avatar', 'initials' => $__initials, 'color' => $__avatarColor, 'url' => $__user->avatar_url, 'self' => true])
                        <div class="header-profile-text">
                            <span class="header-profile-name">{{ $__user->name }}</span>
                            <span class="header-profile-role">{{ $__roleLabel }}</span>
                        </div>
                        <span class="material-symbols-outlined header-profile-caret">expand_more</span>
                    </button>

                    <div class="header-profile-panel" id="profilePanel" role="menu" aria-hidden="true">
                        {{-- ---- View 1: menu utama ---- --}}
                        <div class="header-profile-view is-active" id="profileViewMain">
                            <div class="header-profile-panel-header">
                                {{-- Klik avatar = ganti foto profil (modal: partials/avatar-modal.blade.php) --}}
                                @include('partials.avatar', ['class' => 'header-profile-panel-avatar', 'initials' => $__initials, 'color' => $__avatarColor, 'url' => $__user->avatar_url, 'self' => true, 'edit' => true])
                                <div>
                                    <span class="header-profile-panel-name">{{ $__user->name }}</span>
                                    <span class="header-profile-panel-role">{{ $__roleLabel }}</span>
                                </div>
                            </div>
                            <div class="header-profile-menu">
                                @if(!empty($isProfil))
<a href="{{ url('/profil') }}" class="header-profile-menu-item">
    <span class="material-symbols-outlined">account_circle</span>
    <span>Profil Saya</span>
</a>
@endif
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
                                    <span>{{ !empty($isProfil) ? 'Ganti Email' : 'Email Pemulihan' }}</span>
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


@include('partials.avatar-modal')
