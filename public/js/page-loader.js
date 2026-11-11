/**
 * Loading screen login -> dashboard (HANYA dari login).
 *
 * Penanda "baru login" disimpan di sessionStorage (kunci FLAG) saat form login
 * dikirim. Dashboard hanya menampilkan loader kalau penanda itu ada dan masih
 * baru, lalu langsung menghapusnya. Jadi pindah menu (mis. Persetujuan ->
 * Dashboard), Back, atau refresh TIDAK memunculkan loader lagi.
 *
 * - Halaman login : PageLoader.show() dipanggil saat form dikirim.
 * - Dashboard     : overlay sudah tampil sejak HTML pertama (class is-active +
 *   data-auto-hide). Overlay hilang sendiri begitu SEMUA ini selesai:
 *     1. window "load"  (gambar, CSS, script)
 *     2. document.fonts.ready (font Public Sans & ikon Material Symbols)
 *     3. promise tambahan lewat PageLoader.wait(promise) — mis. fetch data
 *   Tidak ada durasi tetap: kalau masih memuat, loading terus; kalau sudah
 *   selesai, langsung hilang. Dua pengaman:
 *     MIN_MS : tampil minimal sebentar agar tidak berkedip pada koneksi cepat.
 *     MAX_MS : batas atas supaya layar tidak macet selamanya bila ada yang gagal.
 */
(function () {
    var el = document.getElementById('pageLoader');
    if (!el) return;

    var FLAG = 'sida.justLoggedIn';
    var FLAG_TTL = 30000; // penanda kedaluwarsa 30 detik (jaga-jaga login gagal/terputus)
    var MIN_MS = 700;
    var MAX_MS = 15000;
    var shownAt = Date.now();
    var pending = [];
    var done = false;

    function show(instant) {
        shownAt = Date.now();
        done = false;
        el.classList.remove('is-leaving');
        el.classList.toggle('is-instant', !!instant);
        el.classList.add('is-active');
        if (!instant) void el.offsetWidth; // paksa reflow supaya fade-in berjalan
        el.classList.add('is-shown');
        el.setAttribute('aria-busy', 'true');
    }

    function hide() {
        if (done) return;
        done = true;
        var wait = Math.max(0, MIN_MS - (Date.now() - shownAt));
        setTimeout(function () {
            el.classList.remove('is-instant');
            document.body.classList.add('pl-enter');
            el.classList.remove('is-shown');
            el.classList.add('is-leaving');
            el.setAttribute('aria-busy', 'false');
            setTimeout(function () {
                el.classList.remove('is-active', 'is-leaving');
                document.body.classList.remove('pl-enter');
            }, 750);
        }, wait);
    }

    function wait(p) { pending.push(Promise.resolve(p)); }

    function markLogin() {
        try { sessionStorage.setItem(FLAG, String(Date.now())); } catch (e) {}
    }
    function clearMark() {
        try { sessionStorage.removeItem(FLAG); } catch (e) {}
    }
    function consumeMark() {
        var t = 0;
        try { t = parseInt(sessionStorage.getItem(FLAG), 10) || 0; } catch (e) {}
        clearMark();
        return t > 0 && (Date.now() - t) < FLAG_TTL;
    }

    window.PageLoader = { show: show, hide: hide, wait: wait, markLogin: markLogin };

    // Mode dashboard: hilang otomatis setelah semuanya selesai dimuat.
    if (el.hasAttribute('data-auto-hide')) {
        // Bukan datang dari login (pindah menu, Back, refresh): tidak tampil sama sekali.
        if (!consumeMark()) return;
        show(true);

        var loaded = new Promise(function (resolve) {
            if (document.readyState === 'complete') resolve();
            else window.addEventListener('load', resolve, { once: true });
        });
        var fonts = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();

        // Beri kesempatan script lain mendaftar lewat PageLoader.wait() sebelum dicek.
        loaded.then(function () { return fonts; })
            .then(function () { return Promise.all(pending); })
            .catch(function () {})
            .then(hide);

        setTimeout(hide, MAX_MS);
    }

    // Halaman login: bersihkan sisa penanda (mis. login sebelumnya gagal).
    if (!el.hasAttribute('data-auto-hide')) clearMark();

    // Kembali lewat tombol Back (bfcache): jangan tampilkan loader yang basi.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted && !el.hasAttribute('data-auto-hide')) {
            el.classList.remove('is-active', 'is-shown', 'is-leaving');
        }
    });
})();
