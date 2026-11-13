/**
 * Pop-up WAJIB ganti password di dashboard mahasiswa/dosen (password default = NIM/NIDN).
 *
 * - Efek animasi sengaja dibuat halus.
 * - Tidak bisa ditutup: tanpa tombol close, Esc diabaikan, klik latar hanya menggetarkan kartu.
 * - Fokus dikunci di dalam pop-up (Tab berputar) dan isi halaman di belakangnya dibuat `inert`.
 * - Memakai endpoint yang sama dengan menu "Ganti Password" di header (AccountController):
 *     POST /account/password/generate  -> { password }   (6 karakter acak, unik)
 *     POST /account/password           -> { message, logout, redirect }
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
    const tilesEl = $('fpTiles');
    const tiles = Array.from(tilesEl.children);
    const genBtn = $('fpGen');
    const copyBtn = $('fpCopy');
    const savedChk = $('fpSaved');
    const submitBtn = $('fpSubmit');
    const submitText = submitBtn.querySelector('.fp-submit-text');
    const errEl = $('fpError');
    const stepsEl = overlay.querySelector('.fp-steps');
    const stepItems = Array.from(stepsEl.querySelectorAll('li'));
    const formView = $('fpFormView');
    const successView = $('fpSuccessView');

    const CHARS = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*?+=';
    const REDIRECT_MS = 3200;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let password = '';
    let busy = false;
    let finished = false;
    let scrambleTimers = [];
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
    function currentStep() {
        if (!password) return 1;
        return savedChk.checked ? 3 : 2;
    }

    function render() {
        const step = finished ? 4 : currentStep();
        stepItems.forEach((li, i) => {
            const n = i + 1;
            li.classList.toggle('is-done', n < step);
            li.classList.toggle('is-active', n === step);
        });
        stepsEl.style.setProperty('--fp-p', String(Math.min(step - 1, 2) / 2));
        submitBtn.disabled = busy || finished || !(password && savedChk.checked);
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

    // ---------- Animasi kotak password: acak lalu terkunci satu per satu ----------
    function showPassword(value) {
        scrambleTimers.forEach(clearInterval);
        scrambleTimers = [];

        tiles.forEach((tile, i) => {
            tile.classList.remove('is-set', 'is-spinning');
            const finalChar = value[i] || '•';

            if (reduceMotion) {
                tile.textContent = finalChar;
                tile.classList.add('is-set');
                return;
            }

            const frames = 5 + i * 2; // kotak kanan berhenti sedikit lebih lambat
            let frame = 0;
            tile.classList.add('is-spinning');
            const timer = setInterval(() => {
                frame += 1;
                if (frame >= frames) {
                    clearInterval(timer);
                    tile.textContent = finalChar;
                    tile.classList.remove('is-spinning');
                    void tile.offsetWidth;
                    tile.classList.add('is-set');
                } else {
                    tile.textContent = CHARS[Math.floor(Math.random() * CHARS.length)];
                }
            }, 40);
            scrambleTimers.push(timer);
        });

        tilesEl.removeAttribute('aria-labelledby');
        tilesEl.setAttribute('aria-label', 'Password baru: ' + value.split('').join(' '));
    }

    // ---------- Aksi ----------
    async function generate() {
        if (busy || finished || genBtn.classList.contains('is-busy')) return;
        genBtn.classList.add('is-busy');
        genBtn.classList.remove('is-hint');
        genBtn.disabled = true;
        try {
            const { res, data } = await post('/account/password/generate');
            if (!res.ok || !data.password) throw new Error(data.message || 'Password gagal dibuat. Coba lagi.');

            password = data.password;
            showPassword(password);
            setError('');
            copyBtn.disabled = false;
            copyBtn.classList.remove('is-copied');
            savedChk.disabled = false;
            savedChk.checked = false;
        } catch (err) {
            notify('error', err.message || 'Password gagal dibuat. Coba lagi.');
        } finally {
            genBtn.classList.remove('is-busy');
            genBtn.disabled = false;
            render();
        }
    }

    async function copyPassword() {
        if (!password) return notify('error', 'Buat password terlebih dahulu.');
        let ok = false;
        try {
            await navigator.clipboard.writeText(password);
            ok = true;
        } catch (e) {
            // Cadangan untuk peramban/konteks tanpa Clipboard API.
            const area = document.createElement('textarea');
            area.value = password;
            area.setAttribute('readonly', '');
            area.style.cssText = 'position:fixed;opacity:0;pointer-events:none;';
            document.body.appendChild(area);
            area.select();
            try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
            area.remove();
            copyBtn.focus({ preventScroll: true });
        }

        if (!ok) return notify('error', 'Tidak dapat menyalin dari peramban ini. Catat password secara manual.');
        copyBtn.classList.add('is-copied');
        notify('success', 'Password disalin.');
        setTimeout(() => copyBtn.classList.remove('is-copied'), 2400);
    }

    async function submit() {
        if (busy || finished || !password || !savedChk.checked) return;
        setError('');
        busy = true;
        submitBtn.classList.add('is-loading');
        submitText.textContent = 'Menyimpan…';
        render();

        let success = false;
        try {
            const { res, data } = await post('/account/password', { password });

            if (res.ok) {
                success = true;
                showSuccess(data);
                return;
            }

            let message;
            if (res.status === 422 && data.errors) {
                message = Object.values(data.errors)[0][0];
                genBtn.classList.add('is-hint'); // arahkan ke tombol generate untuk membuat ulang
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
            notify('info', 'Ganti password terlebih dahulu untuk melanjutkan.');
        }
    });

    genBtn.addEventListener('click', generate);
    copyBtn.addEventListener('click', copyPassword);
    savedChk.addEventListener('change', render);
    submitBtn.addEventListener('click', submit);
    overlay.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target === savedChk) { e.preventDefault(); savedChk.click(); }
    });

    // ---------- Buka: setelah loading screen login selesai ----------
    function open() {
        lockPage();
        overlay.classList.add('is-open');
        render();

        setTimeout(() => {
            genBtn.classList.add('is-hint');
            genBtn.focus({ preventScroll: true });
        }, reduceMotion ? 0 : 1100);

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
