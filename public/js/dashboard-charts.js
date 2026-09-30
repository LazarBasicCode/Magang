/**
 * JS grafik dashboard (donut, kurva tren, bar chart) + scroll reveal.
 * Dipakai dashboard-admin.blade.php. Sidebar/tema/profil ditangani script.js.
 */
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
    }, { threshold: 0.10, rootMargin: '0px 0px 0px 0px' });
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
// KURVA TREN (per periode: harian/mingguan/bulanan/tahunan)
// Sengaja pakai garis lurus antar titik (bukan bezier/smooth curve)
// dan marker kotak, bukan lingkaran, sesuai gaya "kaku" dashboard ini.
// ================================================================
function renderTrendChart(holder, periodOverride) {
    let allSets = {};
    try { allSets = JSON.parse(holder.dataset.chartSets || '{}'); } catch (_) { allSets = {}; }

    const period = periodOverride || holder.dataset.activePeriod || 'bulanan';
    holder.dataset.activePeriod = period;
    const dataset = allSets[period] || { labels: [], series: [] };
    const labels = dataset.labels || [];
    const series = dataset.series || [];

    const legend = document.getElementById('trendLegend');
    if (legend) {
        legend.innerHTML = series.map((s) => `
            <span class="trend-legend-item">
                <span class="trend-legend-line" style="background:${s.color}"></span>
                <span>${s.label}</span>
            </span>
        `).join('');
    }

    holder.innerHTML = '';

    const hasData = labels.length > 0 && series.some((s) => (s.data || []).some((v) => Number(v) > 0));
    if (!hasData) {
        holder.innerHTML = '<div class="donut-empty">Belum ada data untuk ditampilkan.</div>';
        return;
    }

    // Pakai ukuran asli div di layar sebagai viewBox (bukan angka tetap),
    // supaya 1 unit SVG = 1 pixel asli dan font tidak ikut melar/mengecil
    // saat div dilebar/dikecilkan.
    const rectNow = holder.getBoundingClientRect();
    const W = Math.max(280, Math.round(rectNow.width)) || 640;
    const H = Math.max(160, Math.round(rectNow.height)) || 220;
    const padL = 28, padR = 12, padT = 20, padB = 30;
    const plotW = W - padL - padR;
    const plotH = H - padT - padB;

    const maxVal = Math.max(1, ...series.flatMap((s) => (s.data || []).map((v) => Number(v) || 0)));
    const niceMax = Math.ceil(maxVal / 4) * 4 || 4;

    const ns = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    svg.setAttribute('preserveAspectRatio', 'none');

    const slot = labels.length > 1 ? plotW / (labels.length - 1) : 0;
    const xAt = (i) => labels.length > 1 ? padL + slot * i : padL + plotW / 2;
    const yAt = (v) => padT + plotH - (Number(v) / niceMax) * plotH;

    // Grid horizontal + label sumbu Y
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

    // Label sumbu X (dijarangkan otomatis kalau titiknya banyak)
    const xLabelStep = Math.max(1, Math.ceil(labels.length / 8));
    labels.forEach((label, i) => {
        if (i % xLabelStep !== 0 && i !== labels.length - 1) return;
        const text = document.createElementNS(ns, 'text');
        text.setAttribute('x', xAt(i));
        text.setAttribute('y', H - 8);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('class', 'trend-axis-label');
        text.textContent = label;
        svg.appendChild(text);
    });

    // Satu <path> bergaris LURUS (perintah L, tanpa kurva C/Q) per series
    series.forEach((s) => {
        const data = s.data || [];
        if (!data.length) return;

        let d = '';
        data.forEach((v, i) => {
            const x = xAt(i), y = yAt(v);
            d += (i === 0 ? 'M' : 'L') + x + ' ' + y + ' ';
        });

        const path = document.createElementNS(ns, 'path');
        path.setAttribute('d', d.trim());
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', s.color);
        path.setAttribute('class', 'trend-line');
        svg.appendChild(path);

        // Marker kotak (bukan lingkaran) di tiap titik + tooltip
        data.forEach((v, i) => {
            const x = xAt(i), y = yAt(v);
            const size = 7;
            const marker = document.createElementNS(ns, 'rect');
            marker.setAttribute('x', x - size / 2);
            marker.setAttribute('y', y - size / 2);
            marker.setAttribute('width', size);
            marker.setAttribute('height', size);
            marker.setAttribute('fill', s.color);
            marker.setAttribute('class', 'trend-point');
            const showTip = () => {
                const rBound = holder.getBoundingClientRect();
                const scaleX = rBound.width / W, scaleY = rBound.height / H;
                showChartTooltip(rBound.left + x * scaleX, rBound.top + y * scaleY, `${s.label} · ${labels[i]}: ${v}`);
                marker.classList.add('is-active');
            };
            marker.addEventListener('mouseenter', showTip);
            marker.addEventListener('touchstart', showTip, { passive: true });
            marker.addEventListener('mouseleave', () => { hideChartTooltip(); marker.classList.remove('is-active'); });
            svg.appendChild(marker);
        });
    });

    holder.appendChild(svg);
}

document.querySelectorAll('[data-trendchart]').forEach((holder) => renderTrendChart(holder));

// ---------------- Segmented control: ganti periode kurva tren ----------------
const trendPeriodSwitcher = document.getElementById('trendPeriodSwitcher');
const trendHolder = document.querySelector('[data-trendchart]');
trendPeriodSwitcher?.addEventListener('click', (e) => {
    const btn = e.target.closest('.period-btn');
    if (!btn || !trendHolder) return;

    trendPeriodSwitcher.querySelectorAll('.period-btn').forEach((b) => b.classList.remove('is-active'));
    btn.classList.add('is-active');

    trendHolder.classList.add('is-switching');
    setTimeout(() => {
        renderTrendChart(trendHolder, btn.dataset.period);
        trendHolder.classList.remove('is-switching');
    }, 180);
});

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

    // Sama seperti kurva tren: pakai ukuran div asli di layar, bukan
    // angka tetap, supaya teks label tidak ikut melar/mengecil.
    const rectNow = holder.getBoundingClientRect();
    const W = Math.max(280, Math.round(rectNow.width)) || 640;
    const H = Math.max(160, Math.round(rectNow.height)) || 220;
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

// ---------------- Render ulang saat ukuran window berubah ----------------
// Supaya viewBox (yang sekarang ikut ukuran div asli) selalu presisi,
// bukan cuma dihitung sekali saat halaman pertama dimuat.
let resizeTimer = null;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (trendHolder) renderTrendChart(trendHolder);
        if (barHolder) renderBarChart(barHolder);
    }, 150);
});
