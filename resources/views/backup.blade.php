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

    @include('partials.sidebar', ['active' => 'backup'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'backup'])

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
