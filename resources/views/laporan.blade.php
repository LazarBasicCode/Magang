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
    <title>Laporan &middot; SIDA</title>
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

    @include('partials.sidebar', ['active' => 'laporan'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'laporan'])

        <main class="app-main">
            <div class="page-wrap rp-page rp-loading" id="rpPage">
                <noscript><style>.rp-loading>.rp-card{display:block!important}.rp-skel{display:none!important}</style></noscript>
                @php
                    $k = $report['kpi'];
                    $periode = $report['tahun'] ? 'Tahun ' . $report['tahun'] : 'Semua Tahun';
                    $modules = collect($report['modules']);
                @endphp

                <!-- ===== TOOLBAR (tidak ikut tercetak) ===== -->
                <div class="rp-toolbar no-print">
                    <div>
                        <h1 class="rp-title">Laporan</h1>
                        <p class="rp-sub">Rekap lengkap seluruh data SIDA. Ringkasannya juga tampil di dashboard.</p>
                    </div>
                    <form method="GET" action="{{ url('/laporan') }}" class="rp-filter">
                        <label for="rpTahun">Periode</label>
                        <select id="rpTahun" name="tahun" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            @foreach($report['years'] as $y)
                                <option value="{{ $y }}" @selected($report['tahun'] === $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                        <a href="#rpPrintPanel" class="rp-btn rp-btn-ghost">
                            <span class="material-symbols-outlined">print</span> Ke bagian cetak
                        </a>
                        <button type="button" class="rp-btn rp-btn-ghost" id="rpToggleAll">
                            <span class="material-symbols-outlined">unfold_less</span> <span id="rpToggleAllText">Tutup semua</span>
                        </button>
                    </form>
                </div>

                <!-- ===== KOP LAPORAN (hanya tampil saat dicetak) ===== -->
                <div class="rp-print-header">
                    <div class="rp-print-brand">SISTEM INFORMASI DATA AKADEMIK &middot; INSTITUT ASIA MALANG</div>
                    <h1>Laporan Rekapitulasi Data Akademik</h1>
                    <p>Periode: <strong>{{ $periode }}</strong> &nbsp;&middot;&nbsp; Dicetak: {{ $report['generated_at']->translatedFormat('d F Y, H:i') }} oleh {{ $__user->name }}</p>
                </div>

                <!-- ===== SKELETON (khusus laporan) ===== -->
                <div class="rp-skel" aria-hidden="true">
                    <section class="rp-card"><div class="rp-sk rp-sk-title"></div><div class="rp-sk rp-sk-line"></div>
                        <div class="rp-kpi-grid">@for($i=0;$i<6;$i++)<div class="rp-sk rp-sk-kpi"></div>@endfor</div></section>
                    <section class="rp-card"><div class="rp-sk rp-sk-title"></div><div class="rp-sk rp-sk-line"></div>
                        @for($i=0;$i<6;$i++)<div class="rp-sk rp-sk-row"></div>@endfor</section>
                    <section class="rp-card"><div class="rp-sk rp-sk-title"></div><div class="rp-sk rp-sk-line"></div>
                        <div class="rp-mini-grid">@for($i=0;$i<3;$i++)<div class="rp-sk rp-sk-mini"></div>@endfor</div></section>
                </div>

                <!-- ===== 1. RINGKASAN ===== -->
                <section class="rp-card" data-section="ringkasan">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Ringkasan Utama</h2>
                        <p>Angka kunci untuk periode: {{ $periode }}</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    <div class="rp-kpi-grid">
                        <div class="rp-kpi rp-c-blue"><span class="rp-kpi-ico"><span class="material-symbols-outlined">database</span></span><span class="rp-kpi-label">Total Data</span><span class="rp-kpi-value">{{ $k['total'] }}</span><span class="rp-kpi-note">di 5 menu operasional</span></div>
                        <div class="rp-kpi rp-c-teal"><span class="rp-kpi-ico"><span class="material-symbols-outlined">public</span></span><span class="rp-kpi-label">Capaian Internasional</span><span class="rp-kpi-value">{{ $k['intl_pct'] }}%</span><span class="rp-kpi-note">{{ $k['intl'] }} dari {{ $k['total'] }} data</span></div>
                        <div class="rp-kpi rp-c-violet"><span class="rp-kpi-ico"><span class="material-symbols-outlined">group</span></span><span class="rp-kpi-label">Total Akun</span><span class="rp-kpi-value">{{ $k['accounts'] }}</span><span class="rp-kpi-note">Mhs {{ $k['roles']['mahasiswa'] ?? 0 }} &middot; Dosen {{ $k['roles']['dosen'] ?? 0 }} &middot; Admin {{ ($k['roles']['admin'] ?? 0) + ($k['roles']['superadmin'] ?? 0) }}</span></div>
                        <div class="rp-kpi rp-c-orange"><span class="rp-kpi-ico"><span class="material-symbols-outlined">school</span></span><span class="rp-kpi-label">Mahasiswa Berkontribusi</span><span class="rp-kpi-value">{{ $k['mhs_pct'] }}%</span><span class="rp-kpi-note">{{ $k['mhs_active'] }} dari {{ $k['mhs_total'] }} mahasiswa</span></div>
                        <div class="rp-kpi rp-c-pink"><span class="rp-kpi-ico"><span class="material-symbols-outlined">co_present</span></span><span class="rp-kpi-label">Dosen Berkontribusi</span><span class="rp-kpi-value">{{ $k['dsn_pct'] }}%</span><span class="rp-kpi-note">{{ $k['dsn_active'] }} dari {{ $k['dsn_total'] }} dosen</span></div>
                        <div class="rp-kpi rp-c-red"><span class="rp-kpi-ico"><span class="material-symbols-outlined">gpp_bad</span></span><span class="rp-kpi-label">Login Gagal (30 hari)</span><span class="rp-kpi-value">{{ $report['security']['failed'] }}</span><span class="rp-kpi-note">{{ $report['security']['success'] }} berhasil &middot; {{ $report['security']['locked'] }} diblokir</span></div>
                    </div>
                </div></div>
</section>

                <!-- ===== 2. REKAP PER MODUL ===== -->
                <section class="rp-card" data-section="modul">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Rekap per Menu</h2>
                        <p>Jumlah data tiap menu, dan berapa yang berlabel internasional</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                                <tr><th>Menu</th><th class="num">Seluruh Waktu</th><th class="num">{{ $periode }}</th><th class="num">Internasional</th><th style="width:28%">Porsi periode</th></tr>
                            </thead>
                            <tbody>
                                @foreach($report['modules'] as $m)
                                    <tr>
                                        <td><span class="rp-cell-icon"><span class="material-symbols-outlined">{{ $m['icon'] }}</span>{{ $m['label'] }}</span></td>
                                        <td class="num">{{ $m['all'] }}</td>
                                        <td class="num"><strong>{{ $m['periode'] }}</strong></td>
                                        <td class="num">{{ $m['intl'] }}</td>
                                        <td><div class="rp-bar"><span style="--w: {{ $m['share'] }}%"></span></div><small>{{ $m['share'] }}%</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><td>Total</td><td class="num">{{ $modules->sum('all') }}</td><td class="num">{{ $modules->sum('periode') }}</td><td class="num">{{ $modules->sum('intl') }}</td><td></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div></div>
</section>

                <!-- ===== 3. RINCIAN ===== -->
                <section class="rp-card" data-section="rincian">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Rincian per Jenis &amp; Tingkat</h2>
                        <p>Sebaran data di dalam tiap menu (periode: {{ $periode }})</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    @if(count($report['breakdowns']))
                        <div class="rp-mini-grid">
                            @foreach($report['breakdowns'] as $g)
                                @php
                                    $mm = ['Kemahasiswaan'=>['blue','school'],'LPPM Mahasiswa'=>['teal','person'],'LPPM Dosen'=>['violet','co_present'],'Rekognisi'=>['amber','workspace_premium'],'Kerja Sama'=>['green','handshake']];
                                    [$mc, $mi] = $mm[\Illuminate\Support\Str::before($g['title'], ' ·')] ?? ['blue','bar_chart'];
                                @endphp
                                <div class="rp-mini rp-c-{{ $mc }}">
                                    <h3><span class="rp-mini-ico"><span class="material-symbols-outlined">{{ $mi }}</span></span>{{ $g['title'] }}</h3>
                                    @foreach($g['rows'] as $r)
                                        <div class="rp-mini-row">
                                            <div class="rp-mini-line"><span>{{ $r['label'] }}</span><strong>{{ $r['value'] }}</strong></div>
                                            <div class="rp-bar"><span style="--w: {{ $r['pct'] }}%"></span></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="rp-empty">Belum ada data pada periode ini.</p>
                    @endif
                </div></div>
</section>

                <!-- ===== 4. TREN PER TAHUN ===== -->
                <section class="rp-card" data-section="tren">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Tren per Tahun</h2>
                        <p>Perbandingan jumlah data tiap tahun (maksimal 6 tahun terakhir, tidak terpengaruh filter periode)</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    @php $maxTrend = max(1, collect($report['trend'])->max('total')); @endphp
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                                <tr><th>Tahun</th><th class="num">Kemahasiswaan</th><th class="num">LPPM Mhs</th><th class="num">LPPM Dosen</th><th class="num">Rekognisi</th><th class="num">Kerja Sama</th><th class="num">Total</th><th style="width:20%"></th></tr>
                            </thead>
                            <tbody>
                                @foreach($report['trend'] as $t)
                                    <tr>
                                        <td><strong>{{ $t['tahun'] }}</strong></td>
                                        <td class="num">{{ $t['kem'] }}</td><td class="num">{{ $t['lm'] }}</td><td class="num">{{ $t['ld'] }}</td><td class="num">{{ $t['rek'] }}</td><td class="num">{{ $t['ks'] }}</td>
                                        <td class="num"><strong>{{ $t['total'] }}</strong></td>
                                        <td><div class="rp-bar"><span style="--w: {{ round($t['total'] / $maxTrend * 100) }}%"></span></div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div></div>
</section>

                <!-- ===== 5. KONTRIBUTOR ===== -->
                <section class="rp-card" data-section="kontributor">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Kontributor Teraktif</h2>
                        <p>10 pengguna dengan data terbanyak lintas menu (periode: {{ $periode }})</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    @if(count($report['contributors']))
                        <div class="rp-table-wrap">
                            <table class="rp-table">
                                <thead><tr><th style="width:56px">#</th><th>Nama</th><th>Peran</th><th class="num">Jumlah Data</th></tr></thead>
                                <tbody>
                                    @foreach($report['contributors'] as $i => $c)
                                        <tr><td>{{ $i + 1 }}</td><td>{{ $c['name'] }}</td><td>{{ $c['role'] }}</td><td class="num"><strong>{{ $c['value'] }}</strong></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rp-empty">Belum ada kontributor pada periode ini.</p>
                    @endif
                </div></div>
</section>

                <!-- ===== 6. KUALITAS DATA ===== -->
                <section class="rp-card" data-section="kualitas">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Kualitas Data</h2>
                        <p>Hal yang masih perlu dilengkapi</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    <div class="rp-quality">
                        @foreach($report['quality'] as $q)
                            <div class="rp-quality-row {{ $q['value'] > 0 ? 'is-warn' : 'is-ok' }}">
                                <span class="material-symbols-outlined">{{ $q['value'] > 0 ? 'warning' : 'check_circle' }}</span>
                                <div class="rp-quality-text"><strong>{{ $q['label'] }}</strong><small>{{ $q['note'] }}</small></div>
                                <span class="rp-quality-value">{{ $q['value'] }}</span>
                                @if($q['url'] && $q['value'] > 0)<a class="no-print rp-link" href="{{ $q['url'] }}">Buka</a>@endif
                            </div>
                        @endforeach
                    </div>
                </div></div>
</section>

                <!-- ===== 7. KEAMANAN LOGIN ===== -->
                <section class="rp-card" data-section="keamanan">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Keamanan Login</h2>
                        <p>Aktivitas 30 hari terakhir (tidak terpengaruh filter periode)</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    @php $sec = $report['security']; @endphp
                    <div class="rp-kpi-grid rp-kpi-grid-4">
                        <div class="rp-kpi rp-c-green"><span class="rp-kpi-ico"><span class="material-symbols-outlined">check_circle</span></span><span class="rp-kpi-label">Berhasil</span><span class="rp-kpi-value">{{ $sec['success'] }}</span></div>
                        <div class="rp-kpi rp-c-red"><span class="rp-kpi-ico"><span class="material-symbols-outlined">cancel</span></span><span class="rp-kpi-label">Gagal</span><span class="rp-kpi-value">{{ $sec['failed'] }}</span></div>
                        <div class="rp-kpi rp-c-amber"><span class="rp-kpi-ico"><span class="material-symbols-outlined">lock</span></span><span class="rp-kpi-label">Diblokir</span><span class="rp-kpi-value">{{ $sec['locked'] }}</span></div>
                        <div class="rp-kpi rp-c-cyan"><span class="rp-kpi-ico"><span class="material-symbols-outlined">lan</span></span><span class="rp-kpi-label">IP Unik</span><span class="rp-kpi-value">{{ $sec['unique_ip'] }}</span></div>
                    </div>
                    <div class="rp-mini-grid">
                        <div class="rp-mini rp-c-red">
                            <h3><span class="rp-mini-ico"><span class="material-symbols-outlined">gpp_maybe</span></span>IP dengan percobaan gagal/diblokir terbanyak</h3>
                            @forelse($sec['top_ip'] as $r)
                                <div class="rp-mini-line"><span>{{ $r['label'] }}</span><strong>{{ $r['value'] }}</strong></div>
                            @empty
                                <p class="rp-empty">Tidak ada.</p>
                            @endforelse
                        </div>
                        <div class="rp-mini rp-c-amber">
                            <h3><span class="rp-mini-ico"><span class="material-symbols-outlined">person_off</span></span>Username paling sering gagal login</h3>
                            @forelse($sec['top_user'] as $r)
                                <div class="rp-mini-line"><span>{{ $r['label'] }}</span><strong>{{ $r['value'] }}</strong></div>
                            @empty
                                <p class="rp-empty">Tidak ada.</p>
                            @endforelse
                        </div>
                    </div>
                </div></div>
</section>

                <!-- ===== TANDA TANGAN (hanya tercetak) ===== -->
                <div class="rp-signature">
                    <p>Malang, {{ $report['generated_at']->translatedFormat('d F Y') }}</p>
                    <p>Mengetahui,</p>
                    <div class="rp-signature-space"></div>
                    <p><strong id="rpSignName">{{ $__user->name }}</strong></p>
                    <p id="rpSignRole">Super Admin</p>
                </div>

                <!-- ===== PANEL CETAK (paling bawah) ===== -->
                <section class="rp-card rp-print-panel no-print" id="rpPrintPanel">
                    <div class="rp-card-head" role="button" tabindex="0" aria-expanded="true"><div class="rp-card-title"><h2>Cetak Laporan</h2>
                        <p>Pilih bagian yang ingin dicetak, lalu tekan Cetak. Untuk PDF, pilih "Simpan sebagai PDF" di dialog cetak.</p></div><span class="material-symbols-outlined rp-acc-chev">expand_more</span></div>
<div class="rp-card-body"><div class="rp-card-inner">
                    <div class="rp-print-options">
                        @foreach(['ringkasan' => 'Ringkasan Utama', 'modul' => 'Rekap per Menu', 'rincian' => 'Rincian per Jenis & Tingkat', 'tren' => 'Tren per Tahun', 'kontributor' => 'Kontributor Teraktif', 'kualitas' => 'Kualitas Data', 'keamanan' => 'Keamanan Login'] as $key => $label)
                            <label class="rp-check"><input type="checkbox" class="rp-print-toggle" data-target="{{ $key }}" checked><span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                    <div class="rp-print-sign">
                        <div><label for="rpName">Nama penandatangan</label><input type="text" id="rpName" value="{{ $__user->name }}"></div>
                        <div><label for="rpRole">Jabatan</label><input type="text" id="rpRole" value="Super Admin"></div>
                    </div>
                    <button type="button" class="rp-btn rp-btn-primary" id="rpPrintBtn">
                        <span class="material-symbols-outlined">print</span> Cetak Laporan
                    </button>
                </div></div>
</section>

            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script>
        (function () {
            var toggles = document.querySelectorAll('.rp-print-toggle');
            var nameIn = document.getElementById('rpName');
            var roleIn = document.getElementById('rpRole');

            // Terapkan pilihan cetak: bagian yang tidak dicentang disembunyikan saat print.
            function applyPrintState() {
                toggles.forEach(function (t) {
                    var sec = document.querySelector('[data-section="' + t.dataset.target + '"]');
                    if (sec) sec.toggleAttribute('data-print-off', !t.checked);
                });
                document.getElementById('rpSignName').textContent = nameIn.value || '________';
                document.getElementById('rpSignRole').textContent = roleIn.value || '';
            }

            toggles.forEach(function (t) { t.addEventListener('change', applyPrintState); });
            nameIn.addEventListener('input', applyPrintState);
            roleIn.addEventListener('input', applyPrintState);
            window.addEventListener('beforeprint', applyPrintState); // juga saat Ctrl+P
            document.getElementById('rpPrintBtn').addEventListener('click', function () {
                applyPrintState();
                window.print();
            });
            applyPrintState();
        })();
    </script>

    <script>
        // Skeleton -> konten, lalu animasi angka 0 -> nilai asli (grafik bar dianimasikan lewat CSS)
        (function () {
            var page = document.getElementById('rpPage');
            var t0 = Date.now();
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var els = [].slice.call(document.querySelectorAll(
                '.rp-kpi-value, .rp-table td.num, .rp-table td.num strong, .rp-mini-line strong, .rp-quality-value'
            )).filter(function (el) { return !el.children.length && /^\d+%?$/.test(el.textContent.trim()); });
            els.forEach(function (el) { el._to = parseInt(el.textContent, 10); el._sfx = /%$/.test(el.textContent) ? '%' : ''; });

            function setAll(f) { els.forEach(function (el) { el.textContent = Math.round(el._to * f) + el._sfx; }); }
            function count() {
                if (reduce) return;
                var d = 1200, s = null;
                setAll(0);
                requestAnimationFrame(function step(ts) {
                    if (s === null) s = ts;
                    var p = Math.min((ts - s) / d, 1);
                    setAll(1 - Math.pow(1 - p, 3)); // easeOutCubic
                    if (p < 1) requestAnimationFrame(step); else setAll(1);
                });
            }
            function reveal() { page.classList.remove('rp-loading'); count(); }
            function ready() { setTimeout(reveal, Math.max(0, 500 - (Date.now() - t0))); }

            if (document.readyState === 'complete') ready(); else window.addEventListener('load', ready);
            window.addEventListener('beforeprint', function () { setAll(1); });
            var f = document.querySelector('.rp-filter');
            if (f) f.addEventListener('submit', function () { page.classList.add('rp-loading'); window.scrollTo(0, 0); });
        })();
    </script>

    <script>
        // Accordion card: awalnya terbuka semua
        (function () {
            var cards = [].slice.call(document.querySelectorAll('.rp-page > .rp-card'));
            var allBtn = document.getElementById('rpToggleAll');
            function set(card, open) {
                card.classList.toggle('is-collapsed', !open);
                card.querySelector('.rp-card-head').setAttribute('aria-expanded', open);
            }
            function syncAll() {
                var anyOpen = cards.some(function (c) { return !c.classList.contains('is-collapsed'); });
                document.getElementById('rpToggleAllText').textContent = anyOpen ? 'Tutup semua' : 'Buka semua';
                allBtn.querySelector('.material-symbols-outlined').textContent = anyOpen ? 'unfold_less' : 'unfold_more';
            }
            cards.forEach(function (card) {
                var head = card.querySelector('.rp-card-head');
                function toggle() { set(card, card.classList.contains('is-collapsed')); syncAll(); }
                head.addEventListener('click', toggle);
                head.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
                });
            });
            allBtn.addEventListener('click', function () {
                var open = cards.every(function (c) { return c.classList.contains('is-collapsed'); });
                cards.forEach(function (c) { set(c, open); });
                syncAll();
            });
        })();
    </script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>