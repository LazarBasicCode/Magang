/**
 * =====================================================================
 * FOTO PROFIL — modal "Ubah Foto Profil" (seret & lepas / pilih berkas + atur bagian foto)
 *
 * Alur (sederhana):
 *   pilih/seret foto -> ATUR BAGIAN FOTO (kotak crop persegi: digeser, sudutnya ditarik untuk memperbesar/
 *   memperkecil; lingkaran di tengah = gambaran avatar bulat) -> Simpan: bagian terpilih dipotong & dikecilkan
 *   di browser (persegi, maks 256 px, WebP ±20 KB) -> dikirim ke POST /account/avatar (AvatarController meneruskan ke layanan hosting gambar
 *      dan menyimpan LINK-nya di users.avatar_url) -> semua avatar milik user ini di halaman langsung berganti.
 *
 * Cara memakai di halaman mana pun: beri atribut data-avatar-edit pada elemen pemicu.
 * Avatar milik user login (yang harus ikut berganti) diberi data-avatar-self (partials/avatar.blade.php, self=true).
 * Markup modal: resources/views/partials/avatar-modal.blade.php (dipasang sekali di header).
 * =====================================================================
 */
(function (window, document) {
    'use strict';

    const modal = document.getElementById('avatarModal');
    if (!modal) return;
    document.body.appendChild(modal); // di <body> supaya tidak terkurung header yang punya efek blur/transform

    const SIZE = 256;                    // sisi foto hasil kecil (px)
    const MAX_INPUT = 8 * 1024 * 1024;   // batas berkas asli yang dipilih
    const q = (sel) => modal.querySelector(sel);
    const card = q('.av-card');
    const drop = q('[data-av-drop]');
    const input = q('[data-av-input]');
    const preview = q('[data-av-preview]');
    const info = q('[data-av-info]');
    const errorEl = q('[data-av-error]');
    const btnSave = q('[data-av-save]');
    const btnRemove = q('[data-av-remove]');
    const saveLabel = q('[data-av-save-label]');
    const stageEl = q('.av-stage');
    const cropper = q('[data-av-cropper]');
    const wrap = q('[data-av-wrap]');
    const cstage = q('[data-av-cstage]');
    const cimg = q('[data-av-cimg]');
    const box = q('[data-av-box]');
    const shade = q('[data-av-shade]');
    const live = q('[data-av-live]');
    const btnRechoose = q('[data-av-rechoose]');

    const MIN_BOX = 56;                  // sisi kotak crop terkecil di layar (px)
    const STAGE_MAX_H = 340;             // tinggi maksimum area crop di layar (px)

    let bitmap = null;                   // foto asli (sumber potongan)
    let crop = null;                     // kotak crop { x, y, s } dalam piksel FOTO ASLI
    let view = null;                     // { k, w, h }: 1 px foto = k px layar
    let drag = null;
    let liveQueued = false;
    let busy = false;
    let lastFocus = null;

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = (type, message) => (window.Toast ? window.Toast.show({ type, message }) : window.alert(message));
    const selfAvatars = () => Array.from(document.querySelectorAll('[data-avatar-self]')).filter((el) => !modal.contains(el));
    const currentPhoto = () => selfAvatars().some((el) => el.querySelector('.av-img'));

    // ---------------------------------------------------------------- buka / tutup
    function open() {
        lastFocus = document.activeElement;
        resetState();
        const first = selfAvatars()[0];
        preview.replaceChildren();
        if (first) {
            // Salinan bersih: tanpa atribut tombol, tombol kamera, dan kelas khusus halaman (mis. .pf-avatar & cincin kaca)
            const clone = first.cloneNode(true);
            const color = (first.className.match(/\bc-\w+/) || [''])[0];
            ['data-avatar-self', 'data-avatar-edit', 'role', 'tabindex', 'title', 'aria-label'].forEach((a) => clone.removeAttribute(a));
            clone.className = ('avatar ' + color).trim();
            clone.querySelector('.av-edit-badge')?.remove();
            preview.appendChild(clone);
        }
        btnRemove.hidden = !currentPhoto();
        modal.hidden = false;
        drop.focus({ preventScroll: true });
    }

    function close() {
        if (busy) return;
        modal.hidden = true;
        resetState();
        lastFocus?.focus?.({ preventScroll: true });
    }

    function resetState() {
        if (bitmap && bitmap.close) bitmap.close();
        bitmap = null;
        crop = null;
        view = null;
        drag = null;
        input.value = '';
        cropper.hidden = true;
        stageEl.hidden = false;
        card.classList.remove('is-cropping');
        btnSave.disabled = true;
        info.hidden = true;
        showError('');
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.hidden = !message;
    }

    // ---------------------------------------------------------------- kecilkan foto
    async function loadBitmap(file) {
        if (window.createImageBitmap) {
            try { return await createImageBitmap(file, { imageOrientation: 'from-image' }); } catch (e) { /* lanjut ke <img> */ }
        }
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode')); };
            img.src = url;
        });
    }

    const toBlob = (canvas, type, quality) => new Promise((resolve) => canvas.toBlob(resolve, type, quality));
    const clamp = (v, lo, hi) => Math.min(Math.max(v, lo), Math.max(lo, hi));

    // ---------------------------------------------------------------- atur bagian foto (crop)
    /** Ukuran terkecil kotak, dalam piksel foto asli. */
    const minBox = () => Math.min(MIN_BOX / view.k, Math.min(bitmap.width, bitmap.height));

    /** Hitung skala layar, gambar foto ke kanvas, lalu letakkan kotak crop. */
    function layout() {
        if (!bitmap) return;
        const iw = bitmap.width, ih = bitmap.height;
        const k = Math.min(3, Math.min(Math.max(120, wrap.clientWidth) / iw, STAGE_MAX_H / ih));
        view = { k, w: iw * k, h: ih * k };
        cstage.style.width = view.w + 'px';
        cstage.style.height = view.h + 'px';

        const dpr = window.devicePixelRatio || 1;
        cimg.width = Math.round(view.w * dpr);
        cimg.height = Math.round(view.h * dpr);
        const g = cimg.getContext('2d');
        g.imageSmoothingQuality = 'high';
        g.drawImage(bitmap, 0, 0, cimg.width, cimg.height);
        renderBox();
    }

    function renderBox() {
        if (!crop || !view) return;
        const { k } = view;
        [box, shade].forEach((el) => {
            el.style.transform = 'translate(' + crop.x * k + 'px,' + crop.y * k + 'px)';
            el.style.width = el.style.height = crop.s * k + 'px';
        });

        const side = Math.round(crop.s);
        info.textContent = 'Area dipilih ' + side + ' × ' + side + ' px → disimpan ' + Math.min(SIZE, side) + ' × ' + Math.min(SIZE, side) + ' px';
        info.hidden = false;

        if (!liveQueued) {
            liveQueued = true;
            requestAnimationFrame(() => { liveQueued = false; drawLive(); });
        }
    }

    /** Pratinjau bulat yang ikut berubah saat kotak digeser / diubah ukurannya. */
    function drawLive() {
        if (!bitmap || !crop) return;
        const g = live.getContext('2d');
        g.clearRect(0, 0, live.width, live.height);
        g.imageSmoothingQuality = 'high';
        g.drawImage(bitmap, crop.x, crop.y, crop.s, crop.s, 0, 0, live.width, live.height);
    }

    /** Titik pointer dalam piksel foto asli. */
    function toImagePoint(e) {
        const r = cstage.getBoundingClientRect();
        return { x: (e.clientX - r.left) / view.k, y: (e.clientY - r.top) / view.k };
    }

    function setBox(x, y, size) {
        const side = clamp(size, minBox(), Math.min(bitmap.width, bitmap.height));
        crop = {
            x: clamp(x, 0, bitmap.width - side),
            y: clamp(y, 0, bitmap.height - side),
            s: side,
        };
        renderBox();
    }

    /** Ubah ukuran dari tengah kotak (roda mouse, tombol + / −). */
    function zoomBox(factor) {
        const cx = crop.x + crop.s / 2, cy = crop.y + crop.s / 2;
        const side = clamp(crop.s * factor, minBox(), Math.min(bitmap.width, bitmap.height));
        setBox(cx - side / 2, cy - side / 2, side);
    }

    function startDrag(e) {
        if (!crop || busy || (e.button !== undefined && e.button !== 0)) return;
        const handle = e.target.closest('[data-h]');
        const p = toImagePoint(e);

        if (handle) {
            // Sudut yang ditarik; sudut di seberangnya jadi titik tetap (jangkar).
            const c = handle.dataset.h;
            const east = c.includes('e'), south = c.includes('s');
            drag = {
                mode: 'resize',
                sx: east ? 1 : -1,
                sy: south ? 1 : -1,
                ax: east ? crop.x : crop.x + crop.s,
                ay: south ? crop.y : crop.y + crop.s,
                // selisih titik sentuh dengan sudutnya, agar kotak tidak "melompat" saat mulai ditarik
                ox: p.x - (east ? crop.x + crop.s : crop.x),
                oy: p.y - (south ? crop.y + crop.s : crop.y),
            };
            box.classList.add('is-resizing');
        } else {
            drag = { mode: 'move', px: p.x, py: p.y, x0: crop.x, y0: crop.y };
        }
        box.classList.add('is-drag');
        box.setPointerCapture?.(e.pointerId);
        e.preventDefault();
    }

    function moveDrag(e) {
        if (!drag) return;
        const p = toImagePoint(e);

        if (drag.mode === 'move') {
            setBox(drag.x0 + (p.x - drag.px), drag.y0 + (p.y - drag.py), crop.s);
            return;
        }

        const { sx, sy, ax, ay } = drag;
        const want = Math.max((p.x - drag.ox - ax) * sx, (p.y - drag.oy - ay) * sy);
        const room = Math.min(sx > 0 ? bitmap.width - ax : ax, sy > 0 ? bitmap.height - ay : ay); // tidak boleh keluar foto
        const side = clamp(want, Math.min(minBox(), room), room);
        crop = { x: sx > 0 ? ax : ax - side, y: sy > 0 ? ay : ay - side, s: side };
        renderBox();
    }

    function endDrag() {
        drag = null;
        box.classList.remove('is-drag', 'is-resizing');
    }

    function onKey(e) {
        if (!crop || busy) return;
        const step = Math.max(1, Math.round(6 / view.k)) * (e.shiftKey ? 4 : 1);
        const moves = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] };
        if (moves[e.key]) {
            e.preventDefault();
            setBox(crop.x + moves[e.key][0], crop.y + moves[e.key][1], crop.s);
        } else if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            zoomBox(1.06);
        } else if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            zoomBox(1 / 1.06);
        }
    }

    /** Potong bagian terpilih, kecilkan ke maks SIZE px, simpan sebagai WebP (JPEG bila browser tak mendukung). */
    async function exportCrop() {
        const side = Math.max(1, crop.s);
        const out = Math.min(SIZE, Math.round(side));

        // Kecilkan bertahap (maks. separuh tiap langkah) agar hasilnya tajam, bukan bergerigi.
        let source = bitmap;
        let sx = crop.x, sy = crop.y, sw = side;
        while (sw / 2 > out) {
            const half = Math.round(sw / 2);
            const step = document.createElement('canvas');
            step.width = step.height = half;
            const sg = step.getContext('2d');
            sg.imageSmoothingQuality = 'high';
            sg.drawImage(source, sx, sy, sw, sw, 0, 0, half, half);
            source = step; sx = 0; sy = 0; sw = half;
        }

        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = out;
        const g = canvas.getContext('2d');
        g.imageSmoothingQuality = 'high';
        g.drawImage(source, sx, sy, sw, sw, 0, 0, out, out);

        let result = await toBlob(canvas, 'image/webp', 0.82);
        if (!result || result.type !== 'image/webp') {
            g.globalCompositeOperation = 'destination-over'; // JPEG tak punya transparan -> latar putih
            g.fillStyle = '#fff';
            g.fillRect(0, 0, out, out);
            result = await toBlob(canvas, 'image/jpeg', 0.85);
        }
        return result;
    }

    // ---------------------------------------------------------------- pilih berkas
    async function setFile(file) {
        showError('');
        if (!file) return;
        if (!/^image\/(png|jpe?g|webp|gif)$/i.test(file.type)) return showError('Format harus JPG, PNG, atau WebP.');
        if (file.size > MAX_INPUT) return showError('Ukuran foto terlalu besar (maksimal 8 MB).');

        try {
            const next = await loadBitmap(file);
            if (bitmap && bitmap.close) bitmap.close();
            bitmap = next;

            stageEl.hidden = true;
            cropper.hidden = false;
            card.classList.add('is-cropping');

            // Kotak awal: persegi terbesar di tengah foto. Geser / tarik sudutnya untuk memilih bagian lain.
            const side = Math.min(bitmap.width, bitmap.height);
            crop = { x: (bitmap.width - side) / 2, y: (bitmap.height - side) / 2, s: side };
            layout();
            btnSave.disabled = false;
            box.focus({ preventScroll: true });
        } catch (e) {
            showError('Foto tidak bisa dibaca. Coba foto lain.');
        }
    }

    // ---------------------------------------------------------------- kirim ke server
    function setBusy(on, label) {
        busy = on;
        card.classList.toggle('is-busy', on);
        btnSave.disabled = on || !bitmap;
        saveLabel.textContent = label || 'Simpan';
    }

    /** Ganti foto di semua avatar milik user login yang ada di halaman (header, menu profil, halaman Profil). */
    function applyToPage(url) {
        selfAvatars().forEach((el) => {
            el.querySelector('.av-img')?.remove();
            if (!url) return;
            const img = document.createElement('img');
            img.className = 'av-img';
            img.alt = '';
            img.src = url;
            img.referrerPolicy = 'no-referrer';
            img.onerror = () => img.remove();
            el.appendChild(img);
        });
    }

    async function request(method, body) {
        const res = await fetch('/account/avatar', {
            method,
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body,
            credentials: 'same-origin',
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.errors?.avatar?.[0] || data.message || 'Gagal menyimpan foto (' + res.status + ').');
        return data;
    }

    async function save() {
        if (!bitmap || !crop || busy) return;
        showError('');
        setBusy(true, 'Mengunggah…');
        try {
            const blob = await exportCrop();
            if (!blob) throw new Error('Foto tidak bisa diproses. Coba foto lain.');
            const form = new FormData();
            form.append('avatar', blob, blob.type === 'image/webp' ? 'avatar.webp' : 'avatar.jpg');
            const data = await request('POST', form);
            applyToPage(data.avatar_url);
            setBusy(false);
            close();
            toast('success', data.message || 'Foto profil berhasil diperbarui.');
        } catch (e) {
            setBusy(false);
            showError(e.message || 'Tidak bisa terhubung ke server.');
        }
    }

    async function remove() {
        if (busy || !window.confirm('Hapus foto profil? Avatar akan kembali memakai inisial nama.')) return;
        showError('');
        setBusy(true);
        try {
            const data = await request('DELETE');
            applyToPage(null);
            setBusy(false);
            close();
            toast('success', data.message || 'Foto profil dihapus.');
        } catch (e) {
            setBusy(false);
            showError(e.message || 'Tidak bisa terhubung ke server.');
        }
    }

    // ---------------------------------------------------------------- event
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-avatar-edit]')) {
            e.preventDefault();
            open();
        }
    });
    document.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.matches?.('[data-avatar-edit][role="button"]')) {
            e.preventDefault();
            open();
        }
        if (e.key === 'Escape' && !modal.hidden) close();
    });

    modal.addEventListener('click', (e) => { if (e.target.closest('[data-av-close]')) close(); });
    input.addEventListener('change', () => {
        const file = input.files[0];
        input.value = ''; // supaya memilih berkas yang sama lagi tetap memicu change
        setFile(file);
    });
    btnRechoose.addEventListener('click', () => input.click());

    // Kotak crop: geser (badan kotak) / ubah ukuran (sudut) lewat pointer (mouse, sentuh, pena).
    box.addEventListener('pointerdown', startDrag);
    box.addEventListener('pointermove', moveDrag);
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach((t) => box.addEventListener(t, endDrag));
    box.addEventListener('keydown', onKey);
    cstage.addEventListener('wheel', (e) => {
        if (!crop || busy) return;
        e.preventDefault();
        zoomBox(e.deltaY < 0 ? 1.06 : 1 / 1.06);
    }, { passive: false });
    window.addEventListener('resize', () => { if (bitmap && !modal.hidden) layout(); });
    btnSave.addEventListener('click', save);
    btnRemove.addEventListener('click', remove);
    drop.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });

    ['dragenter', 'dragover'].forEach((t) => drop.addEventListener(t, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'dragend'].forEach((t) => drop.addEventListener(t, () => drop.classList.remove('is-over')));
    drop.addEventListener('drop', (e) => {
        e.preventDefault();
        drop.classList.remove('is-over');
        setFile(e.dataTransfer?.files?.[0]);
    });
    // Saat modal terbuka, foto yang terlepas di luar area drop tidak boleh membuka berkas di tab ini.
    ['dragover', 'drop'].forEach((t) => window.addEventListener(t, (e) => { if (!modal.hidden) e.preventDefault(); }));
})(window, document);
