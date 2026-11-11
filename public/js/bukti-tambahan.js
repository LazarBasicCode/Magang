/**
 * "Bukti Tambahan" dinamis (Rekognisi, Kemahasiswaan, Kerja Sama).
 * Awalnya kosong: hanya tombol "+". Setiap klik "+" memunculkan satu blok:
 *   Link Bukti Tambahan N
 *   [ link ........................... ]
 *   [ Nama bukti (opsional) .... ] [ − ]
 * Tombol − menghapus blok itu; nomor judul disusun ulang. Nama bukti tampil di modal verifikasi
 * supaya jelas itu bukti apa.
 *
 *   const bt = BuktiTambahan.mount(document.getElementById('buktiTambahanList'));
 *   bt.set([{ nama: 'Sertifikat', url: 'https://...' }]);  // saat edit (juga menerima link biasa / JSON string lama)
 *   bt.get();                                               // [{ nama, url }, ...] hanya yang link-nya terisi
 */
(function (window) {
    'use strict';

    const MAX = 10;
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function item(v) {
        if (typeof v === 'string') return v.trim() ? { nama: '', url: v.trim() } : null;
        if (v && typeof v === 'object' && String(v.url || '').trim()) {
            return { nama: String(v.nama || '').trim(), url: String(v.url).trim() };
        }
        return null;
    }

    /** Terima array (link biasa / {nama,url}), JSON string, atau satu link lama -> [{nama,url}]. */
    function normalize(v) {
        if (Array.isArray(v)) return v.map(item).filter(Boolean);
        if (typeof v !== 'string' || !v.trim()) return [];
        try {
            const parsed = JSON.parse(v);
            if (Array.isArray(parsed)) return normalize(parsed);
        } catch (_) { /* bukan JSON: link tunggal lama */ }
        return [{ nama: '', url: v.trim() }];
    }

    function mount(root) {
        if (!root) return null;
        root.classList.add('bt-list');
        root.innerHTML = '<button type="button" class="bt-add bt-initial-add"><span class="material-symbols-outlined">add</span><span>Tambah Bukti</span></button>'
            + '<div class="bt-rows"></div>';
        const rows = root.querySelector('.bt-rows');
        const addBtn = root.querySelector('.bt-add');

        // Judul tiap blok dinomori ulang (1, 2, 3, ...) setiap ada tambah / hapus.
        const sync = () => {
            [...rows.children].forEach((row, i) => {
                row.querySelector('.field-label').textContent = 'Link Bukti Tambahan ' + (i + 1);
                row.querySelector('.bt-row-add').hidden = rows.children.length >= MAX;
            });
            addBtn.hidden = rows.children.length > 0 || rows.children.length >= MAX;
        };
        function addRow(b = { nama: '', url: '' }, focus = false) {
            if (rows.children.length >= MAX) return;
            const row = document.createElement('div');
            row.className = 'bt-row';
            row.innerHTML = '<div class="bt-row-heading"><label class="field-label"></label>'
                + '<div class="bt-actions">'
                + '<button type="button" class="bt-add bt-row-add" title="Tambah bukti" aria-label="Tambah bukti">+</button>'
                + '<button type="button" class="bt-remove" title="Hapus bukti ini" aria-label="Hapus bukti ini">-</button>'
                + '</div></div>'
                + '<div class="field-control"><input type="url" class="bt-link" required placeholder="https://drive.google.com/..." value="' + esc(b.url) + '"></div>'
                + '<div class="field-control bt-name"><input type="text" class="bt-nama" maxlength="100" placeholder="Nama bukti (opsional)" aria-label="Nama bukti (opsional)" value="' + esc(b.nama) + '"></div>';
            rows.appendChild(row);
            if (focus) row.querySelector('.bt-link').focus();
            sync();
        }

        root.addEventListener('click', (e) => {
            const btn = e.target.closest('.bt-add, .bt-remove');
            if (!btn || !root.contains(btn)) return;
            if (btn.classList.contains('bt-add')) {
                addRow(undefined, true);
                return;
            }
            btn.closest('.bt-row').remove(); // bukti tambahan bersifat opsional
            sync();
        });

        return {
            set(v) {
                rows.innerHTML = '';
                normalize(v).forEach((b) => addRow(b));
                sync();
            },
            get() {
                return [...rows.querySelectorAll('.bt-row')]
                    .map((r) => ({ nama: r.querySelector('.bt-nama').value.trim(), url: r.querySelector('.bt-link').value.trim() }))
                    .filter((b) => b.url);
            },
            normalize,
        };
    }

    window.BuktiTambahan = { mount, normalize };
})(window);
