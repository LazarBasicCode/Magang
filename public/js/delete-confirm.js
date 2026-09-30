/* ==========================================================
   DeleteConfirm.ask({ name }) -> Promise<boolean>
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
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && els.backdrop.classList.contains('is-active')) finish(false);
        });
        return els;
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

    function showStep1(name) {
        step = 1;
        clearTimeout(timer);
        els.card.classList.remove('is-final');
        els.icon.textContent = 'warning';
        els.title.textContent = 'Penghapusan pengguna dilarang';
        els.text.innerHTML = `Menghapus akun <strong>${esc(name)}</strong> tidak diperbolehkan kecuali benar-benar diperlukan dan sudah disetujui. Lanjutkan hanya jika Anda yakin.`;
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
        els.text.innerHTML = `Akun <strong>${esc(name)}</strong> beserta datanya akan dihapus dan <strong>tidak bisa dikembalikan</strong>. Tombol hapus aktif setelah 3 detik.`;
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
            setNext('delete', 'Hapus sekarang');
        }, WAIT_MS);
    }

    let currentName = '';
    function onNext() {
        if (els.next.disabled) return;
        if (step === 1) showStep2(currentName);
        else finish(true);
    }

    function finish(result) {
        clearTimeout(timer);
        els.backdrop.classList.remove('is-active');
        els.card.classList.remove('is-pop');
        const r = resolver; resolver = null;
        if (lastFocus && lastFocus.focus) lastFocus.focus();
        if (r) r(result);
    }

    window.DeleteConfirm = {
        ask({ name } = {}) {
            build();
            if (resolver) finish(false);
            currentName = name || 'ini';
            lastFocus = document.activeElement;
            els.backdrop.classList.add('is-active');
            showStep1(currentName);
            els.next.focus();
            return new Promise((resolve) => { resolver = resolve; });
        },
    };
})();
