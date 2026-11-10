/**
 * Sorotan baris dari notifikasi: blur halus + baris melayang dengan gelombang yang melengkung.
 *
 *   FocusHighlight.run({ page: elemenHalaman, target: <tr> });
 *   FocusHighlight.cancel();
 *   opsi: hold (3000) enter (500) leave (900)  amplitude (9 px)  lift (5 px)  waves (3)  wavelength (0.6 = ~1,7 puncak di sepanjang baris)
 *
 * Gelombang: satu fungsi sinus menjalar kiri -> kanan di SEPANJANG baris. Tiap sel digeser ke tinggi
 * gelombang di tengahnya dan dimiringkan (skewY) sesuai kemiringan gelombang di situ, jadi tepi kanan
 * sel ke-i selalu setinggi tepi kiri sel ke-(i+1): barisnya melengkung menyambung, bukan tangga.
 *
 * Waktu (ms): 0 -> blur masuk, baris naik, gelombang menguat pelan
 *             hold -> blur & baris kembali perlahan selama `leave`
 *             hold + leave -> dibersihkan. Gelombang selesai tepat di `hold` (sinkron dengan blur).
 * Gaya (blur, bayangan, cincin cahaya): public/css/focus-highlight.css
 */
(function (window) {
    'use strict';

    var DEFAULTS = { hold: 3000, enter: 500, leave: 900, amplitude: 6, lift: 8, waves: 3, wavelength: 0.6 };
    var current = null;

    function reducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    function smoothstep(a, b, x) {
        if (b <= a) return x >= b ? 1 : 0;
        var t = Math.min(1, Math.max(0, (x - a) / (b - a)));
        return t * t * (3 - 2 * t);
    }

    /**
     * Murni (tanpa DOM, bisa diuji): hitung transform tiap sel pada waktu t.
     * cells: [{ left, width }] posisi sel relatif ke tepi kiri baris (px).
     * Mengembalikan [{ y, skew, yl, yr }] -> y = geser vertikal (px), skew = derajat untuk skewY.
     */
    function calc(cells, rowWidth, t, o) {
        var hold = o.hold, period = hold / o.waves, lambda = Math.max(1, rowWidth * o.wavelength);
        // Amplitudo gelombang menguat pelan di awal dan meredup sebelum hold supaya tidak ada lompatan.
        var envelope = smoothstep(0, hold * 0.15, t) * (1 - smoothstep(hold * 0.8, hold, t));
        // Tinggi "melayang": naik selama blur masuk, bertahan sampai hold, turun selama leave.
        var lift = -o.lift * smoothstep(0, o.enter, t) * (1 - smoothstep(hold, hold + o.leave, t));

        function h(x) { // tinggi gelombang di posisi x (px)
            return envelope * o.amplitude * Math.sin(2 * Math.PI * (x / lambda - t / period));
        }

        return cells.map(function (c) {
            var yl = h(c.left), yr = h(c.left + c.width);
            return {
                y: lift + (yl + yr) / 2,
                skew: Math.atan((yr - yl) / c.width) * 180 / Math.PI,
                yl: lift + yl, yr: lift + yr, // tinggi tepi kiri/kanan (untuk pengujian kontinuitas)
            };
        });
    }

    function clearCells(cells) {
        cells.forEach(function (td) {
            td.style.transform = '';
            td.style.removeProperty('--fx-delay');
            td.style.removeProperty('--fx-period');
        });
    }

    function cancel() {
        if (!current) return;
        var c = current;
        current = null;
        c.timers.forEach(clearTimeout);
        if (c.raf) window.cancelAnimationFrame(c.raf);
        c.page.classList.remove('is-focus-mode', 'fx-armed');
        ['--fx-in', '--fx-hold', '--fx-out'].forEach(function (v) { c.page.style.removeProperty(v); });
        c.target.classList.remove('is-focus-target');
        clearCells(c.cells);
    }

    function run(opts) {
        if (!opts || !opts.page || !opts.target) return;
        cancel();

        var o = {};
        Object.keys(DEFAULTS).forEach(function (k) { o[k] = opts[k] != null ? opts[k] : DEFAULTS[k]; });
        var page = opts.page, target = opts.target, reduce = reducedMotion();

        page.style.setProperty('--fx-in', o.enter + 'ms');
        page.style.setProperty('--fx-hold', o.hold + 'ms');
        page.style.setProperty('--fx-out', o.leave + 'ms');

        var cells = Array.prototype.filter.call(target.children, function (c) { return c.tagName === 'TD'; });

        // Cincin cahaya (CSS) merambat kiri -> kanan dan selesai di `hold`, selaras dengan gelombang.
        var n = cells.length;
        var stagger = n > 1 ? Math.min(90, 700 / (n - 1)) : 0;
        var ringPeriod = Math.max(500, (o.hold - stagger * (n - 1)) / o.waves);
        cells.forEach(function (td, i) {
            td.style.setProperty('--fx-delay', Math.round(i * stagger) + 'ms');
            td.style.setProperty('--fx-period', Math.round(ringPeriod) + 'ms');
        });

        var state = { page: page, target: target, cells: cells, timers: [], raf: 0 };
        current = state;

        // Ukur posisi sel sekali (tata letak tidak berubah selama sorotan).
        var geom = [], rowWidth = 0;
        if (cells.length) {
            var first = cells[0].getBoundingClientRect();
            var last = cells[cells.length - 1].getBoundingClientRect();
            rowWidth = Math.max(1, last.right - first.left);
            geom = cells.map(function (td) {
                var r = td.getBoundingClientRect();
                return { left: r.left - first.left, width: Math.max(1, r.width) };
            });
        }

        // 1) pasang transisi dulu, 2) nyalakan sorotan di frame berikutnya (agar transisi masuk berjalan).
        page.classList.add('fx-armed');
        void page.offsetWidth;
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                if (current !== state) return;
                page.classList.add('is-focus-mode');
                target.classList.add('is-focus-target');
            });
        });

        if (target.scrollIntoView) target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });

        // Gelombang digerakkan per frame dari jam yang sama dengan timer `hold` (tidak melenceng).
        if (!reduce && geom.length) {
            var t0 = window.performance.now();
            var frame = function () {
                if (current !== state) return;
                var t = window.performance.now() - t0;
                if (t >= o.hold + o.leave) { clearCells(cells); return; }
                var out = calc(geom, rowWidth, t, o);
                cells.forEach(function (td, i) {
                    // scaleX kecil menutup garis tipis di sambungan sel akibat pembulatan piksel
                    td.style.transform = 'translateY(' + out[i].y.toFixed(2) + 'px) skewY(' + out[i].skew.toFixed(3) + 'deg) scaleX(1.004)';
                });
                state.raf = window.requestAnimationFrame(frame);
            };
            state.raf = window.requestAnimationFrame(frame);
        }

        // Selesai: blur & gaya baris dilepas bersamaan dengan transisi halus (tidak dipotong).
        state.timers.push(setTimeout(function () {
            if (current !== state) return;
            page.classList.remove('is-focus-mode');
            target.classList.remove('is-focus-target');
        }, o.hold));

        state.timers.push(setTimeout(function () {
            if (current === state) cancel();
        }, o.hold + o.leave + 120));
    }

    window.FocusHighlight = { run: run, cancel: cancel, _calc: calc };
})(window);
