/**
 * Penampil log aplikasi Laravel (superadmin) di halaman Log Aktivitas.
 * Markup: resources/views/partials/app-log-viewer.blade.php · Server: AppLogController
 * GET /login-audit/app-logs?file=&level=&q=&page=  ->  { files, file, truncated, counts, pagination, data[] }
 * Butuh: js/pager.js (Pager) — dimuat sebelum file ini.
 */
(function () {
    'use strict';

    const root = document.getElementById('appLogSection');
    if (!root) return;

    const url = root.dataset.url;
    const body = document.getElementById('alogBody');
    const fileSel = document.getElementById('alogFile');
    const search = document.getElementById('alogSearch');
    const tabs = Array.from(document.querySelectorAll('#alogTabs .view-tab'));
    const note = document.getElementById('alogNote');
    const range = document.getElementById('alogRange');
    const total = document.getElementById('alogTotal');

    const state = { file: '', level: 'all', q: '', page: 1 };
    let seq = 0;
    let timer = null;

    const esc = (s) => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };

    // Warna badge mengikuti kelompok level (kelas .badge bawaan tema)
    const BADGE = { error: 'badge-danger', warning: 'badge-warning', info: 'badge-info', debug: 'badge-neutral' };
    const ICON = { error: 'error', warning: 'warning', info: 'info', debug: 'bug_report' };

    // ------------------------------------------------------------------
    // Tampilan entri aktivitas ala Swagger: pil aksi berwarna (seperti
    // badge GET/POST/DELETE) + panel ringkasan berbagian + tab JSON.
    // Hanya dipakai bila `details` entri berupa objek JSON (konteks log
    // aktivitas); entri biasa (stack trace dsb.) tetap tampil di <pre>.
    // ------------------------------------------------------------------
    const escAttr = (s) => esc(s).replace(/"/g, '&quot;');

    // kode aksi -> [label pil, warna]. Warna mengikuti kebiasaan Swagger:
    // hijau = tambah, oranye = ubah, merah = hapus/gagal, biru = netral/akses.
    const VERBS = {
        'create': ['TAMBAH', 'success'], 'update': ['UBAH', 'warning'], 'delete': ['HAPUS', 'danger'],
        'login': ['LOGIN', 'info'], 'logout': ['LOGOUT', 'info'],
        'login.failed': ['LOGIN GAGAL', 'danger'], 'login.locked': ['TERKUNCI', 'danger'],
        'upload': ['UNGGAH', 'primary'], 'upload.failed': ['UNGGAH GAGAL', 'danger'],
        'export': ['EKSPOR', 'primary'], 'print': ['CETAK', 'primary'],
        'password': ['PASSWORD', 'warning'], 'password.reset_request': ['MINTA RESET', 'info'], 'password.reset': ['RESET PWD', 'warning'],
        'account': ['AKUN', 'warning'], 'permission': ['HAK AKSES', 'warning'],
        'backup': ['BACKUP', 'primary'], 'restore': ['RESTORE', 'danger'],
        'denied': ['DITOLAK', 'danger'], 'page': ['BUKA', 'info'], 'action': ['AKSI', 'neutral'],
        'approval.diajukan': ['AJUKAN', 'warning'], 'approval.diajukan_ulang': ['AJUKAN ULANG', 'warning'],
        'approval.diteruskan': ['TERUSKAN', 'info'], 'approval.disetujui': ['SETUJU', 'success'], 'approval.ditolak': ['TOLAK', 'danger'],
    };

    const LABELS = {
        aksi: 'Aksi', rute: 'Rute', status: 'Status HTTP', data: 'Jenis Data', data_id: 'ID Data',
        menu: 'Menu', item_id: 'ID Item', terkirim: 'Terkirim',
        user_id: 'ID Pengguna', role: 'Peran', ip: 'Alamat IP', akun_id: 'ID Akun Dituju',
        file: 'Nama File', ukuran_kb: 'Ukuran File', perangkat: 'Perangkat',
    };

    // [judul bagian, kunci-kunci di dalamnya] — kunci lain masuk ke "Lainnya"
    const SECTIONS = [
        ['Aktivitas', ['aksi', 'rute', 'status', 'data', 'data_id', 'menu', 'item_id', 'ke', 'dari', 'terkirim']],
        ['Pelaku', ['user_id', 'role', 'ip', 'akun_id']],
        ['File', ['file', 'ukuran_kb']],
        ['Perangkat', ['perangkat']],
    ];

    const HTTP_TEXT = { 200: 'OK', 201: 'Created', 204: 'No Content', 302: 'Found', 403: 'Forbidden', 422: 'Unprocessable Content' };
    const STATE_LABEL = {
        menunggu_dosen: ['Menunggu Dosen Wali', 'warning'], menunggu_superadmin: ['Menunggu Superadmin', 'warning'],
        disetujui: ['Disetujui', 'success'], ditolak: ['Ditolak', 'danger'],
    };

    function verbOf(aksi) {
        if (!aksi) return null;
        const v = VERBS[aksi];
        return v ? { label: v[0], tone: v[1] } : { label: String(aksi).toUpperCase().slice(0, 14), tone: 'neutral' };
    }

    /** Konteks JSON dari `details`, atau null kalau bukan objek JSON (mis. stack trace). */
    function parseContext(e) {
        const t = (e && e.has_details ? String(e.details || '') : '').trim();
        if (t[0] !== '{') return null;
        try {
            const o = JSON.parse(t);
            return o && typeof o === 'object' && !Array.isArray(o) ? o : null;
        } catch (_) { return null; }
    }

    function parseUA(ua) {
        let m, browser = null, os = null;
        if ((m = ua.match(/Edg(?:e|A|iOS)?\/(\d+)/))) browser = 'Edge ' + m[1];
        else if ((m = ua.match(/OPR\/(\d+)/))) browser = 'Opera ' + m[1];
        else if ((m = ua.match(/Firefox\/(\d+)/))) browser = 'Firefox ' + m[1];
        else if ((m = ua.match(/Chrome\/(\d+)/))) browser = 'Chrome ' + m[1];
        else if ((m = ua.match(/Version\/(\d+).*Safari/))) browser = 'Safari ' + m[1];

        if (/Windows NT 10/.test(ua)) os = 'Windows 10/11';
        else if (/Windows/.test(ua)) os = 'Windows';
        else if (/Android/.test(ua)) os = 'Android';
        else if (/iPhone|iPad|iPod/.test(ua)) os = 'iOS';
        else if (/Mac OS X/.test(ua)) os = 'macOS';
        else if (/Linux/.test(ua)) os = 'Linux';

        return browser || os ? [browser, os].filter(Boolean).join(' · ') : null;
    }

    const fmtKB = (kb) => (kb >= 1024 ? (kb / 1024).toFixed(2) + ' MB' : kb + ' KB');

    function stateChip(v) {
        if (v === null || v === undefined || v === '') return '<span class="alog-chip alog-chip--neutral">Baru</span>';
        const known = STATE_LABEL[v];
        const label = known ? known[0] : String(v).replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase());
        return `<span class="alog-chip alog-chip--${known ? known[1] : 'neutral'}">${esc(label)}</span>`;
    }

    function valueHTML(key, v) {
        switch (key) {
            case 'aksi': case 'rute': case 'ip':
                return `<code class="alog-code">${esc(v)}</code>`;
            case 'user_id': case 'data_id': case 'item_id': case 'akun_id':
                return `<code class="alog-code">#${esc(v)}</code>`;
            case 'role':
                return `<span class="alog-chip alog-chip--primary">${esc(v)}</span>`;
            case 'status': {
                const n = Number(v);
                const tone = n >= 500 ? 'danger' : n >= 400 ? 'warning' : n >= 300 ? 'info' : 'success';
                return `<span class="alog-chip alog-chip--${tone}">${esc(v)}</span> ${esc(HTTP_TEXT[n] || '')}`;
            }
            case 'ukuran_kb':
                return esc(fmtKB(Number(v) || 0));
            case 'perangkat': {
                const nice = parseUA(String(v));
                return nice
                    ? `<span title="${escAttr(v)}">${esc(nice)}</span><div class="alog-raw">${esc(v)}</div>`
                    : esc(v);
            }
            default:
                return typeof v === 'object' && v !== null ? `<code class="alog-code">${esc(JSON.stringify(v))}</code>` : esc(String(v));
        }
    }

    function summaryHTML(ctx) {
        const used = new Set();
        const blocks = [];

        SECTIONS.forEach(([title, keys]) => {
            const rows = [];
            keys.forEach((k) => {
                if (k === 'dari') return; // digabung dengan 'ke' sebagai satu baris "Perubahan Status"
                if (k === 'ke') {
                    if ('ke' in ctx) {
                        rows.push(['Perubahan Status', `<span class="alog-flow">${stateChip(ctx.dari)}<span class="material-symbols-outlined">arrow_forward</span>${stateChip(ctx.ke)}</span>`]);
                        used.add('dari'); used.add('ke');
                    }
                    return;
                }
                if (k in ctx) { rows.push([LABELS[k] || k, valueHTML(k, ctx[k])]); used.add(k); }
            });
            if (rows.length) blocks.push([title, rows]);
        });

        const rest = Object.keys(ctx).filter((k) => !used.has(k));
        if (rest.length) blocks.push(['Lainnya', rest.map((k) => [LABELS[k] || k, valueHTML(k, ctx[k])])]);

        return blocks.map(([title, rows]) => `
            <section class="alog-sec">
                <h4 class="alog-sec-title">${esc(title)}</h4>
                <dl class="alog-kv">${rows.map(([l, v]) => `<dt>${esc(l)}</dt><dd>${v}</dd>`).join('')}</dl>
            </section>`).join('');
    }

    /** JSON cantik + pewarnaan sintaks (kunci, teks, angka, true/false/null). */
    function highlightJSON(obj) {
        return esc(JSON.stringify(obj, null, 2)).replace(
            /("(?:\\.|[^"\\])*")(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g,
            (m, str, colon, lit, num) => {
                if (str) return colon ? `<span class="j-k">${str}</span>${colon}` : `<span class="j-s">${str}</span>`;
                if (lit) return `<span class="j-l">${lit}</span>`;
                return `<span class="j-n">${num}</span>`;
            }
        );
    }

    function panelHTML(ctx) {
        return `
            <div class="alog-panel">
                <div class="alog-panel-head">
                    <div class="alog-ptabs" role="tablist">
                        <button type="button" class="alog-ptab is-active" role="tab" aria-selected="true" data-pane="summary">Ringkasan</button>
                        <button type="button" class="alog-ptab" role="tab" aria-selected="false" data-pane="json">JSON</button>
                    </div>
                    <button type="button" class="alog-copy" title="Salin JSON ke clipboard">
                        <span class="material-symbols-outlined">content_copy</span><span class="alog-copy-text">Salin</span>
                    </button>
                </div>
                <div class="alog-pane alog-summary" data-pane="summary">${summaryHTML(ctx)}</div>
                <div class="alog-pane" data-pane="json" hidden><pre class="alog-json">${highlightJSON(ctx)}</pre></div>
            </div>`;
    }

    let current = []; // entri halaman yang sedang tampil (dipakai tombol Salin)

    const pager = Pager.create(document.getElementById('alogPager'), {
        onGo: (n) => {
            load(n);
            root.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
    });

    function rowHTML(e, i) {
        const has = e.has_details;
        const cls = BADGE[e.group] || 'badge-neutral';
        const ctx = parseContext(e);
        const verb = ctx ? verbOf(ctx.aksi) : null;
        const tone = verb ? ` alog-act alog-tone-${verb.tone}` : '';
        return `
            <tr class="alog-row ${has ? 'has-details' : ''}${tone}" data-i="${i}" ${has ? 'tabindex="0" aria-expanded="false"' : ''}>
                <td>
                    <div class="alog-mono">${esc(e.time)}</div>
                    <div class="alog-env">${esc(e.env)}</div>
                </td>
                <td class="center"><span class="badge ${cls}"><span class="material-symbols-outlined">${ICON[e.group] || 'info'}</span>${esc(e.level.toUpperCase())}</span></td>
                <td>
                    <div class="alog-msgwrap">
                        ${verb ? `<span class="alog-verb">${esc(verb.label)}</span>` : ''}
                        <div class="alog-msg">${esc(e.message)}</div>
                    </div>
                </td>
                <td class="center">${has ? '<span class="material-symbols-outlined alog-caret">expand_more</span>' : ''}</td>
            </tr>
            ${has ? `<tr class="alog-detail${tone}" data-for="${i}" hidden><td colspan="4">${ctx ? panelHTML(ctx) : `<pre>${esc(e.details)}</pre>`}</td></tr>` : ''}`;
    }

    function toggleRow(row) {
        if (!row || !row.classList.contains('has-details')) return;
        const detail = body.querySelector(`.alog-detail[data-for="${row.dataset.i}"]`);
        if (!detail) return;
        const open = detail.hidden;
        detail.hidden = !open;
        row.classList.toggle('is-open', open);
        row.setAttribute('aria-expanded', String(open));
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text);
        return new Promise((resolve, reject) => { // cadangan untuk http:// non-localhost
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy') ? resolve() : reject(new Error('copy gagal')); } catch (err) { reject(err); }
            ta.remove();
        });
    }

    body.addEventListener('click', (e) => {
        const detail = e.target.closest('.alog-detail');
        if (detail) { // klik di area detail tidak menutup baris; hanya tab & tombol salin yang aktif
            const tab = e.target.closest('.alog-ptab');
            if (tab) {
                const panel = tab.closest('.alog-panel');
                panel.querySelectorAll('.alog-ptab').forEach((t) => {
                    const on = t === tab;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', String(on));
                });
                panel.querySelectorAll('.alog-pane').forEach((p) => { p.hidden = p.dataset.pane !== tab.dataset.pane; });
                return;
            }
            const copy = e.target.closest('.alog-copy');
            if (copy) {
                const ctx = parseContext(current[Number(detail.dataset.for)]);
                if (!ctx) return;
                copyText(JSON.stringify(ctx, null, 2)).then(() => {
                    const label = copy.querySelector('.alog-copy-text');
                    label.textContent = 'Tersalin';
                    copy.classList.add('is-done');
                    setTimeout(() => { label.textContent = 'Salin'; copy.classList.remove('is-done'); }, 1500);
                }).catch(() => window.Toast?.show?.({ type: 'error', title: 'Gagal menyalin', message: 'Browser menolak akses clipboard.' }));
            }
            return;
        }
        toggleRow(e.target.closest('.alog-row'));
    });
    body.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.classList?.contains('alog-row')) {
            e.preventDefault();
            toggleRow(e.target);
        }
    });

    function fillFiles(files, current) {
        // Daftar file hanya diisi ulang saat berubah (supaya pilihan tidak "berkedip")
        const sig = files.map((f) => f.name).join('|');
        if (fileSel.dataset.sig !== sig) {
            fileSel.dataset.sig = sig;
            fileSel.innerHTML = files.length
                ? files.map((f) => `<option value="${esc(f.name)}">${esc(f.name)} (${(f.size / 1024).toFixed(f.size > 1048576 ? 0 : 1)} KB)</option>`).join('')
                : '<option value="">Tidak ada file log</option>';
        }
        fileSel.value = current;
    }

    async function load(page = state.page) {
        const mine = ++seq;
        state.page = page;
        const params = new URLSearchParams({ page: String(page), level: state.level });
        if (state.file) params.set('file', state.file);
        if (state.q) params.set('q', state.q);

        body.classList.add('is-fetching');
        try {
            const res = await fetch(url + '?' + params, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (res.status === 403) throw Object.assign(new Error('403'), { forbidden: true });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            if (mine !== seq) return;

            state.file = json.file;
            fillFiles(json.files, json.file);

            Object.entries(json.counts).forEach(([k, v]) => {
                const el = root.querySelector(`.alog-count[data-c="${k}"]`);
                if (el) el.textContent = v;
            });

            const p = json.pagination;
            current = json.data;
            body.innerHTML = json.data.length
                ? json.data.map(rowHTML).join('')
                : `<tr><td colspan="4" class="alog-empty">${json.files.length ? 'Tidak ada entri log yang cocok.' : 'Belum ada file log di storage/logs.'}</td></tr>`;

            note.hidden = !json.truncated;
            if (json.truncated) note.textContent = `File log besar: hanya ${json.tail_mb} MB terbaru yang dibaca, entri yang lebih lama tidak ditampilkan.`;

            const from = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
            range.textContent = from + '–' + Math.min(p.current_page * p.per_page, p.total);
            total.textContent = p.total;
            state.page = p.current_page;
            pager.render(p.current_page, p.last_page);
        } catch (err) {
            if (mine !== seq) return;
            body.innerHTML = `<tr><td colspan="4" class="alog-empty">${err.forbidden ? 'Log aplikasi hanya bisa dilihat oleh superadmin.' : 'Gagal memuat log. Coba muat ulang.'}</td></tr>`;
            if (!err.forbidden) window.Toast?.show?.({ type: 'error', title: 'Gagal memuat log', message: 'Log aplikasi tidak bisa dimuat. Coba lagi.' });
        } finally {
            if (mine === seq) body.classList.remove('is-fetching');
        }
    }

    function setLevel(level) {
        state.level = level;
        tabs.forEach((t) => {
            const on = t.dataset.level === level;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
        });
        load(1);
    }
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => { if (tab.dataset.level !== state.level) setLevel(tab.dataset.level); });
        tab.addEventListener('keydown', (e) => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            e.preventDefault();
            const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
            next.focus();
            setLevel(next.dataset.level);
        });
    });

    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => { state.q = search.value.trim(); load(1); }, 350);
    });
    fileSel.addEventListener('change', () => { state.file = fileSel.value; load(1); });
    document.getElementById('alogRefresh').addEventListener('click', () => load(state.page));

    load(1);
})();
