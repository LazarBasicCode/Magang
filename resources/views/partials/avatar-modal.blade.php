{{--
    Modal "Ubah Foto Profil" (seret & lepas / pilih berkas -> atur bagian foto yang dipakai (crop) -> simpan).
    Logika: public/js/avatar-upload.js.
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
        <p class="av-sub">Pilih foto, lalu atur bagian yang ingin dipakai. Foto otomatis dikecilkan agar ringan dan tampil di semua halaman.</p>

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

        {{-- Tampilan crop: muncul setelah foto dipilih. Kotak bisa digeser; sudutnya ditarik untuk mengubah ukuran. --}}
        <div class="av-cropper" data-av-cropper hidden>
            <div class="av-crop-wrap" data-av-wrap>
                <div class="av-crop-stage" data-av-cstage>
                    {{-- Foto + lapisan gelap di luar kotak dipotong di sini; kotak & pegangannya di luar agar tidak ikut terpotong di tepi foto --}}
                    <div class="av-crop-clip">
                        <canvas class="av-crop-img" data-av-cimg></canvas>
                        <div class="av-crop-shade" data-av-shade></div>
                    </div>
                    <div class="av-crop-box" data-av-box tabindex="0" role="group"
                         aria-label="Area foto profil. Gunakan tombol panah untuk menggeser, plus dan minus untuk mengubah ukuran.">
                        <div class="av-crop-inner" aria-hidden="true">
                            <span class="av-crop-circle"></span>
                            <i class="av-gl av-gl-v1"></i><i class="av-gl av-gl-v2"></i>
                            <i class="av-gl av-gl-h1"></i><i class="av-gl av-gl-h2"></i>
                        </div>
                        <span class="av-handle" data-h="nw" aria-hidden="true"></span>
                        <span class="av-handle" data-h="ne" aria-hidden="true"></span>
                        <span class="av-handle" data-h="sw" aria-hidden="true"></span>
                        <span class="av-handle" data-h="se" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
            <div class="av-crop-foot">
                <canvas class="av-live" width="144" height="144" data-av-live aria-label="Pratinjau foto profil"></canvas>
                <p class="av-crop-help">
                    <strong>Atur bagian foto</strong>
                    Geser kotak untuk memilih bagian foto, tarik sudutnya untuk memperbesar atau memperkecil.
                </p>
                <button type="button" class="av-btn" data-av-rechoose>
                    <span class="material-symbols-outlined">image</span>Ganti
                </button>
            </div>
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
