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
    <title>Persetujuan &middot; SIDA</title>
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

    @include('partials.sidebar', ['active' => 'persetujuan'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'persetujuan'])

        <main class="app-main">
            <div class="page-wrap">

                @php
                    $isSuper = auth()->user()->role === 'superadmin';
                    $tabs = [
                        'perlu'   => ['Perlu Tindakan Saya', 'pending_actions'],
                        'proses'  => [$isSuper ? 'Belum Diproses Dosen Wali' : 'Sudah Diteruskan', 'hourglass_top'],
                        'riwayat' => ['Riwayat Keputusan', 'history'],
                    ];
                    $hints = [
                        'perlu'   => $isSuper
                            ? 'Pengajuan yang sudah diperiksa dosen wali. Setujui agar tampil di dashboard & laporan, atau tolak dengan alasan.'
                            : 'Pengajuan dari mahasiswa waliku. Periksa bukti & kelengkapannya, lalu teruskan ke Superadmin atau tolak dengan alasan.',
                        'proses'  => $isSuper
                            ? 'Pengajuan yang masih menunggu dosen wali. Kamu tetap bisa memutuskannya langsung bila perlu (mis. dosen wali berhalangan).'
                            : 'Pengajuan yang sudah kamu teruskan dan sedang menunggu keputusan Superadmin.',
                        'riwayat' => 'Pengajuan yang sudah diputuskan (disetujui / ditolak).',
                    ];
                @endphp

                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Persetujuan Pengajuan</h1>
                        <p class="page-subtitle">Alur ACC prestasi mahasiswa: mahasiswa &rarr; dosen wali &rarr; superadmin &middot; hanya data yang disetujui tampil di laporan</p>
                    </div>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">{{ $tabs['perlu'][0] }}</span>
                            <span class="stat-value" id="cntPerlu">{{ $counts['perlu'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">pending_actions</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">{{ $tabs['proses'][0] }}</span>
                            <span class="stat-value" id="cntProses">{{ $counts['proses'] }}</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">hourglass_top</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">{{ $tabs['riwayat'][0] }}</span>
                            <span class="stat-value" id="cntRiwayat">{{ $counts['riwayat'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">task_alt</span></div>
                    </div>
                </div>

                <div class="appr-tabs" style="margin-top:20px;">
                    @foreach($tabs as $key => [$label, $icon])
                        <a href="{{ route('persetujuan.index', ['tab' => $key, 'menu' => $menu]) }}" class="appr-tab {{ $tab === $key ? 'is-active' : '' }}">
                            <span class="material-symbols-outlined" style="font-size:18px">{{ $icon }}</span>
                            <span>{{ $label }}</span>
                            <span class="appr-tab-count">{{ $counts[$key] }}</span>
                        </a>
                    @endforeach

                    <form class="appr-filter" method="GET" action="{{ route('persetujuan.index') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <label for="apprMenuFilter">Menu</label>
                        <select id="apprMenuFilter" name="menu" onchange="this.form.submit()">
                            <option value="semua" @selected($menu === 'semua')>Semua menu</option>
                            @foreach($menus as $slug => $label)
                                <option value="{{ $slug }}" @selected($menu === $slug)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <p class="page-subtitle" style="margin:0 0 14px;">{{ $hints[$tab] }}</p>

                <div class="appr-list" id="apprList">
                    @forelse($rows as $r)
                        <article class="appr-card" data-menu="{{ $r['menu'] }}" data-id="{{ $r['id'] }}" data-title="{{ $r['title'] }}">
                            <div class="appr-card-main">
                                <div class="appr-card-top">
                                    <span class="appr-menu"><span class="material-symbols-outlined">{{ $r['icon'] }}</span>{{ $r['menu_label'] }}</span>
                                    <span class="appr-chip is-{{ $r['status'] }}">{{ $r['status_label'] }}</span>
                                </div>
                                <h3 class="appr-title">{{ $r['title'] ?: '(tanpa judul)' }}</h3>
                                <p class="appr-who">
                                    {{ $r['name'] }}@if($r['nim']) &middot; NIM {{ $r['nim'] }}@endif
                                    @if($r['wali']) &middot; Dosen wali: {{ $r['wali'] }}@elseif($isSuper) &middot; <i>belum punya dosen wali</i>@endif
                                </p>

                                @if($r['detail'])
                                    <div class="appr-details">
                                        @foreach($r['detail'] as $k => $v)
                                            <span class="appr-detail">{{ $k }}: <b>{{ $v }}</b></span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($r['bukti'] && \Illuminate\Support\Str::startsWith($r['bukti'], ['http://', 'https://']))
                                    <a class="appr-bukti" href="{{ $r['bukti'] }}" target="_blank" rel="noopener noreferrer">
                                        <span class="material-symbols-outlined">open_in_new</span>Lihat bukti kegiatan
                                    </a>
                                @endif

                                @if($r['note'])
                                    <div class="appr-card-note"><b>Catatan:</b> {{ $r['note'] }}</div>
                                @endif
                            </div>

                            <div class="appr-side">
                                <div class="appr-meta">
                                    Diajukan {{ $r['submitted'] }}<br>
                                    Diperbarui {{ $r['updated'] }}
                                </div>
                                <div class="appr-actions">
                                    <button type="button" class="appr-btn js-appr-history" title="Lihat riwayat status">
                                        <span class="material-symbols-outlined">history</span>Riwayat
                                    </button>
                                    @if(in_array('teruskan', $r['actions'], true))
                                        <button type="button" class="appr-btn is-forward js-appr-act" data-aksi="teruskan">
                                            <span class="material-symbols-outlined">forward_to_inbox</span>Teruskan
                                        </button>
                                    @endif
                                    @if(in_array('setujui', $r['actions'], true))
                                        <button type="button" class="appr-btn is-ok js-appr-act" data-aksi="setujui">
                                            <span class="material-symbols-outlined">check_circle</span>Setujui
                                        </button>
                                    @endif
                                    @if(in_array('tolak', $r['actions'], true))
                                        <button type="button" class="appr-btn is-bad js-appr-act" data-aksi="tolak">
                                            <span class="material-symbols-outlined">cancel</span>Tolak
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="appr-empty">
                            <span class="material-symbols-outlined">task_alt</span>
                            <p style="margin:8px 0 0;font-weight:700;color:var(--ink)">Tidak ada pengajuan di sini</p>
                            <p style="margin:4px 0 0;font-size:13px;">{{ $tab === 'perlu' ? 'Semua pengajuan sudah ditangani. 🎉' : 'Belum ada data untuk ditampilkan.' }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Modal catatan (teruskan / setujui / tolak) --}}
    <div class="modal-backdrop" id="apprBackdrop"></div>
    <div class="modal-card" id="apprModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="apprDragHandle">
            <div>
                <h3 class="modal-title" id="apprModalTitle">Konfirmasi</h3>
                <p class="modal-subtitle" id="apprModalSub"></p>
            </div>
            <button type="button" class="modal-close-btn" id="apprCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="apprForm" class="modal-body" novalidate>
            <label class="field-label" for="apprNote" id="apprNoteLabel">Catatan</label>
            <textarea id="apprNote" class="appr-note-input" maxlength="1000" placeholder=""></textarea>
            <div class="bulk-result is-error" id="apprError" hidden style="margin-top:12px"></div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" id="apprCancelBtn">Batal</button>
                <button type="submit" class="btn-apply" id="apprSubmitBtn"><span id="apprSubmitText">Kirim</span></button>
            </div>
        </form>
    </div>

    {{-- Modal riwayat --}}
    <div class="modal-backdrop" id="apprHistBackdrop"></div>
    <div class="modal-card" id="apprHistModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="apprHistDrag">
            <div>
                <h3 class="modal-title">Riwayat Status</h3>
                <p class="modal-subtitle" id="apprHistSub"></p>
            </div>
            <button type="button" class="modal-close-btn" id="apprHistClose" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-body"><ul class="appr-timeline" id="apprHistList"></ul></div>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script src="{{ asset('js/approval.js') }}?v={{ @filemtime(public_path('js/approval.js')) }}"
        data-url-base="{{ url('/persetujuan') }}"></script>
</body>

</html>
