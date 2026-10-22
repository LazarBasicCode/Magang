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

    @include('partials.sidebar', ['active' => 'profil'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'profil'])


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
                        {{-- Klik foto = ganti foto profil (modal: partials/avatar-modal.blade.php, ada di header) --}}
                        <span class="av-edit is-big" data-avatar-edit role="button" tabindex="0" title="Ganti foto profil" aria-label="Ganti foto profil">
                            <span class="pf-glass" aria-hidden="true"></span>
                            @include('partials.avatar', ['class' => 'avatar pf-avatar', 'initials' => $user->initials(), 'color' => $user->avatarColorClass(), 'url' => $user->avatar_url, 'self' => true])
                            <span class="av-edit-badge material-symbols-outlined" aria-hidden="true">photo_camera</span>
                        </span>
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
                                    <small>Email pemulihan belum diatur. Atur sekarang agar akun mudah dipulihkan.</small>
                                    <button type="button" class="pf-btn" data-pf-click="btnEmailPemulihan" data-pf-open="1">Ganti email</button>
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
                                <span class="pf-set-text"><b>Ganti Email</b><small>Ubah email utama yang dipakai untuk pemulihan dan notifikasi.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-modal="pfContactOverlay">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">contact_phone</span></span>
                                <span class="pf-set-text"><b>Kontak Cadangan</b><small>Tambah email sekunder dan nomor telepon untuk pemulihan akun.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-click="btnShowAccessInfo" data-pf-open="1">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">shield_person</span></span>
                                <span class="pf-set-text"><b>Informasi Akses</b><small>Lihat daftar menu yang boleh Anda buka dan kelola.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-modal="pfLoginOverlay">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">devices</span></span>
                                <span class="pf-set-text"><b>Riwayat Login</b><small>Pantau perangkat dan waktu masuk akun Anda.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
                            <li><button type="button" class="pf-set" data-pf-modal="pfPrefOverlay">
                                <span class="pf-set-ico"><span class="material-symbols-outlined">notifications_active</span></span>
                                <span class="pf-set-text"><b>Preferensi Notifikasi</b><small>Atur jenis pemberitahuan yang ingin diterima.</small></span>
                                <span class="material-symbols-outlined pf-go">chevron_right</span>
                            </button></li>
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
                                                <button type="button" class="pf-note-item {{ $n['baca'] ? '' : 'is-unread' }}" data-id="{{ $n['id'] }}"
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

    {{-- Kontak cadangan: email sekunder + telepon (disimpan lewat POST /account/contact) --}}
    <div class="pa-overlay" id="pfContactOverlay" aria-hidden="true">
        <div class="pa-modal" role="dialog" aria-modal="true" aria-labelledby="pfContactTitle">
            <div class="pa-head">
                <span class="material-symbols-outlined">contact_phone</span>
                <span class="pa-title" id="pfContactTitle">Kontak Cadangan</span>
                <button type="button" class="pa-close" id="pfContactClose" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="pfContactForm" data-email="{{ $user->email }}" data-url="{{ route('account.contact') }}" novalidate>
                <div class="pa-body">
                    <div class="pa-field">
                        <label for="pfContactEmail">Email sekunder</label>
                        <div class="pa-input-wrap"><input type="email" id="pfContactEmail" placeholder="nama@contoh.com" autocomplete="email" value="{{ $user->secondary_email }}"></div>
                        <p class="pa-hint">Cadangan jika email utama tidak bisa diakses.</p>
                        <p class="pa-error"></p>
                    </div>
                    <div class="pa-field">
                        <label for="pfContactPhone">Nomor telepon</label>
                        <div class="pa-input-wrap"><input type="tel" id="pfContactPhone" placeholder="0812 3456 7890" autocomplete="tel" value="{{ $user->phone }}"></div>
                        <p class="pa-hint">Nomor seluler Indonesia untuk verifikasi pemulihan.</p>
                        <p class="pa-error"></p>
                    </div>
                </div>
                <div class="pa-foot">
                    <button type="button" class="pa-btn pa-btn-ghost" id="pfContactCancel">Batal</button>
                    <button type="submit" class="pa-btn pa-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Riwayat login: perangkat aktif + aktivitas masuk terakhir (data dimuat dari /account/login-history) --}}
    <div class="pa-overlay" id="pfLoginOverlay" aria-hidden="true" data-base="{{ url('/account') }}">
        <div class="pa-modal pf-wide" role="dialog" aria-modal="true" aria-labelledby="pfLoginTitle">
            <div class="pa-head">
                <span class="material-symbols-outlined">devices</span>
                <span class="pa-title" id="pfLoginTitle">Riwayat Login</span>
                <button type="button" class="pa-close" id="pfLoginClose" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="pa-body">
                <h3 class="pf-mini-title">Perangkat yang sedang login</h3>
                <ul class="pf-dev-list" id="pfSessions"><li class="pf-dev-empty">Memuat…</li></ul>
                <button type="button" class="pa-btn pa-btn-ghost pf-revoke-all" id="pfRevokeOthers" hidden>Keluar dari semua perangkat lain</button>
                <h3 class="pf-mini-title">Aktivitas masuk terakhir</h3>
                <ul class="pf-dev-list" id="pfAttempts"><li class="pf-dev-empty">Memuat…</li></ul>
            </div>
        </div>
    </div>

    {{-- Preferensi notifikasi: kategori yang bisa dimatikan; notifikasi keamanan selalu aktif --}}
    @php $__prefs = \App\Support\NotificationPrefs::forUser($user); @endphp
    <div class="pa-overlay" id="pfPrefOverlay" aria-hidden="true">
        <div class="pa-modal pf-wide" role="dialog" aria-modal="true" aria-labelledby="pfPrefTitle">
            <div class="pa-head">
                <span class="material-symbols-outlined">notifications_active</span>
                <span class="pa-title" id="pfPrefTitle">Preferensi Notifikasi</span>
                <button type="button" class="pa-close" id="pfPrefClose" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="pfPrefForm" data-url="{{ route('account.notification-preferences') }}" novalidate>
                <div class="pa-body">
                    @foreach(\App\Support\NotificationPrefs::CATEGORIES as $key => $cat)
                        <label class="pf-pref">
                            <span class="pf-pref-ico material-symbols-outlined">{{ $cat['icon'] }}</span>
                            <span class="pf-pref-text"><b>{{ $cat['label'] }}</b><small>{{ $cat['desc'] }}</small></span>
                            <span class="pf-sw"><input type="checkbox" name="{{ $key }}" @checked($__prefs[$key])><i></i></span>
                        </label>
                    @endforeach
                    <label class="pf-pref is-locked" title="Tidak bisa dimatikan demi keamanan akun">
                        <span class="pf-pref-ico material-symbols-outlined">{{ \App\Support\NotificationPrefs::LOCKED['icon'] }}</span>
                        <span class="pf-pref-text"><b>{{ \App\Support\NotificationPrefs::LOCKED['label'] }}</b><small>{{ \App\Support\NotificationPrefs::LOCKED['desc'] }} Selalu aktif.</small></span>
                        <span class="pf-sw"><input type="checkbox" checked disabled><i></i></span>
                    </label>
                </div>
                <div class="pa-foot">
                    <button type="button" class="pa-btn pa-btn-ghost" id="pfPrefCancel">Batal</button>
                    <button type="submit" class="pa-btn pa-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

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