<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <style>
        @layer base {

            html,
            body {
                margin: 0;
                padding: 0;
            }

            body {
                overscroll-behavior: none;
            }

            main>:first-child {
                margin-top: 0 !important;
            }

            main>:last-child {
                margin-bottom: 0 !important;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d5dbe8;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#696cff",
                        "primary-dark": "#5f61e0",
                        "primary-soft": "#eeeefd",
                        ink: "#4a5072",
                        "ink-muted": "#a1acc5",
                        "ink-faint": "#c8cfe0",
                        canvas: "#f5f5f9",
                        card: "#ffffff",
                        line: "#eceef3",
                        success: "#71dd37",
                        "success-soft": "#e9fbe5",
                        info: "#03c3ec",
                        "info-soft": "#e2f8fc",
                        warning: "#ffab00",
                        "warning-soft": "#fff2d9",
                        danger: "#ff3e1d",
                        "danger-soft": "#ffe0db",
                        violet: "#8592a3",
                    },
                    fontFamily: {
                        sans: ["Public Sans", "sans-serif"],
                    },
                    boxShadow: {
                        card: "0 2px 6px rgba(67, 89, 113, 0.08)",
                        popover: "0 4px 24px rgba(67, 89, 113, 0.14)",
                        "btn-primary": "0 2px 6px rgba(105, 108, 255, 0.42)",
                    },
                    borderRadius: {
                        xl: "0.625rem",
                        "2xl": "0.875rem",
                    },
                },
            },
        };
    </script>
</head>

