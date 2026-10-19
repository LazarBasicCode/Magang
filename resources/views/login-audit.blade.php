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
    <link rel="stylesheet" href="{{ asset('css/pager.css') }}?v={{ @filemtime(public_path('css/pager.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/profile-account.css') }}?v={{ @filemtime(public_path('css/profile-account.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ @filemtime(public_path('css/notifications.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/skeleton.css') }}?v={{ @filemtime(public_path('css/skeleton.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/login-audit.css') }}?v={{ @filemtime(public_path('css/login-audit.css')) }}">
    <title>Log Aktivitas &middot; SIDA</title>
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

    @include('partials.sidebar', ['active' => 'log'])

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        @include('partials.header', ['active' => 'log'])

        <main class="app-main">
            <div class="page-wrap is-loading" id="pageWrap" aria-busy="true">

                <!-- PAGE TITLE -->
                <div class="title-bar">
                    <div>
                        <h1 class="page-title">Log Aktivitas</h1>
                        <p class="page-subtitle">Riwayat percobaan login ke SIDA{{ $__user->role === 'superadmin' ? ' dan log aplikasi Laravel' : '' }}</p>
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS (24 jam terakhir) -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Berhasil (24 jam)</span>
                            <span class="stat-value" id="sumSuccess">&ndash;</span>
                        </div>
                        <div class="stat-icon success"><span class="material-symbols-outlined">check_circle</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Gagal (24 jam)</span>
                            <span class="stat-value" id="sumFailed">&ndash;</span>
                        </div>
                        <div class="stat-icon danger"><span class="material-symbols-outlined">cancel</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Diblokir (24 jam)</span>
                            <span class="stat-value" id="sumLocked">&ndash;</span>
                        </div>
                        <div class="stat-icon warning"><span class="material-symbols-outlined">lock</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">IP Unik (24 jam)</span>
                            <span class="stat-value" id="sumUniqueIp">&ndash;</span>
                        </div>
                        <div class="stat-icon info"><span class="material-symbols-outlined">public</span></div>
                    </div>
                </div>

                <!-- FILTER CARD: tab status + pencarian cepat -->
                <div class="filter-card">
                    <div class="view-tabs" id="statusTabs" role="tablist" aria-label="Status percobaan">
                        <button type="button" class="view-tab is-active" role="tab" aria-selected="true" data-status="">Semua</button>
                        <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-status="success">Berhasil</button>
                        <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-status="failed">Gagal</button>
                        <button type="button" class="view-tab" role="tab" aria-selected="false" tabindex="-1" data-status="locked">Diblokir</button>
                    </div>

                    <div class="filter-grid">
                        <div class="filter-search-row">
                            <div class="field field-search-wide">
                                <label class="field-label" for="filter-search">Pencarian Cepat</label>
                                <div class="field-control">
                                    <span class="material-symbols-outlined icon-search">search</span>
                                    <input id="filter-search" type="text" placeholder="Cari username, nama akun, atau IP..." />
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
                    <div class="table-card-header">
                        <div>
                            <h2 class="table-card-title">Percobaan Login</h2>
                            <p class="table-card-subtitle">Klik ikon detail untuk melihat akun, perangkat, dan riwayat IP</p>
                        </div>
                    </div>

                    <div class="table-scroll is-scrollable" id="tableScroll">
                        <table class="data-table" id="auditTable">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Username Diinput</th>
                                    <th>Akun</th>
                                    <th>IP Address</th>
                                    <th class="center">Status</th>
                                    <th>Perangkat</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="auditTableBody">
                                <tr id="noResultsRow" hidden>
                                    <td colspan="7" style="text-align:center; padding: 32px; color: var(--ink-faint);">Tidak ada data yang cocok.</td>
                                </tr>
                            </tbody>

                            {{-- Skeleton loading: tampil selama .page-wrap.is-loading --}}
                            <tbody class="sk-body" aria-hidden="true">
                                @for($i = 0; $i < 7; $i++)
                                <tr><td colspan="7"><span class="sk-bar"></span></td></tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong id="footerRange">0&ndash;0</strong> dari <strong id="footerTotalCount">0</strong> percobaan
                        </div>
                        <nav class="pager" id="pager" aria-label="Navigasi halaman" hidden></nav>
                    </div>
                </div>

                {{-- Log aplikasi Laravel (storage/logs): hanya superadmin --}}
                @if($__user->role === 'superadmin')
                    @include('partials.app-log-viewer')
                @endif
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ============ MODAL: Detail Percobaan Login ============ -->
    <div class="modal-backdrop" id="auditBackdrop"></div>
    <div class="modal-card audit-card" id="auditModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-drag-handle" id="auditDragHandle">
            <div>
                <h3 class="modal-title">Detail Percobaan Login</h3>
                <p class="modal-subtitle">Akun, perangkat, dan riwayat IP</p>
            </div>
            <button type="button" class="modal-close-btn" id="auditCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-body" id="auditModalBody"></div>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/pager.js') }}?v={{ @filemtime(public_path('js/pager.js')) }}"></script>
    @if($__user->role === 'superadmin')
        <script src="{{ asset('js/app-log.js') }}?v={{ @filemtime(public_path('js/app-log.js')) }}"></script>
    @endif
    <script>
        // ================================================================
        // Halaman Audit Percobaan Login: filter status, pencarian & paginasi diproses di SERVER
        // GET /login-audit/data?q=&status=&page= (JSON). Tombol halaman: public/js/pager.js
        // ================================================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const tableBody = document.getElementById('auditTableBody');
        const noResultsRow = document.getElementById('noResultsRow');
        const searchInput = document.getElementById('filter-search');
        const statusTabs = Array.from(document.querySelectorAll('#statusTabs .view-tab'));
        const footerRange = document.getElementById('footerRange');
        const footerTotalCount = document.getElementById('footerTotalCount');
        const modalBody = document.getElementById('auditModalBody');

        const state = { q: '', status: '', page: 1 };
        let requestSeq = 0;
        let searchTimer = null;

        function esc(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        // Badge status memakai kelas .badge bawaan tema
        function statusBadge(status) {
            if (status === 'success') return '<span class="badge badge-success"><span class="material-symbols-outlined">check_circle</span>Berhasil</span>';
            if (status === 'failed') return '<span class="badge badge-danger"><span class="material-symbols-outlined">cancel</span>Gagal</span>';
            return '<span class="badge badge-warning"><span class="material-symbols-outlined">lock</span>Diblokir</span>';
        }

        function rowHTML(r) {
            const akun = r.user_name
                ? esc(r.user_name) + (r.user_role ? ' <span class="audit-faint">(' + esc(r.user_role) + ')</span>' : '')
                : '<span class="audit-faint">Tidak ditemukan</span>';
            return `
                <tr data-id="${r.id}">
                    <td>
                        <div class="audit-mono">${esc(r.created_at_fmt)}</div>
                        <div class="audit-faint audit-sm">${esc(r.created_at_rel)}</div>
                    </td>
                    <td>${esc(r.username_input)}</td>
                    <td>${akun}</td>
                    <td class="audit-mono">${esc(r.ip_address)}</td>
                    <td class="center">${statusBadge(r.status)}</td>
                    <td class="audit-ua" title="${esc(r.user_agent || '')}">${esc(r.user_agent || '—')}</td>
                    <td class="center">
                        <div class="row-actions">
                            <button type="button" title="Detail" class="row-action-btn btn-detail-row" data-id="${r.id}">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }

        const pager = Pager.create(document.getElementById('pager'), {
            onGo: (n) => {
                loadData(n);
                document.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        async function loadData(page = state.page) {
            const seq = ++requestSeq;
            state.page = page;
            const params = new URLSearchParams({ page: String(page) });
            if (state.q) params.set('q', state.q);
            if (state.status) params.set('status', state.status);

            tableBody.classList.add('is-fetching');
            try {
                const res = await fetch('{{ url('/login-audit/data') }}?' + params, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (seq !== requestSeq) return; // ada permintaan yang lebih baru

                document.getElementById('sumSuccess').textContent = json.summary.success;
                document.getElementById('sumFailed').textContent = json.summary.failed;
                document.getElementById('sumLocked').textContent = json.summary.locked;
                document.getElementById('sumUniqueIp').textContent = json.summary.unique_ip;

                const p = json.pagination;
                tableBody.querySelectorAll('tr[data-id]').forEach((r) => r.remove());
                tableBody.insertAdjacentHTML('afterbegin', json.data.map(rowHTML).join(''));
                noResultsRow.hidden = p.total !== 0;

                const from = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
                const to = Math.min(p.current_page * p.per_page, p.total);
                footerRange.textContent = from + '–' + to;
                footerTotalCount.textContent = p.total;
                state.page = p.current_page;
                pager.render(p.current_page, Math.max(1, p.last_page));
            } catch (err) {
                if (seq !== requestSeq) return;
                console.error('[Audit Login] Gagal memuat data:', err);
                window.Toast?.show?.({ type: 'error', title: 'Gagal memuat data', message: 'Tidak bisa memuat riwayat login. Coba lagi.' });
            } finally {
                if (seq === requestSeq) tableBody.classList.remove('is-fetching');
            }
        }

        // ---------- Tab status ----------
        function setStatus(status) {
            state.status = status;
            statusTabs.forEach((t) => {
                const on = t.dataset.status === status;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', String(on));
                t.tabIndex = on ? 0 : -1;
            });
            loadData(1);
        }
        statusTabs.forEach((tab, i) => {
            tab.addEventListener('click', () => { if (tab.dataset.status !== state.status) setStatus(tab.dataset.status); });
            tab.addEventListener('keydown', (e) => {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                e.preventDefault();
                const next = statusTabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + statusTabs.length) % statusTabs.length];
                next.focus();
                setStatus(next.dataset.status);
            });
        });

        // ---------- Pencarian (debounce) & reset ----------
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { state.q = searchInput.value.trim(); loadData(1); }, 350);
        });
        document.getElementById('btn-reset-filter').addEventListener('click', () => {
            searchInput.value = '';
            state.q = '';
            setStatus('');
        });

        // ---------- Modal detail (bisa digeser, sama seperti modal halaman lain) ----------
        const { open: openModalBase } = SIDA.modal.attach({
            backdrop: document.getElementById('auditBackdrop'),
            card: document.getElementById('auditModal'),
            closeBtn: document.getElementById('auditCloseBtn'),
            dragHandle: document.getElementById('auditDragHandle'),
        });

        tableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-detail-row');
            if (btn) openDetail(btn.dataset.id);
        });

        async function openDetail(id) {
            modalBody.innerHTML = '<div class="audit-state">Memuat detail...</div>';
            openModalBase();
            try {
                const res = await fetch('{{ url('/login-audit') }}/' + id, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                renderDetail(await res.json());
            } catch (err) {
                modalBody.innerHTML = '<div class="audit-state">Gagal memuat detail percobaan ini.</div>';
            }
        }

        function relatedList(items, emptyText) {
            if (!items || items.length === 0) return `<div class="audit-state audit-sm">${emptyText}</div>`;
            return `<div class="audit-mini-list">${items.map((r) => `
                <div class="audit-mini-row">
                    <div>
                        <div class="audit-strong">${esc(r.username_input)}${r.user_name ? ' · ' + esc(r.user_name) : ''}</div>
                        <div class="audit-faint audit-sm">${esc(r.created_at_fmt)} · ${esc(r.ip_address)}</div>
                    </div>
                    ${statusBadge(r.status)}
                </div>`).join('')}</div>`;
        }

        function renderDetail(json) {
            const a = json.attempt;
            const u = a.user_detail;
            const field = (k, v, cls = '') => `<div class="audit-field ${cls}"><div class="audit-k">${k}</div><div class="audit-v">${v}</div></div>`;

            const accountBlock = u ? `
                <div class="audit-grid">
                    ${field('Nama Lengkap', esc(u.name))}
                    ${field('Role', '<span style="text-transform:capitalize">' + esc(u.role) + '</span>')}
                    ${field('NIM / NIDN', esc(u.identifier || '—'))}
                    ${field('Email', esc(u.email || '—'))}
                    ${field('Akun Dibuat', esc(u.akun_dibuat || '—'), 'span-2')}
                </div>` : `
                <div class="audit-note">
                    <span class="material-symbols-outlined">warning</span>
                    <div>Tidak ada akun yang cocok dengan username <strong>${esc(a.username_input)}</strong>. Ini bisa berarti percobaan asal tebak atau akunnya sudah dihapus.</div>
                </div>`;

            modalBody.innerHTML = `
                <div class="audit-status-row">
                    ${statusBadge(a.status)}
                    <span class="audit-faint audit-sm">${esc(a.created_at_fmt)}</span>
                </div>
                <div><div class="audit-section">Informasi Akun</div>${accountBlock}</div>
                <div><div class="audit-section">Detail Teknis</div>
                    <div class="audit-grid">
                        ${field('Alamat IP', '<span class="audit-mono">' + esc(a.ip_address) + '</span>')}
                        ${field('Username Diinput', esc(a.username_input))}
                        ${field('User Agent', '<span class="audit-sm audit-wrap">' + esc(a.user_agent_full || '—') + '</span>', 'span-2')}
                    </div>
                </div>
                <div><div class="audit-section">Riwayat IP Ini (Sepanjang Waktu)</div>
                    <div class="audit-ipstats">
                        <div><b class="is-ok">${json.ip_stats.success}</b><span>Berhasil</span></div>
                        <div><b class="is-err">${json.ip_stats.failed}</b><span>Gagal</span></div>
                        <div><b class="is-lock">${json.ip_stats.locked}</b><span>Diblokir</span></div>
                    </div>
                </div>
                <div><div class="audit-section">5 Percobaan Terakhir dari IP yang Sama</div>${relatedList(json.from_same_ip, 'Tidak ada percobaan lain dari IP ini.')}</div>
                <div><div class="audit-section">5 Percobaan Terakhir untuk Username yang Sama</div>${relatedList(json.same_username, 'Tidak ada percobaan lain untuk username ini.')}</div>`;
        }

        loadData(1);
    </script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>
