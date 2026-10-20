{{--
    Avatar bulat: inisial nama + (kalau ada) foto dari LINK di kolom users.avatar_url.
    Dipakai di mana pun avatar tampil, jadi foto yang diganti di Profil otomatis terlihat di semua halaman.

    Parameter:
      initials  (wajib)  teks inisial, mis. "BS"
      color     (opsional) kelas warna c-primary / c-info / ... (lihat style.css)
      url       (opsional) link foto; kosong = hanya inisial
      class     (opsional) kelas dasar, default "avatar" (mis. header-profile-avatar)
      self      (opsional) true = avatar milik user yang sedang login; js/avatar-upload.js memperbarui fotonya langsung

    Ringan: foto hanya <img> dari link (±20 KB, lazy). Inisial tetap ada di bawah foto, jadi saat foto lambat/gagal
    dimuat yang terlihat inisial (onerror membuang <img> yang rusak). Tidak ada query tambahan: user sudah ikut ter-load.
--}}
<div class="{{ $class ?? 'avatar' }} {{ $color ?? '' }}"@if(!empty($self)) data-avatar-self @endif>{{ $initials }}@if(!empty($url))<img class="av-img" src="{{ $url }}" alt="" width="96" height="96" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()">@endif</div>
