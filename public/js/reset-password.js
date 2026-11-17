/**
 * Pop-up buat password baru setelah pengguna menekan "Ya, ini saya" di email reset password.
 * Mirip pop-up force password (public/js/force-password.js), tetapi untuk tamu (belum login):
 *   POST /reset-password/generate  { token, email }                                   -> { password }
 *   POST /reset-password           { token, email, password, password_confirmation }  -> { message, redirect }
 * Password acak langsung terisi di kolom 1 (tanpa animasi); pengguna boleh mengganti, lalu konfirmasi di kolom 2.
 * Markup: resources/views/reset-password.blade.php · Gaya: public/css/force-password.css
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
    const genBtn = $('fpGen');
    const copyBtn = $('fpCopy');
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

    const TOKEN = overlay.dataset.token || '';
    const EMAIL = overlay.dataset.email || '';

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
    overlay.querySelectorAll('.fp-eye[data-target]').forEach((btn) => {
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

    // ---------- Password acak: langsung terisi di kolom 1, tanpa animasi ----------
    async function generate(focusConfirm) {
        if (busy || finished || genBtn.disabled) return;
        genBtn.disabled = true;
        try {
            const { res, data } = await post('/reset-password/generate', { token: TOKEN, email: EMAIL });
            if (!res.ok || !data.password) throw new Error(data.message || 'Password acak gagal dibuat.');
            passInput.type = 'text';
            passInput.value = data.password;
            confInput.value = '';
            setError('');
            const eye = overlay.querySelector('.fp-eye[data-target="fpPassword"] .material-symbols-outlined');
            if (eye) eye.textContent = 'visibility_off';
            if (focusConfirm) confInput.focus({ preventScroll: true });
        } catch (err) {
            if (focusConfirm) passInput.focus({ preventScroll: true }); // gagal: pengguna bisa mengetik sendiri
            else notify('error', err.message || 'Password acak gagal dibuat.');
        } finally {
            genBtn.disabled = false;
            render();
        }
    }

    async function copyPassword() {
        if (!passInput.value) return notify('error', 'Belum ada password untuk disalin.');
        let ok = false;
        try {
            await navigator.clipboard.writeText(passInput.value);
            ok = true;
        } catch (e) {
            passInput.select();
            try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
        }
        notify(ok ? 'success' : 'error', ok ? 'Password disalin.' : 'Tidak dapat menyalin. Catat password secara manual.');
    }

    genBtn.addEventListener('click', () => generate(true));
    copyBtn.addEventListener('click', copyPassword);

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
            const { res, data } = await post('/reset-password', {
                token: TOKEN,
                email: EMAIL,
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
        setTimeout(() => { window.location.href = data.redirect || overlay.dataset.redirect || '/'; }, REDIRECT_MS);
    }

    formView.addEventListener('submit', submit);

    // ---------- Buka langsung ----------
    function open() {
        overlay.classList.add('is-open');
        render();
        generate(true); // kolom 1 terisi password acak, fokus ke kolom konfirmasi
    }
    if (document.readyState === 'complete') open();
    else window.addEventListener('load', open, { once: true });
})();
