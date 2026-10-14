/**
 * Halaman Profil — (1) ikon cat: ganti warna aksen SEMENTARA, (2) tombol pengaturan aside,
 * (3) riwayat gabungan berbentuk garis waktu (filter kategori/tahun + tombol halaman).
 * Data riwayat dikirim server lewat window.PROFIL = { items: [...], perPage: 8 } (lihat profil.blade.php).
 * Tombol halaman memakai public/js/pager.js.
 */
(function () {
    'use strict';

    const data = window.PROFIL || { items: [], perPage: 8 };
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ---------- 1. Ikon cat: warna sementara (hilang saat halaman dimuat ulang) ----------
    // Nilai warna ada di profile.css ("DAFTAR WARNA"). Tambah tema: tambah nama di sini + satu blok [data-pal] di CSS.
    const PALETTES = ['indigo', 'zamrud', 'senja', 'mawar', 'samudra'];   // 'indigo' = bawaan tema (tanpa data-pal)
    const page = $('pageWrap');
    const palBtn = $('pfPaletteBtn');
    const swatches = $('pfSwatches');

    function applyPalette(name) {
        if (name === PALETTES[0]) page.removeAttribute('data-pal'); else page.setAttribute('data-pal', name);
        swatches.querySelectorAll('.pf-swatch').forEach((s) => s.classList.toggle('is-on', s.dataset.pal === name));
    }

    if (page && palBtn && swatches) {
        swatches.innerHTML = PALETTES.map((n, i) =>
            `<button type="button" class="pf-swatch${i === 0 ? ' is-on' : ''}" data-pal="${n}" title="${n[0].toUpperCase() + n.slice(1)}" aria-label="Warna ${n}"></button>`
        ).join('');
        palBtn.addEventListener('click', () => {
            swatches.hidden = !swatches.hidden;
            palBtn.setAttribute('aria-expanded', String(!swatches.hidden));
        });
        swatches.addEventListener('click', (e) => {
            const b = e.target.closest('.pf-swatch');
            if (b) applyPalette(b.dataset.pal);
        });
    }

    // ---------- 2. Aside pengaturan: teruskan klik ke tombol bawaan menu profil di header ----------
    document.querySelectorAll('[data-pf-click]').forEach((btn) => btn.addEventListener('click', () => {
        // Informasi Akses tampil di dalam dropdown header, jadi dropdown dibuka dulu.
        if (btn.dataset.pfOpen && btn.dataset.pfClick === 'btnShowAccessInfo') $('profileToggleBtn')?.click();
        $(btn.dataset.pfClick)?.click();
    }));

    // ---------- 3. Riwayat: garis waktu + filter + halaman ----------
    const list = $('pfList');
    if (!list) return;
    const selKat = $('pfKat');
    const selTahun = $('pfTahun');
    const range = $('pfRange');
    const total = $('pfTotal');
    const pager = window.Pager ? Pager.create($('pager'), { onGo: (n) => render(n) }) : { render() {} };

    function filtered() {
        const kat = selKat?.value || 'semua';
        const tahun = selTahun?.value || 'semua';
        return data.items.filter((i) => (kat === 'semua' || i.kat === kat) && (tahun === 'semua' || String(i.tahun) === tahun));
    }

    function item(i) {
        const edit = i.url
            ? `<a class="pf-btn" href="${esc(i.url)}" title="Buka menu ${esc(i.label)}"><span class="material-symbols-outlined">edit</span>Edit</a>`
            : '';
        const bukti = i.bukti
            ? `<a class="pf-btn" href="${esc(i.bukti)}" target="_blank" rel="noopener noreferrer"><span class="material-symbols-outlined">open_in_new</span>Bukti</a>`
            : '';
        return `<div class="pf-tl-item">
            <div class="pf-tl-ico"><span class="material-symbols-outlined">${esc(i.icon)}</span></div>
            <div class="pf-tl-main">
                <p class="pf-tl-title">${esc(i.judul)}</p>
                <p class="pf-tl-sub">${esc(i.label)}${i.sub ? ' · ' + esc(i.sub) : ''}</p>
            </div>
            <div class="pf-actions">${bukti}${edit}</div>
        </div>`;
    }

    const skeleton = () => '<div class="pf-sk-item"><span class="pf-sk pf-sk-ico"></span><span class="pf-sk-lines"><span class="pf-sk"></span><span class="pf-sk"></span></span></div>'.repeat(3);
    let busy = 0;

    function draw(pageNo) {
        const rows = filtered();
        const last = Math.max(1, Math.ceil(rows.length / data.perPage));
        pageNo = Math.min(Math.max(1, pageNo), last);
        const from = (pageNo - 1) * data.perPage;
        const slice = rows.slice(from, from + data.perPage);

        let prev;
        const html = slice.map((i) => {
            const head = i.tahun !== prev ? `<div class="pf-yr">${i.tahun ? esc(i.tahun) : 'Tanpa tahun'}</div>` : '';
            prev = i.tahun;
            return head + item(i);
        }).join('');

        list.innerHTML = slice.length
            ? `<div class="pf-tl">${html}</div>`
            : '<div class="pf-empty">Belum ada data yang cocok dengan filter.</div>';
        if (range) range.textContent = rows.length ? `${from + 1}–${from + slice.length}` : '0';
        if (total) total.textContent = rows.length;
        pager.render(pageNo, last);
    }

    // Skeleton sebentar sebagai umpan balik saat ganti filter/halaman; galat apa pun ditampilkan, bukan dibiarkan kosong.
    function render(pageNo = 1, instant = false) {
        const token = ++busy;
        list.setAttribute('aria-busy', 'true');
        if (!instant) list.innerHTML = skeleton();
        setTimeout(() => {
            if (token !== busy) return;
            try {
                draw(pageNo);
            } catch (err) {
                console.error('[Profil] Gagal menampilkan riwayat:', err);
                list.innerHTML = '<div class="pf-empty">Riwayat gagal ditampilkan (' + esc(err.message) + '). Muat ulang halaman.</div>';
            }
            list.setAttribute('aria-busy', 'false');
        }, instant ? 0 : 180);
    }

    // Dropdown kustom SIDA: script.js mengisi input tersembunyi saat opsi diklik, jadi nilainya dibaca setelahnya.
    document.querySelectorAll('.pf-dd .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(() => render(1), 0)));
    render(1, true);

    // ---------- 4. Notifikasi: klik membuka popup ----------
    const dlg = $('pfNotifDialog');
    if (dlg && typeof dlg.showModal === 'function') {
        document.querySelectorAll('.pf-note-item').forEach((btn) => btn.addEventListener('click', () => {
            $('pfNotifTitle').textContent = btn.dataset.judul || 'Notifikasi';
            $('pfNotifTime').textContent = btn.dataset.waktu || '';
            $('pfNotifBody').textContent = btn.dataset.pesan || 'Tidak ada detail tambahan untuk notifikasi ini.';
            $('pfNotifState').textContent = btn.dataset.baca === '1' ? 'Sudah dibaca' : 'Belum dibaca';
            dlg.showModal();
        }));
        ['pfNotifClose', 'pfNotifOk'].forEach((id) => $(id)?.addEventListener('click', () => dlg.close()));
        dlg.addEventListener('click', (e) => { if (e.target === dlg) dlg.close(); });   // klik area gelap = tutup
    }
})();
