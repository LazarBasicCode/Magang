/**
 * public/js/script.js
 * =====================================================================
 * JS BERSAMA untuk semua halaman SIDA (Kemahasiswaan, LPPM Mahasiswa, dst).
 *
 * Isinya HANYA bagian yang sama persis di semua halaman:
 *   - Custom dropdown            (SIDA.dropdown)
 *   - Sidebar drawer (buka/tutup) (SIDA.sidebar)
 *   - Dark mode toggle           (SIDA.theme)
 *   - Panel notifikasi           (SIDA.notif)
 *   - Modal generik: buka/tutup/drag (SIDA.modal.attachDrag, dst)
 *   - Live search filter helper  (SIDA.filter)
 *   - Helper umum: esc, initials, avatarColor, csrfToken (SIDA.util)
 *   - CRUD row helper (insert/update/remove baris tabel) (SIDA.table)
 *
 * Yang TIDAK ada di sini (karena beda tiap halaman) dan tetap ditulis
 * di masing-masing file Blade:
 *   - Endpoint API (create/update/delete)
 *   - Field-field form & payload yang dikirim
 *   - Bentuk kolom tabel (buildRowHTML)
 *   - Field filter tambahan yang spesifik halaman
 *
 * CARA PAKAI (lihat contoh lengkap di komentar bawah file ini):
 *   1. Load file ini di Blade:  <script src="{{ asset('js/script.js') }}"></script>
 *   2. Panggil SIDA.init({...}) dengan konfigurasi ringan khusus halaman itu.
 * =====================================================================
 */

