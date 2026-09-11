<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
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
            display: none;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script
        id="tailwind-config">tailwind.config = { darkMode: "class", theme: { extend: { "colors": { "primary-fixed": "#e1e0ff", "surface-container-lowest": "#ffffff", "primary": "#4546da", "primary-container": "#5f61f4", "inverse-on-surface": "#e8f2ff", "on-secondary-container": "#576473", "surface-container-high": "#d9eaff", "error": "#ba1a1a", "on-tertiary-fixed-variant": "#004e60", "on-tertiary": "#ffffff", "outline": "#767586", "secondary": "#535f6f", "background": "#f7f9ff", "secondary-fixed-dim": "#bac8da", "tertiary": "#00657b", "on-error-container": "#93000a", "secondary-container": "#d4e1f3", "surface-dim": "#c7dcf5", "on-primary-container": "#fffbff", "inverse-surface": "#1e3245", "on-surface": "#071d2f", "surface-bright": "#f7f9ff", "on-primary-fixed-variant": "#2c2ac5", "surface-container": "#e3efff", "on-secondary-fixed-variant": "#3b4857", "outline-variant": "#c6c4d7", "surface-tint": "#4748dd", "on-background": "#071d2f", "on-primary": "#ffffff", "tertiary-fixed": "#b5ebff", "tertiary-fixed-dim": "#43d6ff", "on-tertiary-container": "#fafdff", "primary-fixed-dim": "#c0c1ff", "surface-container-low": "#edf4ff", "surface-variant": "#d0e5fd", "on-secondary-fixed": "#101d2a", "tertiary-container": "#007f9b", "secondary-fixed": "#d6e4f6", "on-secondary": "#ffffff", "surface": "#f7f9ff", "error-container": "#ffdad6", "surface-container-highest": "#d0e5fd", "inverse-primary": "#c0c1ff", "on-surface-variant": "#464555", "on-tertiary-fixed": "#001f28", "on-error": "#ffffff", "on-primary-fixed": "#07006c" }, "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" }, "spacing": { "space-xs": "0.5rem", "sidebar-width": "260px", "space-sm": "0.75rem", "space-md": "1rem", "header-height": "64px", "space-2xs": "0.25rem", "space-xl": "2rem", "space-2xl": "2.5rem", "space-lg": "1.5rem", "gutter": "1.5rem", "margin-page": "1.5rem" }, "fontFamily": { "headline-xl": ["Public Sans"], "headline-sm": ["Public Sans"], "body-md": ["Public Sans"], "body-lg": ["Public Sans"], "label-sm": ["Public Sans"], "headline-lg-mobile": ["Public Sans"], "headline-md": ["Public Sans"], "label-md": ["Public Sans"], "headline-xl-mobile": ["Public Sans"], "headline-lg": ["Public Sans"], "body-sm": ["Public Sans"], "caption": ["Public Sans"] }, "fontSize": { "headline-xl": ["34px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "headline-sm": ["15px", { "lineHeight": "22px", "fontWeight": "600" }], "body-md": ["14px", { "lineHeight": "21px", "fontWeight": "400" }], "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "label-sm": ["11px", { "lineHeight": "14px", "letterSpacing": "0.04em", "fontWeight": "700" }], "headline-lg-mobile": ["20px", { "lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "600" }], "headline-md": ["18px", { "lineHeight": "24px", "fontWeight": "600" }], "label-md": ["13px", { "lineHeight": "18px", "letterSpacing": "0.01em", "fontWeight": "600" }], "headline-xl-mobile": ["26px", { "lineHeight": "34px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }], "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }], "caption": ["10px", { "lineHeight": "14px", "letterSpacing": "0.02em", "fontWeight": "500" }] } } } }</script>
</head>

<body class="bg-background font-body-md text-on-surface antialiased min-h-screen">
    <aside
        class="fixed left-0 top-0 h-screen w-sidebar-width bg-surface-container-lowest z-50 flex flex-col justify-between shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="flex flex-col">
            <div class="h-header-height flex items-center gap-space-xs px-space-md"><img alt="Logo Institut Asia Malang"
                    class="h-8 w-auto object-contain"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
                <div class="flex flex-col"><span
                        class="font-headline-sm text-headline-sm text-primary uppercase tracking-tight">INSTITUT
                        ASIA</span><span class="font-caption text-caption text-on-surface-variant uppercase">Portal
                        Akademik</span></div>
            </div>
            <div class="px-space-md py-space-xs">
                <nav class="flex flex-col gap-space-2xs"
                    data-active-classes="bg-secondary-container text-primary font-headline-sm"><a
                        class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl font-headline-sm text-headline-sm text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
                        data-path="dashboard" href="#"><span
                            class="material-symbols-outlined text-primary text-xl">dashboard</span><span>Dashboard</span></a><a
                        aria-current="page"
                        class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl transition-colors bg-secondary-container text-primary font-headline-sm"
                        data-path="kemahasiswaan" href="#"><span
                            class="material-symbols-outlined text-primary text-xl">school</span><span>Kemahasiswaan</span></a>
                    <div class="pt-space-sm pb-space-2xs px-space-sm"><span
                            class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">LPPM</span>
                    </div>
                    <div class="flex flex-col gap-space-2xs pl-space-xs"><a
                            class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
                            data-path="lppm-mahasiswa" href="#"><span
                                class="material-symbols-outlined text-secondary text-lg">person</span><span>Mahasiswa</span></a><a
                            class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
                            data-path="lppm-dosen" href="#"><span
                                class="material-symbols-outlined text-secondary text-lg">co_present</span><span>Dosen</span></a><a
                            class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl font-body-md text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
                            data-path="lppm-rekognisi" href="#"><span
                                class="material-symbols-outlined text-secondary text-lg">workspace_premium</span><span>Rekognisi</span></a>
                    </div>
                    <div class="pt-space-sm pb-space-2xs px-space-sm"><span
                            class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Kemitraan</span>
                    </div><a
                        class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-xl font-headline-sm text-headline-sm text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
                        data-path="kerja-sama" href="#"><span
                            class="material-symbols-outlined text-primary text-xl">handshake</span><span>Kerja
                            Sama</span></a>
                </nav>
            </div>
        </div>
        <div class="p-space-md">
            <div class="bg-surface-container-low rounded-xl p-space-sm flex flex-col gap-space-2xs">
                <div class="flex items-center gap-space-2xs text-secondary"><span
                        class="material-symbols-outlined text-sm">info</span><span
                        class="font-label-sm text-label-sm">Bantuan SIAKAD</span></div>
            </div>
        </div>
    </aside>
    <div class="pl-[260px]">
        <header class="fixed top-0 left-[260px] right-0 h-header-height z-40 px-space-lg flex items-center">
            <div
                class="w-full h-12 bg-surface-container-lowest/80 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] rounded-2xl px-space-md flex items-center justify-between">
                <div class="flex items-center gap-space-sm">
                    <div
                        class="flex items-center gap-space-xs px-space-sm py-1 rounded-lg text-secondary"></div>
                    <div class="hidden md:flex items-center gap-space-2xs font-body-sm text-body-sm text-secondary">
                        <span class="hover:text-primary cursor-pointer">Kemahasiswaan</span><span>/</span><span
                            class="font-headline-sm text-on-surface">Data Prestasi &amp; Kegiatan</span></div>
                </div>
                <div class="flex items-center gap-space-md"><button aria-label="Notifikasi"
                        class="relative flex items-center justify-center p-1.5 rounded-xl hover:bg-surface-container transition-colors text-on-surface-variant"
                        type="button"><span class="material-symbols-outlined text-xl">notifications</span><span
                            class="absolute top-1 right-1 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span></button><button
                        aria-label="Ganti Tema"
                        class="flex items-center justify-center p-1.5 rounded-xl hover:bg-surface-container transition-colors text-on-surface-variant"
                        type="button"><span class="material-symbols-outlined text-xl">light_mode</span></button>
                    <div class="flex items-center gap-space-xs">
                        <div class="relative"><img alt="Profile" class="w-8 h-8 rounded-full object-cover"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" /><span
                                class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-tertiary-container rounded-full ring-2 ring-surface-container-lowest"></span>
                        </div>
                        <div class="hidden sm:flex flex-col"><span
                                class="font-headline-sm text-label-md text-on-surface leading-tight">Admin
                                Kemahasiswaan</span><span class="font-caption text-caption text-secondary">Institut Asia
                                Malang</span></div>
                    </div>
                </div>
            </div>
        </header>
        <main class="w-full pt-header-height bg-background px-space-lg py-space-md">
            <div class="flex flex-col w-full gap-space-lg pb-space-2xl">
                <!-- Top Action & Title Bar -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-space-md">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-space-xs text-secondary mb-1">
                            <span
                                class="font-caption text-caption uppercase tracking-wider text-primary font-bold">Direktorat
                                Kemahasiswaan &amp; Kerjasama</span>
                            <span class="text-outline-variant">•</span>
                            <span class="font-caption text-caption text-secondary">Tahun Akademik 2025/2026
                                (Genap)</span>
                        </div>
                        <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Manajemen Prestasi
                            &amp; Kegiatan Kemahasiswaan</h1>
                        <p class="font-body-md text-body-md text-secondary mt-1">Pusat pendataan kegiatan akademik,
                            non-akademik, inbis, dan kompetisi mahasiswa Institut Asia Malang.</p>
                    </div>
                    <div class="flex items-center flex-wrap gap-space-xs sm:gap-space-sm">
                        <button
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary shadow-[0_4px_12px_rgba(69,70,218,0.35)] hover:bg-primary-container hover:shadow-[0_6px_16px_rgba(69,70,218,0.45)] transition-all duration-200 transform active:scale-95"
                            type="button">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="font-label-md text-label-md tracking-wide font-semibold">+ Tambah Kegiatan
                                Baru</span>
                        </button>
                    </div>
                </div>
                <!-- Reference Sneat Modern Concept Preview Bar -->
                <div
                    class="bg-surface-container-low rounded-2xl p-space-md flex flex-col md:flex-row items-center justify-between gap-space-md shadow-sm">
                    <div class="flex items-center gap-space-md">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-headline-sm text-headline-sm text-on-surface">Arsitektur Terintegrasi
                                    SIM-Kemahasiswaan</span>
                            </div>
                            <p class="font-body-sm text-body-sm text-secondary">Sinkronisasi langsung dengan PDDikti,
                                SIMKATMAWA Kemdikbudristek, dan Dashboard Inkubator Bisnis.</p>
                        </div>
                    </div>
                    </div>
                </div>
                <!-- Interactive Filter Bar (Clean White Card, Flat & Elevated) -->
                <div
                    class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-[0_2px_12px_rgba(67,89,113,0.06)] flex flex-col gap-space-md">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 pb-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-lg bg-primary-fixed/50 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-lg">tune</span>
                            </div>
                            <span class="font-headline-sm text-headline-sm text-on-surface">Filter Kriteria
                                Kegiatan</span>
                        </div>
                        <div class="flex items-center gap-2 text-secondary">
                            
                        </div>
                    </div>
                    <!-- Filter Fields Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-space-sm items-end">
                        <!-- 1. Dropdown Jenis -->
                        <div class="flex flex-col gap-1.5">
                            <label class="font-caption text-caption uppercase tracking-wider text-secondary font-bold"
                                for="filter-jenis">Jenis Divisi</label>
                            <div class="relative">
                                <select
                                    class="w-full h-11 px-3 py-2 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary appearance-none cursor-pointer"
                                    id="filter-jenis">
                                    <option value="semua">Semua Jenis</option>
                                    <option value="inbis">inbis (Inkubator Bisnis)</option>
                                    <option value="kemahasiswaan">kemahasiswaan</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-secondary text-lg">expand_more</span>
                            </div>
                        </div>
                        <!-- 2. Dropdown Tab -->
                        <div class="flex flex-col gap-1.5">
                            <label class="font-caption text-caption uppercase tracking-wider text-secondary font-bold"
                                for="filter-tab">Kategori Tab</label>
                            <div class="relative">
                                <select
                                    class="w-full h-11 px-3 py-2 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary appearance-none cursor-pointer"
                                    id="filter-tab">
                                    <option value="semua">Semua Tab</option>
                                    <option value="akademik">akademik</option>
                                    <option value="non_akademik">non_akademik</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-secondary text-lg">expand_more</span>
                            </div>
                        </div>
                        <!-- 3. Dropdown Tingkat -->
                        <div class="flex flex-col gap-1.5">
                            <label class="font-caption text-caption uppercase tracking-wider text-secondary font-bold"
                                for="filter-tingkat">Tingkat Capaian</label>
                            <div class="relative">
                                <select
                                    class="w-full h-11 px-3 py-2 bg-surface-container-low rounded-xl text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary appearance-none cursor-pointer"
                                    id="filter-tingkat">
                                    <option value="semua">Semua Tingkat</option>
                                    <option value="lokal">lokal (Kota/Wilayah)</option>
                                    <option value="nasional">nasional (RI)</option>
                                    <option value="internasional">internasional (Global)</option>
                                </select>
                                <span
                                    class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-secondary text-lg">expand_more</span>
                            </div>
                        </div>
                        <!-- 4. Filter Tahun (Terkunci ke 2026) -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-caption text-caption text-primary font-semibold">Terkunci</span>
                            </div>
                            <div
                                class="h-11 px-3 py-2 bg-surface-container rounded-xl flex items-center justify-between cursor-not-allowed">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base text-primary">lock</span>
                                    <span class="font-headline-sm text-headline-sm text-primary">2026</span>
                                </div>
                            </div>
                        </div>
                        <!-- 5. Search Box -->
                        <div class="flex flex-col gap-1.5 xl:col-span-1">
                            <label class="font-caption text-caption uppercase tracking-wider text-secondary font-bold"
                                for="filter-search">Pencarian Cepat</label>
                            <div class="relative">
                                <input
                                    class="w-full h-11 pl-9 pr-3 py-2 bg-surface-container-low rounded-xl text-on-surface placeholder:text-outline font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    id="filter-search" placeholder="Cari Mahasiswa / NIM..." type="text" />
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary text-base">search</span>
                            </div>
                        </div>
                        <!-- 6. Filter Buttons -->
                        <div class="flex items-center gap-2 pt-1">
                            <button
                                class="h-11 flex-1 px-4 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary-container shadow-sm hover:shadow transition-all flex items-center justify-center gap-1"
                                id="btn-apply-filter" type="button">
                                <span class="material-symbols-outlined text-lg">filter_alt</span>
                                <span>Terapkan</span>
                            </button>
                            <button
                                class="h-11 w-11 rounded-xl bg-surface-container-low hover:bg-surface-container text-secondary hover:text-on-surface flex items-center justify-center transition-colors"
                                id="btn-reset-filter" title="Reset Filter" type="button">
                                <span class="material-symbols-outlined text-lg">restart_alt</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Data Table Section (Clean Sneat-Style Card, Row Hover & Visual Badges) -->
                <div
                    class="bg-surface-container-lowest rounded-2xl shadow-[0_2px_12px_rgba(67,89,113,0.06)] overflow-hidden">
                    <!-- Header of the Table Card -->
                    <div
                        class="p-space-lg flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm bg-surface-container-lowest">
                        <div>
                            <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Daftar Rekap
                                Prestasi Mahasiswa</h2>
                            <p class="font-body-sm text-body-sm text-secondary">Data kegiatan terverifikasi berdasarkan
                                format resmi SIM Kemahasiswaan 2026.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-secondary text-body-sm font-medium transition-colors"
                                type="button">
                                <span class="material-symbols-outlined text-base">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                            <button
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-secondary text-body-sm font-medium transition-colors"
                                type="button">
                                <span class="material-symbols-outlined text-base">view_column</span>
                                <span>Kolom</span>
                            </button>
                        </div>
                    </div>
                    <!-- Table Responsive Wrapper -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr
                                    class="bg-surface-container-low text-secondary uppercase font-caption text-caption tracking-wider">
                                    <th class="py-3.5 px-5 font-bold">NIM</th>
                                    <th class="py-3.5 px-5 font-bold">Mahasiswa</th>
                                    <th class="py-3.5 px-5 font-bold">Nama Kegiatan</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Jenis</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tab</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tingkat</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Tahun</th>
                                    <th class="py-3.5 px-4 font-bold text-center">Bukti Kegiatan</th>
                                    <th class="py-3.5 px-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-transparent font-body-md text-body-md text-on-surface">
                                <!-- Row 1 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">222011005</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm">
                                                AR
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Ahmad
                                                    Rizal Fauzi</span>
                                                <span class="font-caption text-caption text-secondary">Teknik
                                                    Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Juara 1 Kompetisi UI/UX Design Nasional TECHFEST 2026">Juara 1
                                                Kompetisi UI/UX Design Nasional TECHFEST 2026</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm font-bold">
                                            kemahasiswaan
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container text-tertiary font-label-sm text-label-sm font-bold">
                                            akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold shadow-sm">
                                            <span class="material-symbols-outlined text-xs">flag</span> nasional
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">232012014</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-surface-container-high text-tertiary flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm">
                                                SN
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Siti
                                                    Nurhaliza Putri</span>
                                                <span class="font-caption text-caption text-secondary">Sistem
                                                    Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-2 h-2 rounded-full bg-on-secondary-container shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Pendanaan Startup Inbis: Smart Agrotech IoT">Pendanaan Startup
                                                Inbis: Smart Agrotech IoT</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-fixed-variant font-label-sm text-label-sm font-bold">
                                            inbis
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container text-tertiary font-label-sm text-label-sm font-bold">
                                            akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold shadow-sm">
                                            <span class="material-symbols-outlined text-xs">flag</span> nasional
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">211009088</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm">
                                                KA
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Kevin
                                                    Ardiansyah</span>
                                                <span class="font-caption text-caption text-secondary">Desain Komunikasi
                                                    Visual</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-tertiary shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Juara 2 Debat Bahasa Inggris Tingkat Internasional NUDC">Juara 2
                                                Debat Bahasa Inggris Tingkat Internasional NUDC</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm font-bold">
                                            kemahasiswaan
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold">
                                            non_akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-tertiary-fixed text-tertiary font-label-sm text-label-sm font-bold shadow-sm">
                                            <span class="material-symbols-outlined text-xs">public</span> internasional
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">223015022</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-secondary-container text-on-secondary-fixed flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm">
                                                NA
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Nabila
                                                    Amanda</span>
                                                <span class="font-caption text-caption text-secondary">Manajemen
                                                    Bisnis</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-outline shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Pekan Olahraga Mahasiswa Nasional (POMNAS) Bulutangkis">Pekan
                                                Olahraga Mahasiswa Nasional (POMNAS) Bulutangkis</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm font-bold">
                                            kemahasiswaan
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold">
                                            non_akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold shadow-sm">
                                            <span class="material-symbols-outlined text-xs">flag</span> nasional
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 5 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">231008045</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-surface-dim text-on-surface flex items-center justify-center font-headline-sm text-headline-sm font-bold shadow-sm">
                                                DW
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Dimas
                                                    Wahyu Saputra</span>
                                                <span class="font-caption text-caption text-secondary">Teknik
                                                    Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-primary-container shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Finalis Gemastik Divisi Pemrograman 2026">Finalis Gemastik Divisi
                                                Pemrograman 2026</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm font-bold">
                                            kemahasiswaan
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container text-tertiary font-label-sm text-label-sm font-bold">
                                            akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold shadow-sm">
                                            <span class="material-symbols-outlined text-xs">flag</span> nasional
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 6 -->
                                <tr class="hover:bg-surface-container-low/60 transition-colors group">
                                    <td class="py-4 px-5">
                                        <span
                                            class="font-headline-sm text-label-md text-primary font-mono tracking-tight">202011099</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-primary-fixed-dim text-primary font-bold flex items-center justify-center font-headline-sm text-headline-sm shadow-sm">
                                                CJ
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-headline-sm text-headline-sm text-on-surface">Clara
                                                    Jessica</span>
                                                <span class="font-caption text-caption text-secondary">Akuntansi &amp;
                                                    Inbis</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-tertiary-container shrink-0"></span>
                                            <span class="font-body-md text-body-md text-on-surface truncate"
                                                title="Inkubasi Bisnis Mahasiswa Kemenpora 2026">Inkubasi Bisnis
                                                Mahasiswa Kemenpora 2026</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-fixed-variant font-label-sm text-label-sm font-bold">
                                            inbis
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold">
                                            non_akademik
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low text-secondary font-label-sm text-label-sm font-bold">
                                            <span class="material-symbols-outlined text-xs">location_on</span> lokal
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-surface-container-low font-label-sm text-label-sm text-secondary font-mono font-bold">2026</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-surface-container-high text-tertiary hover:bg-tertiary hover:text-on-tertiary font-label-sm text-label-sm font-semibold transition-all duration-200"
                                            href="https://drive.google.com" rel="noopener noreferrer" target="_blank">
                                            <span class="material-symbols-outlined text-base">cloud</span>
                                            <span>Lihat Bukti (GDrive)</span>
                                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                                        </a>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button aria-label="Lihat Detail"
                                                class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface-container transition-colors"
                                                title="Lihat Detail" type="button">
                                                <span class="material-symbols-outlined text-lg">visibility</span>
                                            </button>
                                            <button aria-label="Menu Opsi"
                                                class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container transition-colors"
                                                title="Opsi" type="button">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination & Summary Footer -->
                    <div
                        class="p-space-md sm:px-space-lg flex flex-col sm:flex-row items-center justify-between gap-space-md bg-surface-container-low/40">
                        <div class="text-secondary font-body-sm text-body-sm">
                            Menampilkan <span class="font-semibold text-on-surface">1-6</span> dari <span
                                class="font-semibold text-on-surface">48</span> data kegiatan mahasiswa tahun <span
                                class="font-semibold text-primary font-mono">2026</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-outline hover:bg-surface-container hover:text-on-surface transition-colors disabled:opacity-40"
                                disabled="" type="button">
                                <span class="material-symbols-outlined text-lg">chevron_left</span>
                            </button>
                            <!-- Active Page Number (Blue Sneat Accent) -->
                            <button
                                class="w-9 h-9 rounded-xl bg-primary text-on-primary font-headline-sm text-headline-sm flex items-center justify-center shadow-[0_2px_8px_rgba(69,70,218,0.35)]"
                                type="button">
                                1
                            </button>
                            <button
                                class="w-9 h-9 rounded-xl bg-surface-container-lowest text-secondary hover:bg-surface-container hover:text-on-surface font-headline-sm text-headline-sm flex items-center justify-center transition-colors"
                                type="button">
                                2
                            </button>
                            <button
                                class="w-9 h-9 rounded-xl bg-surface-container-lowest text-secondary hover:bg-surface-container hover:text-on-surface font-headline-sm text-headline-sm flex items-center justify-center transition-colors"
                                type="button">
                                3
                            </button>
                            <span class="px-1 text-outline">...</span>
                            <button
                                class="w-9 h-9 rounded-xl bg-surface-container-lowest text-secondary hover:bg-surface-container hover:text-on-surface font-headline-sm text-headline-sm flex items-center justify-center transition-colors"
                                type="button">
                                8
                            </button>
                            <button
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-secondary hover:bg-surface-container hover:text-on-surface transition-colors"
                                type="button">
                                <span class="material-symbols-outlined text-lg">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // Simple interactive filter resets for high-fidelity feel
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
                        btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span><span>Memuat...</span>';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                        }, 400);
                    }
                });
            </script>
        </main>
    </div>
</body>

</html>
