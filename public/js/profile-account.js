/**
 * Ganti Password & Email Pemulihan dari menu profil kanan atas.
 * - Modal dibuat dinamis, ditaruh di <body> (menu profil tetap terbuka).
 * - Modal bisa digeser lewat header, latar diburamkan.
 * - Suara notifikasi dikelola terpusat oleh notification-sound.js.
 */
(function () {
    'use strict';

    const CONFIGS = {
        password: {
            trigger: 'btnGantiPassword',
            icon: 'key',
            title: 'Ganti Password',
            url: '/account/password',
            submit: 'Simpan Password',
            success: 'Password berhasil diganti.',
            generateUrl: '/account/password/generate',
            fields: [
                { name: 'password', label: 'Password Baru (tepat 6 karakter)', type: 'text', readonly: true, generate: true },
            ],
            validate(v) {
                const e = {};
                if (!v.password) e.password = 'Klik tombol generate untuk membuat password.';
                else if (v.password.length !== 6) e.password = 'Password harus tepat 6 karakter.';
                return e;
            },
        },
        email: {
            trigger: 'btnEmailPemulihan',
            icon: 'mark_email_unread',
            title: 'Email Pemulihan',
            url: '/account/recovery-email',
            submit: 'Simpan Email',
            success: 'Email pemulihan berhasil diperbarui.',
            fields: [
                { name: 'email', label: 'Email Baru', type: 'text', inputmode: 'email', autocomplete: 'email', placeholder: 'nama@domain.com' },
                { name: 'password', label: 'Password', type: 'password', autocomplete: 'current-password' },
            ],
            validate(v) {
                const e = {};
                const em = (v.email || '').trim();
                if (!em) e.email = 'Email baru wajib diisi.';
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(em)) e.email = 'Format email tidak valid (contoh: nama@domain.com).';
                if (!v.password) e.password = 'Password wajib diisi untuk konfirmasi.';
                return e;
            },
        },
    };

    function notify(type, message) {
        if (window.Toast) Toast.show({ type, message });
        else window.NotificationSound?.play(type);
    }

    // ---------- Modal ----------
    const built = {};
    let active = null;

    function build(key) {
        const cfg = CONFIGS[key];
        const overlay = document.createElement('div');
        overlay.className = 'pa-overlay';
        const modalClass = key === 'password' ? ' pa-password-modal' : '';
        overlay.innerHTML = `
            <div class="pa-modal${modalClass}" role="dialog" aria-modal="true" aria-label="${cfg.title}">
                <div class="pa-head">
                    <span class="material-symbols-outlined">${cfg.icon}</span>
                    <span class="pa-title">${cfg.title}</span>
                    <button type="button" class="pa-close" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form novalidate>
                    <div class="pa-body">
                        ${cfg.fields.map((f) => `
                        <div class="pa-field" data-field="${f.name}">
                            <label for="pa-${key}-${f.name}">${f.label}</label>
                            <div class="pa-input-wrap${f.generate ? ' pa-password-control' : ''}">
                                <input id="pa-${key}-${f.name}" name="${f.name}" type="${f.type}"
                                    autocomplete="${f.autocomplete || 'off'}"
                                    ${f.readonly ? 'readonly tabindex="-1" aria-readonly="true"' : ''}
                                    ${f.inputmode ? `inputmode="${f.inputmode}"` : ''}
                                    ${f.placeholder ? `placeholder="${f.placeholder}"` : ''}>
                                ${f.generate ? '<button type="button" class="pa-password-action" data-gen aria-label="Buat password baru" title="Buat password baru"><span class="material-symbols-outlined">autorenew</span></button><button type="button" class="pa-password-action" data-copy aria-label="Salin password" title="Salin password"><span class="material-symbols-outlined">content_copy</span></button>' : ''}
                                ${f.type === 'password' ? '<button type="button" class="pa-eye" tabindex="-1" aria-label="Tampilkan password"><span class="material-symbols-outlined">visibility</span></button>' : ''}
                            </div>
                            <div class="pa-error"></div>
                        </div>`).join('')}
                    </div>
                    <div class="pa-foot">
                        ${key === 'password' ? '' : '<button type="button" class="pa-btn pa-btn-ghost" data-cancel>Batal</button>'}
                        <button type="submit" class="pa-btn pa-btn-primary">${cfg.submit}</button>
                    </div>
                </form>
            </div>`;
        document.body.appendChild(overlay);

        // Klik di dalam overlay tidak boleh sampai ke document, kalau tidak
        // menu profil (yang tertutup saat klik di luar) ikut tertutup.
        overlay.addEventListener('click', (e) => e.stopPropagation());
        overlay.querySelector('.pa-close').addEventListener('click', close);
        overlay.querySelector('[data-cancel]')?.addEventListener('click', close);

        overlay.querySelectorAll('.pa-eye').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('input');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('span').textContent = show ? 'visibility_off' : 'visibility';
            });
        });
        overlay.querySelectorAll('input').forEach((inp) =>
            inp.addEventListener('input', () => setError(overlay, inp.name, '')));

        overlay.querySelector('[data-gen]')?.addEventListener('click', async (e) => {
            const button = e.currentTarget;
            button.disabled = true;
            try {
                const res = await fetch(cfg.generateUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.password) throw new Error(data.message || 'Password gagal dibuat. Coba lagi.');
                overlay.querySelector('[name="password"]').value = data.password;
                setError(overlay, 'password', '');
                notify('success', 'Password unik berhasil dibuat. Salin atau unduh sebelum menyimpan.');
            } catch (err) {
                notify('error', err.message || 'Password gagal dibuat. Coba lagi.');
            } finally {
                button.disabled = false;
            }
        });
        overlay.querySelector('[data-copy]')?.addEventListener('click', async () => {
            const password = overlay.querySelector('[name="password"]').value;
            if (!password) return notify('error', 'Buat password terlebih dahulu.');
            try {
                await navigator.clipboard.writeText(password);
                notify('success', 'Password disalin.');
            } catch (e) {
                notify('error', 'Tidak dapat menyalin password dari browser ini.');
            }
        });

        makeDraggable(overlay.querySelector('.pa-modal'), overlay.querySelector('.pa-head'));
        overlay.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            submit(key, overlay);
        });
        return overlay;
    }

    function setError(overlay, name, msg) {
        const field = overlay.querySelector(`[data-field="${name}"]`);
        if (!field) return;
        field.classList.toggle('has-error', !!msg);
        field.querySelector('.pa-error').textContent = msg || '';
    }

    function makeDraggable(modal, handle) {
        let sx, sy, ox, oy;
        handle.addEventListener('pointerdown', (e) => {
            if (e.button !== undefined && e.button !== 0) return;
            if (e.target.closest('.pa-close')) return;
            const r = modal.getBoundingClientRect();
            // pindah dari posisi center (translate) ke koordinat absolut
            const overlay = modal.closest('.pa-overlay');
            overlay?.classList.add('is-dragging');   // latar berhenti blur selama digeser
            modal.classList.add('is-dragged');
            modal.style.transform = 'none';
            modal.style.left = r.left + 'px';
            modal.style.top = r.top + 'px';
            sx = e.clientX; sy = e.clientY; ox = r.left; oy = r.top;
            handle.classList.add('is-dragging');
            handle.setPointerCapture(e.pointerId);
            const move = (ev) => {
                const w = modal.offsetWidth, h = modal.offsetHeight;
                const x = Math.min(Math.max(ox + ev.clientX - sx, 8 - w + 80), window.innerWidth - 80);
                const y = Math.min(Math.max(oy + ev.clientY - sy, 0), window.innerHeight - 48);
                modal.style.left = x + 'px';
                modal.style.top = y + 'px';
            };
            const up = () => {
                overlay?.classList.remove('is-dragging');
                handle.classList.remove('is-dragging');
                handle.removeEventListener('pointermove', move);
                handle.removeEventListener('pointerup', up);
                handle.removeEventListener('pointercancel', up);
            };
            handle.addEventListener('pointermove', move);
            handle.addEventListener('pointerup', up);
            handle.addEventListener('pointercancel', up);
        });
    }

    function resetPosition(overlay) {
        const m = overlay.querySelector('.pa-modal');
        m.classList.remove('is-dragged');
        m.style.left = m.style.top = m.style.transform = '';
    }

    function open(key) {
        if (active) close();
        const overlay = built[key] || (built[key] = build(key));
        overlay.querySelector('form').reset();
        CONFIGS[key].fields.forEach((f) => setError(overlay, f.name, ''));
        overlay.querySelectorAll('.pa-eye').forEach((b) => {
            b.parentElement.querySelector('input').type = 'password';
            b.querySelector('span').textContent = 'visibility';
        });
        resetPosition(overlay);
        active = overlay;
        requestAnimationFrame(() => {
            overlay.classList.add('is-open');
            (overlay.querySelector('[data-gen]') || overlay.querySelector('input')).focus();
        });
    }

    function close() {
        if (!active) return;
        active.classList.remove('is-open', 'is-dragging');
        active = null;
    }

    async function submit(key, overlay) {
        const cfg = CONFIGS[key];
        const values = {};
        cfg.fields.forEach((f) => { values[f.name] = overlay.querySelector(`[name="${f.name}"]`).value; });

        cfg.fields.forEach((f) => setError(overlay, f.name, ''));
        const errs = cfg.validate(values);
        const names = Object.keys(errs);
        if (names.length) {
            names.forEach((n) => setError(overlay, n, errs[n]));
            overlay.querySelector(`[name="${names[0]}"]`).focus();
            notify('error', errs[names[0]]);
            return;
        }

        const btn = overlay.querySelector('.pa-btn-primary');
        btn.disabled = true;
        try {
            const res = await fetch(cfg.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify(values),
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                notify('success', data.message || cfg.success);
                close();
                // Password diganti -> server mengeluarkan semua sesi (termasuk ini): lanjut ke halaman login.
                if (data.logout && data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 1600);
                return;
            }
            if (res.status === 422 && data.errors) {
                const first = Object.keys(data.errors)[0];
                Object.entries(data.errors).forEach(([n, m]) => setError(overlay, n, m[0]));
                overlay.querySelector(`[name="${first}"]`)?.focus();
                notify('error', data.errors[first][0]);
            } else if (res.status === 419) {
                notify('error', 'Sesi habis. Muat ulang halaman lalu coba lagi.');
            } else {
                notify('error', data.message || 'Terjadi kesalahan. Coba lagi.');
            }
        } catch (e) {
            notify('error', 'Tidak dapat terhubung ke server.');
        } finally {
            btn.disabled = false;
        }
    }

    function init() {
        Object.keys(CONFIGS).forEach((key) => {
            document.getElementById(CONFIGS[key].trigger)?.addEventListener('click', (e) => {
                e.stopPropagation(); // menu profil tetap terbuka
                open(key);
            });
        });
        // Esc: tutup modal saja (fase capture, supaya menu profil tidak ikut tertutup)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && active) {
                e.stopImmediatePropagation();
                close();
            }
        }, true);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
