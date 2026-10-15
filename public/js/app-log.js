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

    const pager = Pager.create(document.getElementById('alogPager'), {
        onGo: (n) => {
            load(n);
            root.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
    });

    function rowHTML(e, i) {
        const has = e.has_details;
        const cls = BADGE[e.group] || 'badge-neutral';
        return `
            <tr class="alog-row ${has ? 'has-details' : ''}" data-i="${i}" ${has ? 'tabindex="0" aria-expanded="false"' : ''}>
                <td>
                    <div class="alog-mono">${esc(e.time)}</div>
                    <div class="alog-env">${esc(e.env)}</div>
                </td>
                <td class="center"><span class="badge ${cls}"><span class="material-symbols-outlined">${ICON[e.group] || 'info'}</span>${esc(e.level.toUpperCase())}</span></td>
                <td><div class="alog-msg">${esc(e.message)}</div></td>
                <td class="center">${has ? '<span class="material-symbols-outlined alog-caret">expand_more</span>' : ''}</td>
            </tr>
            ${has ? `<tr class="alog-detail" data-for="${i}" hidden><td colspan="4"><pre>${esc(e.details)}</pre></td></tr>` : ''}`;
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

    body.addEventListener('click', (e) => {
        if (e.target.closest('.alog-detail')) return; // klik di area detail (mis. memilih teks) tidak menutup
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
