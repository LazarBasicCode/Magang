/**
 * Sistem toast notifikasi SIDA.
 *
 * Pakai di halaman manapun (setelah include toast.css & toast.js):
 *   Toast.show({ type: 'success', message: 'Data berhasil disimpan.' });
 *   Toast.show({ type: 'error', title: 'Gagal', message: 'Username atau password salah.' });
 *
 * type: 'success' | 'info' | 'warning' | 'error'
 */
(function () {
    const ICONS = {
        success: '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>',
        info: '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>',
        warning: '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>',
        error: '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>',
    };
    const CLOSE_ICON = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"></path></svg>';

    const DURATION = 5000;
    let container = null;
    let sound = null;

    function getContainer() {
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        return container;
    }

    function getSound() {
        if (!sound) {
            try {
                // Path relatif ke /public, jadi otomatis benar dari halaman manapun.
                sound = new Audio('/sounds/notify.wav');
                sound.volume = 0.35;
            } catch (e) { /* abaikan kalau Audio tidak tersedia */ }
        }
        return sound;
    }

    function playSound() {
        const s = getSound();
        if (!s) return;
        try {
            // Browser modern memblokir autoplay sebelum user berinteraksi
            // dengan halaman sama sekali — kalau gagal, cukup diabaikan
            // (toast visual tetap muncul, cuma tanpa suara).
            s.currentTime = 0;
            const p = s.play();
            if (p && p.catch) p.catch(() => {});
        } catch (e) { /* no-op */ }
    }

    /**
     * FLIP: catat posisi semua toast yang sedang tampil SEBELUM DOM berubah,
     * supaya sesudah toast baru disisipkan, toast lama bisa dianimasikan
     * geser mulus ke posisi barunya alih-alih "meloncat" instan.
     */
    function captureRects() {
        const items = getContainer().querySelectorAll('.toast-item:not(.is-leaving)');
        const map = new Map();
        items.forEach((el) => map.set(el, el.getBoundingClientRect()));
        return map;
    }

    function playShiftAnimation(oldRects) {
        const items = getContainer().querySelectorAll('.toast-item:not(.is-leaving)');
        items.forEach((el) => {
            const before = oldRects.get(el);
            if (!before) return; // toast yang baru saja ditambahkan, bukan yang digeser
            const after = el.getBoundingClientRect();
            const deltaY = before.top - after.top;
            if (Math.abs(deltaY) < 1) return;

            el.style.transition = 'none';
            el.style.transform = `translateY(${deltaY}px)`;
            el.classList.add('is-blur-pulse');

            // Paksa reflow supaya browser "mencatat" posisi awal di atas
            // sebelum transisi ke posisi akhir dimulai.
            void el.offsetHeight;

            requestAnimationFrame(() => {
                el.classList.add('is-shifting');
                el.style.transform = '';
                setTimeout(() => {
                    el.classList.remove('is-shifting', 'is-blur-pulse');
                    el.style.transition = '';
                }, 340);
            });
        });
    }

    function dismiss(el) {
        if (!el || el.classList.contains('is-leaving')) return;
        clearTimeout(el._toastTimer);
        el.classList.remove('is-entering');
        el.classList.add('is-leaving');
        el.addEventListener('animationend', () => el.remove(), { once: true });
        // fallback kalau animationend tidak terpicu (mis. tab tidak aktif)
        setTimeout(() => el.remove(), 500);
    }

    function show({ type = 'info', title = '', message = '', duration = DURATION, sound: withSound = true } = {}) {
        if (!message && !title) return null;

        const cont = getContainer();
        const oldRects = captureRects();

        const el = document.createElement('div');
        el.className = `toast-item t-${type} is-entering`;
        el.setAttribute('role', 'status');
        el.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">${ICONS[type] || ICONS.info}</div>
                <div class="toast-text">${title ? `<strong>${escapeHtml(title)}</strong>` : ''}${escapeHtml(message)}</div>
            </div>
            <div class="toast-close" role="button" aria-label="Tutup notifikasi">${CLOSE_ICON}</div>
            <div class="toast-progress"></div>
        `;

        el.querySelector('.toast-close').addEventListener('click', () => dismiss(el));

        cont.appendChild(el); // column-reverse => otomatis muncul di atas tumpukan (dekat layar), tanpa dobel/tumpang tindih
        el.addEventListener('animationend', () => el.classList.remove('is-entering'), { once: true });

        playShiftAnimation(oldRects);

        if (withSound) playSound();

        el._toastTimer = setTimeout(() => dismiss(el), duration);

        return el;
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    window.Toast = { show };
})();
