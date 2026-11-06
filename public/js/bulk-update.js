/**
 * =====================================================================
 * UPDATE MASSAL data terpilih (centang baris) — dipakai ulang di semua halaman.
 * Hanya kolom yang diisi yang diubah; kolom kosong = nilai lama tiap baris dipertahankan.
 *
 * Modal & daftar kolom: resources/views/partials/bulk-update-modal.blade.php
 * Dibuka oleh tombol "Update" di bar pilihan (public/js/row-select.js) lewat SIDA.bulkUpdate.open().
 * Server: BulkSelectionController::ubah -> <Controller menu>::updateMany
 * Butuh: js/script.js (SIDA.modal, SIDA.util) dan js/toast.js (opsional)
 * =====================================================================
 */
(function (window, document) {
    'use strict';

    const SIDA = window.SIDA;
    const card = document.getElementById('bulkUpdateModal');
    if (!SIDA || !card) return;

    const form = document.getElementById('bulkUpdateForm');
    const errorEl = document.getElementById('bulkUpdateError');
    const countEl = document.getElementById('bulkUpdateCount');
    const submitBtn = document.getElementById('bulkUpdateSubmitBtn');
    const fields = JSON.parse(card.dataset.fields || '[]');

    const modal = SIDA.modal.attach({
        backdrop: document.getElementById('bulkUpdateBackdrop'),
        card,
        closeBtn: document.getElementById('bulkUpdateCloseBtn'),
        cancelBtn: document.getElementById('bulkUpdateCancelBtn'),
        dragHandle: document.getElementById('bulkUpdateDragHandle'),
    });

    let current = null; // { url, ids, onDone }

    const toast = (type, message) => (window.Toast ? window.Toast.show({ type, message }) : window.alert(message));
    const csrf = () => (SIDA.util && SIDA.util.csrfToken ? SIDA.util.csrfToken() : '');

    function showError(msg) {
        errorEl.textContent = msg || '';
        errorEl.hidden = !msg;
    }

    // Kembalikan satu dropdown ke "— Tidak diubah —".
    function resetDropdown(dd) {
        const hidden = dd.querySelector('input[type="hidden"]');
        const opts = dd.querySelectorAll('.dropdown-option');
        if (hidden) hidden.value = '';
        opts.forEach((o, i) => o.classList.toggle('is-selected', i === 0));
        const valueEl = dd.querySelector('.dropdown-value');
        if (valueEl && opts[0]) valueEl.textContent = opts[0].textContent.trim();
    }

    // Kembalikan semua input & dropdown ke "— Tidak diubah —".
    function reset() {
        form.reset();
        form.querySelectorAll('[data-dropdown]').forEach(resetDropdown);
        showError('');
    }

    const wrapOf = (name) => form.querySelector(`[data-field="${name}"]`);
    const valueOf = (name) => (form.elements[name] ? String(form.elements[name].value || '') : '');
    const rowOf = (id) => document.querySelector(`table[data-selectable] tr[data-id="${id}"]`);

    function clearField(name) {
        const dd = wrapOf(name).querySelector('[data-dropdown]');
        if (dd) resetDropdown(dd);
        else form.elements[name].value = '';
    }

    // Kolom ber-"showWhen" hanya tampil bila kolom pemicunya bernilai yang cocok; yang disembunyikan dikosongkan.
    function syncVisibility() {
        fields.forEach((f) => {
            const showWhen = !f.showWhen
                || Object.entries(f.showWhen).every(([name, values]) => values.includes(valueOf(name)));
            // "follow": bila kolom itu (mis. jenis) sedang diisi, nilainya dipakai menggantikan atribut baris terpilih.
            const showFor = !f.showFor || (() => {
                const { attr, values, follow } = f.showFor;
                const chosen = follow ? valueOf(follow) : '';
                const kinds = chosen ? [chosen] : (current?.ids.map((id) => rowOf(id)?.dataset[attr]).filter(Boolean) || []);
                return kinds.length > 0 && kinds.every((kind) => values.includes(kind));
            })();
            const show = showWhen && showFor;
            wrapOf(f.name).style.display = show ? '' : 'none';
            if (!show) clearField(f.name);
        });
        tidyRows();
    }

    // Pembungkus ".field-row" yang semua isinya tersembunyi ikut disembunyikan (kalau tidak: gap kosong);
    // bila tinggal satu kolom yang tampil, kolom itu dibuat selebar baris.
    function tidyRows() {
        form.querySelectorAll('.field-row').forEach((row) => {
            const shown = [...row.children].filter((el) => el.style.display !== 'none');
            const single = shown.length === 1;
            row.style.display = shown.length ? '' : 'none';
            row.style.gridTemplateColumns = single ? '1fr' : '';
            shown.forEach((el) => { el.style.flex = single ? '1 1 100%' : ''; });
        });
    }

    // Kolom ber-"optionsBy": opsi dibatasi = irisan opsi yang boleh untuk tiap nilai atribut baris terpilih.
    function restrictOptions(ids) {
        fields.forEach((f) => {
            if (!f.optionsBy) return;
            const { attr, allowed } = f.optionsBy;
            const kinds = new Set(ids.map((id) => rowOf(id)?.dataset[attr]).filter(Boolean));
            const lists = [...kinds].map((k) => allowed[k]).filter(Boolean);
            const ok = lists.length ? lists.reduce((a, b) => a.filter((v) => b.includes(v))) : null;

            const options = wrapOf(f.name).querySelectorAll('.dropdown-option');
            options.forEach((opt) => {
                const v = opt.dataset.value;
                opt.style.display = (!v || !ok || ok.includes(v)) ? '' : 'none';
            });

            const selected = valueOf(f.name);
            if (selected && [...options].some((opt) => opt.dataset.value === selected && opt.style.display === 'none')) {
                clearField(f.name);
            }
        });
    }

    // Dropdown modal di-portal ke body; dengarkan change pada input tersembunyi, bukan klik option di form.
    form.addEventListener('change', (e) => {
        if (!e.target.name || !fields.some((f) => f.name === e.target.name)) return;
        restrictOptions(current?.ids || []);
        syncVisibility();
    });

    function collect() {
        const out = {};
        fields.forEach((f) => {
            const el = form.elements[f.name];
            const v = el ? String(el.value || '').trim() : '';
            if (v !== '') out[f.name] = v;
        });
        return out;
    }

    // Kolom ber-"showFor.require" ({when, haveIn}): wajib diisi bila kolom pemicunya dipilih salah satu nilai "when"
    // dan tidak semua baris terpilih sudah termasuk "haveIn" (artinya datanya sudah dimiliki baris itu).
    function missingRequired() {
        return fields.filter((f) => {
            const rule = f.showFor && f.showFor.require;
            if (!rule || !f.showFor.follow || wrapOf(f.name).style.display === 'none') return false;
            const chosen = valueOf(f.showFor.follow);
            if (!chosen || !rule.when.includes(chosen)) return false;
            const have = (current?.ids || []).every((id) => rule.haveIn.includes(rowOf(id)?.dataset[f.showFor.attr]));
            return !have && valueOf(f.name).trim() === '';
        }).map((f) => (wrapOf(f.name).querySelector('.field-label')?.textContent || f.name).trim());
    }

    // ---------- Tampilkan hasil di baris tabel ----------
    function applyRows(rows) {
        const body = document.querySelector('table[data-selectable] tbody');
        if (!body) return;
        (rows || []).forEach((r) => {
            const tr = body.querySelector(`tr[data-id="${r.id}"]`);
            if (!tr) return;
            const cells = Array.from(tr.children).filter((td) => !td.classList.contains('sel-col'));
            const editBtn = tr.querySelector('.btn-edit-row');

            fields.forEach((f) => {
                if (r[f.name] === undefined) return;
                const val = String(r[f.name]);

                // teks di sel tabel
                const cell = f.col != null ? cells[f.col] : null;
                const node = cell ? (f.target ? cell.querySelector(f.target) : cell) : null;
                if (node) {
                    node.textContent = val;
                    if (node.hasAttribute('title')) node.setAttribute('title', val);
                }
                // atribut data-* untuk filter & tombol edit (kalau ada)
                if (tr.hasAttribute('data-' + f.name)) tr.setAttribute('data-' + f.name, val);
                if (editBtn && editBtn.hasAttribute('data-' + f.name)) editBtn.setAttribute('data-' + f.name, val);
            });

            tr.classList.remove('is-updated-flash');
            void tr.offsetWidth; // restart animasi
            tr.classList.add('is-updated-flash');
            setTimeout(() => tr.classList.remove('is-updated-flash'), 1800);
        });
    }

    // Pesan error: bentuk Laravel {field: [pesan]} atau hasil validasi silang [{row, messages}].
    function errorText(result, status) {
        const e = result.errors;
        if (Array.isArray(e) && e.length) return `${result.message} Contoh: data ${e[0].row} — ${e[0].messages[0]}`;
        const first = e && !Array.isArray(e) ? Object.values(e)[0]?.[0] : null;
        return first || result.message || 'Gagal memperbarui data (' + status + ').';
    }

    // ---------- Kirim ----------
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!current) return;

        const changes = collect();
        if (!Object.keys(changes).length) {
            showError('Isi minimal satu kolom yang ingin diubah.');
            toast('error', 'Isi minimal satu kolom yang ingin diubah.');
            return;
        }
        const missing = missingRequired();
        if (missing.length) {
            const msg = 'Lengkapi kolom wajib: ' + missing.join(', ') + '.';
            showError(msg);
            toast('error', msg);
            return;
        }
        showError('');
        submitBtn.disabled = true;

        try {
            const res = await fetch(current.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids: current.ids, ...changes }),
            });
            const result = await res.json().catch(() => ({}));

            if (!res.ok || !result.success) throw new Error(errorText(result, res.status));

            // Halaman boleh memasang SIDA.bulkUpdateApply(rows) untuk merender ulang baris dengan template-nya sendiri.
            (SIDA.bulkUpdateApply || applyRows)(result.rows);
            const n = (result.updated || []).length;
            toast('success', n + ' data berhasil diperbarui.');
            if (result.missing > 0) toast('info', result.missing + ' data sudah tidak ada (mungkin dihapus pengguna lain).');
            if (typeof current.onDone === 'function') current.onDone(result);
            modal.close();
        } catch (err) {
            showError(err.message || 'Gagal terhubung ke server.');
            toast('error', err.message || 'Gagal terhubung ke server.');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // API untuk row-select.js
    SIDA.bulkUpdate = {
        open({ url, ids, onDone }) {
            current = { url, ids, onDone };
            countEl.textContent = ids.length;
            reset();
            restrictOptions(ids);
            syncVisibility();
            modal.open();
        },
    };
})(window, document);