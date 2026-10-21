/**
 * Halaman Persetujuan (resources/views/persetujuan.blade.php).
 *  - Daftar berhalaman (server), filter menu/tahun, pencarian, tab Menunggu / Riwayat
 *  - Modal Periksa: Detail | Komentar | Riwayat  (membuka pengajuan baru = otomatis "Diproses Prodi")
 *  - Aksi: Verifikasi, Tolak (alasan wajib), Batalkan verifikasi, Verifikasi massal
 * Butuh: script.js (SIDA.modal, SIDA.dropdown, SIDA.util.csrfToken), pager.js, toast.js (opsional).
 */
(function () {
    const base = document.currentScript?.dataset.urlBase || '/persetujuan';
    const pageEl = document.getElementById('apprPage');
    if (!pageEl) return;

    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const toast = (type, title, message) => { if (window.Toast) Toast.show({ type, title, message }); };
    const jsonHeaders = () => ({
        'X-CSRF-TOKEN': SIDA.util.csrfToken(),
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    });
    const ROLE = { superadmin: 'Prodi', admin: 'Admin', dosen: 'Dosen', mahasiswa: 'Mahasiswa' };
    const ICON = { diajukan: 'send', menunggu_prodi: 'hourglass_top', disetujui: 'check_circle', ditolak: 'cancel' };

    const ACTIONS = {
        setujui: { title: 'Verifikasi Pengajuan', label: 'Catatan (opsional)', ph: 'Catatan untuk arsip / mahasiswa.', required: false, btn: 'Verifikasi', done: 'success' },
        tolak:   { title: 'Tolak Pengajuan', label: 'Alasan penolakan (wajib)', ph: 'Jelaskan apa yang perlu diperbaiki mahasiswa, mis. link bukti tidak bisa dibuka.', required: true, btn: 'Tolak', done: 'warning', danger: true },
        batal:   { title: 'Batalkan Verifikasi', label: 'Alasan pembatalan (opsional)', ph: 'Mis. data ternyata belum lengkap. Pengajuan kembali ke antrean Prodi.', required: false, btn: 'Batalkan Verifikasi', done: 'warning', danger: true },
        massal:  { title: 'Verifikasi Terpilih', label: 'Catatan untuk semua (opsional)', ph: 'Catatan ini dikirim ke semua pengajuan terpilih.', required: false, btn: 'Verifikasi Semua', done: 'success' },
    };
    const HINT = {
        perlu: 'Pengajuan yang menunggu Prodi, yang paling lama menunggu di atas. Klik baris untuk memeriksa.',
        riwayat: 'Pengajuan yang sudah diputuskan. Komentar tetap bisa ditambah, dan verifikasi bisa dibatalkan bila perlu.',
    };

    const state = {
        tab: pageEl.dataset.tab || 'perlu',
        page: 1,
        seq: 0,
        items: new Map(),          // key -> item (halaman sekarang)
        selected: new Map(),       // key -> {menu,id}  (lintas halaman)
        current: null,             // item di modal Periksa
        pane: 'detail',            // tab aktif di modal Periksa
    };
    const keyOf = (i) => i.menu + ':' + i.id;

    // ------------------------------------------------------------------ chip & checkbox
    const chip = (i) => `<span class="appr-chip is-${esc(i.status)}"><span class="material-symbols-outlined">${ICON[i.status] || 'info'}</span>${esc(i.status_label)}</span>`;
    const boxHTML = (label) => `
        <label class="sel-checkbox"><input type="checkbox" aria-label="${esc(label)}">
            <span class="checkmark"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1.5" y="1.5" width="21" height="21" rx="5" ry="5" stroke-width="3"></rect>
                <polyline points="7 10 12 16 22 2" stroke-width="4"></polyline>
                <line x1="5" y1="12" x2="19" y2="12" stroke-width="3.5"></line></g></svg></span>
        </label>`;

    // ------------------------------------------------------------------ daftar
    const body = $('apprBody');
    const table = $('apprTable');
    const headSel = $('apprHeadSel');
    headSel.innerHTML = boxHTML('Pilih semua di halaman ini');
    const headBox = headSel.querySelector('input');

    function rowHTML(i) {
        const pending = state.tab === 'perlu';
        const wait = pending && i.waiting >= 1
            ? `<span class="appr-wait ${i.waiting >= 3 ? 'is-late' : ''}">Menunggu ${i.waiting} hari</span>` : '';
        return `<tr class="appr-row" data-key="${esc(keyOf(i))}" data-menu="${esc(i.menu)}" data-id="${esc(i.id)}">
            ${pending ? `<td class="sel-col">${boxHTML('Pilih ' + (i.title || 'pengajuan'))}</td>` : ''}
            <td>
                <span class="appr-title-cell" title="${esc(i.title)}">${esc(i.title || '(tanpa judul)')}</span>
                <span class="appr-menu"><span class="material-symbols-outlined">${esc(i.icon)}</span>${esc(i.menu_label)}</span>
            </td>
            <td><div class="appr-person"><b>${esc(i.name)}</b>${i.nim ? `<span>NIM ${esc(i.nim)}</span>` : ''}</div></td>
            <td class="center">${chip(i)}</td>
            <td><div class="appr-time">${esc(pending ? i.submitted : i.updated)}${wait}</div></td>
            <td class="center">
                <div class="row-actions">
                    <button type="button" class="row-action-btn js-review" title="${pending ? 'Periksa' : 'Lihat detail'}"><span class="material-symbols-outlined">${pending ? 'fact_check' : 'visibility'}</span></button>
                    <button type="button" class="row-action-btn is-secondary js-comments" title="Komentar"><span class="material-symbols-outlined">chat_bubble</span>${i.comments ? `<span class="appr-badge">${i.comments}</span>` : ''}</button>
                </div>
            </td>
        </tr>`;
    }

    function render(data) {
        state.items.clear();
        data.items.forEach((i) => state.items.set(keyOf(i), i));
        body.innerHTML = data.items.map(rowHTML).join('');
        body.querySelectorAll('tr').forEach((tr) => { // kembalikan centang yang tersimpan
            const on = state.selected.has(tr.dataset.key);
            tr.classList.toggle('is-selected', on);
            const b = tr.querySelector('.sel-col input'); if (b) b.checked = on;
        });

        const m = data.meta;
        const empty = m.total === 0;
        document.querySelector('.table-scroll').hidden = empty;
        $('apprEmpty').hidden = !empty;
        if (empty) {
            $('apprEmptyDesc').textContent = filtered()
                ? 'Tidak ada pengajuan yang cocok dengan filter.'
                : (state.tab === 'perlu' ? 'Semua pengajuan sudah ditangani.' : 'Belum ada keputusan yang tercatat.');
        }
        $('footerRange').textContent = m.from + '–' + m.to;
        $('footerTotal').textContent = m.total;
        pager.render(m.page, m.last);

        table.classList.toggle('no-sel', state.tab !== 'perlu');
        $('apprTimeHead').textContent = state.tab === 'perlu' ? 'Diajukan' : 'Diputuskan';
        $('apprHint').textContent = HINT[state.tab];
        updateBar();
    }

    function setCounts(c) {
        if (!c) return;
        const set = (id, v) => { const el = $(id); if (el) el.textContent = v; };
        set('cntDiajukan', c.diajukan); set('cntProses', c.menunggu_prodi);
        set('cntOk', c.disetujui); set('cntTolak', c.ditolak);
        set('tabCntPerlu', c.perlu); set('tabCntRiwayat', c.riwayat);
    }
    function setPending(n) {
        document.querySelectorAll('.nav-badge').forEach((badge) => {
            if (n > 0) badge.textContent = n > 99 ? '99+' : n; else badge.remove();
        });
    }

    // ------------------------------------------------------------------ filter, cari, halaman
    const filterVal = (id) => $(id)?.value || 'semua';
    const searchEl = $('filter-search');
    const filtered = () => filterVal('filter-menu') !== 'semua' || filterVal('filter-tahun') !== 'semua' || !!searchEl.value.trim();

    async function load(page = state.page) {
        const seq = ++state.seq;
        const params = new URLSearchParams({ tab: state.tab, page: String(page) });
        const menu = filterVal('filter-menu'), tahun = filterVal('filter-tahun'), q = searchEl.value.trim();
        if (menu !== 'semua') params.set('menu', menu);
        if (tahun !== 'semua') params.set('tahun', tahun);
        if (q) params.set('q', q);

        body.classList.add('is-fetching');
        try {
            const res = await fetch(base + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (seq !== state.seq) return;
            state.page = data.meta.page;
            render(data);
            setCounts(data.counts);
        } catch (_) {
            if (seq === state.seq) toast('error', 'Gagal memuat data', 'Tidak bisa memuat halaman ini. Coba lagi.');
        } finally {
            if (seq === state.seq) body.classList.remove('is-fetching');
        }
    }

    const pager = Pager.create($('apprPager'), {
        onGo: (n) => { load(n); document.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
    });

    let timer = null;
    searchEl.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(1), 300); });
    // setTimeout 0: tunggu script.js selesai mengisi nilai dropdown tersembunyi
    document.querySelectorAll('.filter-card .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(() => load(1), 0)));
    $('btn-reset-filter').addEventListener('click', () => {
        document.querySelectorAll('.filter-card [data-dropdown]').forEach((d) => SIDA.dropdown.reset(d));
        searchEl.value = '';
        load(1);
    });

    document.querySelectorAll('.appr-tabs .appr-tab').forEach((btn) => btn.addEventListener('click', () => {
        if (btn.dataset.tab === state.tab) return;
        state.tab = btn.dataset.tab;
        document.querySelectorAll('.appr-tabs .appr-tab').forEach((b) => b.classList.toggle('is-active', b === btn));
        clearSelection();
        load(1);
    }));

    // ------------------------------------------------------------------ pilih banyak (verifikasi massal)
    const bar = $('apprSelBar');
    function updateBar() {
        const n = state.selected.size;
        $('apprSelCount').textContent = n;
        bar.classList.toggle('is-open', n > 0);
        bar.setAttribute('aria-hidden', n > 0 ? 'false' : 'true');
        table.classList.toggle('has-selection', n > 0);

        const boxes = [...body.querySelectorAll('.sel-col input')];
        const checked = boxes.filter((b) => b.checked).length;
        headBox.checked = boxes.length > 0 && checked === boxes.length;
        headBox.indeterminate = checked > 0 && checked < boxes.length;
    }
    function clearSelection() {
        state.selected.clear();
        body.querySelectorAll('tr').forEach((tr) => { tr.classList.remove('is-selected'); const b = tr.querySelector('.sel-col input'); if (b) b.checked = false; });
        updateBar();
    }
    function toggleRow(tr, on) {
        tr.classList.toggle('is-selected', on);
        if (on) state.selected.set(tr.dataset.key, { menu: tr.dataset.menu, id: Number(tr.dataset.id) });
        else state.selected.delete(tr.dataset.key);
    }
    headBox.addEventListener('change', () => {
        body.querySelectorAll('tr').forEach((tr) => { const b = tr.querySelector('.sel-col input'); if (b) { b.checked = headBox.checked; toggleRow(tr, headBox.checked); } });
        updateBar();
    });
    $('apprSelClear').addEventListener('click', clearSelection);
    $('apprSelApprove').addEventListener('click', () => {
        if (!state.selected.size) return;
        openNote({ aksi: 'massal', title: `${state.selected.size} pengajuan dipilih` });
    });

    body.addEventListener('click', (e) => {
        const tr = e.target.closest('tr.appr-row');
        if (!tr) return;
        if (e.target.closest('.sel-col')) { // klik kotak centang
            const b = tr.querySelector('.sel-col input');
            if (e.target === b) { toggleRow(tr, b.checked); updateBar(); }
            return;
        }
        const item = state.items.get(tr.dataset.key);
        if (!item) return;
        openReview(item, e.target.closest('.js-comments') ? 'komentar' : 'detail');
    });

    // ------------------------------------------------------------------ modal catatan (aksi)
    const noteModal = SIDA.modal.attach({
        backdrop: $('apprBackdrop'), card: $('apprModal'), closeBtn: $('apprCloseBtn'),
        cancelBtn: $('apprCancelBtn'), dragHandle: $('apprDragHandle'),
    });
    const form = $('apprForm'), noteEl = $('apprNote'), errEl = $('apprError'), submitBtn = $('apprSubmitBtn');
    let pendingAct = null; // { aksi, item?, title }

    const showError = (msg) => { errEl.textContent = msg; errEl.hidden = !msg; };

    function openNote(act) {
        const cfg = ACTIONS[act.aksi];
        pendingAct = act;
        $('apprModalTitle').textContent = cfg.title;
        $('apprModalSub').textContent = act.title || act.item?.title || '';
        $('apprNoteLabel').textContent = cfg.label;
        $('apprSubmitText').textContent = cfg.btn;
        noteEl.placeholder = cfg.ph;
        noteEl.value = '';
        submitBtn.className = 'btn-apply' + (cfg.danger ? ' is-danger' : '');
        showError('');
        noteModal.open();
        setTimeout(() => noteEl.focus(), 60);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!pendingAct) return;
        const cfg = ACTIONS[pendingAct.aksi];
        const note = noteEl.value.trim();
        if (cfg.required && note.length < 5) {
            showError('Alasan penolakan wajib diisi (minimal 5 karakter) supaya mahasiswa tahu apa yang perlu diperbaiki.');
            noteEl.focus();
            return;
        }

        const bulk = pendingAct.aksi === 'massal';
        const url = bulk ? `${base}/massal` : `${base}/${pendingAct.item.menu}/${pendingAct.item.id}/${pendingAct.aksi}`;
        const payload = bulk ? { items: [...state.selected.values()], note } : { note };

        submitBtn.disabled = true;
        showError('');
        try {
            const res = await fetch(url, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify(payload) });
            let json = {};
            try { json = await res.json(); } catch (_) { /* bukan JSON */ }

            if (!res.ok || !json.success) {
                const flat = json.errors ? Object.values(json.errors).flat() : [];
                showError(flat[0] || json.message || (res.status === 419 ? 'Sesi habis. Muat ulang halaman lalu coba lagi.' : 'Gagal memproses (' + res.status + ').'));
                return;
            }

            noteModal.close();
            const title = { tolak: 'Pengajuan ditolak', batal: 'Verifikasi dibatalkan', massal: 'Verifikasi massal selesai' }[pendingAct.aksi] || 'Berhasil';
            toast(cfg.done, title, json.message);
            if (bulk) clearSelection();
            else state.selected.delete(keyOf(pendingAct.item));
            setCounts(json.counts);
            setPending(json.pending);
            load(state.page);
        } catch (_) {
            showError('Tidak bisa terhubung ke server.');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // ------------------------------------------------------------------ modal Periksa
    const revModal = SIDA.modal.attach({
        backdrop: $('apprRevBackdrop'), card: $('apprRevModal'), closeBtn: $('apprRevClose'),
        cancelBtn: $('apprRevCancel'), dragHandle: $('apprRevDrag'),
    });
    const loaded = { komentar: null, riwayat: null }; // key item yang panelnya sudah dimuat
    const cm = { items: [], shown: 0 };               // komentar (terbaru di atas) & jumlah yang ditampilkan
    const CM_STEP = 4;

    const initials = (n) => String(n || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase() || '?';
    const safeUrl = (u) => (u && /^https?:\/\//i.test(u) ? u : null);
    const hostOf = (u) => { try { return new URL(u).hostname.replace(/^www\./, ''); } catch (_) { return ''; } };
    const empty = (icon, text) => `<li class="appr-empty-sm"><span class="material-symbols-outlined">${icon}</span>${text}</li>`;

    // ---- header, ringkasan (kanan), detail (kiri)
    function metaHTML(i) {
        const late = (i.actions || []).includes('setujui') && i.waiting >= 1
            ? `<span class="appr-wait ${i.waiting >= 3 ? 'is-late' : ''}">Menunggu ${i.waiting} hari</span>` : '';
        return `<span class="appr-menu"><span class="material-symbols-outlined">${esc(i.icon)}</span>${esc(i.menu_label)}</span>${chip(i)}${late}`;
    }
    function asideHTML(i) {
        const url = safeUrl(i.bukti);
        const decided = i.status === 'disetujui' || i.status === 'ditolak';
        return `<section>
                <h4 class="appr-as-h">Pengaju</h4>
                <div class="appr-who"><span class="appr-ava">${esc(initials(i.name))}</span>
                    <div><b>${esc(i.name)}</b>${i.nim ? `<small>NIM ${esc(i.nim)}</small>` : ''}</div></div>
            </section>
            <section>
                <h4 class="appr-as-h">Waktu</h4>
                <dl class="appr-as-dl">
                    <dt>Diajukan</dt><dd>${esc(i.submitted || '-')}</dd>
                    ${decided ? `<dt>Diputuskan</dt><dd>${esc(i.updated || '-')}</dd>` : ''}
                </dl>
            </section>
            <section>
                <h4 class="appr-as-h">Bukti</h4>
                ${url ? `<a class="appr-proof" href="${esc(url)}" target="_blank" rel="noopener noreferrer">
                            <span class="material-symbols-outlined">open_in_new</span>
                            <span>Lihat bukti kegiatan<small>${esc(hostOf(url))}</small></span></a>`
                      : '<div class="appr-proof is-none"><span class="material-symbols-outlined">link_off</span><span>Tidak ada tautan bukti yang valid</span></div>'}
            </section>`;
    }
    function detailHTML(i) {
        const fields = Object.entries(i.detail || {}).filter(([, v]) => v !== null && v !== '')
            .map(([k, v]) => `<div class="appr-field${String(v).length > 38 ? ' is-wide' : ''}"><dt>${esc(k)}</dt><dd>${esc(v)}</dd></div>`).join('');
        const bad = i.status === 'ditolak';
        return `<h4 class="appr-sec-h">Rincian pengajuan</h4>
            <dl class="appr-fields">${fields || '<div class="appr-field is-wide"><dd>Tidak ada rincian tambahan.</dd></div>'}</dl>
            ${i.note ? `<div class="appr-callout ${bad ? 'is-bad' : 'is-info'}"><span class="material-symbols-outlined">${bad ? 'error' : 'sticky_note_2'}</span>
                <div><b>${bad ? 'Alasan penolakan' : 'Catatan keputusan'}</b>${esc(i.note)}</div></div>` : ''}`;
    }
    function renderHead(i) {
        $('apprRevIcon').innerHTML = `<span class="material-symbols-outlined">${esc(i.icon)}</span>`;
        $('apprRevName').textContent = i.title || '(tanpa judul)';
        $('apprRevHead').innerHTML = metaHTML(i);
        $('apprRevAside').innerHTML = asideHTML(i);
    }

    // ---- footer satu baris. Kiri: Batalkan Verifikasi. Kanan: Tutup + (tab Komentar ? Kirim : Tolak/Verifikasi).
    function syncFooter(i) {
        const a = i.actions || [];
        const komentar = state.pane === 'komentar';
        const pending = a.includes('setujui');
        $('apprCommentSend').hidden = !komentar;
        $('apprRevApprove').hidden = komentar || !pending;
        $('apprRevReject').hidden = komentar || !a.includes('tolak');
        $('apprRevUndo').hidden = !a.includes('batal');
        $('apprRevTitle').textContent = pending ? 'Periksa pengajuan' : 'Detail pengajuan';
    }
    function setPane(name) {
        state.pane = name;
        document.querySelectorAll('#apprRevModal .appr-mtab').forEach((t) => t.classList.toggle('is-active', t.dataset.pane === name));
        document.querySelectorAll('#apprRevModal .appr-pane').forEach((p) => { p.hidden = p.dataset.pane !== name; });
        document.querySelector('.appr-rv-scroll').scrollTop = 0;
        const i = state.current;
        if (!i) return;
        syncFooter(i);
        if (name === 'komentar' && loaded.komentar !== keyOf(i)) loadComments(i);
        if (name === 'riwayat' && loaded.riwayat !== keyOf(i)) loadHistory(i);
    }
    document.querySelectorAll('#apprRevModal .appr-mtab').forEach((t) => t.addEventListener('click', () => setPane(t.dataset.pane)));

    function setCommentCount(i) {
        $('apprMtabComments').textContent = i.comments || 0;
        const btn = body.querySelector(`tr[data-key="${CSS.escape(keyOf(i))}"] .js-comments`);
        if (btn) {
            btn.querySelector('.appr-badge')?.remove();
            if (i.comments) btn.insertAdjacentHTML('beforeend', `<span class="appr-badge">${i.comments}</span>`);
        }
    }

    function openReview(item, pane) {
        state.current = item;
        loaded.komentar = loaded.riwayat = null;
        cm.items = []; cm.shown = 0;
        renderHead(item);
        $('apprPaneDetail').innerHTML = detailHTML(item);
        $('apprCommentList').innerHTML = '';
        $('apprCommentMore').hidden = true;
        $('apprHistList').innerHTML = '';
        $('apprCommentBody').value = '';
        $('apprCommentErr').hidden = true;
        setCommentCount(item);
        setPane(pane || 'detail'); // juga mengatur footer
        revModal.open();
        if (item.status === 'diajukan') startReview(item);
    }

    async function startReview(item) {
        try {
            const res = await fetch(`${base}/${item.menu}/${item.id}/mulai`, { method: 'POST', headers: jsonHeaders() });
            const json = await res.json();
            if (!res.ok || !json.success || !json.changed) return;
            item.status = json.status; item.status_label = json.status_label;
            if (state.current === item) $('apprRevHead').innerHTML = metaHTML(item);
            const rowChip = body.querySelector(`tr[data-key="${CSS.escape(keyOf(item))}"] .appr-chip`);
            if (rowChip) rowChip.outerHTML = chip(item);
            setCounts(json.counts);
            setPending(json.pending);
            loaded.riwayat = null; // linimasa bertambah satu baris
        } catch (_) { /* status tetap Diajukan; keputusan tetap bisa diambil */ }
    }

    $('apprRevApprove').addEventListener('click', () => { const i = state.current; revModal.close(); openNote({ aksi: 'setujui', item: i }); });
    $('apprRevReject').addEventListener('click', () => { const i = state.current; revModal.close(); openNote({ aksi: 'tolak', item: i }); });
    $('apprRevUndo').addEventListener('click', () => { const i = state.current; revModal.close(); openNote({ aksi: 'batal', item: i }); });

    // ---- komentar: kolom tulis di atas, daftar (terbaru dulu) di bawah, "Tampilkan lebih banyak" per 4
    const commentList = $('apprCommentList');
    const moreBtn = $('apprCommentMore');
    function commentHTML(c) {
        return `<li class="appr-cm${c.mine ? ' is-mine' : ''}">
            <span class="appr-ava">${esc(initials(c.author))}</span>
            <div class="appr-cm-bubble">
                <div class="appr-cm-meta"><b>${esc(c.author)}</b>${c.role ? `<span class="appr-role">${esc(ROLE[c.role] || c.role)}</span>` : ''}<time>${esc(c.at)}</time></div>
                <p>${esc(c.body)}</p>
            </div></li>`;
    }
    function renderComments() {
        if (!cm.items.length) {
            commentList.innerHTML = empty('chat_bubble', 'Belum ada komentar.<br>Komentar Prodi akan terlihat oleh mahasiswa.');
            moreBtn.hidden = true;
            return;
        }
        commentList.innerHTML = cm.items.slice(0, cm.shown).map(commentHTML).join('');
        const rest = cm.items.length - cm.shown;
        moreBtn.hidden = rest <= 0;
        if (rest > 0) moreBtn.querySelector('span:last-child').textContent = `Tampilkan lebih banyak (${rest} lagi)`;
    }
    moreBtn.addEventListener('click', () => { cm.shown += CM_STEP; renderComments(); });

    async function loadComments(i) {
        commentList.innerHTML = empty('hourglass_empty', 'Memuat…');
        moreBtn.hidden = true;
        try {
            const res = await fetch(`${base}/${i.menu}/${i.id}/komentar`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error();
            if (state.current !== i) return;
            cm.items = json.data.slice().reverse(); // server: lama -> baru; tampilan: baru -> lama
            cm.shown = CM_STEP;
            renderComments();
            i.comments = json.data.length;
            setCommentCount(i);
            loaded.komentar = keyOf(i);
        } catch (_) {
            commentList.innerHTML = empty('error', 'Gagal memuat komentar.');
        }
    }
    $('apprCommentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const i = state.current;
        const txt = $('apprCommentBody').value.trim();
        const err = $('apprCommentErr'), send = $('apprCommentSend');
        err.hidden = true;
        if (txt.length < 2) { err.textContent = 'Komentar tidak boleh kosong.'; err.hidden = false; return; }
        send.disabled = true;
        try {
            const res = await fetch(`${base}/${i.menu}/${i.id}/komentar`, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify({ body: txt }) });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                const flat = json.errors ? Object.values(json.errors).flat() : [];
                err.textContent = flat[0] || json.message || 'Gagal mengirim komentar.'; err.hidden = false;
                return;
            }
            cm.items.unshift(json.data);
            cm.shown = Math.max(cm.shown, CM_STEP);
            renderComments();
            $('apprCommentBody').value = '';
            i.comments = (i.comments || 0) + 1;
            setCommentCount(i);
            toast('success', 'Komentar terkirim', 'Mahasiswa diberi tahu.');
        } catch (_) {
            err.textContent = 'Tidak bisa terhubung ke server.'; err.hidden = false;
        } finally {
            send.disabled = false;
        }
    });

    // ---- riwayat (linimasa kronologis; entri terakhir ditandai)
    async function loadHistory(i) {
        const list = $('apprHistList');
        list.innerHTML = empty('hourglass_empty', 'Memuat…');
        try {
            const res = await fetch(`${base}/${i.menu}/${i.id}/riwayat`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error();
            if (state.current !== i) return;
            list.innerHTML = json.data.length
                ? json.data.map((l, n) => `<li class="appr-tl-item${n === json.data.length - 1 ? ' is-last' : ''}"><span class="appr-tl-dot"></span>
                    <div class="appr-tl-body"><b>${esc(l.label)}</b>
                    <small>${esc(l.actor)}${l.role ? ' &middot; ' + esc(ROLE[l.role] || l.role) : ''} &middot; ${esc(l.at)}</small>
                    ${l.note ? `<p>${esc(l.note)}</p>` : ''}</div></li>`).join('')
                : empty('history', 'Belum ada riwayat tercatat<br>(data lama sebelum fitur persetujuan).');
            loaded.riwayat = keyOf(i);
        } catch (_) {
            list.innerHTML = empty('error', 'Gagal memuat riwayat.');
        }
    }

    // ------------------------------------------------------------------ awal
    const initial = window.__APPR_INITIAL;
    if (initial) { state.page = initial.meta.page; render(initial); setCounts(initial.counts); }
    else load(1);
})();