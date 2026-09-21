<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Dashboard &middot; SIDA</title>
</head>

<body>
    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <img alt="Logo Institut Asia Malang" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-title">SIDA</span>
                <span class="sidebar-brand-sub">Institut Asia Malang</span>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup Menu">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <a href="{{ url('/dashboard') }}" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ url('/kemahasiswaan') }}" class="nav-link">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>

                <div class="nav-heading">LPPM</div>
                <a href="{{ url('/lppm/mahasiswa') }}" class="nav-link">
                    <span class="material-symbols-outlined">person</span>
                    <span>Mahasiswa</span>
                </a>
                <a href="{{ url('/lppm/rekognisi') }}" class="nav-link">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="nav-heading">Kemitraan</div>
                <a href="{{ url('/kerja-sama') }}" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-help">
            <div class="help-card">
                <p class="help-card-text">© Prodi IT - Institut Asia Malang</p>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        <header class="app-header">
            <div class="header-capsule">
                <div class="header-inner">
                    <button type="button" class="icon-btn sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Buka Menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="header-crumb">
                        <span class="current">Dashboard</span>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="icon-btn" aria-label="Notifikasi">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="dot"></span>
                        </button>
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">{{ auth()->user()->name }}</span>
                                <span class="header-profile-role">{{ auth()->user()->accessLabelFor('dashboard') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                <!-- WELCOME BANNER -->
                <div class="dash-welcome reveal">
                    <p class="dash-welcome-title">Halo, {{ auth()->user()->name }} 👋</p>
                    <p class="dash-welcome-sub">Ringkasan kegiatanmu di Sistem Informasi Data Akademik &middot; 2026</p>
                </div>

                <!-- HERO STAT CARDS (gradient) -->
                <div class="stat-hero-grid">
                    <div class="stat-hero-card grad-1 reveal" style="transition-delay:0ms">
                        <span class="material-symbols-outlined stat-hero-icon">local_fire_department</span>
                        <p class="stat-hero-label">Total Kegiatan</p>
                        <div class="stat-hero-value">{{ $stats['total'] }} <span class="stat-hero-unit">kegiatan</span></div>
                    </div>
                    <div class="stat-hero-card grad-2 reveal" style="transition-delay:70ms">
                        <span class="material-symbols-outlined stat-hero-icon">trending_up</span>
                        <p class="stat-hero-label">Rata-rata per Bulan</p>
                        <div class="stat-hero-value">{{ $stats['avg_per_month'] }} <span class="stat-hero-unit">kegiatan/bln</span></div>
                    </div>
                    <div class="stat-hero-card grad-3 reveal" style="transition-delay:140ms">
                        <span class="material-symbols-outlined stat-hero-icon">public</span>
                        <p class="stat-hero-label">Capaian Internasional</p>
                        <div class="stat-hero-value">{{ $stats['internasional_pct'] }}<span class="stat-hero-unit">%</span></div>
                    </div>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card reveal" style="transition-delay:0ms">
                        <div class="stat-info">
                            <span class="stat-label">Kemahasiswaan</span>
                            <span class="stat-value">{{ $stats['kemahasiswaan'] }}</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">school</span>
                        </div>
                    </div>
                    <div class="stat-card reveal" style="transition-delay:70ms">
                        <div class="stat-info">
                            <span class="stat-label">LPPM Mahasiswa</span>
                            <span class="stat-value">{{ $stats['lppm_mahasiswa'] }}</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                    </div>
                    <div class="stat-card reveal" style="transition-delay:140ms">
                        <div class="stat-info">
                            <span class="stat-label">Rekognisi</span>
                            <span class="stat-value">{{ $stats['rekognisi'] }}</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">workspace_premium</span>
                        </div>
                    </div>
                    <div class="stat-card reveal" style="transition-delay:210ms">
                        <div class="stat-info">
                            <span class="stat-label">Kerja Sama</span>
                            <span class="stat-value">{{ $stats['kerja_sama'] }}</span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">handshake</span>
                        </div>
                    </div>
                </div>

                <!-- DASHBOARD GRID -->
                <div class="dash-grid">

                    <!-- DONUT: kategori -->
                    <div class="dash-card donut-card reveal">
                        <h2 class="dash-card-title">Distribusi Kegiatan per Kategori</h2>
                        <p class="dash-card-sub">Sebaran kegiatanmu di 4 menu</p>
                        <div class="donut-widget" data-donut data-caption="Kegiatan" data-chart='@json($kategoriDonut)'>
                            <div class="donut-svg-holder">
                                <svg viewBox="0 0 200 200"></svg>
                                <div class="donut-center-label">
                                    <span class="donut-center-value">0</span>
                                    <span class="donut-center-caption">Kegiatan</span>
                                </div>
                            </div>
                            <div class="donut-legend"></div>
                        </div>
                    </div>

                    <!-- DONUT: tingkat -->
                    <div class="dash-card donut-card reveal" style="transition-delay:80ms">
                        <h2 class="dash-card-title">Distribusi Tingkat Capaian</h2>
                        <p class="dash-card-sub">Berdasarkan data Kemahasiswaan</p>
                        <div class="donut-widget" data-donut data-caption="Capaian" data-chart='@json($tingkatDonut)'>
                            <div class="donut-svg-holder">
                                <svg viewBox="0 0 200 200"></svg>
                                <div class="donut-center-label">
                                    <span class="donut-center-value">0</span>
                                    <span class="donut-center-caption">Capaian</span>
                                </div>
                            </div>
                            <div class="donut-legend"></div>
                        </div>
                    </div>

                    <!-- BAR CHART: per periode (harian/mingguan/bulanan/tahunan) -->
                    <div class="dash-card reveal">
                        <div class="trend-card-top">
                            <div>
                                <h2 class="dash-card-title">Kegiatan per Periode</h2>
                                <p class="dash-card-sub">Gabungan 4 kategori</p>
                            </div>
                            <span class="trend-hint">
                                <span class="material-symbols-outlined">info</span>
                                Arahkan kursor ke batang untuk detail
                            </span>
                        </div>
                        <div class="period-switcher" id="periodSwitcher" role="tablist">
                            <button type="button" class="period-btn" data-period="harian">Harian</button>
                            <button type="button" class="period-btn" data-period="mingguan">Mingguan</button>
                            <button type="button" class="period-btn is-active" data-period="bulanan">Bulanan</button>
                            <button type="button" class="period-btn" data-period="tahunan">Tahunan</button>
                        </div>
                        <div class="bar-chart-holder" data-barchart data-active-period="bulanan" data-chart-sets='@json($barDatasets)'></div>
                    </div>

                    <!-- AKTIVITAS TERBARU -->
                    <div class="dash-card reveal" style="transition-delay:80ms">
                        <h2 class="dash-card-title">Aktivitas Terbaru</h2>
                        <p class="dash-card-sub">6 kegiatan terakhir yang kamu tambahkan</p>
                        <div class="recent-list">
                            @forelse($recent as $item)
                                <div class="recent-item">
                                    <div class="recent-item-icon">
                                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                                    </div>
                                    <div class="recent-item-text">
                                        <span class="recent-item-title">{{ $item['title'] ?? '(tanpa judul)' }}</span>
                                        <span class="recent-item-meta">{{ $item['menu'] }} &middot; {{ $item['date']?->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="recent-empty">Belum ada aktivitas yang tercatat.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="chart-tooltip" id="chartTooltip"></div>

    <script>
        // ---------------- Sidebar Drawer ----------------
        const sidebar = document.querySelector('.app-sidebar');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('is-open');
            sidebarOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('is-open');
            sidebarOverlay.classList.remove('is-active');
            document.body.style.overflow = '';
        }
        sidebarToggleBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeSidebar();
        });

        // ---------------- Dark Mode ----------------
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeIcon) themeIcon.textContent = 'light_mode';
        }
        themeToggleBtn?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            if (themeIcon) themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // ================================================================
        // SCROLL REVEAL: fade + blur + scale saat elemen masuk viewport,
        // dan diulang lagi setiap kali elemen keluar lalu masuk ulang.
        // ================================================================
        const revealEls = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    entry.target.classList.toggle('is-visible', entry.isIntersecting);
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
            revealEls.forEach((el) => revealObserver.observe(el));
        } else {
            revealEls.forEach((el) => el.classList.add('is-visible'));
        }

        // ================================================================
        // TOOLTIP BERSAMA (dipakai donut, kurva, dan bar chart)
        // ================================================================
        const chartTooltip = document.getElementById('chartTooltip');
        function showChartTooltip(x, y, text) {
            chartTooltip.textContent = text;
            chartTooltip.style.left = x + 'px';
            chartTooltip.style.top = y + 'px';
            chartTooltip.classList.add('is-active');
        }
        function hideChartTooltip() {
            chartTooltip.classList.remove('is-active');
        }

        // ================================================================
        // DONUT CHART
        // ================================================================
        function polarToCartesian(cx, cy, r, angleDeg) {
            const a = (angleDeg - 90) * Math.PI / 180;
            return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
        }

        function sliceLargePath(cx, cy, rOuter, rInner, startAngle, endAngle) {
            if (endAngle - startAngle >= 359.99) endAngle = startAngle + 359.98;
            const startOuter = polarToCartesian(cx, cy, rOuter, endAngle);
            const endOuter = polarToCartesian(cx, cy, rOuter, startAngle);
            const startInner = polarToCartesian(cx, cy, rInner, endAngle);
            const endInner = polarToCartesian(cx, cy, rInner, startAngle);
            const largeArc = endAngle - startAngle <= 180 ? 0 : 1;
            return [
                'M', startOuter.x, startOuter.y,
                'A', rOuter, rOuter, 0, largeArc, 0, endOuter.x, endOuter.y,
                'L', endInner.x, endInner.y,
                'A', rInner, rInner, 0, largeArc, 1, startInner.x, startInner.y,
                'Z',
            ].join(' ');
        }

        function renderDonut(widget) {
            let data = [];
            try { data = JSON.parse(widget.dataset.chart || '[]'); } catch (_) { data = []; }

            const svg = widget.querySelector('svg');
            const centerValue = widget.querySelector('.donut-center-value');
            const centerCaption = widget.querySelector('.donut-center-caption');
            const legend = widget.querySelector('.donut-legend');
            const holder = widget.querySelector('.donut-svg-holder');
            const caption = widget.dataset.caption || '';

            const total = data.reduce((sum, d) => sum + (Number(d.value) || 0), 0);
            centerValue.textContent = total;
            centerCaption.textContent = caption;
            legend.innerHTML = '';
            svg.innerHTML = '';

            if (total === 0) {
                holder.style.display = 'none';
                const empty = document.createElement('div');
                empty.className = 'donut-empty';
                empty.textContent = 'Belum ada data untuk ditampilkan.';
                widget.appendChild(empty);
                return;
            }

            const cx = 100, cy = 100, rOuter = 90, rInner = 55;
            let cursor = 0;
            const ns = 'http://www.w3.org/2000/svg';

            data.forEach((d) => {
                const value = Number(d.value) || 0;
                const pct = value / total;
                if (value > 0) {
                    const startAngle = cursor * 360;
                    const endAngle = (cursor + pct) * 360;
                    const path = document.createElementNS(ns, 'path');
                    path.setAttribute('d', sliceLargePath(cx, cy, rOuter, rInner, startAngle, endAngle));
                    path.setAttribute('fill', d.color);
                    path.classList.add('donut-slice');

                    const midAngle = (startAngle + endAngle) / 2;
                    const anchor = polarToCartesian(cx, cy, rOuter, midAngle);

                    const showTip = () => {
                        const rect = holder.getBoundingClientRect();
                        const scaleX = rect.width / 200;
                        const scaleY = rect.height / 200;
                        showChartTooltip(rect.left + anchor.x * scaleX, rect.top + anchor.y * scaleY, `${d.label}: ${value}`);
                        path.classList.add('is-active');
                    };
                    path.addEventListener('mouseenter', showTip);
                    path.addEventListener('touchstart', showTip, { passive: true });
                    path.addEventListener('mouseleave', () => { hideChartTooltip(); path.classList.remove('is-active'); });

                    svg.appendChild(path);
                    cursor += pct;
                }

                const legendItem = document.createElement('div');
                legendItem.className = 'donut-legend-item';
                legendItem.innerHTML = `
                    <span class="donut-legend-dot" style="background:${d.color}"></span>
                    <span>${d.label}</span>
                    <span class="donut-legend-value">${value}</span>
                `;
                legend.appendChild(legendItem);
            });
        }

        document.querySelectorAll('[data-donut]').forEach(renderDonut);

        // ================================================================
        // BAR CHART (per periode: harian/mingguan/bulanan/tahunan)
        // ================================================================
        function renderBarChart(holder, periodOverride) {
            let allSets = {};
            try { allSets = JSON.parse(holder.dataset.chartSets || '{}'); } catch (_) { allSets = {}; }

            const period = periodOverride || holder.dataset.activePeriod || 'bulanan';
            holder.dataset.activePeriod = period;
            const data = allSets[period] || [];

            holder.innerHTML = '';

            if (!data.length) {
                holder.innerHTML = '<div class="donut-empty">Belum ada data untuk ditampilkan.</div>';
                return;
            }

            const W = 640, H = 220;
            const padL = 28, padR = 12, padT = 20, padB = 30;
            const plotW = W - padL - padR;
            const plotH = H - padT - padB;

            const maxVal = Math.max(1, ...data.map((d) => Number(d.value) || 0));
            const niceMax = Math.ceil(maxVal / 4) * 4 || 4;

            const ns = 'http://www.w3.org/2000/svg';
            const svg = document.createElementNS(ns, 'svg');
            svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
            svg.setAttribute('preserveAspectRatio', 'none');

            // Gradient dipakai semua batang
            const defs = document.createElementNS(ns, 'defs');
            const gradient = document.createElementNS(ns, 'linearGradient');
            gradient.setAttribute('id', 'barGradient');
            gradient.setAttribute('x1', '0'); gradient.setAttribute('y1', '1');
            gradient.setAttribute('x2', '0'); gradient.setAttribute('y2', '0');
            gradient.innerHTML = `
                <stop offset="0%" stop-color="var(--chart-1)" />
                <stop offset="100%" stop-color="var(--chart-2)" />
            `;
            defs.appendChild(gradient);
            svg.appendChild(defs);

            // Grid horizontal + label sumbu Y (sama gaya seperti kurva tren)
            [0, 0.25, 0.5, 0.75, 1].forEach((f) => {
                const y = padT + plotH * (1 - f);
                const line = document.createElementNS(ns, 'line');
                line.setAttribute('x1', padL); line.setAttribute('x2', W - padR);
                line.setAttribute('y1', y); line.setAttribute('y2', y);
                line.setAttribute('class', 'trend-grid-line');
                svg.appendChild(line);

                const text = document.createElementNS(ns, 'text');
                text.setAttribute('x', 2); text.setAttribute('y', y + 3);
                text.setAttribute('class', 'trend-axis-label');
                text.textContent = Math.round(niceMax * f);
                svg.appendChild(text);
            });

            // Batang + label nilai + label sumbu X
            const slot = plotW / data.length;
            const barWidth = Math.min(46, slot * 0.5);

            // Grid vertikal tipis di tengah tiap slot batang
            data.forEach((d, i) => {
                const cx = padL + slot * (i + 0.5);
                const vLine = document.createElementNS(ns, 'line');
                vLine.setAttribute('x1', cx); vLine.setAttribute('x2', cx);
                vLine.setAttribute('y1', padT); vLine.setAttribute('y2', padT + plotH);
                vLine.setAttribute('class', 'trend-grid-line-v');
                svg.appendChild(vLine);
            });

            data.forEach((d, i) => {
                const value = Number(d.value) || 0;
                const barH = (value / niceMax) * plotH;
                const cx = padL + slot * (i + 0.5);
                const x = cx - barWidth / 2;
                const y = padT + plotH - barH;

                const rect = document.createElementNS(ns, 'rect');
                rect.setAttribute('x', x);
                rect.setAttribute('y', y);
                rect.setAttribute('width', barWidth);
                rect.setAttribute('height', Math.max(3, barH));
                rect.setAttribute('rx', 6);
                rect.setAttribute('class', 'bar-rect');
                const showTip = () => {
                    const rBound = holder.getBoundingClientRect();
                    const scaleX = rBound.width / W, scaleY = rBound.height / H;
                    showChartTooltip(rBound.left + cx * scaleX, rBound.top + y * scaleY, `${d.label}: ${value} kegiatan`);
                    rect.classList.add('is-active');
                };
                rect.addEventListener('mouseenter', showTip);
                rect.addEventListener('touchstart', showTip, { passive: true });
                rect.addEventListener('mouseleave', () => { hideChartTooltip(); rect.classList.remove('is-active'); });
                svg.appendChild(rect);

                const valueLabel = document.createElementNS(ns, 'text');
                valueLabel.setAttribute('x', cx);
                valueLabel.setAttribute('y', y - 8);
                valueLabel.setAttribute('class', 'bar-value-label');
                valueLabel.textContent = value;
                svg.appendChild(valueLabel);

                const xLabel = document.createElementNS(ns, 'text');
                xLabel.setAttribute('x', cx);
                xLabel.setAttribute('y', H - 8);
                xLabel.setAttribute('text-anchor', 'middle');
                xLabel.setAttribute('class', 'trend-axis-label');
                xLabel.textContent = d.label;
                svg.appendChild(xLabel);
            });

            holder.appendChild(svg);
        }

        document.querySelectorAll('[data-barchart]').forEach((holder) => renderBarChart(holder));

        // ---------------- Segmented control: ganti periode bar chart ----------------
        const periodSwitcher = document.getElementById('periodSwitcher');
        const barHolder = document.querySelector('[data-barchart]');
        periodSwitcher?.addEventListener('click', (e) => {
            const btn = e.target.closest('.period-btn');
            if (!btn || !barHolder) return;

            periodSwitcher.querySelectorAll('.period-btn').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            barHolder.classList.add('is-switching');
            setTimeout(() => {
                renderBarChart(barHolder, btn.dataset.period);
                barHolder.classList.remove('is-switching');
            }, 180);
        });
    </script>
</body>

</html>
