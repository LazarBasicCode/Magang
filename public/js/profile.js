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
    const PALETTES = [
        { name: 'Indigo',  top: null },   // bawaan tema
        { name: 'Zamrud',  top: '#10b981', teal: 'rgba(132,204,22,.85)',  primary: '#0f9d6e', dark: '#0b7d57' },
        { name: 'Senja',   top: '#f97316', teal: 'rgba(236,72,153,.85)',  primary: '#ea580c', dark: '#c2410c' },
        { name: 'Mawar',   top: '#e11d48', teal: 'rgba(244,114,182,.85)', primary: '#e11d48', dark: '#be123c' },
        { name: 'Samudra', top: '#0ea5e9', teal: 'rgba(99,102,241,.85)',  primary: '#0284c7', dark: '#0369a1' },
    ];
    const page = $('pageWrap');
    const palBtn = $('pfPaletteBtn');
    const swatches = $('pfSwatches');

    function applyPalette(i) {
        const p = PALETTES[i];
        ['--pf-top', '--pf-teal', '--primary', '--primary-dark'].forEach((k) => page.style.removeProperty(k));
        if (p.top) {
            page.style.setProperty('--pf-top', p.top);
            page.style.setProperty('--pf-teal', p.teal);
            page.style.setProperty('--primary', p.primary);
            page.style.setProperty('--primary-dark', p.dark);
        }
        swatches.querySelectorAll('.pf-swatch').forEach((s, n) => s.classList.toggle('is-on', n === i));
    }

    if (page && palBtn && swatches) {
        const base = getComputedStyle(page).getPropertyValue('--primary').trim() || '#5b5fef';
        swatches.innerHTML = PALETTES.map((p, i) =>
            `<button type="button" class="pf-swatch${i === 0 ? ' is-on' : ''}" data-i="${i}" style="--sw:${p.top || base}" title="${p.name}" aria-label="Warna ${p.name}"></button>`
        ).join('');
        palBtn.addEventListener('click', () => {
            swatches.hidden = !swatches.hidden;
            palBtn.setAttribute('aria-expanded', String(!swatches.hidden));
        });
        swatches.addEventListener('click', (e) => {
            const b = e.target.closest('.pf-swatch');
            if (b) applyPalette(Number(b.dataset.i));
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

    function render(pageNo = 1) {
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

    // Dropdown kustom SIDA: script.js mengisi input tersembunyi saat opsi diklik, jadi nilainya dibaca setelahnya.
    document.querySelectorAll('.pf-dd .dropdown-option').forEach((o) => o.addEventListener('click', () => setTimeout(() => render(1), 0)));
    render(1);
})();
