/**
 * Engine lonceng notifikasi SIDA. Cukup include file ini di halaman yang
 * sudah punya markup standar (#notifDropdown, #notifToggleBtn, #notifPanel,
 * #notifListWrap, #notifEmpty, #notifMarkAllBtn) — semuanya jalan otomatis,
 * tidak perlu tulis JS lagi per halaman.
 *
 * Notifikasi baru yang belum pernah terlihat sejak halaman dibuka akan
 * memicu Toast + suara (lihat toast.js). Notifikasi yang sudah ada saat
 * halaman pertama kali dimuat TIDAK di-toast ulang, supaya tidak "banjir"
 * toast tiap kali buka halaman.
 */
(function () {
    const POLL_INTERVAL = 20000; // 20 detik

    const dropdown = document.getElementById('notifDropdown');
    const toggleBtn = document.getElementById('notifToggleBtn');
    const panel = document.getElementById('notifPanel');
    const listWrap = document.getElementById('notifListWrap');
    const emptyState = document.getElementById('notifEmpty');
    const markAllBtn = document.getElementById('notifMarkAllBtn');

    // Kalau halaman ini tidak punya markup notifikasi (misal halaman publik
    // seperti login), engine ini tidak melakukan apa pun.
    if (!dropdown || !toggleBtn || !panel) return;

    let seenIds = null; // null = belum pernah polling sama sekali (initial load)

    const COLOR_TO_TOAST_TYPE = {
        success: 'success',
        danger: 'error',
        warning: 'warning',
        info: 'info',
        primary: 'info',
    };

    function ensureDot() {
        let dot = document.getElementById('notifDot');
        if (!dot) {
            dot = document.createElement('span');
            dot.id = 'notifDot';
            dot.className = 'dot';
            toggleBtn.appendChild(dot);
        }
        return dot;
    }

    function removeDot() {
        document.getElementById('notifDot')?.remove();
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    function renderItem(n) {
        return `
            <a href="#" class="notif-item ${n.is_unread ? 'is-unread' : ''}" data-id="${n.id}">
                <span class="notif-item-icon c-${escapeHtml(n.color)}">
                    <span class="material-symbols-outlined">${escapeHtml(n.icon)}</span>
                </span>
                <span class="notif-item-body">
                    <span class="notif-item-title">${escapeHtml(n.title)}</span>
                    <span class="notif-item-desc">${escapeHtml(n.description)}</span>
                    <span class="notif-item-time">${escapeHtml(n.time_rel)}</span>
                </span>
                ${n.is_unread ? '<span class="notif-item-dot" aria-hidden="true"></span>' : ''}
            </a>
        `;
    }

    function render(notifications, unreadCount) {
        if (notifications.length === 0) {
            listWrap.innerHTML = '';
            listWrap.hidden = true;
            emptyState.hidden = false;
        } else {
            let html = '';
            let lastGroup = null;
            notifications.forEach((n) => {
                if (n.group !== lastGroup) {
                    html += `<div class="notif-group-label">${escapeHtml(n.group)}</div>`;
                    lastGroup = n.group;
                }
                html += renderItem(n);
            });
            listWrap.innerHTML = html;
            listWrap.hidden = false;
            emptyState.hidden = true;
        }

        if (unreadCount > 0) ensureDot(); else removeDot();
    }

    async function poll() {
        try {
            const res = await fetch('/notifications', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const json = await res.json();
            const notifications = json.notifications || [];

            if (seenIds === null) {
                // Initial load: cukup render, jangan toast apa pun supaya
                // tidak membanjiri user dengan notifikasi-notifikasi lama.
                seenIds = new Set(notifications.map((n) => n.id));
            } else {
                const freshUnread = notifications.filter((n) => n.is_unread && !seenIds.has(n.id));
                freshUnread.forEach((n) => {
                    if (window.Toast) {
                        window.Toast.show({
                            type: COLOR_TO_TOAST_TYPE[n.color] || 'info',
                            title: n.title,
                            message: n.description,
                        });
                    }
                });
                notifications.forEach((n) => seenIds.add(n.id));
            }

            render(notifications, json.unread_count || 0);
        } catch (e) {
            // Diam saja kalau polling gagal (mis. koneksi putus sebentar) —
            // tidak perlu ganggu user dengan error di lonceng notifikasi.
        }
    }

    function closePanel() {
        dropdown.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
    }

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        // Tutup dropdown lain (filter/form) yang mungkin ada di halaman ini.
        document.querySelectorAll('.dropdown.is-open, [data-dropdown].is-open').forEach((d) => {
            if (d !== dropdown) d.classList.remove('is-open');
        });

        const willOpen = !dropdown.classList.contains('is-open');
        dropdown.classList.toggle('is-open', willOpen);
        toggleBtn.setAttribute('aria-expanded', String(willOpen));
        panel.setAttribute('aria-hidden', String(!willOpen));

        if (willOpen) poll(); // refresh begitu dibuka, biar datanya selalu terbaru
    });

    panel.addEventListener('click', (e) => e.stopPropagation());
    document.addEventListener('click', closePanel);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePanel(); });

    markAllBtn?.addEventListener('click', async () => {
        document.querySelectorAll('.notif-item.is-unread').forEach((item) => item.classList.remove('is-unread'));
        document.querySelectorAll('.notif-item-dot').forEach((dot) => dot.remove());
        removeDot();
        try {
            await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
        } catch (e) { /* UI sudah terlanjur update optimistis, aman diabaikan */ }
    });

    // Klik satu notifikasi -> tandai dibaca (tanpa reload / tanpa menutup panel)
    listWrap.addEventListener('click', (e) => {
        const item = e.target.closest('.notif-item');
        if (!item) return;
        e.preventDefault();
        if (!item.classList.contains('is-unread')) return;

        item.classList.remove('is-unread');
        item.querySelector('.notif-item-dot')?.remove();
        if (!document.querySelector('.notif-item.is-unread')) removeDot();

        fetch(`/notifications/${item.dataset.id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        }).catch(() => {});
    });

    poll();
    setInterval(poll, POLL_INTERVAL);
})();
