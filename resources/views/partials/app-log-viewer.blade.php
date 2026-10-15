{{--
    Penampil log aplikasi Laravel (storage/logs/*.log) — hanya untuk superadmin.
    Data: AppLogController (GET /login-audit/app-logs) · logika: public/js/app-log.js · gaya: public/css/app-log.css
    Letakkan di dalam .page-wrap (di bawah kartu tabel percobaan login), muat js/app-log.js setelah js/pager.js:
        @if($__user->role === 'superadmin')
            @include('partials.app-log-viewer')
        @endif
--}}
<link rel="stylesheet" href="{{ asset('css/app-log.css') }}?v={{ @filemtime(public_path('css/app-log.css')) }}">

<div class="alog" id="appLogSection" data-url="{{ route('app-logs.index') }}">

    <!-- FILTER: tab level + pencarian + pilih file -->
    <div class="filter-card">
        <div class="view-tabs" id="alogTabs" role="tablist" aria-label="Level log">
            <button type="button" class="view-tab is-active" role="tab" aria-selected="true" data-level="all">Semua <span class="alog-count" data-c="all"></span></button>
            <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-level="error">Error <span class="alog-count" data-c="error"></span></button>
            <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-level="warning">Warning <span class="alog-count" data-c="warning"></span></button>
            <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-level="info">Info <span class="alog-count" data-c="info"></span></button>
            <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-level="debug">Debug <span class="alog-count" data-c="debug"></span></button>
        </div>

        <div class="filter-grid">
            <div class="filter-search-row">
                <div class="field field-search-wide">
                    <label class="field-label" for="alogSearch">Cari di Log</label>
                    <div class="field-control">
                        <span class="material-symbols-outlined icon-search">search</span>
                        <input id="alogSearch" type="text" placeholder="Cari pesan error, nama file, atau isi stack trace..." />
                    </div>
                </div>
                <div class="field alog-file-field">
                    <label class="field-label" for="alogFile">File Log</label>
                    <select id="alogFile" class="alog-select"><option value="">Memuat...</option></select>
                </div>
                <div class="field field-reset">
                    <button type="button" id="alogRefresh" class="btn-rst" title="Muat ulang log">
                        <span class="material-symbols-outlined">refresh</span>
                        <span class="btn-rst-text">Muat Ulang</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DAFTAR LOG -->
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h2 class="table-card-title">Log Aplikasi (Laravel)</h2>
                <p class="table-card-subtitle" id="alogSubtitle">Pesan error dan peringatan dari <code>storage/logs</code>. Klik baris untuk melihat detail / stack trace.</p>
            </div>
        </div>

        <div class="alog-note" id="alogNote" hidden></div>

        <div class="table-scroll is-scrollable">
            <table class="data-table alog-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th class="center">Level</th>
                        <th>Pesan</th>
                        <th class="center"></th>
                    </tr>
                </thead>
                <tbody id="alogBody">
                    <tr><td colspan="4" class="alog-empty">Memuat log...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-summary">
                Menampilkan <strong id="alogRange">0&ndash;0</strong> dari <strong id="alogTotal">0</strong> entri
            </div>
            <nav class="pager" id="alogPager" aria-label="Navigasi halaman log" hidden></nav>
        </div>
    </div>
</div>
