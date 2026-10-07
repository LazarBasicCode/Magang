/**
 * Menu ⋮ Data Massal (Unduh Template / Upload / Download / Cetak Laporan) + modal upload Excel + modal cetak laporan.
 * Markup: resources/views/partials/bulk-menu.blade.php (termasuk modal cetak) & bulk-import-modal.blade.php
 * Isi modal cetak: resources/views/cetak-laporan.blade.php (dimuat lewat fetch ke route cetak.show)
 * Butuh: script.js (SIDA.modal, SIDA.util.csrfToken) dan toast.js (opsional).
 */
(function () {
    const menu = document.getElementById('bulkMenu');
    if (!menu) return;

    const menuBtn = document.getElementById('bulkMenuBtn');
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

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

    // ---------------- Modal cetak laporan ----------------
    const printBtn = document.getElementById('bulkOpenPrint');
    const printCard = document.getElementById('printModal');
    if (printBtn && printCard) {
        const printBackdrop = document.getElementById('printBackdrop');
        const printBody = document.getElementById('printBody');
        const printNow = document.getElementById('printNowBtn');
        const printUrl = printBtn.dataset.printUrl;

        // Pindah ke <body>: lepas dari .title-bar (stacking context) & jadi satu-satunya elemen yang dicetak.
        document.body.append(printBackdrop, printCard);

        const printModal = SIDA.modal.attach({
            backdrop: printBackdrop,
            card: printCard,
            closeBtn: document.getElementById('printCloseBtn'),
            cancelBtn: document.getElementById('printCancelBtn'),
            dragHandle: document.getElementById('printDragHandle'),
        });

        const val = (id) => printBody.querySelector('#' + id)?.value ?? null;
        let loadSeq = 0;
        // Cetak data terpilih (centang baris, public/js/row-select.js): {url, ids}. null = cetak semua data menu.
        let selection = null;
        const printSubtitle = printCard.querySelector('.modal-subtitle');
        const printSubtitleDefault = printSubtitle ? printSubtitle.textContent : '';

        async function loadReport(tahun) {
            const seq = ++loadSeq;
            const keep = { name: val('pmName'), role: val('pmRole') }; // nama/jabatan yang sudah diketik tetap dipakai
            printNow.disabled = true;
            printBody.innerHTML = '<p class="pm-state">Memuat laporan…</p>';

            try {
                const res = selection
                    ? await fetch(selection.url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': SIDA.util.csrfToken(),
                        },
                        body: JSON.stringify({ ids: selection.ids }),
                        credentials: 'same-origin',
                    })
                    : await fetch(printUrl + (tahun ? '?tahun=' + encodeURIComponent(tahun) : ''), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                    });
                if (res.redirected || res.status === 419 || res.status === 401) throw new Error('Sesi habis. Muat ulang halaman lalu coba lagi.');
                if (res.status === 403) throw new Error('Kamu tidak punya akses untuk fitur ini.');
                if (!res.ok) throw new Error('Gagal memuat laporan (' + res.status + ').');
                const html = await res.text();
                if (seq !== loadSeq) return; // ada permintaan yang lebih baru

                printBody.innerHTML = html;
                if (keep.name !== null) { printBody.querySelector('#pmName').value = keep.name; printBody.querySelector('#pmRole').value = keep.role; }
                syncSign();
                printNow.disabled = false;
            } catch (err) {
                if (seq !== loadSeq) return;
                printBody.innerHTML = '<p class="pm-state is-error">' + esc(err.message || 'Tidak bisa terhubung ke server.') + '</p>';
            }
        }

        // Nama & jabatan penandatangan -> bagian tanda tangan di laporan
        function syncSign() {
            const n = printBody.querySelector('#pmSignName');
            const r = printBody.querySelector('#pmSignRole');
            if (n) n.textContent = val('pmName') || '________';
            if (r) r.textContent = val('pmRole') || '';
        }

        printBtn.addEventListener('click', () => {
            selection = null;
            if (printSubtitle) printSubtitle.textContent = printSubtitleDefault;
            printModal.open();
            loadReport('');
        });

        // Dipakai row-select.js: buka modal yang sama, tapi hanya untuk id terpilih (tanpa filter periode).
        SIDA.reportPrint = {
            open(url, ids) {
                selection = { url, ids };
                if (printSubtitle) printSubtitle.textContent = ids.length + ' data terpilih siap cetak. Untuk PDF, pilih "Simpan sebagai PDF" di dialog cetak.';
                printModal.open();
                loadReport('');
            },
        };
        printBody.addEventListener('input', (e) => { if (e.target.closest('#pmName, #pmRole')) syncSign(); });

        // Dropdown Periode (komponen custom .dropdown). Isi modal dimuat belakangan lewat fetch, jadi
        // SIDA.dropdown.init() tidak ikut memasangnya — cukup satu listener di modal ini.
        printCard.addEventListener('click', (e) => {
            const dd = e.target.closest('[data-dropdown]');
            printCard.querySelectorAll('[data-dropdown].is-open').forEach((d) => { if (d !== dd) d.classList.remove('is-open'); });
            if (!dd) return;

            const opt = e.target.closest('.dropdown-option');
            if (opt) {
                dd.classList.remove('is-open');
                if (opt.dataset.value !== dd.querySelector('input[type="hidden"]').value) loadReport(opt.dataset.value);
            } else if (e.target.closest('.dropdown-trigger')) {
                dd.classList.toggle('is-open');
            }
        });
        printNow.addEventListener('click', () => window.print());

        // Saat mencetak (tombol Cetak maupun Ctrl+P): hanya modal yang tercetak, orientasi kertas mengikuti menu.
        let pageStyle = null;
        window.addEventListener('beforeprint', () => {
            if (!printCard.classList.contains('is-active')) return;
            syncSign();
            const orient = printBody.querySelector('[data-orient]')?.dataset.orient || 'portrait';
            pageStyle = document.createElement('style');
            pageStyle.textContent = '@page { size: A4 ' + orient + '; margin: 14mm; }';
            document.head.append(pageStyle);
            document.body.classList.add('is-printing-report');
        });
        window.addEventListener('afterprint', () => {
            document.body.classList.remove('is-printing-report');
            pageStyle?.remove();
            pageStyle = null;
        });
    }

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
