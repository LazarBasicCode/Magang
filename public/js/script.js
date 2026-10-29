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
 *   - Profile Dropdown
 *   - Custom datepicker          (SIDA.datepicker)
 *   - Skeleton loading           (SIDA.skeleton)
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
    "use strict";

    const SIDA = {};

    // ---------------------------------------------------------------
    // UTIL
    // ---------------------------------------------------------------
    SIDA.util = {
        csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : "";
        },

        esc(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        },

        initials(name) {
            return (
                (name || "")
                    .split(" ")
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((w) => w[0])
                    .join("")
                    .toUpperCase() || "-"
            );
        },

        avatarColor(id) {
            const colors = [
                "c-primary",
                "c-info",
                "c-warning",
                "c-success",
                "c-danger",
            ];
            return colors[Number(id) % colors.length];
        },

        // HTML avatar (inisial + foto dari link bila ada) — padanan JS dari partials/avatar.blade.php.
        avatarHtml(name, id, url, cls = "avatar") {
            const u = SIDA.util;
            const img = url
                ? `<img class="av-img" src="${u.esc(url)}" alt="" width="96" height="96" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()">`
                : "";
            return `<div class="${cls} ${u.avatarColor(id)}">${u.esc(u.initials(name))}${img}</div>`;
        },
    };

    // ---------------------------------------------------------------
    // CUSTOM DROPDOWN  (dipakai di filter bar & modal form)
    // ---------------------------------------------------------------
    SIDA.dropdown = {
        allNodes: [],
        // dropdown -> panel yang sedang "dipindah" ke <body> (lihat openPortal)
        portaled: new Map(),
        _globalBound: false,

        init() {
            this.allNodes = Array.from(
                document.querySelectorAll("[data-dropdown]"),
            );

            this.allNodes.forEach((dropdown) => {
                // Dropdown yang sudah dikelola halaman (mis. dropdown portal
                // di modal) ditandai data-bound="1" -> jangan dipasang listener
                // ganda, kalau tidak klik akan buka lalu langsung tutup.
                if (dropdown.dataset.bound === "1") return;
                // init() boleh dipanggil lagi (dropdown baru): yang sudah terpasang dilewati.
                if (dropdown.dataset.sidaBound === "1") return;
                dropdown.dataset.sidaBound = "1";

                const trigger = dropdown.querySelector(".dropdown-trigger");
                const valueEl = dropdown.querySelector(".dropdown-value");
                const hiddenInput = dropdown.querySelector(
                    'input[type="hidden"]',
                );
                const options = dropdown.querySelectorAll(".dropdown-option");

                trigger?.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const wasOpen = dropdown.classList.contains("is-open");
                    this.closeAll();
                    if (!wasOpen) {
                        dropdown.classList.add("is-open");
                        this.openPortal(dropdown);
                    }
                });

                options.forEach((option) => {
                    option.addEventListener("click", () => {
                        options.forEach((o) =>
                            o.classList.remove("is-selected"),
                        );
                        option.classList.add("is-selected");
                        if (valueEl)
                            valueEl.textContent = option.textContent.trim();
                        if (hiddenInput) {
                            hiddenInput.value = option.dataset.value;
                            hiddenInput.dispatchEvent(
                                new Event("change", { bubbles: true }),
                            );
                        }
                        dropdown.classList.remove("is-open");
                        this.closePortal(dropdown);
                    });
                });
            });

            if (this._globalBound) return;
            this._globalBound = true;

            document.addEventListener("click", () => this.closeAll());

            // Esc: tutup daftar yang sedang terbuka di luar modal lebih dulu
            // (modalnya tetap terbuka; Esc kedua baru menutup modal).
            window.addEventListener(
                "keydown",
                (e) => {
                    if (e.key !== "Escape" || !this.portaled.size) return;
                    this.closeAll();
                    e.stopPropagation();
                },
                true,
            );

            // Panel yang dipindah ke <body> tidak ikut bergeser saat modal/halaman
            // di-scroll atau jendela diubah ukurannya, jadi ditutup saja.
            // Scroll DI DALAM panel (daftar panjang) tidak boleh menutupnya.
            window.addEventListener(
                "scroll",
                (e) => {
                    if (!this.portaled.size) return;
                    if (
                        e.target instanceof Element &&
                        e.target.closest(".dropdown-panel")
                    )
                        return;
                    this.closeAll();
                },
                true,
            );
            window.addEventListener("resize", () => {
                if (this.portaled.size) this.closeAll();
            });
        },

        /**
         * Dropdown DI DALAM modal: daftar pilihannya dipindah ke <body> (position: fixed)
         * supaya tidak terpotong / ikut ter-scroll oleh .modal-body (overflow) dan tidak
         * terkurung transform modal. Dikembalikan ke induknya saat ditutup.
         * Otomatis berlaku untuk semua dropdown di semua modal.
         */
        openPortal(dropdown) {
            if (!dropdown.closest(".modal-card")) return;
            const panel = dropdown.querySelector(".dropdown-panel");
            const trigger = dropdown.querySelector(".dropdown-trigger");
            if (!panel || !trigger) return;

            const rect = trigger.getBoundingClientRect();
            const gap = 6;
            const margin = 12;
            const below = window.innerHeight - rect.bottom - gap - margin;
            const above = rect.top - gap - margin;
            // Ruang di bawah sempit -> buka ke atas; tinggi daftar menyesuaikan ruang yang ada.
            const flip = below < 160 && above > below;
            const maxH = Math.max(120, Math.min(240, flip ? above : below));

            // Aturan CSS yang bergantung pada induk (mis. ".permission-row .dropdown-panel":
            // rata kanan + lebar minimum) hilang setelah panel pindah ke <body>, jadi dibaca
            // dulu selagi panel masih di dalam dropdown lalu diterapkan sebagai gaya inline.
            const cs = window.getComputedStyle(panel);
            const minW = parseFloat(cs.minWidth) || 0;
            const alignRight = cs.left === "auto" && cs.right !== "auto";
            const width = Math.max(rect.width, minW);
            let left = alignRight ? rect.right - width : rect.left;
            left = Math.max(8, Math.min(left, window.innerWidth - width - 8));

            panel.classList.add("dropdown-panel--portal");
            Object.assign(panel.style, {
                display: "flex",
                position: "fixed",
                left: left + "px",
                width: width + "px",
                right: "auto",
                zIndex: "9999",
                maxHeight: maxH + "px",
                overflowY: "auto",
                overscrollBehavior: "contain", // gulir mentok tidak menggulir modal di belakangnya
            });
            if (flip) {
                panel.style.top = "auto";
                panel.style.bottom = window.innerHeight - rect.top + gap + "px";
            } else {
                panel.style.top = rect.bottom + gap + "px";
                panel.style.bottom = "auto";
            }

            document.body.appendChild(panel);
            this.portaled.set(dropdown, panel);
        },

        closePortal(dropdown) {
            const panel = this.portaled.get(dropdown);
            if (!panel) return;
            this.portaled.delete(dropdown);
            panel.classList.remove("dropdown-panel--portal");
            [
                "display", "position", "left", "top", "bottom", "width",
                "right", "zIndex", "maxHeight", "overflowY", "overscrollBehavior",
            ].forEach((prop) => (panel.style[prop] = ""));
            dropdown.appendChild(panel);
        },

        closeAll() {
            this.allNodes.forEach((d) => d.classList.remove("is-open"));
            Array.from(this.portaled.keys()).forEach((d) => this.closePortal(d));
        },

        // Set nilai terpilih dari luar (mis. saat buka modal Edit)
        select(dropdownEl, value, placeholderIfEmpty) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll(".dropdown-option");
            const valueEl = dropdownEl.querySelector(".dropdown-value");
            const hiddenInput = dropdownEl.querySelector(
                'input[type="hidden"]',
            );
            let matched = false;

            options.forEach((o) => {
                const isMatch = o.dataset.value === String(value);
                o.classList.toggle("is-selected", isMatch);
                if (isMatch) {
                    if (valueEl) valueEl.textContent = o.textContent.trim();
                    matched = true;
                }
            });

            if (hiddenInput) hiddenInput.value = matched ? value : "";
            if (!matched && options.length && valueEl) {
                valueEl.textContent =
                    placeholderIfEmpty || options[0].textContent.trim();
            }
            return matched;
        },

        // Kembalikan dropdown ke opsi pertama (dipakai tombol "Reset Filter")
        reset(dropdownEl) {
            if (!dropdownEl) return;
            const options = dropdownEl.querySelectorAll(".dropdown-option");
            const valueEl = dropdownEl.querySelector(".dropdown-value");
            const hiddenInput = dropdownEl.querySelector(
                'input[type="hidden"]',
            );
            options.forEach((o, i) => {
                o.classList.toggle("is-selected", i === 0);
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
            const sidebar = document.querySelector(".app-sidebar");
            const toggleBtn = document.getElementById("sidebarToggleBtn");
            const closeBtn = document.getElementById("sidebarCloseBtn");
            const overlay = document.getElementById("sidebarOverlay");
            if (!sidebar) return;

            const open = () => {
                sidebar.classList.add("is-open");
                overlay?.classList.add("is-active");
                document.body.style.overflow = "hidden";
            };
            const close = () => {
                sidebar.classList.remove("is-open");
                overlay?.classList.remove("is-active");
                document.body.style.overflow = "";
            };

            toggleBtn?.addEventListener("click", open);
            closeBtn?.addEventListener("click", close);
            overlay?.addEventListener("click", close);
            window.addEventListener("resize", () => {
                if (window.innerWidth >= 800) close();
            });
        },
    };

    // ---------------------------------------------------------------
    // DARK MODE
    // ---------------------------------------------------------------
    SIDA.theme = {
        init() {
            const btn = document.getElementById("themeToggleBtn");
            const icon = document.getElementById("themeIcon");
            const applyTheme = (isDark) => {
                document.body.classList.toggle("dark-mode", isDark);
                if (icon) icon.textContent = isDark ? "light_mode" : "dark_mode";
            };

            try {
                applyTheme(localStorage.getItem("theme") === "dark");
            } catch (error) {
                console.error("[SIDA] Tema gagal dibaca:", error);
                applyTheme(false);
            }

            window.addEventListener("storage", (event) => {
                if (event.key === "theme") applyTheme(event.newValue === "dark");
            });

            btn?.addEventListener("click", () => {
                const isDark = !document.body.classList.contains("dark-mode");
                applyTheme(isDark);
                try {
                    localStorage.setItem("theme", isDark ? "dark" : "light");
                } catch (error) {
                    console.error("[SIDA] Tema gagal disimpan:", error);
                }
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
            const dropdown = document.getElementById("notifDropdown");
            const toggleBtn = document.getElementById("notifToggleBtn");
            const panel = document.getElementById("notifPanel");
            const dot = document.getElementById("notifDot");
            const markAllBtn = document.getElementById("notifMarkAllBtn");
            if (!dropdown || !toggleBtn || !panel) return;

            const close = () => {
                dropdown.classList.remove("is-open");
                toggleBtn.setAttribute("aria-expanded", "false");
                panel.setAttribute("aria-hidden", "true");
            };

            toggleBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                SIDA.dropdown.closeAll(); // tutup dropdown filter/form lain
                const willOpen = !dropdown.classList.contains("is-open");
                dropdown.classList.toggle("is-open", willOpen);
                toggleBtn.setAttribute("aria-expanded", String(willOpen));
                panel.setAttribute("aria-hidden", String(!willOpen));
            });

            panel.addEventListener("click", (e) => e.stopPropagation());
            document.addEventListener("click", close);
            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape") close();
            });

            // Tandai semua dibaca — efek visual saja sampai endpoint controller ada
            markAllBtn?.addEventListener("click", () => {
                document
                    .querySelectorAll(".notif-item.is-unread")
                    .forEach((item) => {
                        item.classList.remove("is-unread");
                    });
                document.getElementById("notifDot")?.remove();
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
            let dragState = null;
            // Elemen interaktif di dalam header tidak boleh memicu geser (tombol, tautan, input, dst.)
            const INTERACTIVE = "button, a, input, select, textarea, label, [contenteditable]";

            // Hentikan geser & kembalikan latar ke blur normal (dipakai saat lepas / tutup / buka)
            function stopDrag() {
                dragState = null;
                card?.classList.remove("is-dragging");
                backdrop?.classList.remove("is-dragging");
            }
            function close() {
                stopDrag();
                backdrop?.classList.remove("is-active");
                card?.classList.remove("is-active");
                card?.setAttribute("aria-hidden", "true");
            }
            function open() {
                stopDrag();
                card.style.left = "";
                card.style.top = "";
                card.style.transform = "";
                backdrop?.classList.add("is-active");
                card?.classList.add("is-active");
                card?.setAttribute("aria-hidden", "false");
            }

            closeBtn?.addEventListener("click", close);
            cancelBtn?.addEventListener("click", close);
            backdrop?.addEventListener("click", close);
            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape" && card?.classList.contains("is-active"))
                    close();
            });

            // Drag lewat header (pointer events -> jalan di mouse & touch).
            // Selama digeser, latar belakang berhenti blur (transisi halus, lihat .modal-backdrop.is-dragging)
            // dan blur lagi setelah dilepas.
            if (dragHandle && card) {
                dragHandle.addEventListener("pointerdown", (e) => {
                    if (e.button !== undefined && e.button !== 0) return; // hanya klik kiri / sentuhan
                    if (e.target.closest(INTERACTIVE)) return;
                    const rect = card.getBoundingClientRect();
                    dragState = {
                        startX: e.clientX,
                        startY: e.clientY,
                        originX: rect.left,
                        originY: rect.top,
                    };
                    card.style.left = rect.left + "px";
                    card.style.top = rect.top + "px";
                    card.style.transform = "none";
                    card.classList.add("is-dragging");
                    backdrop?.classList.add("is-dragging");
                    dragHandle.setPointerCapture(e.pointerId);
                });

                dragHandle.addEventListener("pointermove", (e) => {
                    if (!dragState) return;
                    const dx = e.clientX - dragState.startX;
                    const dy = e.clientY - dragState.startY;
                    const maxLeft = window.innerWidth - card.offsetWidth - 8;
                    const maxTop = window.innerHeight - card.offsetHeight - 8;
                    const newLeft = Math.min(
                        Math.max(8, dragState.originX + dx),
                        Math.max(8, maxLeft),
                    );
                    const newTop = Math.min(
                        Math.max(8, dragState.originY + dy),
                        Math.max(8, maxTop),
                    );
                    card.style.left = newLeft + "px";
                    card.style.top = newTop + "px";
                });

                function endDrag(e) {
                    if (!dragState) return;
                    stopDrag();
                    try {
                        dragHandle.releasePointerCapture(e.pointerId);
                    } catch (_) {}
                }
                dragHandle.addEventListener("pointerup", endDrag);
                dragHandle.addEventListener("pointercancel", endDrag);
                dragHandle.addEventListener("lostpointercapture", endDrag);

                // Jendela diperkecil saat modal sudah digeser: jaga supaya modal tidak keluar layar
                window.addEventListener("resize", () => {
                    if (!card.classList.contains("is-active") || !card.style.left) return;
                    const r = card.getBoundingClientRect();
                    card.style.left =
                        Math.min(Math.max(8, r.left), Math.max(8, window.innerWidth - card.offsetWidth - 8)) + "px";
                    card.style.top =
                        Math.min(Math.max(8, r.top), Math.max(8, window.innerHeight - card.offsetHeight - 8)) + "px";
                });
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
        setup({
            tableBody,
            dropdownFilterIds = [],
            searchInputId,
            matches,
            emptyMessage,
        }) {
            function applyFilters() {
                const rows = tableBody.querySelectorAll("tr[data-id]");
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = matches(row);
                    row.style.display = visible ? "" : "none";
                    if (visible) visibleCount++;
                });

                let noResultRow = document.getElementById("noResultRow");
                if (visibleCount === 0 && rows.length > 0) {
                    if (!noResultRow) {
                        noResultRow = document.createElement("tr");
                        noResultRow.id = "noResultRow";
                        const colCount =
                            tableBody
                                .closest("table")
                                ?.querySelectorAll("thead th").length || 8;
                        noResultRow.innerHTML = `<td colspan="${colCount}" style="text-align:center; padding: 32px; color: var(--ink-faint);">${emptyMessage || "Tidak ada data yang cocok dengan filter."}</td>`;
                        tableBody.appendChild(noResultRow);
                    }
                    noResultRow.style.display = "";
                } else if (noResultRow) {
                    noResultRow.style.display = "none";
                }
            }

            dropdownFilterIds.forEach((id) => {
                // Dengarkan 'change' dari hidden input (dipicu SIDA.dropdown
                // SETELAH nilainya diperbarui), bukan klik opsi: urutan
                // listener klik tidak terjamin dan bikin filter telat 1 langkah.
                document
                    .getElementById(id)
                    ?.addEventListener("change", () => applyFilters());
            });

            let searchDebounce;
            const searchInput = document.getElementById(searchInputId);
            searchInput?.addEventListener("input", () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(applyFilters, 150);
            });

            document
                .getElementById("btn-reset-filter")
                ?.addEventListener("click", () => {
                    document
                        .querySelectorAll(".filter-grid [data-dropdown]")
                        .forEach((d) => SIDA.dropdown.reset(d));
                    if (searchInput) searchInput.value = "";
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
                document.getElementById("emptyRow")?.remove();
                const wrap = document.createElement("tbody");
                wrap.innerHTML = buildRowHTML(item);
                const row = wrap.firstElementChild;
                row.classList.add("is-new");
                tableBody.prepend(row);
                return row;
            }

            function updateRow(item) {
                const existing = tableBody.querySelector(
                    `tr[data-id="${item.id}"]`,
                );
                if (!existing) return insertRow(item);
                const wrap = document.createElement("tbody");
                wrap.innerHTML = buildRowHTML(item);
                const newRow = wrap.firstElementChild;
                existing.replaceWith(newRow);
                return newRow;
            }

            function removeRow(id) {
                const row = tableBody.querySelector(`tr[data-id="${id}"]`);
                if (!row) return;
                row.classList.add("is-removing");
                row.addEventListener("transitionend", () => row.remove(), {
                    once: true,
                });
            }

            return { insertRow, updateRow, removeRow };
        },

        // Kembalikan area scroll tabel ke baris teratas (ganti halaman/tab/cari).
        scrollTop(id = "tableScroll") {
            document.getElementById(id)?.scrollTo({ top: 0 });
        },
    };

    // ---------------------------------------------------------------
    // LABEL KOLOM UNTUK TABEL MODE KARTU (layar sempit)
    // Menyalin teks <th> ke atribut data-label di tiap <td>, termasuk baris
    // yang ditambah/diganti lewat fetch() (SIDA.table.insertRow/updateRow).
    // CSS memakai td::before { content: attr(data-label) }.
    // ---------------------------------------------------------------
    SIDA.tableLabels = {
        init() {
            document.querySelectorAll("table.data-table").forEach((table) => {
                const heads = Array.from(
                    table.querySelectorAll("thead th"),
                ).map((th) => th.textContent.trim());

                const label = (tr) => {
                    Array.from(tr.children).forEach((td, i) => {
                        if (td.tagName === "TD" && !td.hasAttribute("colspan") && heads[i]) {
                            td.setAttribute("data-label", heads[i]);
                        }
                    });
                };

                table.querySelectorAll("tbody:not(.sk-body)").forEach((tbody) => {
                    tbody.querySelectorAll("tr").forEach(label);
                    new MutationObserver((mutations) => {
                        mutations.forEach((m) =>
                            m.addedNodes.forEach((n) => {
                                if (n.nodeType === 1 && n.tagName === "TR") label(n);
                            }),
                        );
                    }).observe(tbody, { childList: true });
                });
            });
        },
    };

    // ---------------------------------------------------------------
    // PROFILE DROPDOWN (header) — dipakai di semua halaman
    // Dipindah apa adanya dari kemahasiswaan.blade.php
    // ---------------------------------------------------------------
    SIDA.profile = {
        init() {
            const wrap = document.getElementById("profileDropdown");
            const toggleBtn = document.getElementById("profileToggleBtn");
            const panel = document.getElementById("profilePanel");
            const viewMain = document.getElementById("profileViewMain");
            const viewAccess = document.getElementById("profileViewAccess");
            const btnShowAccess = document.getElementById("btnShowAccessInfo");
            const btnBack = document.getElementById("btnBackToMain");
            if (!wrap || !toggleBtn || !panel) return;

            function showView(view) {
                [viewMain, viewAccess].forEach((v) =>
                    v?.classList.remove("is-active"),
                );
                view?.classList.add("is-active");
            }

            function openPanel() {
                wrap.classList.add("is-open");
                toggleBtn.setAttribute("aria-expanded", "true");
                panel.setAttribute("aria-hidden", "false");
            }

            function closePanel() {
                wrap.classList.remove("is-open");
                toggleBtn.setAttribute("aria-expanded", "false");
                panel.setAttribute("aria-hidden", "true");
                showView(viewMain);
            }

            toggleBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                wrap.classList.contains("is-open") ? closePanel() : openPanel();
            });

            btnShowAccess?.addEventListener("click", () =>
                showView(viewAccess),
            );
            btnBack?.addEventListener("click", () => showView(viewMain));

            // Klik di luar panel -> tutup.
            document.addEventListener("click", (e) => {
                if (!wrap.contains(e.target)) closePanel();
            });

            // Esc -> tutup.
            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape") closePanel();
            });

            // Ganti Password & Email Pemulihan ditangani public/js/profile-account.js
        },
    };

    // ---------------------------------------------------------------
    // CUSTOM DATEPICKER (dipakai Kerja Sama & Rekognisi)
    // Markup: .datepicker[data-datepicker] (lihat modal di blade).
    // Panel kalender dipindah ke <body> supaya lepas dari overflow modal.
    //   const { start, end } = SIDA.datepicker.pair(elMulai, elSelesai);
    //   start.setValue('2026-01-31'); end.setMinDate(...); end.clear();
    // ---------------------------------------------------------------
    SIDA.datepicker = {
        MONTHS: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
        DAYS: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
        active: new Set(),
        _bound: false,

        pad2(n) {
            return String(n).padStart(2, "0");
        },
        toISO(y, m, d) {
            return `${y}-${this.pad2(m + 1)}-${this.pad2(d)}`;
        },
        parseISO(str) {
            if (!str) return null;
            const [y, m, d] = str.split("-").map(Number);
            if (!y || !m || !d) return null;
            return new Date(y, m - 1, d);
        },
        format(dateObj) {
            return `${this.DAYS[dateObj.getDay()]}, ${dateObj.getDate()} ${this.MONTHS[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
        },
        closeAll() {
            this.active.forEach((dp) => dp.close());
        },

        // Listener global (klik di luar, resize, scroll) dipasang sekali saja
        bindGlobal() {
            if (this._bound) return;
            this._bound = true;
            document.addEventListener("click", (e) => {
                this.active.forEach((dp) => {
                    if (!dp.isOpen()) return;
                    if (dp.el.contains(e.target) || dp.panel.contains(e.target)) return;
                    dp.close();
                });
            });
            window.addEventListener("resize", () => this.active.forEach((dp) => dp.reposition()));
            document.addEventListener("scroll", () => this.active.forEach((dp) => dp.reposition()), true);
        },

        create(rootEl) {
            const self = this;
            self.bindGlobal();

            const hiddenInput = rootEl.querySelector('input[type="hidden"]');
            const trigger = rootEl.querySelector(".datepicker-trigger");
            const valueEl = rootEl.querySelector(".datepicker-value");
            const panel = rootEl.querySelector(".datepicker-panel");
            const monthLabel = rootEl.querySelector(".dp-month-label");
            const daysGrid = rootEl.querySelector(".dp-days");
            const prevBtn = rootEl.querySelector(".dp-prev");
            const nextBtn = rootEl.querySelector(".dp-next");
            const todayBtn = rootEl.querySelector(".dp-today-btn");
            const clearBtn = rootEl.querySelector(".dp-clear-btn");

            document.body.appendChild(panel);

            let selected = null;
            let minDate = null;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            let view = { year: today.getFullYear(), month: today.getMonth() };

            function render() {
                monthLabel.textContent = `${self.MONTHS[view.month]} ${view.year}`;
                daysGrid.innerHTML = "";

                const startWeekday = new Date(view.year, view.month, 1).getDay();
                const daysInMonth = new Date(view.year, view.month + 1, 0).getDate();

                for (let i = 0; i < startWeekday; i++) {
                    const blank = document.createElement("span");
                    blank.className = "dp-day is-empty";
                    daysGrid.appendChild(blank);
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const cellDate = new Date(view.year, view.month, d);
                    const btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "dp-day";
                    btn.textContent = d;

                    if (cellDate.getTime() === today.getTime()) btn.classList.add("is-today");
                    if (selected && cellDate.getTime() === selected.getTime()) btn.classList.add("is-selected");
                    if (minDate && cellDate.getTime() < minDate.getTime()) btn.disabled = true;

                    btn.addEventListener("click", () => selectDate(cellDate));
                    daysGrid.appendChild(btn);
                }
            }

            function positionPanel() {
                const rect = trigger.getBoundingClientRect();
                const panelWidth = panel.offsetWidth || 268;
                let left = Math.min(rect.left, window.innerWidth - panelWidth - 8);
                left = Math.max(8, left);
                panel.style.left = left + "px";

                const panelHeight = panel.offsetHeight;
                let top = rect.bottom + 6;
                if (top + panelHeight > window.innerHeight - 8) {
                    const above = rect.top - panelHeight - 6;
                    top = above > 8 ? above : Math.max(8, window.innerHeight - panelHeight - 8);
                }
                panel.style.top = top + "px";
            }

            function emitChange(date) {
                rootEl.dispatchEvent(new CustomEvent("datepicker:change", {
                    detail: { date, iso: hiddenInput.value },
                }));
            }

            function selectDate(dateObj) {
                selected = dateObj;
                hiddenInput.value = self.toISO(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
                valueEl.textContent = self.format(dateObj);
                valueEl.classList.add("has-value");
                closePanel();
                emitChange(dateObj);
            }

            function clear() {
                selected = null;
                hiddenInput.value = "";
                valueEl.textContent = "Pilih tanggal";
                valueEl.classList.remove("has-value");
                emitChange(null);
            }

            function openPanel() {
                self.closeAll();
                document.querySelectorAll(".dropdown.is-open").forEach((d) => d.classList.remove("is-open"));
                view = selected
                    ? { year: selected.getFullYear(), month: selected.getMonth() }
                    : { year: today.getFullYear(), month: today.getMonth() };
                render();
                panel.style.display = "block";
                positionPanel();
                rootEl.classList.add("is-open");
                self.active.add(api);
            }

            function closePanel() {
                panel.style.display = "none";
                rootEl.classList.remove("is-open");
                self.active.delete(api);
            }

            trigger.addEventListener("click", (e) => {
                e.stopPropagation();
                const wasOpen = rootEl.classList.contains("is-open");
                self.closeAll();
                document.querySelectorAll(".dropdown.is-open").forEach((d) => d.classList.remove("is-open"));
                if (!wasOpen) openPanel();
            });
            panel.addEventListener("click", (e) => e.stopPropagation());
            prevBtn.addEventListener("click", () => {
                view.month--;
                if (view.month < 0) { view.month = 11; view.year--; }
                render();
            });
            nextBtn.addEventListener("click", () => {
                view.month++;
                if (view.month > 11) { view.month = 0; view.year++; }
                render();
            });
            todayBtn.addEventListener("click", () => selectDate(new Date(today)));
            clearBtn.addEventListener("click", clear);

            render();

            const api = {
                el: rootEl,
                panel,
                isOpen: () => rootEl.classList.contains("is-open"),
                close: closePanel,
                reposition: positionPanel,
                setValue(iso) {
                    const d = self.parseISO(iso);
                    if (d) {
                        selected = d;
                        valueEl.textContent = self.format(d);
                        valueEl.classList.add("has-value");
                        hiddenInput.value = iso;
                    } else {
                        selected = null;
                        valueEl.textContent = "Pilih tanggal";
                        valueEl.classList.remove("has-value");
                        hiddenInput.value = "";
                    }
                },
                setMinDate(iso) {
                    minDate = self.parseISO(iso);
                },
                clear,
            };
            return api;
        },

        // Pasangan Tanggal Mulai & Selesai: tanggal selesai tidak boleh sebelum tanggal mulai
        pair(startRoot, endRoot) {
            const start = this.create(startRoot);
            const end = this.create(endRoot);
            const endInput = endRoot.querySelector('input[type="hidden"]');
            startRoot.addEventListener("datepicker:change", (e) => {
                end.setMinDate(e.detail.iso || null);
                if (e.detail.iso && endInput.value && endInput.value < e.detail.iso) end.clear();
            });
            return { start, end };
        },
    };

    // ---------------------------------------------------------------
    // SKELETON LOADING: lepas .is-loading dari #pageWrap setelah font siap
    // (maks. 2,5 dtk) dan minimal tampil N ms sejak halaman dimuat.
    // Ubah durasi minimal per halaman lewat atribut: data-skeleton-min="500"
    // ---------------------------------------------------------------
    SIDA.skeleton = {
        init() {
            const wrap = document.getElementById("pageWrap");
            if (!wrap) return;
            const minMs = parseInt(wrap.dataset.skeletonMin, 10) || 350;
            const fontsReady = (document.fonts && document.fonts.ready) || Promise.resolve();
            Promise.race([fontsReady, new Promise((r) => setTimeout(r, 2500))]).then(() => {
                setTimeout(() => {
                    wrap.classList.remove("is-loading");
                    wrap.classList.add("is-loaded");
                    wrap.removeAttribute("aria-busy");
                }, Math.max(0, minMs - performance.now()));
            });
        },
    };

    // ---------------------------------------------------------------
    // AUTO-INIT bagian yang selalu ada di semua halaman berlayout ini
    // ---------------------------------------------------------------
    function autoInit() {
        const steps = [
            ['dropdown', SIDA.dropdown],
            ['sidebar',  SIDA.sidebar],
            ['theme',    SIDA.theme],
            // ['notif',    SIDA.notif],
            ['profile',  SIDA.profile],
            ['tableLabels', SIDA.tableLabels],
            ['skeleton', SIDA.skeleton],
        ];
        steps.forEach(([name, mod]) => {
            try { mod?.init?.(); }
            catch (err) { console.error(`[SIDA] ${name}.init() gagal:`, err); }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", autoInit);
    } else {
        autoInit();
    }

    // Kunci field "nama pemilik data" (dropdown mahasiswa/dosen/user) saat modal mode Edit.
    // Pemakaian (SETELAH SIDA.dropdown.select): SIDA.ownerLock(dropdownEl, mode === 'edit')
    SIDA.ownerLock = function (dropdownEl, locked) {
        if (!dropdownEl || !dropdownEl.parentElement) return;
        const parent = dropdownEl.parentElement;
        let box = parent.querySelector(".field-locked[data-owner-lock]");

        if (!locked) {
            if (box) box.remove();
            dropdownEl.style.display = "";
            return;
        }

        if (!box) {
            box = document.createElement("div");
            box.className = "field-locked";
            box.setAttribute("data-owner-lock", "");
            box.innerHTML =
                '<div class="field-locked-inner">' +
                '<span class="material-symbols-outlined">lock</span>' +
                '<span class="field-locked-value"></span></div>';
            parent.appendChild(box);
        }
        const label = dropdownEl.querySelector(".dropdown-value");
        box.querySelector(".field-locked-value").textContent = label
            ? label.textContent.trim()
            : "";
        dropdownEl.style.display = "none";
    };

    window.SIDA = SIDA;
})(window, document);
/**
 * Sidebar bisa diperlebar / diperkecil dengan menyeret garis titik tiga di tepi kanan sidebar
 * (di desktop maupun saat tampil sebagai drawer di layar kecil; lebarnya satu & sama di semua tampilan).
 * Gaya, animasi & mode ikon-saja ada di style.css (bagian "SIDEBAR RESIZE"); JS ini hanya
 * membuat pegangannya dan mengubah --sidebar-w.
 * Lebar tersimpan juga dipasang lebih awal oleh resources/views/partials/sidebar-init.blade.php (di <head>).
 *
 * Pasang: tempel isi file ini di akhir public/js/script.js (tanpa markup tambahan),
 *         atau muat sebagai <script> terpisah di layout.
 */
(function () {
    const sidebar = document.querySelector('.app-sidebar');
    if (!sidebar) return;

    const root = document.documentElement;
    const MIN = 72;            // mode ikon (samakan dengan --sidebar-min)
    const EXPANDED_MIN = 200;  // lebar terkecil yang masih menampilkan teks
    const MAX = 340;           // samakan dengan --sidebar-max
    const SNAP = 140;          // dilepas di bawah ini -> mode ikon, di atasnya -> minimal EXPANDED_MIN
    const DEFAULT = 260;
    const KEY = 'sida.sidebarW';

    // Lebar maksimum: 340px, tapi di layar kecil (drawer) sisakan ruang overlay (samakan dengan CSS: 100vw - 48px)
    const maxW = () => Math.max(MIN, Math.min(MAX, window.innerWidth - 48));
    const apply = (w) => root.style.setProperty('--sidebar-w', w + 'px');
    const snapTo = (w) => (w < SNAP ? MIN : Math.min(maxW(), Math.max(EXPANDED_MIN, w)));
    const current = () => Math.round(sidebar.getBoundingClientRect().width);
    const save = (w) => { try { localStorage.setItem(KEY, w); } catch (e) {} };

    // Pegangan dibuat di sini supaya tidak perlu mengubah markup 11 halaman
    const handle = document.createElement('div');
    handle.className = 'sidebar-resizer';
    handle.setAttribute('aria-hidden', 'true');
    handle.title = 'Seret untuk ubah lebar · klik 2x untuk ciutkan/lebarkan';
    sidebar.append(handle);

    try {
        const saved = parseInt(localStorage.getItem(KEY), 10);
        if (saved) apply(snapTo(saved));
    } catch (e) {}

    // Pindah ke lebar akhir dengan animasi halus (class hanya aktif selama animasi)
    let snapTimer;
    function animateTo(w) {
        clearTimeout(snapTimer);
        document.body.classList.add('is-snapping-sidebar');
        void sidebar.offsetWidth;            // pastikan transisi terbaca sebelum nilai berubah
        apply(w);
        save(w);
        snapTimer = setTimeout(() => {
            document.body.classList.remove('is-snapping-sidebar');
            window.dispatchEvent(new Event('resize')); // supaya chart ikut menyesuaikan
        }, 360);
    }

    handle.addEventListener('pointerdown', (e) => {
        if (e.button !== 0) return;
        e.preventDefault();
        clearTimeout(snapTimer);
        document.body.classList.remove('is-snapping-sidebar');
        handle.setPointerCapture(e.pointerId);
        document.body.classList.add('is-resizing-sidebar');

        // Saat menyeret: mengikuti mouse langsung (tanpa transisi), teks memudar & blur bila terpotong
        const move = (ev) => apply(Math.min(maxW(), Math.max(MIN, ev.clientX)));
        const stop = () => {
            handle.removeEventListener('pointermove', move);
            handle.removeEventListener('pointerup', stop);
            handle.removeEventListener('pointercancel', stop);
            document.body.classList.remove('is-resizing-sidebar');
            animateTo(snapTo(current())); // lepas -> menempel ke mode ikon / lebar normal dengan animasi
        };
        handle.addEventListener('pointermove', move);
        handle.addEventListener('pointerup', stop);
        handle.addEventListener('pointercancel', stop);
    });

    // Klik dua kali: bolak-balik mode ikon <-> lebar normal
    handle.addEventListener('dblclick', () => {
        animateTo(current() <= MIN ? Math.min(DEFAULT, maxW()) : MIN);
    });
})();

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