<body class="bg-canvas font-sans text-ink antialiased min-h-screen text-[13.5px]">
    <!-- ============ SIDEBAR ============ -->
    <aside
        class="fixed left-0 top-0 h-screen w-[260px] bg-card z-50 flex flex-col shadow-[0_1px_0_rgba(67,89,113,0.06)]">
        <div class="h-[64px] flex items-center gap-2.5 px-6 shrink-0">
            <img alt="Logo Institut Asia Malang" class="h-8 w-8 object-contain rounded-md"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
            <div class="flex flex-col leading-none">
                <span class="text-[16px] font-extrabold text-ink tracking-tight">SITA</span>
                <span class="text-[11px] text-ink-muted mt-0.5">Institut Asia Malang</span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 pt-2 pb-6">
            <div class="flex flex-col gap-0.5">
                <a href="#"
                    class="flex items-center gap-3 px-3 h-10 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink transition-colors text-[13.5px] font-medium">
                    <span class="material-symbols-outlined text-[19px]">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <a href="#" aria-current="page"
                    class="relative flex items-center gap-3 px-3 h-10 rounded-lg bg-primary-soft text-primary font-semibold text-[13.5px] transition-colors">
                    <span
                        class="absolute left-[-16px] top-1/2 -translate-y-1/2 h-6 w-1 rounded-r-full bg-primary"></span>
                    <span class="material-symbols-outlined text-[19px]">school</span>
                    <span>Kemahasiswaan</span>
                </a>

                <div class="pt-5 pb-1.5 px-3">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-ink-faint">LPPM</span>
                </div>
                <a href="#"
                    class="flex items-center gap-3 px-3 h-10 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink transition-colors text-[13.5px] font-medium">
                    <span class="material-symbols-outlined text-[19px]">person</span>
                    <span>Mahasiswa</span>
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 h-10 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink transition-colors text-[13.5px] font-medium">
                    <span class="material-symbols-outlined text-[19px]">co_present</span>
                    <span>Dosen</span>
                </a>
                <a href="#"
                    class="flex items-center gap-3 px-3 h-10 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink transition-colors text-[13.5px] font-medium">
                    <span class="material-symbols-outlined text-[19px]">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="pt-5 pb-1.5 px-3">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-ink-faint">Kemitraan</span>
                </div>
                <a href="#"
                    class="flex items-center gap-3 px-3 h-10 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink transition-colors text-[13.5px] font-medium">
                    <span class="material-symbols-outlined text-[19px]">handshake</span>
                    <span>Kerja Sama</span>
                </a>
            </div>
        </nav>

        <div class="p-4 shrink-0">
            <div class="bg-canvas rounded-xl p-3.5 flex flex-col gap-2">
                <div class="flex items-center gap-2 text-ink">
                    <span class="material-symbols-outlined text-base text-primary">info</span>
                    <span class="text-[12.5px] font-semibold">Bantuan SIAKAD</span>
                </div>
                <p class="text-[11.5px] text-ink-muted leading-relaxed">Butuh bantuan input data? Hubungi tim IT
                    Kampus.</p>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="pl-[260px] min-h-screen flex flex-col">
        <!-- HEADER -->
        <header class="sticky top-0 z-40 h-[64px] bg-card shadow-[0_1px_0_rgba(67,89,113,0.06)] shrink-0">
            <div class="h-full px-6 flex items-center justify-between gap-4">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint text-[19px]">search</span>
                        <input type="text" placeholder="Cari menu / aksi..."
                            class="w-full h-10 pl-10 pr-14 rounded-lg bg-canvas text-[13px] text-ink placeholder:text-ink-faint focus:outline-none focus:ring-2 focus:ring-primary/40" />
                        <kbd
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10.5px] font-semibold text-ink-faint bg-white border border-line rounded px-1.5 py-0.5">Ctrl
                            K</kbd>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" aria-label="Notifikasi"
                        class="relative w-10 h-10 flex items-center justify-center rounded-lg hover:bg-canvas text-ink-muted transition-colors">
                        <span class="material-symbols-outlined text-[21px]">notifications</span>
                        <span
                            class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-danger ring-2 ring-card"></span>
                    </button>
                    <button type="button" aria-label="Ganti Tema"
                        class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-canvas text-ink-muted transition-colors">
                        <span class="material-symbols-outlined text-[21px]">dark_mode</span>
                    </button>
                    <div class="w-px h-6 bg-line mx-1"></div>
                    <div class="flex items-center gap-2.5 pl-1 pr-2 py-1 rounded-lg hover:bg-canvas cursor-pointer transition-colors">
                        <div class="flex flex-col items-end leading-tight">
                            <span class="text-[12.5px] font-semibold text-ink">Admin Kemahasiswaan</span>
                            <span class="text-[11px] text-ink-muted">Institut Asia Malang</span>
                        </div>
                        <img alt="Profile" class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/20"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-6 py-6">
            <div class="flex flex-col gap-5 max-w-[1600px]">

                <!-- PAGE TITLE + ACTION -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-col gap-0.5">
                        <div class="flex items-center gap-1.5 text-[12px] text-ink-muted">
                            <span>Kemahasiswaan</span>
                            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                            <span class="text-ink font-medium">Data Prestasi &amp; Kegiatan</span>
                        </div>
                        <h1 class="text-[22px] font-bold text-ink tracking-tight leading-tight">Prestasi &amp; Kegiatan
                            Mahasiswa</h1>
                        <p class="text-[13px] text-ink-muted">Pendataan kegiatan akademik, non-akademik, inbis, dan
                            kompetisi &middot; Tahun Akademik 2025/2026 (Genap)</p>
                    </div>
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg bg-primary text-white shadow-btn-primary hover:bg-primary-dark transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[19px]">add</span>
                        <span class="text-[13.5px] font-semibold">Tambah Kegiatan</span>
                    </button>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="bg-card rounded-xl p-5 shadow-card flex items-start justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-ink-muted font-medium">Total Kegiatan</span>
                            <span class="text-[22px] font-bold text-ink">48</span>
                            <span class="text-[11.5px] text-success font-semibold flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-[13px]">arrow_upward</span>12% bulan ini
                            </span>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-primary-soft text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">emoji_events</span>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-5 shadow-card flex items-start justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-ink-muted font-medium">Tingkat Nasional</span>
                            <span class="text-[22px] font-bold text-ink">27</span>
                            <span class="text-[11.5px] text-ink-muted font-medium">56% dari total</span>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-warning-soft text-warning flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">flag</span>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-5 shadow-card flex items-start justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-ink-muted font-medium">Tingkat Internasional</span>
                            <span class="text-[22px] font-bold text-ink">9</span>
                            <span class="text-[11.5px] text-ink-muted font-medium">19% dari total</span>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-info-soft text-info flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">public</span>
                        </div>
                    </div>
                    <div class="bg-card rounded-xl p-5 shadow-card flex items-start justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-[12px] text-ink-muted font-medium">Unit Inbis</span>
                            <span class="text-[22px] font-bold text-ink">12</span>
                            <span class="text-[11.5px] text-ink-muted font-medium">25% dari total</span>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-success-soft text-success flex items-center justify-center">
                            <span class="material-symbols-outlined text-[22px]">storefront</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="bg-card rounded-xl shadow-card p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
                        <!-- Jalur / Jenis -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-medium text-ink-muted" for="filter-jenis">Jenis
                                Divisi</label>
                            <div class="relative">
                                <select id="filter-jenis"
                                    class="w-full h-10 pl-3 pr-9 rounded-lg bg-canvas text-[13px] text-ink focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer">
                                    <option value="semua">Semua Jenis</option>
                                    <option value="inbis">Inbis (Inkubator Bisnis)</option>
                                    <option value="kemahasiswaan">Kemahasiswaan</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-ink-faint text-[19px]">expand_more</span>
                            </div>
                        </div>
                        <!-- Tahap / Tab -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-medium text-ink-muted" for="filter-tab">Kategori
                                Tab</label>
                            <div class="relative">
                                <select id="filter-tab"
                                    class="w-full h-10 pl-3 pr-9 rounded-lg bg-canvas text-[13px] text-ink focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer">
                                    <option value="semua">Semua Tab</option>
                                    <option value="akademik">Akademik</option>
                                    <option value="non_akademik">Non Akademik</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-ink-faint text-[19px]">expand_more</span>
                            </div>
                        </div>
                        <!-- Tingkat / Angkatan -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-medium text-ink-muted" for="filter-tingkat">Tingkat
                                Capaian</label>
                            <div class="relative">
                                <select id="filter-tingkat"
                                    class="w-full h-10 pl-3 pr-9 rounded-lg bg-canvas text-[13px] text-ink focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer">
                                    <option value="semua">Semua Tingkat</option>
                                    <option value="lokal">Lokal (Kota/Wilayah)</option>
                                    <option value="nasional">Nasional (RI)</option>
                                    <option value="internasional">Internasional (Global)</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-ink-faint text-[19px]">expand_more</span>
                            </div>
                        </div>
                        <!-- Tahun (locked) -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-medium text-ink-muted">Tahun Akademik</label>
                            <div
                                class="h-10 px-3 rounded-lg bg-primary-soft flex items-center justify-between cursor-not-allowed">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-primary">lock</span>
                                    <span class="text-[13px] font-semibold text-primary">2026</span>
                                </div>
                            </div>
                        </div>
                        <!-- Search -->
                        <div class="flex flex-col gap-1.5 sm:col-span-2 lg:col-span-4 xl:col-span-1">
                            <label class="text-[12px] font-medium text-ink-muted" for="filter-search">Pencarian
                                Cepat</label>
                            <div class="relative">
                                <input id="filter-search" type="text" placeholder="Cari NIM/nama mahasiswa..."
                                    class="w-full h-10 pl-9 pr-3 rounded-lg bg-canvas text-[13px] text-ink placeholder:text-ink-faint focus:outline-none focus:ring-2 focus:ring-primary/40" />
                                <span
                                    class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-ink-faint text-[18px]">search</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 mt-4 pt-4 border-t border-line">
                        <button type="button" id="btn-reset-filter"
                            class="h-10 px-4 rounded-lg text-ink-muted hover:bg-canvas hover:text-ink text-[13px] font-medium transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px]">restart_alt</span>
                            <span>Reset</span>
                        </button>
                        <button type="button" id="btn-apply-filter"
                            class="h-10 px-5 rounded-lg bg-primary text-white shadow-btn-primary hover:bg-primary-dark text-[13px] font-semibold transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px]">filter_alt</span>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>

                <!-- DATA TABLE CARD -->
                <div class="bg-card rounded-xl shadow-card overflow-hidden">
                    <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line">
                        <div>
                            <h2 class="text-[15px] font-bold text-ink">Daftar Rekap Prestasi Mahasiswa</h2>
                            <p class="text-[12.5px] text-ink-muted">Data kegiatan terverifikasi sesuai format resmi
                                SIM Kemahasiswaan 2026</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-canvas hover:bg-line/60 text-ink-muted text-[12.5px] font-medium transition-colors">
                                <span class="material-symbols-outlined text-[16px]">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                            <button type="button"
                                class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-canvas hover:bg-line/60 text-ink-muted text-[12.5px] font-medium transition-colors">
                                <span class="material-symbols-outlined text-[16px]">view_column</span>
                                <span>Kolom</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap border-collapse">
                            <thead>
                                <tr class="bg-canvas text-ink-muted uppercase text-[11px] tracking-wider">
                                    <th class="py-3.5 px-6 font-bold">NIM</th>
                                    <th class="py-3.5 px-4 font-bold">Mahasiswa</th>
                                    <th class="py-3.5 px-4 font-bold">Nama Kegiatan</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Jenis</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tab</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tingkat</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tahun</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Bukti Kegiatan</th>
                                    <th class="py-3.5 px-6 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-[13px] text-ink">
                                <!-- Row 1 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">222011005</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                AR
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Ahmad Rizal
                                                    Fauzi</span>
                                                <span class="text-[11.5px] text-ink-muted">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Juara 1 Kompetisi UI/UX Design Nasional TECHFEST 2026">Juara 1
                                            Kompetisi UI/UX Design Nasional TECHFEST 2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary-soft text-primary text-[11px] font-bold">kemahasiswaan</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-info-soft text-info text-[11px] font-bold">akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-warning-soft text-warning text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">232012014</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-info-soft text-info flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                SN
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Siti Nurhaliza
                                                    Putri</span>
                                                <span class="text-[11.5px] text-ink-muted">Sistem Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Pendanaan Startup Inbis: Smart Agrotech IoT">Pendanaan Startup
                                            Inbis: Smart Agrotech IoT</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-success-soft text-success text-[11px] font-bold">inbis</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-info-soft text-info text-[11px] font-bold">akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-warning-soft text-warning text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">211009088</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-warning-soft text-warning flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                KA
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Kevin
                                                    Ardiansyah</span>
                                                <span class="text-[11.5px] text-ink-muted">Desain Komunikasi
                                                    Visual</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Juara 2 Debat Bahasa Inggris Tingkat Internasional NUDC">Juara 2
                                            Debat Bahasa Inggris Tingkat Internasional NUDC</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary-soft text-primary text-[11px] font-bold">kemahasiswaan</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-canvas text-ink-muted text-[11px] font-bold">non_akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-danger-soft text-danger text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">public</span>internasional
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">201007044</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-danger-soft text-danger flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                RP
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Rafi Pratama
                                                    Wijaya</span>
                                                <span class="text-[11.5px] text-ink-muted">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Publikasi Jurnal Nasional Terakreditasi SINTA 2 Bidang AI">Publikasi
                                            Jurnal Nasional Terakreditasi SINTA 2 Bidang AI</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary-soft text-primary text-[11px] font-bold">kemahasiswaan</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-info-soft text-info text-[11px] font-bold">akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-warning-soft text-warning text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 5 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">212010071</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-success-soft text-success flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                DM
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Dinda Maharani</span>
                                                <span class="text-[11.5px] text-ink-muted">Manajemen Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Finalis Hackathon Nasional BUMN Innovation Week 2026">Finalis
                                            Hackathon Nasional BUMN Innovation Week 2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-primary-soft text-primary text-[11px] font-bold">kemahasiswaan</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-info-soft text-info text-[11px] font-bold">akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-warning-soft text-warning text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 6 -->
                                <tr class="hover:bg-canvas/70 transition-colors">
                                    <td class="py-3.5 px-6">
                                        <span class="font-semibold text-primary text-[12.5px]">202011099</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center font-bold text-[12.5px] shrink-0">
                                                CJ
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-ink text-[13px]">Clara Jessica</span>
                                                <span class="text-[11.5px] text-ink-muted">Akuntansi &amp;
                                                    Inbis</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <span class="text-[13px] text-ink truncate block"
                                            title="Inkubasi Bisnis Mahasiswa Kemenpora 2026">Inkubasi Bisnis
                                            Mahasiswa Kemenpora 2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-success-soft text-success text-[11px] font-bold">inbis</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md bg-canvas text-ink-muted text-[11px] font-bold">non_akademik</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-canvas text-ink-muted text-[11px] font-bold">
                                            <span class="material-symbols-outlined text-[12px]">location_on</span>lokal
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-[12px] text-ink-muted font-semibold">2026</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-canvas hover:bg-primary hover:text-white text-ink-muted text-[11.5px] font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-[15px]">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" title="Lihat Detail"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-primary hover:bg-primary-soft transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi"
                                                class="p-1.5 rounded-lg text-ink-muted hover:text-ink hover:bg-canvas transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER: pagination -->
                    <div
                        class="px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-line">
                        <div class="text-ink-muted text-[12.5px]">
                            Menampilkan <span class="font-semibold text-ink">1-6</span> dari
                            <span class="font-semibold text-ink">48</span> data kegiatan mahasiswa tahun
                            <span class="font-semibold text-primary">2026</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" disabled
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-ink-faint disabled:opacity-40">
                                <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            </button>
                            <button type="button"
                                class="w-8 h-8 rounded-lg bg-primary text-white font-semibold text-[12.5px] flex items-center justify-center shadow-btn-primary">1</button>
                            <button type="button"
                                class="w-8 h-8 rounded-lg text-ink-muted hover:bg-canvas font-semibold text-[12.5px] flex items-center justify-center transition-colors">2</button>
                            <button type="button"
                                class="w-8 h-8 rounded-lg text-ink-muted hover:bg-canvas font-semibold text-[12.5px] flex items-center justify-center transition-colors">3</button>
                            <span class="px-1 text-ink-faint text-[12.5px]">&hellip;</span>
                            <button type="button"
                                class="w-8 h-8 rounded-lg text-ink-muted hover:bg-canvas font-semibold text-[12.5px] flex items-center justify-center transition-colors">8</button>
                            <button type="button"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-ink-muted hover:bg-canvas transition-colors">
                                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            const jenis = document.getElementById('filter-jenis');
            const tab = document.getElementById('filter-tab');
            const tingkat = document.getElementById('filter-tingkat');
            const search = document.getElementById('filter-search');

            if (jenis) jenis.value = 'semua';
            if (tab) tab.value = 'semua';
            if (tingkat) tingkat.value = 'semua';
            if (search) search.value = '';
        });

        document.getElementById('btn-apply-filter')?.addEventListener('click', () => {
            const btn = document.getElementById('btn-apply-filter');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined text-[17px] animate-spin">progress_activity</span><span>Memuat...</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 400);
            }
        });
    </script>
</body>

</html>
