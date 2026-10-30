/**
 * Halaman Rekognisi terpadu (resources/views/lppm_rekognisi.blade.php).
 * Data & aturan: window.RK  (dibuat RekognisiHubController: items, meta, people, can, cfg, urls)
 *
 *  - Tabel, filter (Tipe User / Jenis / Skala / Tahun / pencarian) dan paginasi diproses SERVER.
 *  - Modal tambah / ubah berubah mengikuti Tipe User -> Jenis -> Skala:
 *        Mahasiswa : Jurnal, Conference, Rekognisi          Dosen : Jurnal, HKI, Buku, Rekognisi
 *        Alumni    : Rekognisi (+ jabatan)                  Skala (Nasional / Internasional) terpisah dari jenis
 */
(function () {
    'use strict';

    const RK = window.RK;
    if (!RK) return;
    const { cfg, people, can, urls } = RK;

    const $ = (id) => document.getElementById(id);
    const U = SIDA.util;
    const esc = U.esc;
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = (type, message, title) => window.Toast?.show?.({ type, message, title });
    const val = (id) => ($(id) ? $(id).value : '');

    const tableBody = $('rkBody');
    const emptyRow = $('emptyRow');
    const state = { page: RK.meta.page, seq: 0 };

    const TIPE_BADGE = { mahasiswa: 'badge-info', dosen: 'badge-success', alumni: 'badge-warning' };
    const DETAIL = { hak_cipta: 'Hak Cipta', paten: 'Paten', merek: 'Merek', ajar: 'Buku Ajar', referensi: 'Referensi', chapter: 'Chapter' };

    // ------------------------------------------------------------------ tabel
    const safeUrl = (u) => (/^https?:\/\//i.test(u || '') ? u : '');

    function rowHTML(r) {
        const writable = !!(can[r.tipe_user] && can[r.tipe_user][r.jenis]);
        const url = safeUrl(r.bukti);
        const detail = r.detail ? `<span class="rk-detail">${esc(DETAIL[r.detail] || r.detail)}</span>` : '';
        return `
        <tr data-key="${esc(r.key)}">
            <td><div class="student-cell">${U.avatarHtml(r.nama, r.user_id, r.foto)}
                <div class="student-name"><span class="name">${esc(r.nama)}</span><span class="rk-sub">${esc(r.nim_nidn || '')}</span></div></div></td>
            <td class="center"><span class="badge ${TIPE_BADGE[r.tipe_user] || 'badge-neutral'} rk-tipe">${esc(cfg.tipe[r.tipe_user] || r.tipe_user)}</span></td>
            <td>${esc(r.label)}${detail ? '<br>' + detail : ''}</td>
            <td><div class="rk-title">${esc(r.judul || '-')}</div>${r.penulis ? `<span class="rk-sub">${esc(r.penulis)}</span>` : ''}${window.apprChip ? window.apprChip(r) : ''}</td>
            <td class="center">${r.tahun || '-'}</td>
            <td class="center">${url ? `<a href="${esc(url)}" target="_blank" rel="noopener noreferrer" class="evidence-link"><span class="material-symbols-outlined">link</span>Lihat</a>` : '-'}</td>
            <td class="center">${writable ? `<div class="row-actions">
                <button type="button" class="row-action-btn btn-edit-row" title="Ubah" data-key="${esc(r.key)}"><span class="material-symbols-outlined">edit</span></button>
                <button type="button" class="row-action-btn btn-delete-row" title="Hapus" data-key="${esc(r.key)}"><span class="material-symbols-outlined">delete</span></button>
            </div>` : ''}</td>
        </tr>`;
    }

    let rowsByKey = {};
    function renderRows(list) {
        tableBody.querySelectorAll('tr[data-key]').forEach((tr) => tr.remove());
        tableBody.insertAdjacentHTML('afterbegin', list.map(rowHTML).join(''));
        rowsByKey = Object.fromEntries(list.map((r) => [r.key, r]));
    }

    const pager = Pager.create($('pager'), {
        onGo: (n) => { loadPage(n); document.querySelector('.table-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
    });

    function applyMeta(meta, stats) {
        state.page = meta.page;
        emptyRow.hidden = meta.total !== 0;
        $('footerRange').textContent = meta.from + '–' + meta.to;
        $('footerTotalCount').textContent = meta.total;
        pager.render(meta.page, meta.last);
        if (stats) Object.entries(stats).forEach(([k, v]) => { const el = document.querySelector(`[data-stat="${k}"]`); if (el) el.textContent = v; });
    }

    // ------------------------------------------------------------------ muat halaman (filter, cari, paginasi)
    const FILTERS = ['tipe_user', 'jenis', 'skala', 'tahun'];
    const search = $('filter-search');

    async function loadPage(page = state.page) {
        const seq = ++state.seq;
        const params = new URLSearchParams({ page: String(page) });
        FILTERS.forEach((f) => { const v = val('filter-' + f) || 'semua'; if (v !== 'semua') params.set(f, v); });
        const q = search.value.trim();
        if (q) params.set('q', q);

        tableBody.classList.add('is-fetching');
        try {
            const res = await fetch(urls.list + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            if (seq !== state.seq) return;
            renderRows(json.items);
            applyMeta(json.meta, json.stats);
        } catch (e) {
            if (seq !== state.seq) return;
            toast('error', 'Tidak bisa memuat data. Coba lagi.', 'Gagal memuat');
        } finally {
            if (seq === state.seq) tableBody.classList.remove('is-fetching');
        }
    }

    document.querySelectorAll('.filter-card .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(() => loadPage(1), 0)));
    let timer = null;
    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => loadPage(1), 300); });
    $('btn-reset-filter').addEventListener('click', () => {
        search.value = '';
        document.querySelectorAll('.filter-card .dropdown').forEach((dd) => SIDA.dropdown.select(dd, 'semua', 'Semua'));
        loadPage(1);
    });

    // ------------------------------------------------------------------ modal tambah / ubah
    const modalCard = $('rekognisiModal');
    const { open: openBase, close: closeModal } = SIDA.modal.attach({
        backdrop: $('modalBackdrop'), card: modalCard, closeBtn: $('modalCloseBtn'), cancelBtn: $('modalCancelBtn'), dragHandle: $('dragHandle'),
    });
    const dd = (id) => $(id);
    const pick = (wrap, value, placeholder) => SIDA.dropdown.select(wrap, value, placeholder);
    const namaWrap = (tipe) => modalCard.querySelector(`[data-nama="${tipe}"]`);
    let editing = false;

    const showError = (msg) => { $('rkError').textContent = msg || ''; $('rkError').hidden = !msg; };

    /** Skala yang berlaku untuk pilihan sekarang ('' = tidak berlaku). */
    function skalaNow() {
        const tipe = val('f-tipe'), jenis = val('f-jenis');
        if (tipe === 'alumni') return '';
        if (cfg.skalaTetap[jenis]) return cfg.skalaTetap[jenis];
        return cfg.jenisBerskala.includes(jenis) ? val('f-skala') : '';
    }

    /** Tampilkan / sembunyikan isian sesuai Tipe User -> Jenis -> Skala. */
    function sync() {
        const tipe = val('f-tipe');

        modalCard.querySelectorAll('[data-nama]').forEach((w) => { w.hidden = w.dataset.nama !== tipe; });
        $('namaPlaceholder').hidden = !!tipe;
        $('rowJenis').hidden = !tipe;

        if (tipe) {
            const allowed = (cfg.jenisByTipe[tipe] || []).filter((j) => can[tipe] && can[tipe][j]);
            dd('ddJenis').querySelectorAll('.dropdown-option').forEach((o) => { o.hidden = !allowed.includes(o.dataset.value); });
            if (!allowed.includes(val('f-jenis'))) pick(dd('ddJenis'), allowed.length === 1 ? allowed[0] : '', 'Pilih jenis');
        }

        const jenis = val('f-jenis');
        const berskala = !!tipe && tipe !== 'alumni' && cfg.jenisBerskala.includes(jenis) && !cfg.skalaTetap[jenis];
        $('fieldSkala').hidden = !berskala;
        if (!berskala) pick(dd('ddSkala'), '', 'Pilih skala');
        const skala = skalaNow();

        const publ = ['jurnal', 'conference', 'hki', 'buku'].includes(jenis);
        const rek = jenis === 'rekognisi';
        const doiPeringkat = jenis === 'jurnal' || (tipe === 'mahasiswa' && jenis === 'conference');
        const show = {
            judul: publ, penulis: publ, tahun: publ,
            nama_jurnal: jenis === 'jurnal', peringkat: doiPeringkat, link_doi: doiPeringkat,
            jenis_hki: jenis === 'hki', kategori_buku: jenis === 'buku',
            mitra: rek, jabatan: rek && tipe === 'alumni', tanggal_mulai: rek, tanggal_selesai: rek,
            bukti_kegiatan: !!jenis, bukti_tambahan: rek,
        };
        modalCard.querySelectorAll('[data-show]').forEach((el) => { el.hidden = !show[el.dataset.show]; });
        modalCard.querySelectorAll('[data-show-row]').forEach((row) => { row.hidden = !row.dataset.showRow.split(' ').some((k) => show[k]); });

        $('lblPeringkat').textContent = cfg.labelPeringkat[skala] || 'Peringkat';
        $('f-peringkat').placeholder = skala === 'nasional' ? 'Contoh: S2' : skala === 'internasional' ? 'Contoh: Q2' : 'Contoh: Q2 atau S3';
        $('lblMitra').textContent = tipe === 'alumni' ? 'Tempat Bekerja / Instansi' : 'Mitra / Instansi';
    }

    // Setelah memilih tipe: kalau hanya ada satu nama (mis. akun "hanya data sendiri"), pilih otomatis
    function autoPickName() {
        const tipe = val('f-tipe');
        if (!tipe || editing) return;
        const list = people[tipe] || [];
        if (list.length === 1 && !val('f-user-' + tipe)) pick(namaWrap(tipe), String(list[0].id), 'Pilih');
    }

    modalCard.querySelectorAll('.dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(() => { sync(); autoPickName(); }, 0)));

    const TEXT_FIELDS = ['judul', 'penulis', 'nama_jurnal', 'peringkat', 'tahun', 'link_doi', 'mitra', 'jabatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan', 'bukti_tambahan'];

    function resetForm() {
        $('f-key').value = '';
        TEXT_FIELDS.forEach((k) => { $('f-' + k).value = ''; });
        pick(dd('ddTipe'), '', 'Pilih tipe user');
        ['mahasiswa', 'dosen', 'alumni'].forEach((t) => pick(namaWrap(t), '', 'Pilih ' + (cfg.tipe[t] || t).toLowerCase()));
        pick(dd('ddJenis'), '', 'Pilih jenis');
        pick(dd('ddSkala'), '', 'Pilih skala');
        modalCard.querySelectorAll('.field.is-locked').forEach((f) => f.classList.remove('is-locked'));
        showError('');
        // hanya tipe user yang punya izin tambah yang ditawarkan
        dd('ddTipe').querySelectorAll('.dropdown-option').forEach((o) => { o.hidden = !Object.values(can[o.dataset.value] || {}).some(Boolean); });
    }

    function openCreate() {
        editing = false;
        resetForm();
        $('modalTitle').textContent = 'Tambah Rekognisi';
        $('modalSubtitle').textContent = 'Pilih tipe user dan nama, isian menyesuaikan.';
        const tipes = Array.from(dd('ddTipe').querySelectorAll('.dropdown-option')).filter((o) => !o.hidden);
        if (tipes.length === 1) pick(dd('ddTipe'), tipes[0].dataset.value, 'Pilih tipe user');
        sync();
        autoPickName();
        openBase();
    }

    async function openEdit(key) {
        try {
            const res = await fetch(urls.item + '/' + key, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error();
            const d = (await res.json()).data;

            editing = true;
            resetForm();
            $('f-key').value = d.key;
            pick(dd('ddTipe'), d.tipe_user, 'Pilih tipe user');
            sync();
            pick(namaWrap(d.tipe_user), String(d.user_id), 'Pilih');
            pick(dd('ddJenis'), d.jenis, 'Pilih jenis');
            sync();
            if (d.skala && !cfg.skalaTetap[d.jenis]) pick(dd('ddSkala'), d.skala, 'Pilih skala');
            sync();
            TEXT_FIELDS.forEach((k) => { $('f-' + k).value = d[k] ?? ''; });
            if (d.jenis_hki) pick($('f-jenis_hki').closest('.dropdown'), d.jenis_hki, 'Hak Cipta');
            if (d.kategori_buku) pick($('f-kategori_buku').closest('.dropdown'), d.kategori_buku, 'Buku Ajar');
            // Pemilik tidak bisa diganti saat ubah
            dd('ddTipe').closest('.field').classList.add('is-locked');
            namaWrap(d.tipe_user).closest('.field').classList.add('is-locked');

            $('modalTitle').textContent = 'Ubah Rekognisi';
            $('modalSubtitle').textContent = 'Perbarui data lalu simpan.';
            openBase();
        } catch (e) {
            toast('error', 'Data tidak bisa dibuka. Coba lagi.', 'Gagal');
        }
    }

    function payload() {
        const tipe = val('f-tipe');
        const p = { tipe_user: tipe, user_id: val('f-user-' + tipe), jenis: val('f-jenis'), skala: skalaNow() || null };
        TEXT_FIELDS.forEach((k) => { p[k] = $('f-' + k).value.trim(); });
        p.jenis_hki = val('f-jenis_hki');
        p.kategori_buku = val('f-kategori_buku');
        return p;
    }

    $('rkForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        showError('');
        const p = payload();
        if (!p.tipe_user) return showError('Pilih tipe user terlebih dahulu.');
        if (!p.user_id) return showError('Pilih nama terlebih dahulu.');
        if (!p.jenis) return showError('Pilih jenis terlebih dahulu.');
        if (cfg.jenisBerskala.includes(p.jenis) && p.tipe_user !== 'alumni' && !cfg.skalaTetap[p.jenis] && !p.skala) return showError('Pilih skala (Nasional / Internasional).');

        const key = val('f-key');
        const btn = $('rkSubmit');
        btn.disabled = true;
        try {
            const res = await fetch(key ? urls.row + '/' + key : urls.store, {
                method: key ? 'PUT' : 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(p),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const first = json.errors ? Object.values(json.errors)[0][0] : null;
                throw new Error(first || json.message || 'Gagal menyimpan (' + res.status + ').');
            }
            closeModal();
            toast('success', key ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');
            loadPage(key ? state.page : 1);
        } catch (err) {
            showError(err.message);
        } finally {
            btn.disabled = false;
        }
    });

    $('btnTambah')?.addEventListener('click', openCreate);

    // ------------------------------------------------------------------ aksi baris
    tableBody.addEventListener('click', async (e) => {
        const edit = e.target.closest('.btn-edit-row');
        if (edit) return openEdit(edit.dataset.key);

        const del = e.target.closest('.btn-delete-row');
        if (!del) return;
        const r = rowsByKey[del.dataset.key];
        if (!(await DeleteConfirm.ask({ name: (r && (r.judul || r.nama)) || 'data ini', entity: 'data' }))) return;
        try {
            const res = await fetch(urls.row + '/' + del.dataset.key, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'Gagal menghapus.');
            toast('success', 'Data berhasil dihapus.');
            loadPage(state.page);
        } catch (err) {
            toast('error', err.message, 'Gagal menghapus');
        }
    });

    // ------------------------------------------------------------------ awal
    // Filter awal dari alamat (mis. tautan lama /lppm/mahasiswa -> ?tipe_user=mahasiswa): cerminkan di dropdown
    Object.entries(RK.filters || {}).forEach(([k, v]) => {
        if (v === null || v === undefined) return;
        if (k === 'q') { search.value = v; return; }
        const hid = $('filter-' + k);
        if (hid) SIDA.dropdown.select(hid.closest('.dropdown'), String(v), 'Semua');
    });
    renderRows(RK.items);
    applyMeta(RK.meta, null);
    sync();
})();