(function (window, document) {
    'use strict';

    const SIDA = {};

    // ---------------------------------------------------------------
    // UTIL
    // ---------------------------------------------------------------
    SIDA.util = {
        csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        },

        esc(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        },

        initials(name) {
            return (name || '')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((w) => w[0])
                .join('')
                .toUpperCase() || '-';
        },

        avatarColor(id) {
            const colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
            return colors[Number(id) % colors.length];
        },
    };

    // ---------------------------------------------------------------
    // CUSTOM DROPDOWN  (dipakai di filter bar & modal form)
    // ---------------------------------------------------------------
    SIDA.dropdown = {
        allNodes: [],

        init() {
            this.allNodes = Array.from(document.querySelectorAll('[data-dropdown]'));

            this.allNodes.forEach((dropdown) => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const valueEl = dropdown.querySelector('.dropdown-value');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const options = dropdown.querySelectorAll('.dropdown-option');

                trigger?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const wasOpen = dropdown.classList.contains('is-open');
                    this.closeAll();
                    if (!wasOpen) dropdown.classList.add('is-open');
                });

                options.forEach((option) => {
                    option.addEventListener('click', () => {
                        options.forEach((o) => o.classList.remove('is-selected'));
                        option.classList.add('is-selected');
                        if (valueEl) valueEl.textContent = option.textContent.trim();
                        if (hiddenInput) hiddenInput.value = option.dataset.value;
                        dropdown.classList.remove('is-open');
                    });
                });
            });

            document.addEventListener('click', () => this.closeAll());
        },

        closeAll() {
            this.allNodes.forEach((d) => d.classList.remove('is-open'));
        },

        // Set nilai terpilih dari luar (mis. saat buka modal Edit)
        select(dropdownEl, value, placeholderIfEmpty) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll('.dropdown-option');
            const valueEl = dropdownEl.querySelector('.dropdown-value');
            const hiddenInput = dropdownEl.querySelector('input[type="hidden"]');
            let matched = false;

            options.forEach((o) => {
                const isMatch = o.dataset.value === String(value);
                o.classList.toggle('is-selected', isMatch);
                if (isMatch) {
                    if (valueEl) valueEl.textContent = o.textContent.trim();
                    matched = true;
                }
            });

            if (hiddenInput) hiddenInput.value = matched ? value : '';
            if (!matched && options.length && valueEl) {
                valueEl.textContent = placeholderIfEmpty || options[0].textContent.trim();
            }
        },

        // Kembalikan dropdown ke opsi pertama (dipakai tombol "Reset Filter")
        reset(dropdownEl) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll('.dropdown-option');
            const valueEl = dropdownEl.querySelector('.dropdown-value');
            const hiddenInput = dropdownEl.querySelector('input[type="hidden"]');
            options.forEach((o, i) => {
                o.classList.toggle('is-selected', i === 0);
                if (i === 0) {
                    if (valueEl) valueEl.textContent = o.textContent.trim();
                    if (hiddenInput) hiddenInput.value = o.dataset.value;
                }
            });
        },
    };

    // ---------------------------------------------------------------
    // SIDEBAR DRAWER
    // ---------------------------------------------------------------
    SIDA.sidebar = {
        init() {
            const sidebar = document.querySelector('.app-sidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar) return;

            const open = () => {
                sidebar.classList.add('is-open');
                overlay?.classList.add('is-active');
                document.body.style.overflow = 'hidden';
            };
            const close = () => {
                sidebar.classList.remove('is-open');
                overlay?.classList.remove('is-active');
                document.body.style.overflow = '';
            };

            toggleBtn?.addEventListener('click', open);
            closeBtn?.addEventListener('click', close);
            overlay?.addEventListener('click', close);
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) close();
            });
        },
    };

    // ---------------------------------------------------------------
    // DARK MODE
    // ---------------------------------------------------------------
    SIDA.theme = {
        init() {
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeIcon');
            const saved = localStorage.getItem('theme');

            if (saved === 'dark') {
                document.body.classList.add('dark-mode');
                if (icon) icon.textContent = 'light_mode';
            }

            btn?.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                if (icon) icon.textContent = isDark ? 'light_mode' : 'dark_mode';
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        },
    };

    // ---------------------------------------------------------------
    // PANEL NOTIFIKASI
    // (UI generik. Sumber data notifikasi nanti disambungkan lewat
    //  SIDA.notif.load(fetchFn) begitu endpoint controllernya siap.)
    // ---------------------------------------------------------------
    SIDA.notif = {
        init() {
            const dropdown = document.getElementById('notifDropdown');
            const toggleBtn = document.getElementById('notifToggleBtn');
            const panel = document.getElementById('notifPanel');
            const dot = document.getElementById('notifDot');
            const markAllBtn = document.getElementById('notifMarkAllBtn');
            if (!dropdown || !toggleBtn || !panel) return;

            const close = () => {
                dropdown.classList.remove('is-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                panel.setAttribute('aria-hidden', 'true');
            };

            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                SIDA.dropdown.closeAll(); // tutup dropdown filter/form lain
                const willOpen = !dropdown.classList.contains('is-open');
                dropdown.classList.toggle('is-open', willOpen);
                toggleBtn.setAttribute('aria-expanded', String(willOpen));
                panel.setAttribute('aria-hidden', String(!willOpen));
            });

            panel.addEventListener('click', (e) => e.stopPropagation());
            document.addEventListener('click', close);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });

            // Tandai semua dibaca — efek visual saja sampai endpoint controller ada
            markAllBtn?.addEventListener('click', () => {
                document.querySelectorAll('.notif-item.is-unread').forEach((item) => {
                    item.classList.remove('is-unread');
                });
                document.getElementById('notifDot')?.remove();
            });
        },
    };

    // ---------------------------------------------------------------
    // MODAL GENERIK: buka/tutup + drag (khusus mekanismenya, bukan isi form)
    // ---------------------------------------------------------------
    SIDA.modal = {
        /**
         * @param {Object} opts
         * @param {HTMLElement} opts.backdrop
         * @param {HTMLElement} opts.card
         * @param {HTMLElement} opts.closeBtn
         * @param {HTMLElement} opts.cancelBtn
         * @param {HTMLElement} opts.dragHandle
         */
        attach({ backdrop, card, closeBtn, cancelBtn, dragHandle }) {
            function close() {
                backdrop?.classList.remove('is-active');
                card?.classList.remove('is-active');
                card?.setAttribute('aria-hidden', 'true');
            }
            function open() {
                card.style.left = '';
                card.style.top = '';
                card.style.transform = '';
                backdrop?.classList.add('is-active');
                card?.classList.add('is-active');
                card?.setAttribute('aria-hidden', 'false');
            }

            closeBtn?.addEventListener('click', close);
            cancelBtn?.addEventListener('click', close);
            backdrop?.addEventListener('click', close);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && card?.classList.contains('is-active')) close();
            });

            // Drag lewat header (pointer events -> jalan di mouse & touch)
            if (dragHandle && card) {
                let dragState = null;

                dragHandle.addEventListener('pointerdown', (e) => {
                    if (e.target.closest('.modal-close-btn')) return;
                    const rect = card.getBoundingClientRect();
                    dragState = {
                        startX: e.clientX,
                        startY: e.clientY,
                        originX: rect.left,
                        originY: rect.top,
                    };
                    card.style.left = rect.left + 'px';
                    card.style.top = rect.top + 'px';
                    card.style.transform = 'none';
                    card.classList.add('is-dragging');
                    dragHandle.setPointerCapture(e.pointerId);
                });

                dragHandle.addEventListener('pointermove', (e) => {
                    if (!dragState) return;
                    const dx = e.clientX - dragState.startX;
                    const dy = e.clientY - dragState.startY;
                    const maxLeft = window.innerWidth - card.offsetWidth - 8;
                    const maxTop = window.innerHeight - card.offsetHeight - 8;
                    const newLeft = Math.min(Math.max(8, dragState.originX + dx), Math.max(8, maxLeft));
                    const newTop = Math.min(Math.max(8, dragState.originY + dy), Math.max(8, maxTop));
                    card.style.left = newLeft + 'px';
                    card.style.top = newTop + 'px';
                });

                function endDrag(e) {
                    if (!dragState) return;
                    dragState = null;
                    card.classList.remove('is-dragging');
                    try {
                        dragHandle.releasePointerCapture(e.pointerId);
                    } catch (_) {}
                }
                dragHandle.addEventListener('pointerup', endDrag);
                dragHandle.addEventListener('pointercancel', endDrag);
            }

            return { open, close };
        },
    };

    // ---------------------------------------------------------------
    // LIVE FILTER (search + dropdown filter, tanpa tombol "Terapkan")
    // ---------------------------------------------------------------
    SIDA.filter = {
        /**
         * @param {Object} opts
         * @param {HTMLElement} opts.tableBody
         * @param {string[]} opts.dropdownFilterIds  contoh: ['filter-jenis','filter-tab']
         * @param {string} opts.searchInputId
         * @param {function(HTMLElement): boolean} opts.matches  cek 1 baris cocok filter atau tidak
         * @param {string} [opts.emptyMessage]
         */
        setup({ tableBody, dropdownFilterIds = [], searchInputId, matches, emptyMessage }) {
            function applyFilters() {
                const rows = tableBody.querySelectorAll('tr[data-id]');
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = matches(row);
                    row.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                let noResultRow = document.getElementById('noResultRow');
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResultRow) {
                        noResultRow = document.createElement('tr');
                        noResultRow.id = 'noResultRow';
                        const colCount = tableBody.closest('table')?.querySelectorAll('thead th').length || 8;
                        noResultRow.innerHTML = `<td colspan="${colCount}" style="text-align:center; padding: 32px; color: var(--ink-faint);">${emptyMessage || 'Tidak ada data yang cocok dengan filter.'}</td>`;
                        tableBody.appendChild(noResultRow);
                    }
                    noResultRow.style.display = '';
                } else if (noResultRow) {
                    noResultRow.style.display = 'none';
                }
            }

            dropdownFilterIds.forEach((id) => {
                const input = document.getElementById(id);
                const dropdownEl = input?.closest('[data-dropdown]');
                dropdownEl?.querySelectorAll('.dropdown-option').forEach((opt) => {
                    opt.addEventListener('click', () => applyFilters());
                });
            });

            let searchDebounce;
            const searchInput = document.getElementById(searchInputId);
            searchInput?.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(applyFilters, 150);
            });

            document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
                document.querySelectorAll('.filter-grid [data-dropdown]').forEach((d) => SIDA.dropdown.reset(d));
                if (searchInput) searchInput.value = '';
                applyFilters();
            });

            return applyFilters;
        },
    };

    // ---------------------------------------------------------------
    // TABLE ROW HELPER (insert/update/remove, generik untuk semua tabel)
    // ---------------------------------------------------------------
    SIDA.table = {
        /**
         * @param {HTMLElement} tableBody
         * @param {function(Object): string} buildRowHTML  HTML <tr> berdasarkan item (beda tiap halaman)
         */
        create(tableBody, buildRowHTML) {
            function insertRow(item) {
                document.getElementById('emptyRow')?.remove();
                const wrap = document.createElement('tbody');
                wrap.innerHTML = buildRowHTML(item);
                const row = wrap.firstElementChild;
                row.classList.add('is-new');
                tableBody.prepend(row);
                return row;
            }

            function updateRow(item) {
                const existing = tableBody.querySelector(`tr[data-id="${item.id}"]`);
                if (!existing) return insertRow(item);
                const wrap = document.createElement('tbody');
                wrap.innerHTML = buildRowHTML(item);
                const newRow = wrap.firstElementChild;
                existing.replaceWith(newRow);
                return newRow;
            }

            function removeRow(id) {
                const row = tableBody.querySelector(`tr[data-id="${id}"]`);
                if (!row) return;
                row.classList.add('is-removing');
                row.addEventListener('transitionend', () => row.remove(), { once: true });
            }

            return { insertRow, updateRow, removeRow };
        },
    };

    // ---------------------------------------------------------------
    // AUTO-INIT bagian yang selalu ada di semua halaman berlayout ini
    // ---------------------------------------------------------------
    function autoInit() {
        SIDA.dropdown.init();
        SIDA.sidebar.init();
        SIDA.theme.init();
        SIDA.notif.init();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

    window.SIDA = SIDA;
})(window, document);

