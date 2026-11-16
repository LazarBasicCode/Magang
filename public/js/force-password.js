/**
 * Pop-up WAJIB ganti password di dashboard mahasiswa/dosen (password default = NIM/NIDN).
 *
 * - Pengguna mengetik password baru + konfirmasi password (bukan lagi password acak).
 * - Efek animasi sengaja dibuat halus.
 * - Tidak bisa ditutup: tanpa tombol close, Esc diabaikan, klik latar hanya menggetarkan kartu.
 * - Fokus dikunci di dalam pop-up (Tab berputar) dan isi halaman di belakangnya dibuat `inert`.
 * - Endpoint (AccountController):
 *     POST /account/password  { password, password_confirmation } -> { message, logout, redirect }
 *   Setelah berhasil server mengeluarkan semua sesi, jadi pop-up menampilkan animasi sukses
 *   lalu mengarahkan ke halaman login.
 * - Penguncian sesungguhnya ada di server (middleware ForcePasswordChange); ini hanya antarmuka.
 *
 * Markup: resources/views/partials/force-password-modal.blade.php
 * Gaya  : public/css/force-password.css
 */
(function () {
    'use strict';

    const overlay = document.getElementById('fpOverlay');
    if (!overlay) return;

    const $ = (id) => document.getElementById(id);
    const card = $('fpCard');
    const field = $('fpField');
    const passInput = $('fpPassword');
    const confInput = $('fpConfirm');
    const matchEl = $('fpMatch');
    const submitBtn = $('fpSubmit');
    const submitText = submitBtn.querySelector('.fp-submit-text');
    const errEl = $('fpError');
    const stepsEl = overlay.querySelector('.fp-steps');
    const stepItems = Array.from(stepsEl.querySelectorAll('li'));
    const formView = $('fpFormView');
    const successView = $('fpSuccessView');

    const MIN_LENGTH = 6;
    const REDIRECT_MS = 3200;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let busy = false;
    let finished = false;
    let lastBackdropNudge = 0;

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function notify(type, message, title) {
        if (window.Toast) window.Toast.show({ type, title: title || '', message });
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf(),
                ...(body ? { 'Content-Type': 'application/json' } : {}),
            },
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : undefined,
        });
        const data = await res.json().catch(() => ({}));
        return { res, data };
    }

    // ---------- Status tampilan ----------
    const passOk = () => passInput.value.length >= MIN_LENGTH;
    const confirmed = () => passOk() && confInput.value === passInput.value;

    function currentStep() {
        if (!passOk()) return 1;
        return confirmed() ? 3 : 2;
    }

    function render() {
        const step = finished ? 4 : currentStep();
        stepItems.forEach((li, i) => {
            const n = i + 1;
            li.classList.toggle('is-done', n < step);
            li.classList.toggle('is-active', n === step);
        });
        stepsEl.style.setProperty('--fp-p', String(Math.min(step - 1, 2) / 2));

        // Petunjuk kecocokan password saat mengetik konfirmasi.
        const typing = confInput.value.length > 0;
        matchEl.classList.toggle('is-ok', typing && confirmed());
        matchEl.classList.toggle('is-bad', typing && !confirmed());
        matchEl.textContent = !typing ? '' : (confirmed() ? 'Password cocok' : 'Konfirmasi belum sama dengan password baru');

        submitBtn.disabled = busy || finished || !confirmed();
    }

    function setError(message) {
        errEl.textContent = message || '';
        errEl.classList.toggle('is-visible', !!message);
        field.classList.toggle('has-error', !!message);
    }

    function shake() {
        card.classList.remove('is-shaking');
        void card.offsetWidth; // mulai ulang animasi
        card.classList.add('is-shaking');
    }
    card.addEventListener('animationend', (e) => {
        if (e.animationName === 'fpShake') card.classList.remove('is-shaking');
    });

    // ---------- Tampilkan / sembunyikan password ----------
    overlay.querySelectorAll('.fp-eye').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = $(btn.dataset.target);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.material-symbols-outlined').textContent = show ? 'visibility_off' : 'visibility';
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            input.focus({ preventScroll: true });
        });
    });

    [passInput, confInput].forEach((input) => {
        input.addEventListener('input', () => { setError(''); render(); });
    });

    // ---------- Simpan ----------
    async function submit(e) {
        if (e) e.preventDefault();
        if (busy || finished) return;

        if (!passOk()) {
            const msg = 'Password minimal ' + MIN_LENGTH + ' karakter.';
            setError(msg); shake(); passInput.focus();
            return;
        }
        if (!confirmed()) {
            const msg = 'Konfirmasi password tidak sama dengan password baru.';
            setError(msg); shake(); confInput.focus();
            return;
        }

        setError('');
        busy = true;
        submitBtn.classList.add('is-loading');
        submitText.textContent = 'Menyimpan…';
        render();

        let success = false;
        try {
            const { res, data } = await post('/account/password', {
                password: passInput.value,
                password_confirmation: confInput.value,
            });

            if (res.ok) {
                success = true;
                showSuccess(data);
                return;
            }

            let message;
            if (res.status === 422 && data.errors) {
                message = Object.values(data.errors)[0][0];
            } else if (res.status === 419) {
                message = 'Sesi habis. Muat ulang halaman lalu coba lagi.';
            } else {
                message = data.message || 'Terjadi kesalahan. Coba lagi.';
            }
            setError(message);
            shake();
            notify('error', message);
        } catch (err) {
            setError('Tidak dapat terhubung ke server.');
            shake();
            notify('error', 'Tidak dapat terhubung ke server.');
        } finally {
            if (!success) {
                busy = false;
                submitBtn.classList.remove('is-loading');
                submitText.textContent = 'Simpan password baru';
                render();
            }
        }
    }

    // ---------- Tampilan sukses ----------
    function showSuccess(data) {
        finished = true;
        render();
        formView.hidden = true;
        successView.hidden = false;
        card.setAttribute('tabindex', '-1');
        card.focus({ preventScroll: true });

        overlay.style.setProperty('--fp-redirect', REDIRECT_MS / 1000 + 's');
        $('fpCountdown').classList.add('is-running');
        notify('success', data.message || 'Password berhasil diganti. Silakan masuk lagi.');

        // Server sudah mengeluarkan semua sesi (termasuk sesi ini) -> lanjut ke halaman login.
        setTimeout(() => { window.location.href = data.redirect || '/'; }, REDIRECT_MS);
    }

    // ---------- Penguncian: fokus, Esc, klik latar, isi halaman ----------
    function focusables() {
        const root = finished ? successView : formView;
        return Array.from(root.querySelectorAll('button:not(:disabled), input:not(:disabled), [tabindex]:not([tabindex="-1"])'))
            .filter((el) => el.offsetParent !== null);
    }

    function lockPage() {
        document.body.classList.add('fp-lock');
        Array.from(document.body.children).forEach((el) => {
            const tag = el.tagName.toLowerCase();
            if (el === overlay || ['script', 'link', 'style', 'svg', 'noscript'].includes(tag)) return;
            if (el.id === 'pageLoader' || /toast/i.test(el.className || '')) return;
            el.setAttribute('inert', '');
        });
    }

    document.addEventListener('keydown', (e) => {
        if (!overlay.classList.contains('is-open')) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (!finished) shake();
            return;
        }

        if (e.key === 'Tab') {
            const items = focusables();
            if (!items.length) { e.preventDefault(); return; }
            const first = items[0];
            const last = items[items.length - 1];
            if (!overlay.contains(document.activeElement)) {
                e.preventDefault();
                first.focus();
            } else if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }, true);

    overlay.addEventListener('click', (e) => {
        e.stopPropagation();
        if (finished || card.contains(e.target)) return;
        // Klik di luar kartu: getarkan + ingatkan (dibatasi agar toast tidak menumpuk).
        shake();
        const now = Date.now();
        if (now - lastBackdropNudge > 2500) {
            lastBackdropNudge = now;
            notify('info', 'Buat password baru terlebih dahulu untuk melanjutkan.');
        }
    });

    formView.addEventListener('submit', submit);

    // ---------- Buka: setelah loading screen login selesai ----------
    function open() {
        lockPage();
        overlay.classList.add('is-open');
        render();

        setTimeout(() => passInput.focus({ preventScroll: true }), reduceMotion ? 0 : 700);

        const notice = overlay.dataset.notice;
        if (notice) setTimeout(() => notify('warning', notice), 900);
    }

    function whenLoaderDone(callback) {
        const loader = document.getElementById('pageLoader');
        if (!loader || !loader.classList.contains('is-active')) return callback();

        let called = false;
        const go = () => { if (!called) { called = true; observer.disconnect(); callback(); } };
        const observer = new MutationObserver(() => { if (!loader.classList.contains('is-active')) go(); });
        observer.observe(loader, { attributes: true, attributeFilter: ['class'] });
        setTimeout(go, 16000); // pengaman bila loader macet
    }

    const start = () => whenLoaderDone(() => setTimeout(open, 350));
    if (document.readyState === 'complete') start();
    else window.addEventListener('load', start, { once: true });
})();
