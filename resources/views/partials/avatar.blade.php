{{--
    Avatar bulat: inisial nama + (kalau ada) foto dari LINK di kolom users.avatar_url.
    Dipakai di mana pun avatar tampil, jadi foto yang diganti di Profil otomatis terlihat di semua halaman.

    Parameter:
      initials  (wajib)  teks inisial, mis. "BS"
      color     (opsional) kelas warna c-primary / c-info / ... (lihat style.css)
      url       (opsional) link foto; kosong = hanya inisial
      class     (opsional) kelas dasar, default "avatar" (mis. header-profile-avatar)
      self      (opsional) true = avatar milik user yang sedang login; js/avatar-upload.js memperbarui fotonya langsung
      edit      (opsional) true = avatar ini bisa diklik untuk ganti foto (ada tombol kamera). Elemen avatar-nya sendiri yang
                jadi tombol, TANPA pembungkus, supaya tata letak/CSS halaman (mis. grid-area & cincin kaca .pf-avatar) tidak berubah.

    Catatan: tidak ada spasi/baris baru di antara isi avatar (inisial, <img>, badge) — node teks ekstra menggeser
    posisi inisial sub-piksel pada avatar bergaya khusus seperti .pf-avatar. (Badge kamera ditulis lewat {!! !!}: di Blade,
    "@if" yang menempel langsung setelah "@endif" TIDAK dikenali sebagai direktif, jadi tidak bisa dipisah dengan @if lagi.)

    Ringan: foto hanya <img> dari link (±20 KB, lazy). Inisial tetap ada di bawah foto, jadi saat foto lambat/gagal
    dimuat yang terlihat inisial (onerror membuang <img> yang rusak). Tidak ada query tambahan: user sudah ikut ter-load.
--}}
<div class="{{ $class ?? 'avatar' }} {{ $color ?? '' }}@if(!empty($edit)) av-editable @endif"@if(!empty($self)) data-avatar-self @endif @if(!empty($edit)) data-avatar-edit role="button" tabindex="0" title="Ganti foto profil" aria-label="Ganti foto profil" @endif>{{ $initials }}@if(!empty($url))<img class="av-img" src="{{ $url }}" alt="" width="96" height="96" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()">@endif</div>