/**
 * =====================================================================
 * CONTOH PEMAKAIAN DI HALAMAN (ditulis di Blade, bukan di file ini)
 * =====================================================================
 *
 * <script src="{{ asset('js/script.js') }}"></script>
 * <script>
 *   const tableBody = document.getElementById('kegiatanTableBody');
 *
 *   function buildRowHTML(item) {
 *       // ... khusus halaman ini, pakai SIDA.util.esc / initials / avatarColor
 *   }
 *   const { insertRow, updateRow, removeRow } = SIDA.table.create(tableBody, buildRowHTML);
 *
 *   const applyFilters = SIDA.filter.setup({
 *       tableBody,
 *       dropdownFilterIds: ['filter-jenis', 'filter-tab', 'filter-tingkat'],
 *       searchInputId: 'filter-search',
 *       matches: (row) => { ... logika kondisi khusus halaman ini ... },
 *   });
 *
 *   const { open: openModalBase, close: closeModal } = SIDA.modal.attach({
 *       backdrop: document.getElementById('modalBackdrop'),
 *       card: document.getElementById('kegiatanModal'),
 *       closeBtn: document.getElementById('modalCloseBtn'),
 *       cancelBtn: document.getElementById('modalCancelBtn'),
 *       dragHandle: document.getElementById('modalDragHandle'),
 *   });
 *
 *   // ... sisanya: openModal(mode,data), submit fetch ke endpoint halaman ini, dst.
 * </script>
 * =====================================================================
 */