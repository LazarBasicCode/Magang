{{--
    Sidebar global SIDA (brand + navigasi + kartu bantuan).

    Dipakai oleh semua halaman utama via:
        @include('partials.sidebar', ['active' => '<kunci menu>'])

    Kunci menu yang dikenal ($active):
        dashboard | kemahasiswaan | lppm_mahasiswa | lppm_dosen | rekognisi
        kerja_sama | data_master | hak_akses | log | backup | laporan | profil

    Visibilitas tiap menu ditentukan User::canAccessMenu() (lihat HakAkses),
    jadi menu yang tidak boleh diakses role tersebut otomatis tidak dirender.
    $__user disuntikkan oleh View::composer di AppServiceProvider — nama view
    ini ('partials.sidebar') juga didaftarkan di sana supaya tetap aman
    walau partial dipanggil dari luar konteks halaman utama.
--}}
@php
    $active = $active ?? null;
    $navActive = fn ($key) => $active === $key;
@endphp

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
            <a href="{{ url('/dashboard') }}"@if($navActive('dashboard')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>

            {{-- Persetujuan pengajuan mahasiswa: hanya dosen (wali) & superadmin. Lencana = jumlah yang menunggu tindakan. --}}
            @if(in_array($__user->role, ['dosen', 'superadmin'], true))
            @php $__approvalPending = \App\Support\Approval::pendingCount($__user); @endphp
            <a href="{{ url('/persetujuan') }}"@if($navActive('persetujuan')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">fact_check</span>
                <span>Persetujuan</span>
                @if($__approvalPending > 0)<span class="nav-badge">{{ $__approvalPending > 99 ? '99+' : $__approvalPending }}</span>@endif
            </a>
            @endif

            @if($__user->canAccessMenu('kemahasiswaan'))
            <a href="{{ url('/kemahasiswaan') }}"@if($navActive('kemahasiswaan')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">school</span>
                <span>Kemahasiswaan</span>
            </a>
            @endif

            @if($__user->canAccessMenu('lppm_mahasiswa') || $__user->canAccessMenu('lppm_dosen') || $__user->canAccessMenu('rekognisi'))
            <div class="nav-heading">LPPM</div>
            @endif
            @if($__user->canAccessMenu('lppm_mahasiswa'))
            <a href="{{ url('/lppm/mahasiswa') }}"@if($navActive('lppm_mahasiswa')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">person</span>
                <span>Mahasiswa</span>
            </a>
            @endif
            @if($__user->canAccessMenu('lppm_dosen'))
            <a href="{{ url('/lppm/dosen') }}"@if($navActive('lppm_dosen')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">co_present</span>
                <span>Dosen</span>
            </a>
            @endif
            @if($__user->canAccessMenu('rekognisi'))
            <a href="{{ url('/lppm/rekognisi') }}"@if($navActive('rekognisi')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">workspace_premium</span>
                <span>Rekognisi</span>
            </a>
            @endif

            @if($__user->canAccessMenu('kerja_sama'))
            <div class="nav-heading">Kemitraan</div>
            @endif
            @if($__user->canAccessMenu('kerja_sama'))
            <a href="{{ url('/kerja-sama') }}"@if($navActive('kerja_sama')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">handshake</span>
                <span>Kerja Sama</span>
            </a>
            @endif

            @if($__user->canAccessMenu('data_master') || $__user->canAccessMenu('hak_akses') || $__user->canAccessMenu('log'))
            <div class="nav-heading">Administrasi</div>
            @endif
            @if($__user->canAccessMenu('data_master'))
            <a href="{{ url('/data-master/users') }}"@if($navActive('data_master')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">manage_accounts</span>
                <span>Data Master</span>
            </a>
            @endif
            @if($__user->canAccessMenu('hak_akses'))
            <a href="{{ url('/hak-akses') }}"@if($navActive('hak_akses')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">admin_panel_settings</span>
                <span>Hak Akses</span>
            </a>
            @endif
            @if($__user->canAccessMenu('log'))
            <a href="{{ url('/login-audit') }}"@if($navActive('log')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">history</span>
                <span>Log Aktivitas</span>
            </a>
            @endif
            @if($__user->canAccessMenu('backup'))
            <a href="{{ url('/backup') }}"@if($navActive('backup')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">backup</span>
                <span>Backup &amp; Restore</span>
            </a>
            @endif
            @if($__user->role === 'superadmin')
            <a href="{{ url('/laporan') }}"@if($navActive('laporan')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
                <span class="material-symbols-outlined">assignment</span>
                <span>Laporan</span>
            </a>
            @endif
            <a href="{{ url('/profil') }}"@if($navActive('profil')) aria-current="page" class="nav-link is-active"@else class="nav-link"@endif>
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
