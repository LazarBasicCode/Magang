/**
 * Gembok "belum pernah login" (Data Master) — animasi hanya untuk yang TERLIHAT di layar,
 * dan muncul BERURUTAN mengikuti arah scroll.
 *
 * - Gembok yang masuk viewport diberi kelas .is-visible (memulai animasi di public/css/never-login.css),
 *   kelas itu dicabut saat gembok keluar layar -> animasi mulai ulang setiap kali baris di-scroll kembali.
 * - Gembok yang masuk bersamaan diberi --nl-order (0,1,2,...) sesuai arah scroll:
 *     scroll ke bawah -> dari yang paling atas ke bawah
 *     scroll ke atas  -> dari yang paling bawah ke atas
 *   (saat halaman baru dimuat / ganti tab / cari, urutannya dari atas ke bawah).
 * Baris yang diganti lewat paginasi / tab / pencarian (AJAX) ikut diamati otomatis.
 */
(function () {
    'use strict';

    const SELECTOR = '.never-login-badge';
    const MAX_ORDER = 8; // batas urutan supaya baris paling bawah tidak menunggu terlalu lama
    const body = document.getElementById('userTableBody');
    if (!body) return;

    // Browser sangat lama tanpa IntersectionObserver: langsung tampilkan semua.
    if (!('IntersectionObserver' in window)) {
        const all = () => body.querySelectorAll(SELECTOR).forEach((b) => b.classList.add('is-visible'));
        all();
        new MutationObserver(all).observe(body, { childList: true });
        return;
    }

    // ---- Arah scroll terakhir (scroller manapun: window atau kontainer di dalam halaman) ----
    let direction = 'down';
    const lastTop = new WeakMap();
    document.addEventListener('scroll', (e) => {
        const el = e.target === document ? document.scrollingElement : e.target;
        if (!el || el.scrollTop === undefined) return;
        const prev = lastTop.get(el);
        if (prev !== undefined && el.scrollTop !== prev) direction = el.scrollTop > prev ? 'down' : 'up';
        lastTop.set(el, el.scrollTop);
    }, { capture: true, passive: true });

    const io = new IntersectionObserver((entries) => {
        // Yang keluar layar: cabut kelas supaya animasinya bisa mulai ulang nanti
        entries.filter((en) => !en.isIntersecting).forEach((en) => en.target.classList.remove('is-visible'));

        // Yang masuk layar: urutkan menurut arah scroll lalu beri nomor urut
        const entering = entries.filter((en) => en.isIntersecting && !en.target.classList.contains('is-visible'));
        entering.sort((a, b) => (direction === 'up'
            ? b.boundingClientRect.top - a.boundingClientRect.top
            : a.boundingClientRect.top - b.boundingClientRect.top));
        entering.forEach((en, i) => {
            en.target.style.setProperty('--nl-order', Math.min(i, MAX_ORDER));
            en.target.classList.add('is-visible');
        });
    }, { threshold: 0.6 });

    function observeAll() {
        direction = 'down'; // data baru dimuat: urut dari atas ke bawah
        body.querySelectorAll(SELECTOR).forEach((b) => io.observe(b));
    }

    observeAll();
    // Baris diganti saat pindah halaman / tab / cari -> amati gembok yang baru
    new MutationObserver(observeAll).observe(body, { childList: true });
})();
