/**
 * Ganti Password dari menu profil (tombol #btnGantiPassword di header & halaman Profil).
 *
 * Alur:
 *   1. choose  : pilih "Konfirmasi lewat email" atau "Kembalikan ke default (NIM/NIDN)" -> tombol kirim.
 *   2. wait    : menunggu pengguna menekan "Ya, ini saya" di email. Status dicek tiap 2 detik; juga langsung dicek saat
 *                tab kembali aktif atau tab konfirmasi di browser yang sama memberi sinyal (BroadcastChannel).
 *   3. create  : terbuka SEKETIKA begitu email terkonfirmasi (tanpa jeda buatan): 2 input, kolom pertama bisa acak /
 *                diketik sendiri / disalin — sama seperti pop-up wajib ganti password.
 *   4. done    : semua perangkat dikeluarkan -> diarahkan ke halaman masuk.
 *
 * Endpoint: POST /account/password/verify · GET /account/password/verify/status · POST /account/password/default
 *           POST /account/password/generate · POST /account/password (AccountController, wajib terverifikasi email)
 *
 * Markup: resources/views/partials/password-change-modal.blade.php · Gaya: public/css/password-change.css
 */
(function () {
    'use strict';

    const overlay = document.getElementById('pcOverlay');
    if (!overlay) return;

    const $ = (id) => document.getElementById(id);
    const card = $('pcCard');
    const views = {};
    overlay.querySelectorAll('[data-pc-view]').forEach((el) => { views[el.dataset.pcView] = el; });

    const HAS_EMAIL = overlay.dataset.hasEmail === '1';
    const CAN_DEFAULT = overlay.dataset.canDefault === '1';
    const POLL_MS = 2000;
    const POLL_HIDDEN_MS = 5000;
    const REDIRECT_MS = 1600;
    const MIN_LENGTH = 6;

    let current = 'choose';
    let busy = false;
    let finished = false;
    let polling = false;
    let pollTimer = null;
    let tickTimer = null;
    let expireAt = 0;      // ms; 0 = belum diketahui
    let resendAt = 0;      // ms
    let lastFocus = null;

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const notify = (type, message) => { if (window.Toast) window.Toast.show({ type, message }); };

    async function api(method, url, body) {
        const res = await fetch(url, {
            method,
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

    const firstError = (data) => (data.errors ? Object.values(data.errors)[0][0] : data.message);

    // ------------------------------------------------------------------ tampilan
    function show(name) {
        current = name;
        Object.entries(views).forEach(([key, el]) => { el.hidden = key !== name; });
        const title = views[name].querySelector('.pc-title');
        card.removeAttribute('aria-labelledby');
        card.setAttribute('aria-label', title ? title.textContent : 'Ganti password');
        if (name !== 'wait') stopWait();
    }

    function setError(view, message) {
        const el = overlay.querySelector('[data-pc-error="' + view + '"]');
        if (el) el.textContent = message || '';
        if (view === 'create') views.create.querySelector('.pc-field').classList.toggle('has-error', !!message);
    }

    function setBusy(on, button, busyText) {
        busy = on;
        if (!button) return;
        if (on) {
            button.dataset.label = button.textContent;
            button.textContent = busyText || 'Memproses…';
            button.disabled = true;
        } else {
            button.textContent = button.dataset.label || button.textContent;
        }
    }

    // ------------------------------------------------------------------ 1. pilih cara
    const chooseForm = views.choose;
    const radios = Array.from(chooseForm.querySelectorAll('input[name="pc_method"]'));
    const confirmBox = chooseForm.querySelector('[data-pc-confirm]');
    const confirmInput = $('pcConfirmIdentity');
    const chooseBtn = chooseForm.querySelector('[data-pc-submit="choose"]');
    const method = () => (radios.find((r) => r.checked) || {}).value || '';

    function applyMethod() {
        radios.forEach((r) => r.closest('.pc-option').classList.toggle('is-selected', r.checked));
        const m = method();
        confirmBox.hidden = m !== 'default';
        chooseBtn.textContent = m === 'default' ? 'Kembalikan ke default' : 'Kirim konfirmasi ke email';
        chooseBtn.disabled = !m || busy;
        setError('choose', '');
    }

    function resetChoose() {
        radios.forEach((r) => { r.checked = false; });
        const preferred = HAS_EMAIL ? 'email' : (CAN_DEFAULT ? 'default' : '');
        const target = radios.find((r) => r.value === preferred);
        if (target) target.checked = true;
        confirmInput.value = '';
        busy = false;
        applyMethod();
    }

    radios.forEach((r) => r.addEventListener('change', applyMethod));

    chooseForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (busy) return;
        const m = method();
        if (m === 'email') sendEmail(false);
        else if (m === 'default') resetToDefault();
    });

    async function resetToDefault() {
        const identity = confirmInput.value.trim();
        if (!identity) {
            setError('choose', 'Ketik ulang ' + overlay.dataset.label + ' untuk konfirmasi.');
            confirmInput.focus();
            return;
        }
        setError('choose', '');
        setBusy(true, chooseBtn, 'Memproses…');
        try {
            const { res, data } = await api('POST', '/account/password/default', { confirm_identity: identity });
            if (res.ok) {
                finish(data, 'Password dikembalikan ke default', 'Mengalihkan ke halaman masuk. Buat password baru setelah masuk.');
                return;
            }
            setError('choose', res.status === 429
                ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                : (firstError(data) || 'Gagal mengembalikan password. Coba lagi.'));
        } catch (err) {
            setError('choose', 'Tidak dapat terhubung ke server.');
        }
        setBusy(false, chooseBtn);
        chooseBtn.disabled = false;
    }

    // ------------------------------------------------------------------ 2. menunggu konfirmasi email
    const waitView = views.wait;
    const waitBox = waitView.querySelector('.pc-wait');
    const waitText = waitView.querySelector('[data-pc-wait-text]');
    const waitTimer = waitView.querySelector('[data-pc-wait-timer]');
    const resendBtn = waitView.querySelector('[data-pc-resend]');

    async function sendEmail(isResend) {
        if (busy) return;
        const button = isResend ? resendBtn : chooseBtn;
        setError('choose', '');
        setBusy(true, button, 'Mengirim…');
        try {
            const { res, data } = await api('POST', '/account/password/verify');
            if (res.ok) {
                setBusy(false, button);
                startWait({ email: data.email, expiresIn: data.expires_in, resendIn: data.resend_after });
                if (isResend) notify('success', 'Email konfirmasi dikirim ulang.');
                return;
            }
            // 429 dengan retry_after: email baru saja dikirim dari sesi lain (pesan server memuat sisa detiknya).
            // 429 tanpa retry_after: batas laju permintaan (pesan bawaan berbahasa Inggris, jadi diganti).
            const message = res.status === 429 && !data.retry_after
                ? 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.'
                : (data.message || 'Email konfirmasi gagal dikirim. Coba lagi.');
            if (isResend) notify('error', message); else setError('choose', message);
        } catch (err) {
            const message = 'Tidak dapat terhubung ke server.';
            if (isResend) notify('error', message); else setError('choose', message);
        }
        setBusy(false, button);
        if (!isResend) chooseBtn.disabled = false;
        else updateTick();
    }

    function setWait(text, bad) {
        waitText.textContent = text;
        waitBox.classList.toggle('is-bad', !!bad);
    }

    function startWait({ email, expiresIn, resendIn } = {}) {
        if (email) waitView.querySelector('[data-pc-email]').textContent = email;
        if (expiresIn) expireAt = Date.now() + expiresIn * 1000;
        resendAt = Date.now() + (resendIn || 0) * 1000;
        show('wait');
        setWait('Menunggu konfirmasi…', false);
        clearInterval(tickTimer);
        tickTimer = setInterval(updateTick, 1000);
        updateTick();
        poll();
    }

    function stopWait() {
        clearTimeout(pollTimer);
        clearInterval(tickTimer);
        pollTimer = tickTimer = null;
    }

    const mmss = (s) => String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');

    function updateTick() {
        if (current !== 'wait') return;
        if (expireAt) {
            const left = Math.max(0, Math.ceil((expireAt - Date.now()) / 1000));
            waitTimer.textContent = left > 0 ? 'Tautan berlaku ' + mmss(left) : '';
        } else {
            waitTimer.textContent = '';
        }
        const wait = Math.max(0, Math.ceil((resendAt - Date.now()) / 1000));
        resendBtn.disabled = busy || wait > 0;
        resendBtn.textContent = wait > 0 ? 'Kirim ulang email (' + wait + ')' : 'Kirim ulang email';
    }

    /** Cek status; begitu terkonfirmasi LANGSUNG buka modal password baru. */
    async function poll() {
        clearTimeout(pollTimer);
        if (current !== 'wait' || overlay.hidden || polling) return;
        polling = true;
        let again = true;
        try {
            const { res, data } = await api('GET', '/account/password/verify/status');
            if (current !== 'wait') { again = false; return; }
            if (res.ok) {
                if (data.status === 'verified') { again = false; showCreate(); return; }
                if (data.status === 'pending') {
                    if (data.expires_in) expireAt = Date.now() + data.expires_in * 1000;
                    if (data.resend_in != null) resendAt = Date.now() + data.resend_in * 1000;
                    if (data.email) waitView.querySelector('[data-pc-email]').textContent = data.email;
                } else if (data.status === 'expired') {
                    again = false;
                    resendAt = 0;
                    setWait('Tautan konfirmasi sudah kedaluwarsa. Kirim ulang email untuk melanjutkan.', true);
                    updateTick();
                } else {
                    again = false;
                    show('choose');
                }
            } else if (res.status === 401 || res.status === 419) {
                again = false;
                setWait('Sesi habis. Muat ulang halaman lalu coba lagi.', true);
            }
        } catch (err) {
            // Jaringan putus sebentar: coba lagi pada giliran berikutnya.
        } finally {
            polling = false;
            if (again && current === 'wait') pollTimer = setTimeout(poll, document.hidden ? POLL_HIDDEN_MS : POLL_MS);
        }
    }

    resendBtn.addEventListener('click', () => sendEmail(true));
    waitView.querySelector('[data-pc-back]').addEventListener('click', () => { show('choose'); resetChoose(); });

    // Cek segera saat pengguna kembali ke tab ini / tab konfirmasi (browser yang sama) memberi sinyal.
    document.addEventListener('visibilitychange', () => { if (!document.hidden && current === 'wait') poll(); });
    window.addEventListener('focus', () => { if (current === 'wait') poll(); });
    try {
        const channel = new BroadcastChannel('sida-password-change');
        channel.addEventListener('message', () => { if (current === 'wait') poll(); });
    } catch (e) { /* browser lama: cukup mengandalkan pengecekan berkala */ }

    // ------------------------------------------------------------------ 3. buat password baru
    const createForm = views.create;
    const passInput = $('pcPassword');
    const confInput = $('pcConfirm');
    const matchEl = createForm.querySelector('[data-pc-match]');
    const createBtn = createForm.querySelector('[data-pc-submit="create"]');
    const genBtn = createForm.querySelector('[data-pc-gen]');
    const copyBtn = createForm.querySelector('[data-pc-copy]');
    const stepItems = Array.from(createForm.querySelectorAll('.pc-steps li'));

    const passOk = () => passInput.value.length >= MIN_LENGTH;
    const confirmed = () => passOk() && confInput.value === passInput.value;

    function renderCreate() {
        const step = !passOk() ? 1 : (confirmed() ? 3 : 2);
        stepItems.forEach((li, i) => {
            li.classList.toggle('is-done', i + 1 < step);
            li.classList.toggle('is-active', i + 1 === step);
        });
        const typing = confInput.value.length > 0;
        matchEl.classList.toggle('is-ok', typing && confirmed());
        matchEl.classList.toggle('is-bad', typing && !confirmed());
        matchEl.textContent = !typing ? '' : (confirmed() ? 'Password cocok' : 'Konfirmasi belum sama dengan password baru');
        createBtn.disabled = busy || !confirmed();
    }

    function setEye(btn, visible) {
        const input = $(btn.dataset.pcEye);
        input.type = visible ? 'text' : 'password';
        btn.querySelector('.material-symbols-outlined').textContent = visible ? 'visibility_off' : 'visibility';
        const label = visible ? 'Sembunyikan password' : 'Tampilkan password';
        btn.setAttribute('aria-label', label);
        btn.title = label;
    }

    createForm.querySelectorAll('[data-pc-eye]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = $(btn.dataset.pcEye);
            setEye(btn, input.type === 'password');
            input.focus({ preventScroll: true });
        });
    });

    [passInput, confInput].forEach((input) => input.addEventListener('input', () => { setError('create', ''); renderCreate(); }));

    /** Password acak: langsung terisi di kolom pertama (tetap bisa diubah / diketik sendiri). */
    async function generate(focusConfirm) {
        if (genBtn.disabled) return;
        genBtn.disabled = true;
        try {
            const { res, data } = await api('POST', '/account/password/generate');
            if (!res.ok || !data.password) throw new Error(data.message || 'Password acak gagal dibuat.');
            passInput.value = data.password;
            setEye(createForm.querySelector('[data-pc-eye="pcPassword"]'), true);
            confInput.value = '';
            setError('create', '');
            if (focusConfirm) confInput.focus({ preventScroll: true });
        } catch (err) {
            if (focusConfirm) passInput.focus({ preventScroll: true }); // gagal: pengguna bisa mengetik sendiri
            else notify('error', err.message || 'Password acak gagal dibuat.');
        } finally {
            genBtn.disabled = false;
            renderCreate();
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

    /** Email sudah terkonfirmasi -> tampilkan modal password baru SEKETIKA. */
    function showCreate() {
        show('create');
        passInput.value = '';
        confInput.value = '';
        setError('create', '');
        setEye(createForm.querySelector('[data-pc-eye="pcPassword"]'), true);
        setEye(createForm.querySelector('[data-pc-eye="pcConfirm"]'), false);
        renderCreate();
        generate(true);
    }

    createForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (busy) return;
        if (!passOk()) { setError('create', 'Password minimal ' + MIN_LENGTH + ' karakter.'); passInput.focus(); return; }
        if (!confirmed()) { setError('create', 'Konfirmasi password tidak sama dengan password baru.'); confInput.focus(); return; }

        setError('create', '');
        setBusy(true, createBtn, 'Menyimpan…');
        try {
            const { res, data } = await api('POST', '/account/password', {
                password: passInput.value,
                password_confirmation: confInput.value,
            });
            if (res.ok) {
                finish(data, 'Password berhasil diganti', 'Semua perangkat dikeluarkan. Mengalihkan ke halaman masuk…');
                return;
            }
            if (res.status === 403 && data.verification_required) {
                // Izin dari email sudah habis: ulangi dari awal.
                setBusy(false, createBtn);
                show('choose');
                resetChoose();
                setError('choose', 'Konfirmasi email sudah kedaluwarsa. Kirim konfirmasi lagi untuk melanjutkan.');
                return;
            }
            setError('create', res.status === 419 ? 'Sesi habis. Muat ulang halaman lalu coba lagi.' : (firstError(data) || 'Terjadi kesalahan. Coba lagi.'));
        } catch (err) {
            setError('create', 'Tidak dapat terhubung ke server.');
        }
        setBusy(false, createBtn);
        renderCreate();
    });

    // ------------------------------------------------------------------ 4. selesai
    function finish(data, title, text) {
        finished = true;
        busy = false;
        show('done');
        views.done.querySelector('[data-pc-done-title]').textContent = title;
        views.done.querySelector('[data-pc-done-text]').textContent = text;
        notify('success', data.message || title);
        // Server sudah mengeluarkan semua sesi (termasuk sesi ini): lanjut ke halaman masuk.
        setTimeout(() => { window.location.href = data.redirect || '/'; }, REDIRECT_MS);
    }

    // ------------------------------------------------------------------ buka / tutup
    async function open() {
        if (!overlay.hidden) return;
        lastFocus = document.activeElement;
        finished = false;
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        show('choose');
        resetChoose();
        (radios.find((r) => r.checked) || chooseBtn).focus({ preventScroll: true });

        // Sudah ada permintaan yang berjalan / sudah terkonfirmasi? Lanjutkan dari situ.
        try {
            const { res, data } = await api('GET', '/account/password/verify/status');
            if (!res.ok || overlay.hidden || current !== 'choose') return;
            if (data.status === 'verified') showCreate();
            else if (data.status === 'pending') startWait({ email: data.email, expiresIn: data.expires_in, resendIn: data.resend_in });
        } catch (e) { /* tampilkan pilihan cara seperti biasa */ }
    }

    function close() {
        if (overlay.hidden || busy || finished) return;
        overlay.hidden = true;
        document.body.style.overflow = '';
        stopWait();
        lastFocus?.focus?.({ preventScroll: true });
    }

    overlay.addEventListener('click', (e) => {
        e.stopPropagation(); // supaya menu profil di belakangnya tidak ikut tertutup
        if (e.target === overlay || e.target.closest('[data-pc-close]')) close();
    });

    document.addEventListener('keydown', (e) => {
        if (overlay.hidden) return;
        if (e.key === 'Escape') {
            e.stopImmediatePropagation();
            close();
            return;
        }
        if (e.key === 'Tab') {
            const items = Array.from(card.querySelectorAll('button:not(:disabled), input:not(:disabled):not([type="radio"]), input[type="radio"]:checked'))
                .filter((el) => el.offsetParent !== null);
            if (!items.length) return;
            const first = items[0];
            const last = items[items.length - 1];
            if (!card.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
            else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    }, true);

    const trigger = $('btnGantiPassword');
    trigger?.addEventListener('click', (e) => {
        e.stopPropagation(); // menu profil tetap terbuka (sama seperti modal lain di menu ini)
        open();
    });
})();
