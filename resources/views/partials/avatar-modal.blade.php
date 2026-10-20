{{--
    Modal "Ubah Foto Profil" (seret & lepas / pilih berkas, pratinjau, simpan). Logika: public/js/avatar-upload.js.
    Dipasang SEKALI lewat partials/header.blade.php (jadi ada di semua halaman). Pemicunya: elemen apa pun dengan
    atribut data-avatar-edit (mis. avatar di menu profil, avatar di halaman Profil, item menu "Ganti Foto Profil").
--}}
<link rel="stylesheet" href="{{ asset('css/avatar-upload.css') }}?v={{ @filemtime(public_path('css/avatar-upload.css')) }}">

<div class="av-modal" id="avatarModal" hidden>
    <div class="av-backdrop" data-av-close></div>
    <div class="av-card" role="dialog" aria-modal="true" aria-labelledby="avTitle">
        <button type="button" class="av-x" data-av-close aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
        <h2 class="av-title" id="avTitle">Ubah Foto Profil</h2>
        <p class="av-sub">Foto otomatis dikecilkan agar ringan. Foto ini tampil di semua halaman.</p>

        <div class="av-stage">
            <div class="av-preview" data-av-preview></div>
            <label class="av-drop" data-av-drop tabindex="0">
                <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" data-av-input hidden>
                <span class="material-symbols-outlined av-drop-icon">cloud_upload</span>
                <strong>Seret &amp; lepas foto ke sini</strong>
                <span>atau <u>klik untuk memilih</u></span>
                <small>JPG, PNG, WebP &middot; maks. 8 MB</small>
            </label>
        </div>

        <p class="av-info" data-av-info hidden></p>
        <p class="av-error" data-av-error role="alert" hidden></p>

        <div class="av-actions">
            <button type="button" class="av-btn is-danger" data-av-remove hidden>
                <span class="material-symbols-outlined">delete</span>Hapus foto
            </button>
            <span class="av-spacer"></span>
            <button type="button" class="av-btn" data-av-close>Batal</button>
            <button type="button" class="av-btn is-primary" data-av-save disabled>
                <span class="av-spin" aria-hidden="true"></span><span data-av-save-label>Simpan</span>
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/avatar-upload.js') }}?v={{ @filemtime(public_path('js/avatar-upload.js')) }}"></script>
