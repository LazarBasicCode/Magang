/**
 * Halaman Backup & Restore.
 * Alur restore: pilih file (upload atau backup tersimpan) -> /backup/inspect (hanya memeriksa)
 * -> modal ringkasan + ketik PULIHKAN -> /backup/restore.
 */
(function () {
    'use strict';

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var overlay = document.getElementById('bkOverlay');
    if (!overlay) return;

    var fileInput = document.getElementById('bkFile');
    var drop = document.getElementById('bkDrop');
    var meta = document.getElementById('bkModalMeta');
    var compareBody = document.getElementById('bkCompare');
    var warnings = document.getElementById('bkWarnings');
    var confirmInput = document.getElementById('bkConfirm');
    var runBtn = document.getElementById('bkRun');
    var cancelBtn = document.getElementById('bkCancel');
    var source = null; // {type: 'upload'|'stored', value: '...'}
    var busy = false;

    function toast(type, message, title) {
        if (window.Toast) window.Toast.show({ type: type, message: message, title: title || '' });
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function fmt(n) {
        return n == null ? '—' : Number(n).toLocaleString('id-ID');
    }

    function openModal() {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        confirmInput.value = '';
        runBtn.disabled = true;
        setTimeout(function () { confirmInput.focus(); }, 250);
    }

    function closeModal() {
        if (busy) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        source = null;
        if (fileInput) fileInput.value = '';
    }

    function render(info) {
        var parts = [];
        if (info.created_at) parts.push('Dibuat ' + new Date(info.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }));
        if (info.created_by) parts.push('oleh ' + info.created_by);
        if (info.label) parts.push('(' + info.label + ')');
        meta.textContent = parts.join(' ') || 'File backup SIDA';

        compareBody.innerHTML = info.compare.map(function (r) {
            var changed = r.backup !== r.current;
            return '<tr class="' + (changed ? 'is-changed' : '') + '"><td>' + esc(r.label) + '</td>' +
                '<td class="num">' + fmt(r.current) + '</td>' +
                '<td class="num"><strong>' + (r.backup == null ? 'dikosongkan' : fmt(r.backup)) + '</strong></td></tr>';
        }).join('');

        warnings.innerHTML = info.warnings.map(function (w) {
            return '<li><span class="material-symbols-outlined">info</span>' + esc(w) + '</li>';
        }).join('');
        warnings.style.display = info.warnings.length ? '' : 'none';
    }

    async function inspect(formData) {
        if (busy) return;
        busy = true;
        toast('info', 'Memeriksa file backup...');
        try {
            var res = await fetch('/backup/inspect', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            var json = await res.json().catch(function () { return {}; });
            if (!res.ok || !json.success) {
                var msg = json.message || (json.errors && Object.values(json.errors)[0][0]) || 'File backup tidak valid.';
                toast('error', msg, 'Tidak bisa memulihkan');
                if (fileInput) fileInput.value = '';
                return;
            }
            source = json.source;
            render(json.info);
            busy = false;
            openModal();
        } catch (e) {
            toast('error', 'Tidak bisa terhubung ke server.');
        } finally {
            busy = false;
        }
    }

    // --- Upload dari komputer ---
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (!fileInput.files.length) return;
            var fd = new FormData();
            fd.append('file', fileInput.files[0]);
            inspect(fd);
        });
    }
    if (drop) {
        ['dragenter', 'dragover'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); });
        });
        drop.addEventListener('drop', function (e) {
            if (!e.dataTransfer.files.length) return;
            var fd = new FormData();
            fd.append('file', e.dataTransfer.files[0]);
            inspect(fd);
        });
    }

    // --- Dari backup yang tersimpan di server ---
    document.querySelectorAll('[data-restore-stored]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var fd = new FormData();
            fd.append('stored', btn.dataset.restoreStored);
            inspect(fd);
        });
    });

    // --- Konfirmasi & eksekusi ---
    confirmInput.addEventListener('input', function () {
        runBtn.disabled = confirmInput.value.trim() !== 'PULIHKAN';
    });
    confirmInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !runBtn.disabled) runBtn.click();
    });
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    runBtn.addEventListener('click', async function () {
        if (busy || !source) return;
        busy = true;
        runBtn.disabled = true;
        cancelBtn.disabled = true;
        runBtn.innerHTML = '<span class="material-symbols-outlined bk-spin">progress_activity</span> Memulihkan...';
        try {
            var res = await fetch('/backup/restore', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ source_type: source.type, source: source.value, confirm: confirmInput.value.trim() }),
            });
            var json = await res.json().catch(function () { return {}; });
            if (!res.ok || !json.success) {
                throw new Error(json.message || (json.errors && Object.values(json.errors)[0][0]) || 'Pemulihan gagal.');
            }
            toast('success', json.message + ' Snapshot sebelum pemulihan: ' + json.snapshot, 'Pemulihan selesai');
            setTimeout(function () { window.location.href = json.redirect || '/'; }, 2200);
        } catch (e) {
            busy = false;
            cancelBtn.disabled = false;
            runBtn.disabled = confirmInput.value.trim() !== 'PULIHKAN';
            runBtn.innerHTML = '<span class="material-symbols-outlined">restore</span> Pulihkan Sekarang';
            toast('error', e.message, 'Pemulihan gagal');
        }
    });

    // Tombol "Buat & Unduh": beri umpan balik singkat karena unduhan tidak memuat ulang halaman.
    var dl = document.getElementById('bkCreateDownload');
    if (dl) {
        dl.addEventListener('submit', function () {
            var b = dl.querySelector('button');
            b.disabled = true;
            toast('info', 'Backup sedang dibuat, unduhan akan dimulai sebentar lagi...');
            setTimeout(function () { b.disabled = false; window.location.reload(); }, 4000);
        });
    }
})();
