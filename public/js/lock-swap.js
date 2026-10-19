/**
 * Kolom form yang "terkunci" saat tidak dibutuhkan, dengan animasi kunci & buka kunci.
 * Dipakai ulang di form manapun (contoh: "Jenis Kerja Sama Lainnya" di halaman Kerja Sama).
 *
 * Markup (gaya kotak terkunci memakai .field-locked yang sudah ada di style.css):
 *   <div class="lock-swap is-locked" id="lainnyaSwap">
 *     <div class="field-control lock-swap-input"><input id="..." type="text"></div>
 *     <div class="field-locked lock-swap-locked">
 *       <div class="field-locked-inner">
 *         <span class="material-symbols-outlined lock-ic">lock</span>
 *         <span class="field-locked-value">Teks saat terkunci</span>
 *       </div>
 *     </div>
 *   </div>
 *
 * Pakai:
 *   const lock = LockSwap.create(document.getElementById('lainnyaSwap'));
 *   lock.setLocked(true);          // langsung terkunci, tanpa animasi (mis. saat modal dibuka)
 *   lock.setLocked(false, true);   // buka kunci DENGAN animasi, lalu input aktif & fokus
 * Saat terkunci, input otomatis disabled. Gaya & animasi: public/css/lock-swap.css
 */
(function (window) {
    'use strict';

    function create(root, opts) {
        opts = opts || {};
        var control = root.querySelector('input, textarea, select');
        var icon = root.querySelector('.lock-ic');
        var timers = [];
        var locked = root.classList.contains('is-locked');
        var reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

        function later(fn, ms) { timers.push(setTimeout(fn, ms)); }
        function clearTimers() { timers.forEach(clearTimeout); timers = []; }
        function setIcon(name) { if (icon) icon.textContent = name; }

        function setLocked(next, animate) {
            clearTimers();
            var anim = !!animate && !reduce;
            locked = next;

            if (next) {
                if (control) control.disabled = true;
                root.classList.remove('is-unlocked', 'is-unlocking');
                root.classList.add('is-locked');
                if (!anim) {
                    root.classList.remove('is-snap');
                    setIcon('lock');
                    return;
                }
                // gembok masih terbuka sesaat lalu "klik" menutup
                setIcon('lock_open');
                later(function () {
                    setIcon('lock');
                    root.classList.add('is-snap');
                    later(function () { root.classList.remove('is-snap'); }, 420);
                }, 180);
                return;
            }

            // buka kunci
            root.classList.remove('is-snap');
            if (!anim) {
                root.classList.remove('is-locked', 'is-unlocking');
                root.classList.add('is-unlocked');
                setIcon('lock_open');
                if (control) control.disabled = false;
                return;
            }
            root.classList.add('is-unlocking');   // gembok bergetar sambil terbuka
            setIcon('lock_open');
            later(function () {
                root.classList.remove('is-locked', 'is-unlocking');
                root.classList.add('is-unlocked');
                if (control) {
                    control.disabled = false;
                    if (opts.focus !== false) control.focus({ preventScroll: true });
                }
            }, 380);
        }

        return { setLocked: setLocked, isLocked: function () { return locked; } };
    }

    window.LockSwap = { create: create };
})(window);
