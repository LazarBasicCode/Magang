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
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/row-select.css') }}?v={{ @filemtime(public_path('css/row-select.css')) }}">
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
            <div class="page-wrap" id="apprPage" data-tab="{{ $tab }}">

                <!-- PAGE TITLE -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Persetujuan Pengajuan</h1>
                        <p class="page-subtitle">Verifikasi prestasi &amp; publikasi mahasiswa &middot; diajukan &rarr; diproses Prodi &rarr; terverifikasi &middot; hanya data terverifikasi yang tampil di laporan</p>
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Baru Diajukan</span>
                            <span class="stat-value" id="cntDiajukan">{{ $counts['diajukan'] }}</span>
                        </div>
                        <div class="stat-icon primary"><span class="material-symbols-outlined">send</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Sedang Diproses</span>
                            <span class="stat-value" id="cntProses">{{ $counts['menunggu_prodi'] }}</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">hourglass_top</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Terverifikasi</span>
                            <span class="stat-value" id="cntOk">{{ $counts['disetujui'] }}</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">task_alt</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Ditolak</span>
                            <span class="stat-value" id="cntTolak">{{ $counts['ditolak'] }}</span>
                        </div>
                        <div class="stat-icon danger"><span class="material-symbols-outlined">cancel</span></div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <div class="field">
                            <label class="field-label">Menu</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-menu" value="{{ $menu }}" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">{{ $menu === 'semua' ? 'Semua Menu' : ($menus[$menu] ?? 'Semua Menu') }}</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option {{ $menu === 'semua' ? 'is-selected' : '' }}" data-value="semua">Semua Menu</button>
                                    @foreach($menus as $slug => $label)
                                    <button type="button" class="dropdown-option {{ $menu === $slug ? 'is-selected' : '' }}" data-value="{{ $slug }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            @php $tsYear = now()->year; @endphp
                            <label class="field-label">Tahun</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tahun" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tahun</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua Tahun</button>
                                    @foreach([0, 1, 2] as $off)
                                    <button type="button" class="dropdown-option" data-value="{{ $tsYear - $off }}">{{ $off === 0 ? 'TS' : 'TS-' . $off }} ({{ $tsYear - $off }})</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari nama, NIM, atau judul pengajuan..." autocomplete="off" />
                                </div>
                            </div>
                            <div class="field field-reset">
                                <button type="button" id="btn-reset-filter" class="btn-rst" title="Reset Filter">
                                    <span class="material-symbols-outlined">restart_alt</span>
                                    <span class="btn-rst-text">Reset Filter</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATA TABLE CARD -->
                <div class="table-card">
                    <div class="appr-tabs" role="tablist" aria-label="Status pengajuan">
                        <button type="button" class="appr-tab {{ $tab === 'perlu' ? 'is-active' : '' }}" data-tab="perlu" role="tab">
                            <span class="material-symbols-outlined">pending_actions</span>
                            <span>Menunggu Verifikasi</span>
                            <span class="appr-tab-count" id="tabCntPerlu">{{ $counts['perlu'] }}</span>
                        </button>
                        <button type="button" class="appr-tab {{ $tab === 'riwayat' ? 'is-active' : '' }}" data-tab="riwayat" role="tab">
                            <span class="material-symbols-outlined">history</span>
                            <span>Riwayat Keputusan</span>
                            <span class="appr-tab-count" id="tabCntRiwayat">{{ $counts['riwayat'] }}</span>
                        </button>
                    </div>
                    <p class="appr-tabhint" id="apprHint"></p>

                    {{-- Bar aksi data terpilih (verifikasi massal) --}}
                    <div class="sel-bar" id="apprSelBar" role="region" aria-label="Aksi data terpilih" aria-hidden="true">
                        <div class="sel-bar-info" aria-live="polite">
                            <strong id="apprSelCount">0</strong><span>pengajuan dipilih</span>
                        </div>
                        <div class="sel-bar-actions">
                            <button type="button" class="sel-btn" id="apprSelApprove">
                                <span class="material-symbols-outlined">check_circle</span><span>Verifikasi terpilih</span>
                            </button>
                            <button type="button" class="sel-btn is-ghost" id="apprSelClear">
                                <span class="material-symbols-outlined">close</span><span>Batal pilih</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-scroll is-scrollable">
                        <table class="data-table appr-table" id="apprTable">
                            <thead>
                                <tr>
                                    <th class="sel-col sel-head" id="apprHeadSel"></th>
                                    <th>Pengajuan</th>
                                    <th>Pengaju</th>
                                    <th class="center">Status</th>
                                    <th id="apprTimeHead">Diajukan</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="apprBody"></tbody>
                        </table>
                    </div>

                    <div class="appr-empty" id="apprEmpty" hidden>
                        <span class="material-symbols-outlined">task_alt</span>
                        <p class="appr-empty-title">Tidak ada pengajuan di sini</p>
                        <p class="appr-empty-desc" id="apprEmptyDesc"></p>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">0&ndash;0</strong> dari <strong id="footerTotal">0</strong> pengajuan
                        </div>
                        <nav class="pager" id="apprPager" aria-label="Navigasi halaman" hidden></nav>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Modal Periksa: Detail | Komentar | Riwayat. Membuka pengajuan baru = otomatis "Diproses Prodi". --}}
    <div class="modal-backdrop" id="apprRevBackdrop"></div>
    <div class="modal-card" id="apprRevModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="apprRevDrag">
            <div>
                <h3 class="modal-title" id="apprRevTitle">Periksa Pengajuan</h3>
                <p class="modal-subtitle" id="apprRevSub">Cek bukti &amp; kelengkapan, lalu verifikasi atau tolak</p>
            </div>
            <button type="button" class="modal-close-btn" id="apprRevClose" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="appr-rev-head" id="apprRevHead"></div>

            <div class="appr-mtabs" role="tablist">
                <button type="button" class="appr-mtab is-active" data-pane="detail">Detail</button>
                <button type="button" class="appr-mtab" data-pane="komentar">Komentar <span class="appr-mtab-count" id="apprMtabComments">0</span></button>
                <button type="button" class="appr-mtab" data-pane="riwayat">Riwayat</button>
            </div>

            <div class="appr-pane" data-pane="detail" id="apprPaneDetail"></div>

            <div class="appr-pane" data-pane="komentar" id="apprPaneKomentar" hidden>
                <ul class="appr-comments" id="apprCommentList"></ul>
                <form class="appr-comment-form" id="apprCommentForm" novalidate>
                    <textarea id="apprCommentBody" class="appr-note-input" maxlength="1000" rows="2" placeholder="Tulis komentar untuk mahasiswa, mis. bukti perlu dilengkapi..."></textarea>
                    <span class="appr-comment-err" id="apprCommentErr" hidden></span>
                </form>
            </div>

            <div class="appr-pane" data-pane="riwayat" id="apprPaneRiwayat" hidden>
                <ul class="appr-timeline" id="apprHistList"></ul>
            </div>

        </div>
        <div class="appr-rev-footer" id="apprRevFooter">
                <button type="button" class="appr-btn-cancelver" id="apprRevUndo" hidden>
                    <span class="material-symbols-outlined">undo</span><span>Batalkan Verifikasi</span>
                </button>
                <button type="button" class="btn-ghost" id="apprRevCancel">Tutup</button>
                <button type="submit" form="apprCommentForm" class="btn-apply" id="apprCommentSend" hidden><span class="material-symbols-outlined">send</span><span>Kirim</span></button>
                <button type="button" class="btn-apply is-danger" id="apprRevReject" hidden><span class="material-symbols-outlined">cancel</span><span>Tolak</span></button>
                <button type="button" class="btn-apply" id="apprRevApprove" hidden><span class="material-symbols-outlined">check_circle</span><span>Verifikasi</span></button>
        </div>
    </div>

    {{-- Modal catatan (verifikasi / tolak / batalkan / verifikasi massal) --}}
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

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script>window.__APPR_INITIAL = @json($initial);</script>
    <script src="{{ asset('js/approval.js') }}?v={{ @filemtime(public_path('js/approval.js')) }}"
        data-url-base="{{ url('/persetujuan') }}"></script>
</body>

</html>