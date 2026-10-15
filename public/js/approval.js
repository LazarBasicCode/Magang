/**
 * Halaman Persetujuan (resources/views/persetujuan.blade.php):
 * aksi Teruskan / Setujui / Tolak lewat modal catatan, plus modal riwayat status.
 * Butuh: script.js (SIDA.modal, SIDA.util.csrfToken), toast.js (opsional).
 */
(function () {
    const base = document.currentScript?.dataset.urlBase || '/persetujuan';
    const list = document.getElementById('apprList');
    if (!list) return;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const toast = (type, title, message) => { if (window.Toast) Toast.show({ type, title, message }); };
    const headers = () => ({
        'X-CSRF-TOKEN': SIDA.util.csrfToken(),
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    });

    const ACTIONS = {
        teruskan: { title: 'Teruskan ke Superadmin', label: 'Catatan untuk Superadmin (opsional)', ph: 'Mis. bukti sudah saya cek dan sesuai.', required: false, btn: 'Teruskan', done: 'success' },
        setujui:  { title: 'Setujui Pengajuan', label: 'Catatan (opsional)', ph: 'Catatan untuk arsip / mahasiswa.', required: false, btn: 'Setujui', done: 'success' },
        tolak:    { title: 'Tolak Pengajuan', label: 'Alasan penolakan (wajib)', ph: 'Jelaskan apa yang perlu diperbaiki mahasiswa, mis. link bukti tidak bisa dibuka.', required: true, btn: 'Tolak', done: 'warning' },
    };

    // ---------------- Modal catatan ----------------
    const noteModal = SIDA.modal.attach({
        backdrop: document.getElementById('apprBackdrop'),
        card: document.getElementById('apprModal'),
        closeBtn: document.getElementById('apprCloseBtn'),
        cancelBtn: document.getElementById('apprCancelBtn'),
        dragHandle: document.getElementById('apprDragHandle'),
    });
    const form = document.getElementById('apprForm');
    const noteEl = document.getElementById('apprNote');
    const errEl = document.getElementById('apprError');
    const submitBtn = document.getElementById('apprSubmitBtn');
    let current = null; // { card, menu, id, aksi }

    function showError(msg) {
        errEl.textContent = msg;
        errEl.hidden = !msg;
    }

    list.addEventListener('click', (e) => {
        const actBtn = e.target.closest('.js-appr-act');
        const histBtn = e.target.closest('.js-appr-history');
        const card = e.target.closest('.appr-card');
        if (!card) return;

        if (actBtn) {
            const aksi = actBtn.dataset.aksi;
            const cfg = ACTIONS[aksi];
            current = { card, menu: card.dataset.menu, id: card.dataset.id, aksi };
            document.getElementById('apprModalTitle').textContent = cfg.title;
            document.getElementById('apprModalSub').textContent = card.dataset.title;
            document.getElementById('apprNoteLabel').textContent = cfg.label;
            document.getElementById('apprSubmitText').textContent = cfg.btn;
            noteEl.placeholder = cfg.ph;
            noteEl.value = '';
            submitBtn.className = 'btn-apply' + (aksi === 'tolak' ? ' is-danger' : '');
            showError('');
            noteModal.open();
            setTimeout(() => noteEl.focus(), 60);
        } else if (histBtn) {
            openHistory(card);
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!current) return;
        const cfg = ACTIONS[current.aksi];
        const note = noteEl.value.trim();

        if (cfg.required && note.length < 5) {
            showError('Alasan penolakan wajib diisi (minimal 5 karakter) supaya mahasiswa tahu apa yang perlu diperbaiki.');
            noteEl.focus();
            return;
        }

        submitBtn.disabled = true;
        showError('');
        try {
            const res = await fetch(`${base}/${current.menu}/${current.id}/${current.aksi}`, {
                method: 'POST',
                headers: headers(),
                body: JSON.stringify({ note }),
            });
            let json = {};
            try { json = await res.json(); } catch (_) { /* bukan JSON */ }

            if (!res.ok || !json.success) {
                const flat = json.errors ? Object.values(json.errors).flat() : [];
                showError(flat[0] || json.message || (res.status === 419 ? 'Sesi habis. Muat ulang halaman lalu coba lagi.' : 'Gagal memproses (' + res.status + ').'));
                return;
            }

            noteModal.close();
            toast(cfg.done, current.aksi === 'tolak' ? 'Pengajuan ditolak' : 'Berhasil', json.message);
            afterAction(current.card, json);
        } catch (_) {
            showError('Tidak bisa terhubung ke server.');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // Kartu keluar dari tab ini (status sudah berpindah); angka ringkasan & lencana sidebar ikut diperbarui.
    function afterAction(card, json) {
        card.classList.add('is-leaving');
        setTimeout(() => {
            card.remove();
            if (!list.querySelector('.appr-card')) location.reload();
        }, 260);

        if (json.counts) {
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('cntPerlu', json.counts.perlu);
            set('cntProses', json.counts.proses);
            set('cntRiwayat', json.counts.riwayat);
            const tabs = ['perlu', 'proses', 'riwayat'];
            document.querySelectorAll('.appr-tab-count').forEach((el, i) => { el.textContent = json.counts[tabs[i]] ?? el.textContent; });
        }
        document.querySelectorAll('.nav-badge').forEach((badge) => {
            if (json.pending > 0) badge.textContent = json.pending > 99 ? '99+' : json.pending;
            else badge.remove();
        });
    }

    // ---------------- Modal riwayat ----------------
    const histModal = SIDA.modal.attach({
        backdrop: document.getElementById('apprHistBackdrop'),
        card: document.getElementById('apprHistModal'),
        closeBtn: document.getElementById('apprHistClose'),
        dragHandle: document.getElementById('apprHistDrag'),
    });
    const histList = document.getElementById('apprHistList');

    async function openHistory(card) {
        document.getElementById('apprHistSub').textContent = card.dataset.title;
        histList.innerHTML = '<li>Memuat…</li>';
        histModal.open();
        try {
            const res = await fetch(`${base}/${card.dataset.menu}/${card.dataset.id}/riwayat`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error();
            histList.innerHTML = json.data.length
                ? json.data.map((l) =>
                    `<li><b>${esc(l.label)}</b> &middot; ${esc(l.actor)}${l.role ? ' (' + esc(l.role) + ')' : ''}<br><span>${esc(l.at)}</span>` +
                    (l.note ? `<br>Catatan: ${esc(l.note)}` : '') + '</li>').join('')
                : '<li>Belum ada riwayat tercatat (data lama sebelum fitur persetujuan).</li>';
        } catch (_) {
            histList.innerHTML = '<li>Gagal memuat riwayat.</li>';
        }
    }
})();
