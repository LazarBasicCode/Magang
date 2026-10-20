/**
 * Tampilan MAHASISWA (Prestasi Saya / Publikasi Saya) — logika bersama.
 *
 * Tiap halaman (resources/views/mahasiswa/*.blade.php) cukup memanggil:
 *   MhsView.init({ endpoint, initial, titleOf, metaOf, detailRows, linksOf, fillForm, readForm, emptyText })
 * dan menyediakan markup standar (id berawalan "mv"). Selebihnya — daftar, filter status,
 * pencarian, progres verifikasi, modal detail, simpan & hapus — dikerjakan di sini.
 * Bergantung pada: script.js (SIDA), pager.js, delete-confirm.js.
 */
(function (window) {
    'use strict';

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const $ = (id) => document.getElementById(id);

    // Langkah verifikasi yang ditampilkan ke mahasiswa: Diajukan -> Prodi -> Terverifikasi.
    const STEPS = ['Diajukan', 'Prodi', 'Terverifikasi'];

    // status server -> { states tiap langkah, tone teks }
    function progressOf(status) {
        switch (status) {
            case 'diajukan':       return { states: ['current', 'todo', 'todo'], tone: 'wait' };
            case 'menunggu_prodi': return { states: ['done', 'current', 'todo'], tone: 'wait' };
            case 'ditolak':        return { states: ['done', 'done', 'reject'], tone: 'bad' };
            default:               return { states: ['done', 'done', 'done'], tone: 'ok' };
        }
    }

    // Kolom status di tabel: terverifikasi / ditolak = ikon + teks saja; diproses = titik progres.
    function miniProgress(item) {
        const st = item.approval_status || 'disetujui';
        if (st === 'disetujui') {
            return '<span class="mv-state is-ok"><span class="material-symbols-outlined">check_circle</span>Terverifikasi</span>';
        }
        if (st === 'ditolak') {
            return '<span class="mv-state is-bad"><span class="material-symbols-outlined">cancel</span>Ditolak</span>';
        }
        const p = progressOf(st);
        const dots = p.states.map((s) => `<span class="mv-dot ${s === 'done' ? 'is-done' : s === 'current' ? 'is-current' : ''}"></span>`).join('');
        return `<div class="mv-prog-mini"><div class="mv-dots">${dots}</div><span class="mv-prog-text is-${p.tone}">${esc(item.approval_label)}</span></div>`;
    }

    function fullProgress(item) {
        const p = progressOf(item.approval_status);
        const icon = { done: 'check', current: 'hourglass_top', reject: 'close', todo: 'more_horiz' };
        const steps = p.states.map((s, i) => {
            const label = s === 'reject' ? 'Ditolak' : STEPS[i];
            return `<div class="mv-step is-${s}"><div class="mv-step-circle"><span class="material-symbols-outlined">${icon[s]}</span></div>${label}</div>`;
        }).join('');
        const note = item.approval_status === 'ditolak' && item.approval_note
            ? `<div class="mv-reject-note"><strong>Alasan:</strong> ${esc(item.approval_note)}</div>` : '';
        return `<div class="mv-status-box"><p class="mv-status-title is-${p.tone}">${esc(item.approval_label)}</p><div class="mv-steps">${steps}</div>${note}</div>`;
    }

    function init(cfg) {
        const csrf = SIDA.util.csrfToken();
        const items = new Map();
        const state = { page: 1, status: 'semua', term: '', seq: 0, current: null };

        const list = $('mvList');
        const empty = $('mvEmpty');
        const tableWrap = $('mvTableWrap');
        const footer = $('mvFooter');
        const search = $('mvSearch');

        // ---------- Modal (mekanisme dari script.js) ----------
        const detailModal = SIDA.modal.attach({
            backdrop: $('mvDetailBackdrop'), card: $('mvDetailModal'),
            closeBtn: $('mvDetailClose'), cancelBtn: $('mvDetailCancel'), dragHandle: null,
        });
        const formModal = SIDA.modal.attach({
            backdrop: $('mvFormBackdrop'), card: $('mvFormModal'),
            closeBtn: $('mvFormClose'), cancelBtn: $('mvFormCancel'), dragHandle: $('mvFormDrag'),
        });

        // ---------- Daftar ----------
        function rowHTML(item) {
            return `<tr class="mv-row" data-id="${item.id}">
                <td><div class="mv-title">${esc(cfg.titleOf(item))}</div><div class="mv-meta">${esc(cfg.metaOf(item))}</div></td>
                <td>${miniProgress(item)}</td>
                <td class="center"><button type="button" class="mv-more" data-id="${item.id}" title="Lihat detail" aria-label="Lihat detail"><span class="material-symbols-outlined">more_vert</span></button></td>
            </tr>`;
        }

        function renderSummary(summary) {
            if (!summary) return;
            Object.keys(summary).forEach((k) => {
                const el = document.querySelector(`[data-count="${k}"]`);
                if (el) el.textContent = summary[k];
            });
        }

        function render(data) {
            items.clear();
            data.items.forEach((i) => items.set(String(i.id), i));
            list.innerHTML = data.items.map(rowHTML).join('');
            const none = data.meta.total === 0;
            tableWrap.hidden = none;
            empty.hidden = !none;
            if (none) {
                const filtered = state.status !== 'semua' || state.term !== '';
                $('mvEmptyTitle').textContent = filtered ? 'Tidak ada data yang cocok' : cfg.emptyTitle;
                $('mvEmptyDesc').textContent = filtered ? 'Coba ubah kata kunci atau pilih status lain.' : cfg.emptyDesc;
            }
            footer.textContent = none ? '' : `Menampilkan ${data.meta.from}–${data.meta.to} dari ${data.meta.total} data`;
            pager.render(data.meta.page, data.meta.last);
            state.page = data.meta.page;
            renderSummary(data.summary);
        }

        const pager = Pager.create($('mvPager'), { onGo: (n) => load(n) });

        async function load(page = state.page) {
            const seq = ++state.seq;
            const params = new URLSearchParams({ page: String(page) });
            if (state.status !== 'semua') params.set('status', state.status);
            if (state.term) params.set('q', state.term);
            tableWrap.classList.add('is-fetching');
            try {
                const res = await fetch(cfg.endpoint + '?' + params, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (seq === state.seq) render(data);
            } catch (e) {
                if (seq === state.seq) window.Toast?.show?.({ type: 'error', title: 'Gagal memuat data', message: 'Coba lagi sebentar.' });
            } finally {
                if (seq === state.seq) tableWrap.classList.remove('is-fetching');
            }
        }

        // Kartu ringkasan = filter status
        document.querySelectorAll('.mv-tile').forEach((tile) => tile.addEventListener('click', () => {
            document.querySelectorAll('.mv-tile').forEach((t) => t.classList.toggle('is-active', t === tile));
            state.status = tile.dataset.status;
            load(1);
        }));
        let timer = null;
        search?.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => { state.term = search.value.trim(); load(1); }, 300);
        });

        // ---------- Detail ----------
        function openDetail(id) {
            const item = items.get(String(id));
            if (!item) return;
            state.current = item;
            const rows = cfg.detailRows(item).filter(([, v]) => v !== null && v !== undefined && v !== '');
            const links = (cfg.linksOf(item) || []).filter((l) => l.url);
            const hint = item.approval_status === 'ditolak'
                ? 'Perbaiki data lalu simpan — pengajuan akan dikirim ulang untuk diverifikasi.'
                : 'Mengubah data akan mengirim ulang pengajuan untuk diverifikasi.';

            $('mvDetailBody').innerHTML = `
                ${fullProgress(item)}
                <h4 class="mv-detail-name">${esc(cfg.titleOf(item))}</h4>
                <dl class="mv-dl">${rows.map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`).join('')}</dl>
                ${links.length ? `<div class="mv-links">${links.map((l) => `<a class="mv-link-btn" href="${esc(l.url)}" target="_blank" rel="noopener noreferrer"><span class="material-symbols-outlined">${l.icon || 'link'}</span>${esc(l.label)}</a>`).join('')}</div>` : ''}
                <p class="mv-hint">${hint}</p>`;
            $('mvDetailEditLabel').textContent = item.approval_status === 'ditolak' ? 'Perbaiki' : 'Edit';
            detailModal.open();
            loadComments(item);
        }

        // Komentar dari Prodi (hanya baca). Perlu cfg.approvalMenu = slug menu di Approval::menus().
        async function loadComments(item) {
            if (!cfg.approvalMenu) return;
            try {
                const res = await fetch(`/persetujuan/${cfg.approvalMenu}/${item.id}/komentar`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (!res.ok || !json.success || !json.data.length || state.current !== item) return;
                $('mvDetailBody').insertAdjacentHTML('beforeend', `
                    <div class="mv-comments">
                        <h5 class="mv-comments-title"><span class="material-symbols-outlined">chat</span>Komentar Prodi (${json.data.length})</h5>
                        ${json.data.map((c) => `<div class="mv-comment"><div class="mv-comment-meta"><b>${esc(c.author)}</b><time>${esc(c.at)}</time></div><p>${esc(c.body)}</p></div>`).join('')}
                    </div>`);
            } catch (_) { /* komentar bersifat tambahan; abaikan bila gagal */ }
        }

        list.addEventListener('click', (e) => {
            const row = e.target.closest('tr[data-id]');
            if (row) openDetail(row.dataset.id);
        });

        $('mvDetailEdit').addEventListener('click', () => {
            detailModal.close();
            openForm(state.current);
        });

        $('mvDetailDelete').addEventListener('click', async () => {
            const item = state.current;
            if (!item) return;
            detailModal.close();
            const ok = await DeleteConfirm.ask({ name: cfg.titleOf(item), entity: 'data' });
            if (!ok) return;
            try {
                const res = await fetch(`${cfg.endpoint}/${item.id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const result = await res.json();
                if (res.ok && result.success) load(state.page);
                else window.Toast?.show?.({ type: 'error', title: 'Gagal menghapus', message: result.message || 'Coba lagi.' });
            } catch (e) {
                window.Toast?.show?.({ type: 'error', title: 'Gagal menghapus', message: 'Tidak bisa terhubung ke server.' });
            }
        });

        // ---------- Form tambah / edit ----------
        function openForm(item) {
            $('mvForm').reset();
            $('mvFormError').hidden = true;
            $('mvFormId').value = item ? item.id : '';
            $('mvFormTitle').textContent = item ? cfg.editTitle : cfg.addTitle;
            cfg.fillForm(item);
            formModal.open();
        }
        $('mvAdd')?.addEventListener('click', () => openForm(null));

        $('mvForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const err = $('mvFormError');
            const btn = $('mvFormSubmit');
            err.hidden = true;
            btn.disabled = true;
            const id = $('mvFormId').value;
            try {
                const res = await fetch(id ? `${cfg.endpoint}/${id}` : cfg.endpoint, {
                    method: id ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(cfg.readForm()),
                });
                const result = await res.json();
                if (!res.ok) {
                    err.textContent = result.errors ? Object.values(result.errors)[0][0] : (result.message || 'Terjadi kesalahan, coba lagi.');
                    err.hidden = false;
                    return;
                }
                formModal.close();
                window.Toast?.show?.({ type: 'success', title: id ? 'Perubahan disimpan' : 'Berhasil dikirim', message: 'Pengajuanmu masuk antrean verifikasi.' });
                await load(id ? state.page : 1);
            } catch (e2) {
                err.textContent = 'Gagal terhubung ke server.';
                err.hidden = false;
            } finally {
                btn.disabled = false;
            }
        });

        // Render awal dari data yang sudah dikirim server (tanpa fetch ulang)
        render(cfg.initial);
    }

    window.MhsView = { init };
})(window);
