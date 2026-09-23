<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audit Percobaan Login &middot; SIDA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        // Terapkan tema tersimpan sebelum body dirender, supaya tidak ada
        // "kedipan" putih sesaat sebelum dark mode aktif.
        (function () {
            try {
                var saved = localStorage.getItem('sida-audit-theme');
                var theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    <style>
        :root {
            --font: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        :root[data-theme="light"] {
            --bg: #f4f6fb; --panel: #ffffff; --panel-2: #f8faff; --ink: #12204a; --ink-soft: #5b6b90; --ink-faint: #93a0c2;
            --line: #e6ebf7; --line-strong: #d9e2f6; --primary: #2f52d9; --primary-ink: #ffffff;
            --ok-bg: #f0fdf4; --ok-ink: #15803d; --ok-line: #bbf7d0;
            --err-bg: #fef2f2; --err-ink: #b42318; --err-line: #fecaca;
            --lock-bg: #fff7ed; --lock-ink: #c2410c; --lock-line: #fed7aa;
            --shadow: 0 20px 45px -25px rgba(20, 30, 80, .35);
            --overlay: rgba(15, 23, 55, .5);
        }
        :root[data-theme="dark"] {
            --bg: #0b1220; --panel: #131b2e; --panel-2: #182238; --ink: #eaf0ff; --ink-soft: #93a3c6; --ink-faint: #5d6b8f;
            --line: #233052; --line-strong: #2c3b63; --primary: #5b7fff; --primary-ink: #ffffff;
            --ok-bg: rgba(34,197,94,.12); --ok-ink: #86efac; --ok-line: rgba(34,197,94,.3);
            --err-bg: rgba(239,68,68,.12); --err-ink: #fca5a5; --err-line: rgba(239,68,68,.3);
            --lock-bg: rgba(249,115,22,.12); --lock-ink: #fdba74; --lock-line: rgba(249,115,22,.3);
            --shadow: 0 20px 45px -25px rgba(0, 0, 0, .6);
            --overlay: rgba(0, 0, 0, .65);
        }

        * { box-sizing: border-box; }
        html {
            scrollbar-width: thin; scrollbar-color: var(--line-strong, #d9e2f6) transparent;
        }
        html::-webkit-scrollbar { width: 10px; height: 10px; }
        html::-webkit-scrollbar-track { background: transparent; }
        html::-webkit-scrollbar-thumb { background: var(--line-strong); border-radius: 999px; border: 2px solid var(--bg); background-clip: padding-box; }
        html::-webkit-scrollbar-thumb:hover { background: var(--ink-faint); background-clip: padding-box; }
        body {
            font-family: var(--font); background: var(--bg); color: var(--ink); margin: 0;
            padding: clamp(1.25rem, 3.5vw, 2.5rem); transition: background .2s ease, color .2s ease;
            -webkit-font-smoothing: antialiased;
        }
        button, input, select { font: inherit; }
        a { color: inherit; }

        /* ---------- Header ---------- */
        .topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .title-wrap h1 { font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -.01em; }
        .title-wrap p { margin: .35rem 0 0; font-size: .85rem; color: var(--ink-soft); }
        .header-actions { display: flex; align-items: center; gap: .6rem; }

        .icon-btn {
            width: 40px; height: 40px; border-radius: 12px; border: 1px solid var(--line-strong);
            background: var(--panel); color: var(--ink-soft); display: grid; place-items: center;
            cursor: pointer; font-size: 1rem; transition: .15s ease;
        }
        .icon-btn:hover { color: var(--primary); border-color: var(--primary); }

        .back-link {
            display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; font-weight: 600;
            color: var(--primary); text-decoration: none; padding: .55rem .9rem; border-radius: 10px;
            border: 1px solid var(--line-strong); background: var(--panel);
        }
        .back-link:hover { background: var(--panel-2); }

        /* ---------- Summary cards ---------- */
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .9rem; margin-bottom: 1.5rem; }
        .summary .card {
            background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 1.1rem 1.2rem;
            display: flex; align-items: center; gap: .9rem; box-shadow: var(--shadow);
        }
        .summary .card .icon {
            width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; font-size: 1.05rem; flex-shrink: 0;
        }
        .summary .card .num { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
        .summary .card .lbl { font-size: .74rem; color: var(--ink-soft); margin-top: .15rem; }
        .icon-ok { background: var(--ok-bg); color: var(--ok-ink); }
        .icon-err { background: var(--err-bg); color: var(--err-ink); }
        .icon-lock { background: var(--lock-bg); color: var(--lock-ink); }
        .icon-ip { background: rgba(99,102,241,.12); color: var(--primary); }

        /* ---------- Toolbar (filter langsung, tanpa tombol) ---------- */
        .toolbar {
            display: flex; gap: .7rem; flex-wrap: wrap; align-items: center;
            background: var(--panel); border: 1px solid var(--line); border-radius: 14px;
            padding: .7rem .9rem; margin-bottom: 1rem; box-shadow: var(--shadow);
        }
        .search-wrap { position: relative; flex: 1 1 240px; min-width: 200px; }
        .search-wrap i { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: var(--ink-faint); font-size: .82rem; }
        .search-wrap input {
            width: 100%; padding: .6rem .8rem .6rem 2.3rem; border-radius: 10px; border: 1px solid var(--line-strong);
            background: var(--panel-2); color: var(--ink); font-size: .84rem;
        }
        .search-wrap input:focus { outline: none; border-color: var(--primary); }

        .status-chips { display: flex; gap: .4rem; flex-wrap: wrap; }
        .chip {
            padding: .5rem .85rem; border-radius: 999px; border: 1px solid var(--line-strong); background: var(--panel-2);
            color: var(--ink-soft); font-size: .78rem; font-weight: 600; cursor: pointer; transition: .15s ease;
            display: inline-flex; align-items: center; gap: .35rem;
        }
        .chip:hover { border-color: var(--primary); color: var(--primary); }
        .chip.is-active { background: var(--primary); border-color: var(--primary); color: var(--primary-ink); }

        .live-indicator {
            font-size: .72rem; color: var(--ink-faint); display: flex; align-items: center; gap: .35rem;
            margin-left: auto; white-space: nowrap;
        }
        .live-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--ok-ink); animation: pulse 1.6s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

        /* ---------- Table ---------- */
        .table-wrap {
            background: var(--panel); border: 1px solid var(--line); border-radius: 16px; overflow-x: auto; box-shadow: var(--shadow);
            position: relative; min-height: 200px;
            scrollbar-width: thin; scrollbar-color: var(--line-strong) transparent;
        }
        .table-wrap::-webkit-scrollbar { height: 8px; }
        .table-wrap::-webkit-scrollbar-track { background: transparent; }
        .table-wrap::-webkit-scrollbar-thumb { background: var(--line-strong); border-radius: 999px; }
        .table-wrap::-webkit-scrollbar-thumb:hover { background: var(--ink-faint); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .75rem .9rem; text-align: left; font-size: .82rem; border-bottom: 1px solid var(--line); white-space: nowrap; }
        th {
            background: var(--panel-2); color: var(--ink-soft); font-weight: 700; font-size: .7rem;
            text-transform: uppercase; letter-spacing: .04em; position: sticky; top: 0;
        }
        tbody tr { transition: background .12s ease; }
        tbody tr:hover { background: var(--panel-2); }
        tbody tr:last-child td { border-bottom: none; }
        .muted { color: var(--ink-soft); }
        .faint { color: var(--ink-faint); }
        .mono { font-variant-numeric: tabular-nums; }

        .badge {
            display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .65rem; border-radius: 999px;
            font-size: .72rem; font-weight: 700; border: 1px solid transparent;
        }
        .badge-success { background: var(--ok-bg); color: var(--ok-ink); border-color: var(--ok-line); }
        .badge-failed  { background: var(--err-bg); color: var(--err-ink); border-color: var(--err-line); }
        .badge-locked  { background: var(--lock-bg); color: var(--lock-ink); border-color: var(--lock-line); }

        .ua-cell { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }

        .detail-btn {
            border: 1px solid var(--line-strong); background: var(--panel-2); color: var(--primary);
            padding: .4rem .7rem; border-radius: 8px; font-size: .74rem; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; gap: .35rem; transition: .15s ease;
        }
        .detail-btn:hover { background: var(--primary); color: var(--primary-ink); border-color: var(--primary); }

        .empty-state, .loading-state {
            padding: 3rem 1rem; text-align: center; color: var(--ink-soft); font-size: .85rem;
        }
        .loading-state i { font-size: 1.3rem; margin-bottom: .5rem; display: block; color: var(--primary); }

        /* ---------- Pagination ---------- */
        .pager { display: flex; align-items: center; justify-content: center; gap: .8rem; margin-top: 1.1rem; font-size: .82rem; color: var(--ink-soft); }
        .pager button {
            width: 34px; height: 34px; border-radius: 9px; border: 1px solid var(--line-strong); background: var(--panel);
            color: var(--ink); cursor: pointer; display: grid; place-items: center;
        }
        .pager button:disabled { opacity: .4; cursor: default; }
        .pager button:not(:disabled):hover { border-color: var(--primary); color: var(--primary); }

        /* ---------- Modal ---------- */
        .modal-overlay {
            position: fixed; inset: 0; background: var(--overlay); display: none; align-items: center; justify-content: center;
            padding: 1.25rem; z-index: 50; backdrop-filter: blur(2px);
        }
        .modal-overlay.is-open { display: flex; }
        .modal-box {
            background: var(--panel); border-radius: 20px; width: 100%; max-width: 560px; max-height: 88vh; overflow-y: auto;
            box-shadow: 0 30px 70px -20px rgba(0,0,0,.5); border: 1px solid var(--line);
            scrollbar-width: thin; scrollbar-color: var(--line-strong) transparent;
        }
        .modal-box::-webkit-scrollbar { width: 8px; }
        .modal-box::-webkit-scrollbar-track { background: transparent; }
        .modal-box::-webkit-scrollbar-thumb { background: var(--line-strong); border-radius: 999px; border: 2px solid var(--panel); background-clip: padding-box; }
        .modal-box::-webkit-scrollbar-thumb:hover { background: var(--ink-faint); background-clip: padding-box; }
        .modal-head {
            display: flex; align-items: center; justify-content: space-between; padding: 1.15rem 1.3rem;
            border-bottom: 1px solid var(--line); position: sticky; top: 0; background: var(--panel); z-index: 1;
        }
        .modal-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; }
        .modal-close {
            width: 32px; height: 32px; border-radius: 9px; border: 1px solid var(--line-strong); background: var(--panel-2);
            color: var(--ink-soft); cursor: pointer; display: grid; place-items: center;
        }
        .modal-close:hover { color: var(--err-ink); border-color: var(--err-line); }
        .modal-body { padding: 1.3rem; display: flex; flex-direction: column; gap: 1.2rem; }

        .detail-status-row { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
        .detail-time { font-size: .78rem; color: var(--ink-soft); }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
        .detail-field { background: var(--panel-2); border: 1px solid var(--line); border-radius: 12px; padding: .7rem .85rem; }
        .detail-field .k { font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-faint); font-weight: 700; margin-bottom: .25rem; }
        .detail-field .v { font-size: .86rem; font-weight: 600; word-break: break-word; }
        .detail-field.span-2 { grid-column: span 2; }

        .no-account-note {
            background: var(--err-bg); border: 1px solid var(--err-line); color: var(--err-ink);
            border-radius: 12px; padding: .8rem .9rem; font-size: .8rem; display: flex; gap: .55rem; align-items: flex-start;
        }

        .ip-stat-row { display: flex; gap: .6rem; }
        .ip-stat { flex: 1; text-align: center; border-radius: 12px; padding: .6rem; border: 1px solid var(--line); background: var(--panel-2); }
        .ip-stat .n { font-size: 1.1rem; font-weight: 800; }
        .ip-stat .l { font-size: .68rem; color: var(--ink-soft); margin-top: .1rem; }

        .section-title { font-size: .76rem; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .5rem; }
        .mini-list { display: flex; flex-direction: column; gap: .4rem; }
        .mini-row {
            display: flex; align-items: center; justify-content: space-between; gap: .6rem;
            padding: .55rem .7rem; border-radius: 10px; background: var(--panel-2); border: 1px solid var(--line); font-size: .78rem;
        }
        .mini-row .badge { flex-shrink: 0; }
        .mini-empty { font-size: .78rem; color: var(--ink-faint); padding: .4rem .2rem; }

        @media (max-width: 640px) {
            .detail-grid { grid-template-columns: 1fr; }
            .detail-field.span-2 { grid-column: span 1; }
            th, td { font-size: .76rem; padding: .6rem .65rem; }
        }
    </style>
</head>
<body>

    <div class="topline">
        <div class="title-wrap">
            <h1>Audit Percobaan Login</h1>
            <p>Riwayat lengkap setiap percobaan login ke SIDA — berhasil, gagal, maupun yang diblokir rate limit.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="icon-btn" id="themeToggle" title="Ganti tema">
                <i class="fa-solid fa-moon"></i>
            </button>
            <a href="{{ url('/kemahasiswaan') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>

    <div class="summary" id="summaryCards">
        <div class="card">
            <div class="icon icon-ok"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="num" id="sumSuccess">–</div><div class="lbl">Berhasil (24 jam)</div></div>
        </div>
        <div class="card">
            <div class="icon icon-err"><i class="fa-solid fa-circle-xmark"></i></div>
            <div><div class="num" id="sumFailed">–</div><div class="lbl">Gagal (24 jam)</div></div>
        </div>
        <div class="card">
            <div class="icon icon-lock"><i class="fa-solid fa-lock"></i></div>
            <div><div class="num" id="sumLocked">–</div><div class="lbl">Diblokir (24 jam)</div></div>
        </div>
        <div class="card">
            <div class="icon icon-ip"><i class="fa-solid fa-globe"></i></div>
            <div><div class="num" id="sumUniqueIp">–</div><div class="lbl">IP unik (24 jam)</div></div>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari username, nama akun, atau IP...">
        </div>
        <div class="status-chips" id="statusChips">
            <button type="button" class="chip is-active" data-status="">Semua</button>
            <button type="button" class="chip" data-status="success"><i class="fa-solid fa-circle-check"></i> Berhasil</button>
            <button type="button" class="chip" data-status="failed"><i class="fa-solid fa-circle-xmark"></i> Gagal</button>
            <button type="button" class="chip" data-status="locked"><i class="fa-solid fa-lock"></i> Diblokir</button>
        </div>
        <div class="live-indicator"><span class="live-dot"></span> Filter otomatis</div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Username diinput</th>
                    <th>Akun</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>User Agent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="7"><div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i>Memuat data...</div></td></tr>
            </tbody>
        </table>
    </div>

    <div class="pager" id="pager" style="display:none;">
        <button type="button" id="pagerPrev"><i class="fa-solid fa-chevron-left"></i></button>
        <span id="pagerInfo">Halaman 1 dari 1</span>
        <button type="button" id="pagerNext"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <!-- ======= Modal Detail ======= -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-box">
            <div class="modal-head">
                <h2>Detail Percobaan Login</h2>
                <button type="button" class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i>Memuat detail...</div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const themeToggle = document.getElementById('themeToggle');
        const searchInput = document.getElementById('searchInput');
        const statusChips = document.getElementById('statusChips');
        const tableBody = document.getElementById('tableBody');
        const pager = document.getElementById('pager');
        const pagerInfo = document.getElementById('pagerInfo');
        const pagerPrev = document.getElementById('pagerPrev');
        const pagerNext = document.getElementById('pagerNext');
        const modalOverlay = document.getElementById('modalOverlay');
        const modalBody = document.getElementById('modalBody');
        const modalClose = document.getElementById('modalClose');

        // ---------- Dark mode ----------
        function applyThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            themeToggle.innerHTML = theme === 'dark'
                ? '<i class="fa-solid fa-sun"></i>'
                : '<i class="fa-solid fa-moon"></i>';
        }
        applyThemeIcon();
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('sida-audit-theme', next); } catch (e) {}
            applyThemeIcon();
        });

        // ---------- State ----------
        let state = { q: '', status: '', page: 1 };
        let debounceTimer = null;
        let currentRows = []; // dipakai supaya klik baris tidak perlu fetch ulang untuk data dasar

        const statusBadge = (status) => {
            if (status === 'success') return '<span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Berhasil</span>';
            if (status === 'failed') return '<span class="badge badge-failed"><i class="fa-solid fa-circle-xmark"></i> Gagal</span>';
            return '<span class="badge badge-locked"><i class="fa-solid fa-lock"></i> Diblokir</span>';
        };

        function esc(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        async function loadData() {
            tableBody.innerHTML = '<tr><td colspan="7"><div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i>Memuat data...</div></td></tr>';

            const params = new URLSearchParams();
            if (state.q) params.set('q', state.q);
            if (state.status) params.set('status', state.status);
            params.set('page', state.page);

            try {
                const res = await fetch(`{{ url('/login-audit/data') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();

                document.getElementById('sumSuccess').textContent = json.summary.success;
                document.getElementById('sumFailed').textContent = json.summary.failed;
                document.getElementById('sumLocked').textContent = json.summary.locked;
                document.getElementById('sumUniqueIp').textContent = json.summary.unique_ip;

                currentRows = json.data;

                if (json.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-inbox" style="font-size:1.3rem;display:block;margin-bottom:.5rem;"></i>Tidak ada data yang cocok.</div></td></tr>';
                } else {
                    tableBody.innerHTML = json.data.map(row => `
                        <tr>
                            <td class="muted mono">
                                <div>${esc(row.created_at_fmt)}</div>
                                <div class="faint" style="font-size:.72rem;">${esc(row.created_at_rel)}</div>
                            </td>
                            <td>${esc(row.username_input)}</td>
                            <td class="muted">${row.user_name ? esc(row.user_name) + (row.user_role ? ` <span class="faint">(${esc(row.user_role)})</span>` : '') : '<span class="faint">Tidak ditemukan</span>'}</td>
                            <td class="muted mono">${esc(row.ip_address)}</td>
                            <td>${statusBadge(row.status)}</td>
                            <td class="muted ua-cell" title="${esc(row.user_agent || '')}">${esc(row.user_agent || '—')}</td>
                            <td>
                                <button type="button" class="detail-btn" data-id="${row.id}">
                                    <i class="fa-solid fa-circle-info"></i> Detail
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }

                const p = json.pagination;
                if (p.total > 0) {
                    pager.style.display = 'flex';
                    pagerInfo.textContent = `Halaman ${p.current_page} dari ${p.last_page} · ${p.total} data`;
                    pagerPrev.disabled = p.current_page <= 1;
                    pagerNext.disabled = p.current_page >= p.last_page;
                } else {
                    pager.style.display = 'none';
                }
            } catch (err) {
                tableBody.innerHTML = '<tr><td colspan="7"><div class="empty-state">Gagal memuat data. Coba muat ulang halaman.</div></td></tr>';
            }
        }

        // ---------- Filter langsung, tanpa tombol ----------
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                state.q = searchInput.value.trim();
                state.page = 1;
                loadData();
            }, 350); // debounce kecil supaya tidak fetch di setiap ketukan huruf
        });

        statusChips.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip');
            if (!btn) return;
            statusChips.querySelectorAll('.chip').forEach(c => c.classList.remove('is-active'));
            btn.classList.add('is-active');
            state.status = btn.dataset.status;
            state.page = 1;
            loadData();
        });

        pagerPrev.addEventListener('click', () => { state.page = Math.max(1, state.page - 1); loadData(); });
        pagerNext.addEventListener('click', () => { state.page += 1; loadData(); });

        // ---------- Modal detail ----------
        tableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.detail-btn');
            if (btn) openModal(btn.dataset.id);
        });

        async function openModal(id) {
            modalOverlay.classList.add('is-open');
            modalBody.innerHTML = '<div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i>Memuat detail...</div>';

            try {
                const res = await fetch(`{{ url('/login-audit') }}/${id}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                renderModal(json);
            } catch (err) {
                modalBody.innerHTML = '<div class="empty-state">Gagal memuat detail percobaan ini.</div>';
            }
        }

        function renderRelatedList(items, emptyText) {
            if (!items || items.length === 0) {
                return `<div class="mini-empty">${emptyText}</div>`;
            }
            return `<div class="mini-list">${items.map(r => `
                <div class="mini-row">
                    <div>
                        <div style="font-weight:600;">${esc(r.username_input)}${r.user_name ? ' · ' + esc(r.user_name) : ''}</div>
                        <div class="faint" style="font-size:.72rem;">${esc(r.created_at_fmt)} · ${esc(r.ip_address)}</div>
                    </div>
                    ${statusBadge(r.status)}
                </div>
            `).join('')}</div>`;
        }

        function renderModal(json) {
            const a = json.attempt;
            const u = a.user_detail;

            const accountBlock = u ? `
                <div class="detail-grid">
                    <div class="detail-field"><div class="k">Nama Lengkap</div><div class="v">${esc(u.name)}</div></div>
                    <div class="detail-field"><div class="k">Role</div><div class="v" style="text-transform:capitalize;">${esc(u.role)}</div></div>
                    <div class="detail-field"><div class="k">NIM / NIDN</div><div class="v">${esc(u.identifier || '—')}</div></div>
                    <div class="detail-field"><div class="k">Email</div><div class="v">${esc(u.email || '—')}</div></div>
                    <div class="detail-field span-2"><div class="k">Akun dibuat</div><div class="v">${esc(u.akun_dibuat || '—')}</div></div>
                </div>
            ` : `
                <div class="no-account-note">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
                    <div>Tidak ada akun yang cocok dengan username <strong>${esc(a.username_input)}</strong> di database. Ini bisa berarti percobaan asal tebak atau akunnya sudah dihapus.</div>
                </div>
            `;

            modalBody.innerHTML = `
                <div class="detail-status-row">
                    ${statusBadge(a.status)}
                    <div class="detail-time"><i class="fa-regular fa-clock"></i> ${esc(a.created_at_fmt)}</div>
                </div>

                <div>
                    <div class="section-title">Informasi Akun</div>
                    ${accountBlock}
                </div>

                <div>
                    <div class="section-title">Detail Teknis</div>
                    <div class="detail-grid">
                        <div class="detail-field"><div class="k">Alamat IP</div><div class="v mono">${esc(a.ip_address)}</div></div>
                        <div class="detail-field"><div class="k">Username diinput</div><div class="v">${esc(a.username_input)}</div></div>
                        <div class="detail-field span-2"><div class="k">User Agent (browser/perangkat)</div><div class="v" style="font-weight:500; font-size:.78rem; white-space: normal;">${esc(a.user_agent_full || '—')}</div></div>
                    </div>
                </div>

                <div>
                    <div class="section-title">Riwayat IP ini (sepanjang waktu)</div>
                    <div class="ip-stat-row">
                        <div class="ip-stat"><div class="n" style="color:var(--ok-ink);">${json.ip_stats.success}</div><div class="l">Berhasil</div></div>
                        <div class="ip-stat"><div class="n" style="color:var(--err-ink);">${json.ip_stats.failed}</div><div class="l">Gagal</div></div>
                        <div class="ip-stat"><div class="n" style="color:var(--lock-ink);">${json.ip_stats.locked}</div><div class="l">Diblokir</div></div>
                    </div>
                </div>

                <div>
                    <div class="section-title">5 Percobaan Terakhir dari IP yang Sama</div>
                    ${renderRelatedList(json.from_same_ip, 'Tidak ada percobaan lain dari IP ini.')}
                </div>

                <div>
                    <div class="section-title">5 Percobaan Terakhir untuk Username yang Sama</div>
                    ${renderRelatedList(json.same_username, 'Tidak ada percobaan lain untuk username ini.')}
                </div>
            `;
        }

        modalClose.addEventListener('click', () => modalOverlay.classList.remove('is-open'));
        modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) modalOverlay.classList.remove('is-open'); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') modalOverlay.classList.remove('is-open'); });

        // ---------- Init ----------
        loadData();
    </script>
</body>
</html>
