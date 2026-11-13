{{--
    Pop-up WAJIB ganti password — tampil di dashboard untuk mahasiswa/dosen yang masih memakai
    password default (NIM/NIDN). Tidak bisa ditutup (tanpa tombol close, Esc & klik latar diabaikan).
    Logika: public/js/force-password.js · Gaya & animasi: public/css/force-password.css
    Endpoint yang dipakai: POST /account/password/generate dan POST /account/password (AccountController).
    Penguncian sebenarnya ada di server (middleware ForcePasswordChange), pop-up ini hanya antarmukanya.
--}}
@php
    $fpUser  = auth()->user();
    $fpLabel = $fpUser->role === 'dosen' ? 'NIDN' : 'NIM';
@endphp
<div class="fp-overlay" id="fpOverlay" role="alertdialog" aria-modal="true"
     aria-labelledby="fpTitle" aria-describedby="fpDesc"
     data-notice="{{ session('force_password_notice') }}">
    <div class="fp-card" id="fpCard">
        {{-- ================= Tampilan 1: form ================= --}}
        <div class="fp-view" id="fpFormView">
            <div class="fp-hero fp-rise" style="--i:0" aria-hidden="true">
                <div class="fp-badge">
                    <svg viewBox="0 0 48 48" width="40" height="40" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="fp-shackle" d="M16 21v-5a8 8 0 0 1 16 0v5"/>
                        <rect x="10" y="21" width="28" height="20" rx="5" fill="currentColor" fill-opacity=".18"/>
                        <circle cx="24" cy="31" r="2.4" fill="currentColor" stroke="none"/>
                    </svg>
                </div>
            </div>

            <h2 class="fp-title fp-rise" style="--i:1" id="fpTitle">Amankan akun Anda</h2>
            <p class="fp-desc fp-rise" style="--i:2" id="fpDesc">
                Halo, <strong>{{ $fpUser->name }}</strong>. Anda masih memakai password awal (<strong>{{ $fpLabel }}</strong>).
                Demi keamanan, buat password baru sekarang untuk melanjutkan.
            </p>

            <ol class="fp-steps fp-rise" style="--i:3" aria-label="Langkah">
                <li class="is-active" data-step="1"><b>1</b><span>Buat</span></li>
                <li data-step="2"><b>2</b><span>Salin</span></li>
                <li data-step="3"><b>3</b><span>Simpan</span></li>
            </ol>

            <div class="fp-field fp-rise" style="--i:4" id="fpField">
                <span class="fp-label" id="fpLabel">Password baru (tepat 6 karakter)</span>
                <div class="fp-input">
                    <div class="fp-tiles" id="fpTiles" role="text" aria-labelledby="fpLabel">
                        <span class="fp-tile">•</span><span class="fp-tile">•</span><span class="fp-tile">•</span>
                        <span class="fp-tile">•</span><span class="fp-tile">•</span><span class="fp-tile">•</span>
                    </div>
                    <button type="button" class="fp-icon-btn" id="fpGen" aria-label="Buat password baru" title="Buat password baru">
                        <span class="material-symbols-outlined">autorenew</span>
                    </button>
                    <button type="button" class="fp-icon-btn" id="fpCopy" aria-label="Salin password" title="Salin password" disabled>
                        <span class="material-symbols-outlined fp-ico-copy">content_copy</span>
                        <span class="material-symbols-outlined fp-ico-done">check</span>
                    </button>
                </div>
                <div class="fp-error" id="fpError" role="alert"></div>
            </div>

            <label class="fp-check fp-rise" style="--i:5">
                <input type="checkbox" id="fpSaved" disabled>
                <span class="fp-box" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </span>
                <span>Saya sudah menyalin / mencatat password ini</span>
            </label>

            <button type="button" class="fp-submit fp-rise" style="--i:6" id="fpSubmit" disabled>
                <span class="fp-spin" aria-hidden="true"></span>
                <span class="fp-submit-text">Simpan password baru</span>
            </button>

            <p class="fp-foot fp-rise" style="--i:7">
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                Setelah disimpan, Anda diminta masuk lagi memakai password baru.
            </p>
        </div>

        {{-- ================= Tampilan 2: berhasil ================= --}}
        <div class="fp-view fp-success" id="fpSuccessView" hidden>
            <div class="fp-confetti" id="fpConfetti" aria-hidden="true"></div>
            <div class="fp-check-circle" aria-hidden="true">
                <svg viewBox="0 0 52 52" width="76" height="76" fill="none">
                    <circle class="fp-check-ring" cx="26" cy="26" r="23"/>
                    <path class="fp-check-mark" d="M15 27.5l8 8 15-17"/>
                </svg>
            </div>
            <h2 class="fp-title">Password berhasil diganti</h2>
            <p class="fp-desc">Akun Anda sekarang lebih aman. Mengalihkan ke halaman masuk…</p>
            <div class="fp-countdown" aria-hidden="true"><i id="fpCountdown"></i></div>
        </div>
    </div>
</div>
