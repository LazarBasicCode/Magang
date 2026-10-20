/**
 * =====================================================================
 * FOTO PROFIL — modal "Ubah Foto Profil" (seret & lepas / pilih berkas)
 *
 * Alur (sederhana):
 *   pilih/seret foto -> dikecilkan di browser (persegi, maks 256 px, WebP ±20 KB) -> pratinjau
 *   -> Simpan: dikirim ke POST /account/avatar (AvatarController meneruskan ke layanan hosting gambar
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

    let blob = null;
    let previewUrl = null;
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
        preview.innerHTML = first ? first.outerHTML.replace(' data-avatar-self', '') : '';
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
        blob = null;
        if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
        input.value = '';
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

    /** Potong persegi dari tengah, kecilkan ke maks SIZE px, simpan sebagai WebP (JPEG bila browser tak mendukung). */
    async function shrink(file) {
        const bmp = await loadBitmap(file);
        const w = bmp.width, h = bmp.height;
        const side = Math.min(w, h);
        const out = Math.min(SIZE, side);
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = out;
        const g = canvas.getContext('2d');
        g.imageSmoothingQuality = 'high';
        g.drawImage(bmp, (w - side) / 2, (h - side) / 2, side, side, 0, 0, out, out);

        let result = await toBlob(canvas, 'image/webp', 0.82);
        if (!result || result.type !== 'image/webp') {
            g.globalCompositeOperation = 'destination-over'; // JPEG tak punya transparan -> latar putih
            g.fillStyle = '#fff';
            g.fillRect(0, 0, out, out);
            result = await toBlob(canvas, 'image/jpeg', 0.85);
        }
        return { blob: result, size: out };
    }

    // ---------------------------------------------------------------- pilih berkas
    async function setFile(file) {
        showError('');
        if (!file) return;
        if (!/^image\/(png|jpe?g|webp|gif)$/i.test(file.type)) return showError('Format harus JPG, PNG, atau WebP.');
        if (file.size > MAX_INPUT) return showError('Ukuran foto terlalu besar (maksimal 8 MB).');

        try {
            const { blob: small, size } = await shrink(file);
            if (!small) throw new Error('blob');
            blob = small;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(small);
            preview.innerHTML = '<img class="av-img" alt="Pratinjau foto" src="' + previewUrl + '">';
            info.textContent = size + ' × ' + size + ' px · ' + Math.max(1, Math.round(small.size / 1024)) + ' KB (asli ' + Math.round(file.size / 1024) + ' KB)';
            info.hidden = false;
            btnSave.disabled = false;
        } catch (e) {
            blob = null;
            btnSave.disabled = true;
            showError('Foto tidak bisa dibaca. Coba foto lain.');
        }
    }

    // ---------------------------------------------------------------- kirim ke server
    function setBusy(on, label) {
        busy = on;
        card.classList.toggle('is-busy', on);
        btnSave.disabled = on || !blob;
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
        if (!blob || busy) return;
        showError('');
        setBusy(true, 'Mengunggah…');
        try {
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
        if ((e.key === 'Enter' || e.key === ' ') && e.target.matches?.('.av-edit[data-avatar-edit]')) {
            e.preventDefault();
            open();
        }
        if (e.key === 'Escape' && !modal.hidden) close();
    });

    modal.addEventListener('click', (e) => { if (e.target.closest('[data-av-close]')) close(); });
    input.addEventListener('change', () => setFile(input.files[0]));
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
