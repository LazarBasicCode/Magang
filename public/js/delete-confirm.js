/* ==========================================================
   DeleteConfirm.ask({ name, entity, count }) -> Promise<boolean>
   entity: 'user' (default untuk akun pengguna) | 'data' (data biasa)
   count : (opsional) hapus banyak sekaligus -> teks "N data terpilih" menggantikan name
   Langkah 1: peringatan + [Lanjutkan]
   Langkah 2: peringatan lagi, tombol terkunci 3 dtk (garis progress)
   ========================================================== */
(function () {
    const WAIT_MS = 3000;
    let els = null, timer = null, resolver = null, step = 1, lastFocus = null;

    function build() {
        if (els) return els;
        const backdrop = document.createElement('div');
        backdrop.className = 'dc-backdrop';
        backdrop.innerHTML = `
            <div class="dc-card" role="alertdialog" aria-modal="true" aria-labelledby="dcTitle" aria-describedby="dcText">
                <div class="dc-icon"><span class="material-symbols-outlined" data-el="icon">warning</span></div>
                <h3 class="dc-title" id="dcTitle"></h3>
                <p class="dc-text" id="dcText"></p>
                <div class="dc-actions">
                    <button type="button" class="dc-btn" data-el="cancel">Batal</button>
                    <button type="button" class="dc-btn dc-btn--next" data-el="next"></button>
                </div>
                <div class="dc-progress" data-el="progress"></div>
            </div>`;
        document.body.appendChild(backdrop);
        const q = (n) => backdrop.querySelector(`[data-el="${n}"]`);
        els = {
            backdrop,
            card: backdrop.querySelector('.dc-card'),
            title: backdrop.querySelector('#dcTitle'),
            text: backdrop.querySelector('#dcText'),
            icon: q('icon'), cancel: q('cancel'), next: q('next'), progress: q('progress'),
        };
        els.cancel.addEventListener('click', () => finish(false));
        backdrop.addEventListener('mousedown', (e) => { if (e.target === backdrop) finish(false); });
        els.next.addEventListener('click', onNext);
        enableDrag();
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && els.backdrop.classList.contains('is-active')) finish(false);
        });
        return els;
    }

    // Kartu bisa digeser dengan menarik bagian mana pun selain tombol. Memakai properti `translate`
    // (bukan `transform`) supaya tidak bentrok dengan animasi dcPop. Saat digeser, latar berhenti blur.
    function enableDrag() {
        const { card, backdrop } = els;
        let d = null;
        card.addEventListener('pointerdown', (e) => {
            if (e.button !== undefined && e.button !== 0) return;
            if (e.target.closest('button')) return;
            d = { x: e.clientX, y: e.clientY, tx: card._tx || 0, ty: card._ty || 0 };
            card.classList.add('is-dragging');
            backdrop.classList.add('is-dragging');
            card.setPointerCapture(e.pointerId);
        });
        card.addEventListener('pointermove', (e) => {
            if (!d) return;
            // batas: kartu tetap berada di dalam layar
            const r = card.getBoundingClientRect();
            const baseLeft = r.left - (card._tx || 0), baseTop = r.top - (card._ty || 0);
            const tx = Math.min(Math.max(d.tx + e.clientX - d.x, 8 - baseLeft), window.innerWidth - 8 - r.width - baseLeft);
            const ty = Math.min(Math.max(d.ty + e.clientY - d.y, 8 - baseTop), window.innerHeight - 8 - r.height - baseTop);
            card._tx = tx; card._ty = ty;
            card.style.translate = tx + 'px ' + ty + 'px';
        });
        const end = (e) => {
            if (!d) return;
            d = null;
            card.classList.remove('is-dragging');
            backdrop.classList.remove('is-dragging');
            try { card.releasePointerCapture(e.pointerId); } catch (_) { /* sudah terlepas */ }
        };
        card.addEventListener('pointerup', end);
        card.addEventListener('pointercancel', end);
        card.addEventListener('lostpointercapture', end);
    }

    function resetDrag() {
        els.card._tx = 0; els.card._ty = 0;
        els.card.style.translate = '';
        els.card.classList.remove('is-dragging');
        els.backdrop.classList.remove('is-dragging');
    }

    function esc(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function replayPop() {
        els.card.classList.remove('is-pop');
        void els.card.offsetWidth; // restart animasi
        els.card.classList.add('is-pop');
    }

    function setNext(icon, label) {
        els.next.innerHTML = `<span class="material-symbols-outlined">${icon}</span>${label}`;
    }

    function subject(name) {
        const who = currentEntity === 'user' ? 'akun' : 'data';
        if (currentCount > 0) return `<strong>${currentCount} ${who} terpilih</strong>`;
        return name ? `${who} <strong>${esc(name)}</strong>` : `${who} ini`;
    }

    function showStep1(name) {
        step = 1;
        clearTimeout(timer);
        els.card.classList.remove('is-final');
        els.icon.textContent = 'warning';
        els.title.textContent = currentEntity === 'user' ? 'Penghapusan pengguna dilarang' : 'Penghapusan data dilarang';
        els.text.innerHTML = `Menghapus ${subject(name)} tidak diperbolehkan kecuali benar-benar diperlukan dan sudah disetujui. Lanjutkan hanya jika Anda yakin.`;
        els.next.disabled = false;
        els.next.className = 'dc-btn dc-btn--next';
        setNext('arrow_forward', 'Lanjutkan');
        els.progress.className = 'dc-progress';
        replayPop();
    }

    function showStep2(name) {
        step = 2;
        els.card.classList.add('is-final');
        els.icon.textContent = 'block';
        els.title.textContent = 'Tindakan ini permanen';
        els.text.innerHTML = currentCount > 0
            ? `${subject(name)} akan dihapus permanen dan <strong>tidak bisa dikembalikan</strong>. Tombol hapus aktif setelah 3 detik.`
            : `${currentEntity === 'user' ? 'Akun' : 'Data'} ${name ? '<strong>' + esc(name) + '</strong> ' : 'ini '}akan dihapus permanen dan <strong>tidak bisa dikembalikan</strong>. Tombol hapus aktif setelah 3 detik.`;
        els.next.disabled = true;
        els.next.className = 'dc-btn dc-btn--next is-locked';
        setNext('lock', 'Hapus');
        els.progress.className = 'dc-progress';
        void els.progress.offsetWidth;
        els.progress.classList.add('is-run');
        replayPop();

        timer = setTimeout(() => {
            els.next.disabled = false;
            els.next.className = 'dc-btn dc-btn--next is-danger is-ready';
            setNext('delete', currentCount > 0 ? `Hapus ${currentCount} data` : 'Hapus sekarang');
        }, WAIT_MS);
    }

    let currentName = '', currentEntity = 'user', currentCount = 0;
    function onNext() {
        if (els.next.disabled) return;
        if (step === 1) showStep2(currentName);
        else finish(true);
    }

    function finish(result) {
        clearTimeout(timer);
        els.backdrop.classList.remove('is-active');
        els.card.classList.remove('is-pop');
        resetDrag();
        const r = resolver; resolver = null;
        if (lastFocus && lastFocus.focus) lastFocus.focus();
        if (r) r(result);
    }

    window.DeleteConfirm = {
        ask({ name, entity = 'user', count = 0 } = {}) {
            build();
            if (resolver) finish(false);
            currentName = name || '';
            currentEntity = entity;
            currentCount = Number(count) > 0 ? Number(count) : 0;
            lastFocus = document.activeElement;
            resetDrag();   // selalu mulai dari tengah layar
            els.backdrop.classList.add('is-active');
            showStep1(currentName);
            els.next.focus();
            return new Promise((resolve) => { resolver = resolve; });
        },
    };
})();
