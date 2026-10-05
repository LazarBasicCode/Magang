/**
 * Menu "+" Data Massal (Unduh Template / Upload / Download) + modal upload CSV.
 * Markup: resources/views/partials/bulk-menu.blade.php & bulk-import-modal.blade.php
 * Butuh: script.js (SIDA.modal, SIDA.util.csrfToken) dan toast.js (opsional).
 */
(function () {
    const menu = document.getElementById('bulkMenu');
    if (!menu) return;

    const menuBtn = document.getElementById('bulkMenuBtn');

    // ---------------- Dropdown ----------------
    function setMenu(open) {
        menu.classList.toggle('is-open', open);
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    menuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        setMenu(!menu.classList.contains('is-open'));
    });
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) setMenu(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setMenu(false);
    });
    // Klik item (termasuk link unduhan) menutup dropdown.
    menu.querySelectorAll('.bulk-menu-item').forEach((item) => item.addEventListener('click', () => setMenu(false)));

    // ---------------- Modal upload ----------------
    const card = document.getElementById('bulkModal');
    const openBtn = document.getElementById('bulkOpenImport');
    if (!card || !openBtn) return;

    const form = document.getElementById('bulkForm');
    const input = document.getElementById('bulkFileInput');
    const drop = document.getElementById('bulkDrop');
    const nameEl = document.getElementById('bulkFileName');
    const metaEl = document.getElementById('bulkFileMeta');
    const resultEl = document.getElementById('bulkResult');
    const submitBtn = document.getElementById('bulkSubmitBtn');
    const importUrl = card.dataset.importUrl;
    const DEFAULT_NAME = nameEl.textContent;
    const DEFAULT_META = metaEl.textContent;

    const modal = SIDA.modal.attach({
        backdrop: document.getElementById('bulkBackdrop'),
        card,
        closeBtn: document.getElementById('bulkCloseBtn'),
        cancelBtn: document.getElementById('bulkCancelBtn'),
        dragHandle: document.getElementById('bulkDragHandle'),
    });

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function reset() {
        form.reset();
        nameEl.textContent = DEFAULT_NAME;
        metaEl.textContent = DEFAULT_META;
        drop.classList.remove('has-file');
        resultEl.hidden = true;
        resultEl.innerHTML = '';
        resultEl.className = 'bulk-result';
        submitBtn.disabled = true;
    }

    function showResult(kind, html) {
        resultEl.className = 'bulk-result is-' + kind;
        resultEl.innerHTML = html;
        resultEl.hidden = false;
    }

    function pickFile(file) {
        if (!file) return;
        nameEl.textContent = file.name;
        metaEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
        drop.classList.add('has-file');
        resultEl.hidden = true;
        submitBtn.disabled = false;
    }

    openBtn.addEventListener('click', () => {
        reset();
        modal.open();
    });
    input.addEventListener('change', () => pickFile(input.files[0]));

    ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => {
        e.preventDefault();
        drop.classList.add('is-dragover');
    }));
    ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, (e) => {
        e.preventDefault();
        drop.classList.remove('is-dragover');
    }));
    drop.addEventListener('drop', (e) => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        pickFile(file);
    });

    function errorHtml(json) {
        let html = '<strong>' + esc(json.message || 'Unggah gagal.') + '</strong>';
        // Format error validasi bawaan Laravel: { errors: { file: [..] } }
        if (json.errors && !Array.isArray(json.errors)) {
            const flat = Object.values(json.errors).flat();
            if (flat.length) html = '<strong>' + esc(flat[0]) + '</strong>';
        } else if (Array.isArray(json.errors) && json.errors.length) {
            html += '<ul class="bulk-errors">' + json.errors.map((er) =>
                '<li><span class="bulk-err-row">Baris ' + esc(er.row) + '</span> ' + er.messages.map(esc).join(' · ') + '</li>'
            ).join('') + '</ul>';
            if (json.more > 0) html += '<p class="bulk-more">…dan ' + json.more + ' baris bermasalah lainnya.</p>';
        }
        return html;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!input.files[0]) return;

        const body = new FormData();
        body.append('file', input.files[0]);

        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');
        showResult('info', 'Mengunggah dan memeriksa data…');

        try {
            const res = await fetch(importUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': SIDA.util.csrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
            });
            let json = {};
            try { json = await res.json(); } catch (_) { /* respons bukan JSON */ }

            if (res.ok && json.success) {
                showResult('success', '<strong>' + esc(json.message) + '</strong><p class="bulk-more">Memuat ulang tabel…</p>');
                if (window.Toast) Toast.show({ type: 'success', title: 'Upload berhasil', message: json.message });
                setTimeout(() => location.reload(), 1400);
                return;
            }

            if (res.status === 419) json = { message: 'Sesi habis. Muat ulang halaman lalu coba lagi.' };
            else if (res.status === 403) json = { message: json.message || 'Kamu tidak punya akses untuk fitur ini.' };
            else if (!json.message && !json.errors) json = { message: 'Terjadi kesalahan di server (' + res.status + ').' };

            showResult('error', errorHtml(json));
            submitBtn.disabled = false;
        } catch (err) {
            showResult('error', '<strong>Tidak bisa terhubung ke server.</strong>');
            submitBtn.disabled = false;
        } finally {
            submitBtn.classList.remove('is-loading');
        }
    });
})();
