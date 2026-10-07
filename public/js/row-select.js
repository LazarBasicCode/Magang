/**
 * =====================================================================
 * PILIH BANYAK BARIS TABEL  (Export Excel / Cetak PDF / Batal pilih)
 * Dipakai ulang di semua halaman — tidak ada logika khusus menu di sini.
 *
 * Aktif pada <table data-selectable="slug">. Atribut lain:
 *   data-select-export  URL POST export data terpilih   (route pilihan.export)
 *   data-select-print   URL POST isi laporan terpilih    (route pilihan.cetak)
 *   data-select-delete  URL POST hapus data terpilih     (route pilihan.hapus; tanpa atribut ini tombol Hapus disembunyikan)
 *   data-select-update  URL POST update massal terpilih  (route pilihan.ubah; tanpa atribut ini tombol Update disembunyikan;
 *                       modal & logika: partials/bulk-update-modal.blade.php + js/bulk-update.js)
 * Markup bar aksi & cara pasang: resources/views/partials/row-select-bar.blade.php
 * Server: App\Http\Controllers\BulkSelectionController
 *
 * Perilaku:
 *  - Tiap baris <tr data-id> otomatis diberi checkbox (juga baris yang ditambah/diganti lewat JS).
 *  - Checkbox "pilih semua" di header baru muncul setelah ada baris yang dicentang; hanya memilih
 *    baris yang sedang tampil (hasil filter). Pilihan di luar filter tetap tersimpan & dihitung.
 *  - Bar aksi muncul selama ada pilihan: Export Excel, Cetak PDF, Hapus, Batal pilih.
 *  - Hapus: konfirmasi 2 langkah (DeleteConfirm, js/delete-confirm.js), lalu baris terhapus langsung hilang dari tabel.
 *  - Cetak PDF memakai modal Cetak Laporan yang sudah ada (SIDA.reportPrint di bulk-import.js).
 * =====================================================================
 */
