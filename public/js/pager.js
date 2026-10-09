/**
 * Tombol halaman (paginasi) — dipakai ulang di halaman manapun.
 *
 *   const pager = Pager.create(document.getElementById('pager'), { onGo: (n) => muatHalaman(n) });
 *   pager.render(halamanSekarang, halamanTerakhir);
 *
 * Contoh untuk 8 halaman (angka 1 dan 8 selalu ada = langsung loncat ke awal / akhir):
 *   hal 1   :            1 2 3 .. 8  [Lanjut]
 *   hal 2   : [Kembali]  1 2 3 4 .. 8  [Lanjut]
 *   hal 3   : [Kembali]  1 2 3 4 5 .. 8  [Lanjut]
 *   hal 4   : [Kembali]  1 .. 3 4 5 .. 8  [Lanjut]
 *   hal 5   : [Kembali]  1 .. 4 5 6 .. 8  [Lanjut]
 *   hal 6   : [Kembali]  1 .. 4 5 6 7 8  [Lanjut]
 *   hal 8   : [Kembali]  1 .. 6 7 8  [Lanjut nonaktif]
 * - Tombol Kembali hanya dari halaman 2 ke atas; Lanjut nonaktif di halaman terakhir.
 * - Titik-titik ".." hanya penanda, tidak bisa diklik.
 * - Kalau hanya ada 1 halaman (data <= jumlah per halaman), seluruh pager disembunyikan.
 * Gaya: public/css/pager.css
 */
(function (window) {
    'use strict';

    const EDGE = 3; // sampai halaman ini dianggap "wilayah awal" (dan simetris untuk akhir)

    function create(el, { onGo }) {
        if (!el) return { render() {} };

        el.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-page]');
            if (!btn || btn.disabled) return;
            onGo(Number(btn.dataset.page));
        });

        function button(label, page, { cls = '', disabled = false, current = false, title = '' } = {}) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'pager-btn ' + cls;
            b.textContent = label;
            b.dataset.page = String(page);
            if (title) { b.title = title; b.setAttribute('aria-label', title); }
            if (disabled) b.disabled = true;
            if (current) { b.classList.add('is-current'); b.setAttribute('aria-current', 'page'); }
            return b;
        }

        function render(page, last) {
            el.replaceChildren();
            el.hidden = last <= 1;
            if (last <= 1) return;

            // Tombol Kembali: hanya dari halaman 2 ke atas
            if (page > 1) {
                el.append(button('‹ Kembali', page - 1, { cls: 'pager-nav', title: 'Halaman sebelumnya' }));
            }

            // Halaman yang ditampilkan: selalu 1 & halaman terakhir, ditambah jendela di sekitar halaman sekarang.
            //  - awal  (hal <= 3)        : 1 .. hal+2
            //  - akhir (hal >= last - 2) : hal-2 .. last
            //  - tengah                  : hal-1 .. hal+1
            let from; let to;
            if (page <= EDGE) { from = 1; to = Math.min(page + 2, last); }
            else if (page >= last - EDGE + 1) { from = Math.max(page - 2, 1); to = last; }
            else { from = page - 1; to = page + 1; }

            const shown = new Set([1, last]);
            for (let n = from; n <= to; n++) shown.add(n);
            const pages = Array.from(shown).sort((x, y) => x - y);

            pages.forEach((n, i) => {
                // Ada halaman yang dilewati di antara dua angka -> titik-titik (tidak bisa diklik)
                if (i > 0 && n - pages[i - 1] > 1) {
                    const gap = document.createElement('span');
                    gap.className = 'pager-gap';
                    gap.setAttribute('aria-hidden', 'true');
                    gap.textContent = '…';
                    el.append(gap);
                }
                el.append(button(String(n), n, { current: n === page, title: 'Halaman ' + n }));
            });

            // Tombol Lanjut: nonaktif di halaman terakhir
            el.append(button('Lanjut ›', Math.min(page + 1, last), {
                cls: 'pager-nav', disabled: page >= last, title: 'Halaman berikutnya',
            }));
        }

        return { render };
    }

    window.Pager = { create };
})(window);