(function (window, document) {
    'use strict';

    const table = document.querySelector('table[data-selectable]');
    if (!table) return;

    const bar = document.querySelector('[data-sel-bar]');
    const body = table.querySelector('tbody:not(.sk-body)');
    const headRow = table.querySelector('thead tr');
    if (!bar || !body || !headRow) return;

    const exportUrl = table.dataset.selectExport || '';
    const printUrl = table.dataset.selectPrint || '';
    const deleteUrl = table.dataset.selectDelete || '';
    const updateUrl = table.dataset.selectUpdate || '';
    const selected = new Set();

    const countEl = bar.querySelector('[data-sel-count]');
    const hintEl = bar.querySelector('[data-sel-hint]');
    const btnExport = bar.querySelector('[data-sel-action="export"]');
    const btnPrint = bar.querySelector('[data-sel-action="print"]');
    const btnDelete = bar.querySelector('[data-sel-action="delete"]');
    const btnUpdate = bar.querySelector('[data-sel-action="update"]');
    const btnClear = bar.querySelector('[data-sel-action="clear"]');

    // ---------- Markup checkbox (animasi: public/css/row-select.css) ----------
    const boxHTML = (label) => `
        <label class="sel-checkbox">
            <input type="checkbox" aria-label="${label}">
            <span class="checkmark">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1.5" y="1.5" width="21" height="21" rx="5" ry="5" stroke-width="3"></rect>
                        <polyline points="7 10 12 16 22 2" stroke-width="4"></polyline>
                        <line x1="5" y1="12" x2="19" y2="12" stroke-width="3.5"></line>
                    </g>
                </svg>
            </span>
        </label>`;

    const isRow = (tr) => tr.nodeType === 1 && tr.tagName === 'TR' && tr.hasAttribute('data-id');
    const rowBox = (tr) => tr.querySelector(':scope > td.sel-col input');
    const rows = () => Array.from(body.querySelectorAll('tr[data-id]'));
    const isVisible = (tr) => tr.style.display !== 'none';

    // ---------- Sisipkan kolom checkbox ----------
    const headBox = (() => {
        const th = document.createElement('th');
        th.className = 'sel-col sel-head';
        th.innerHTML = boxHTML('Pilih semua data yang tampil');
        headRow.prepend(th);
        return th.querySelector('input');
    })();

    // Baris placeholder (kosong / skeleton) melebar 1 kolom lagi.
    table.querySelectorAll('tbody tr:not([data-id]) > td[colspan]').forEach((td) => {
        td.colSpan = (parseInt(td.getAttribute('colspan'), 10) || 1) + 1;
    });

    function decorate(tr) {
        if (!isRow(tr) || tr.querySelector(':scope > td.sel-col')) return;
        const td = document.createElement('td');
        td.className = 'sel-col';
        td.innerHTML = boxHTML('Pilih baris ini');
        tr.prepend(td);
        const on = selected.has(tr.dataset.id);
        td.querySelector('input').checked = on;
        tr.classList.toggle('is-selected', on);
    }

    // ---------- Sinkron tampilan ----------
    let raf = 0;
    const schedule = () => { cancelAnimationFrame(raf); raf = requestAnimationFrame(refresh); };

    function refresh() {
        const all = rows();
        const present = new Set(all.map((tr) => tr.dataset.id));
        selected.forEach((id) => { if (!present.has(id)) selected.delete(id); }); // baris yang sudah dihapus

        const visible = all.filter(isVisible);
        const selVisible = visible.filter((tr) => selected.has(tr.dataset.id)).length;

        headBox.checked = visible.length > 0 && selVisible === visible.length;
        headBox.indeterminate = selVisible > 0 && selVisible < visible.length;

        const total = selected.size;
        table.classList.toggle('has-selection', total > 0);
        bar.classList.toggle('is-open', total > 0);
        bar.setAttribute('aria-hidden', total > 0 ? 'false' : 'true');
        countEl.textContent = total;

        const hidden = total - selVisible;
        hintEl.hidden = hidden <= 0;
        hintEl.textContent = hidden > 0 ? `(${hidden} di luar filter yang aktif)` : '';

        all.forEach((tr) => tr.classList.toggle('is-selected', selected.has(tr.dataset.id)));
    }

    function setRow(tr, on) {
        const id = tr.dataset.id;
        on ? selected.add(id) : selected.delete(id);
        const box = rowBox(tr);
        if (box) box.checked = on;
    }

    // ---------- Event ----------
    table.addEventListener('change', (e) => {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'checkbox') return;

        if (input === headBox) {
            // Hanya baris yang tampil; kalau semua sudah terpilih -> kosongkan.
            const visible = rows().filter(isVisible);
            const allOn = visible.length > 0 && visible.every((tr) => selected.has(tr.dataset.id));
            visible.forEach((tr) => setRow(tr, !allOn));
        } else if (input.closest('td.sel-col')) {
            setRow(input.closest('tr'), input.checked);
        } else {
            return;
        }
        refresh();
    });

    // Baris ditambah / diganti / dihapus lewat JS, dan baris disembunyikan filter (style.display).
    new MutationObserver((mutations) => {
        let changed = false;
        mutations.forEach((m) => {
            if (m.type === 'childList') {
                m.addedNodes.forEach((n) => { if (isRow(n)) decorate(n); });
                changed = true;
            } else if (m.type === 'attributes') {
                changed = true;
            }
        });
        if (changed) schedule();
    }).observe(body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style'] });

    rows().forEach(decorate);

    // ---------- Aksi ----------
    const ids = () => Array.from(selected);

    function postDownload(url, idList) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.hidden = true;
        [['_token', window.SIDA?.util?.csrfToken?.() || ''], ['ids', idList.join(',')]].forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    btnExport?.addEventListener('click', () => {
        if (!selected.size || !exportUrl) return;
        postDownload(exportUrl, ids());
        // Unduhan berjalan di latar; cegah klik ganda sesaat.
        btnExport.disabled = true;
        setTimeout(() => { btnExport.disabled = false; }, 1500);
    });

    if (btnPrint) {
        // Modal Cetak Laporan hanya ada bila halaman memasang menu cetak (bulk-menu + bulk-import.js).
        const printReady = () => !!(window.SIDA && window.SIDA.reportPrint && printUrl);
        if (!printReady()) btnPrint.hidden = true;
        btnPrint.addEventListener('click', () => {
            if (!selected.size || !printReady()) return;
            window.SIDA.reportPrint.open(printUrl, ids());
        });
    }

    // ---------- Hapus data terpilih ----------
    const toast = (type, message) => (window.Toast ? window.Toast.show({ type, message }) : window.alert(message));

    function removeRows(idList) {
        const gone = new Set(idList.map(String));
        gone.forEach((id) => selected.delete(id));
        rows().filter((tr) => gone.has(tr.dataset.id)).forEach((tr) => {
            tr.classList.add('is-removing');
            const drop = () => tr.remove();
            tr.addEventListener('transitionend', drop, { once: true });
            setTimeout(drop, 450); // jaga-jaga bila tidak ada transisi CSS
        });
        refresh();
    }

    async function deleteSelected() {
        const list = ids();
        if (!list.length || !deleteUrl) return;

        const ok = window.DeleteConfirm
            ? await window.DeleteConfirm.ask({ entity: 'data', count: list.length })
            : window.confirm('Hapus ' + list.length + ' data terpilih secara permanen?');
        if (!ok) return;

        btnDelete.disabled = true;
        try {
            const res = await fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.SIDA?.util?.csrfToken?.() || '',
                },
                body: JSON.stringify({ ids: list }),
                credentials: 'same-origin',
            });
            const result = await res.json().catch(() => ({}));
            if (!res.ok || !result.success) throw new Error(result.message || 'Gagal menghapus data (' + res.status + ').');

            removeRows(result.deleted || []);
            const n = (result.deleted || []).length;
            toast('success', n + ' data berhasil dihapus.');
            if (result.missing > 0) toast('info', result.missing + ' data sudah tidak ada (mungkin dihapus pengguna lain).');
        } catch (err) {
            toast('error', err.message || 'Gagal terhubung ke server.');
        } finally {
            btnDelete.disabled = false;
        }
    }

    if (btnDelete) {
        if (!deleteUrl) btnDelete.hidden = true;
        else btnDelete.addEventListener('click', deleteSelected);
    }

    // ---------- Update massal (modal: js/bulk-update.js) ----------
    if (btnUpdate) {
        if (!updateUrl) btnUpdate.hidden = true;
        else btnUpdate.addEventListener('click', () => {
            if (!selected.size) return;
            if (!window.SIDA || !window.SIDA.bulkUpdate) {
                (window.Toast ? window.Toast.show({ type: 'error', message: 'Modal Update Massal belum dipasang di halaman ini.' }) : null);
                return;
            }
            window.SIDA.bulkUpdate.open({ url: updateUrl, ids: ids() });
        });
    }

    btnClear?.addEventListener('click', () => {
        selected.clear();
        rows().forEach((tr) => { const b = rowBox(tr); if (b) b.checked = false; });
        refresh();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && selected.size && !document.querySelector('.modal-card.is-active')) btnClear?.click();
    });

    // Tombol Cetak disembunyikan di atas jika SIDA.reportPrint belum ada saat script ini jalan
    // (bulk-import.js dimuat sebelum file ini, jadi seharusnya sudah ada).
    refresh();
})(window, document);
