{{--
    Pop-up WAJIB ganti password — tampil di dashboard untuk mahasiswa/dosen yang masih memakai
    password default (NIM/NIDN). Tidak bisa ditutup (tanpa tombol close, Esc & klik latar diabaikan).
    Pengguna mengetik password baru + konfirmasi password.
    Logika: public/js/force-password.js · Gaya & animasi: public/css/force-password.css
    Endpoint: POST /account/password (AccountController, mode manual: password + password_confirmation).
    Penguncian sebenarnya ada di server (middleware ForcePasswordChange), pop-up ini hanya antarmuka.
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
        <form class="fp-view" id="fpFormView" autocomplete="off" novalidate>
            <div class="fp-hero fp-rise" style="--i:0" aria-hidden="true">
                <div class="fp-badge">
                    <svg viewBox="0 0 48 48" width="40" height="40" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="fp-shackle" d="M16 21v-5a8 8 0 0 1 16 0v5"/>
                        <rect x="10" y="21" width="28" height="20" rx="5" fill="currentColor" fill-opacity=".18"/>
                        <circle cx="24" cy="31" r="2.4" fill="currentColor" stroke="none"/>
                    </svg>
                </div>
            </div>

            <h2 class="fp-title fp-rise" style="--i:1" id="fpTitle">Buat password baru</h2>
            <p class="fp-desc fp-rise" style="--i:2" id="fpDesc">
                Halo, <strong>{{ $fpUser->name }}</strong>. Ini pertama kalinya Anda masuk dengan password awal
                (<strong>{{ $fpLabel }}</strong>). Buat password baru lalu konfirmasi untuk melanjutkan.
            </p>

            <ol class="fp-steps fp-rise" style="--i:3" aria-label="Langkah">
                <li class="is-active" data-step="1"><b>1</b><span>Password</span></li>
                <li data-step="2"><b>2</b><span>Konfirmasi</span></li>
                <li data-step="3"><b>3</b><span>Simpan</span></li>
            </ol>

            <div class="fp-field fp-rise" style="--i:4" id="fpField">
                <label class="fp-label" for="fpPassword">Password baru</label>
                <div class="fp-input-text">
                    <input type="password" id="fpPassword" name="password" minlength="6" maxlength="64"
                           placeholder="Minimal 6 karakter" autocomplete="new-password" spellcheck="false">
                    <button type="button" class="fp-eye" data-target="fpPassword" aria-label="Tampilkan password" title="Tampilkan password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>

                <label class="fp-label fp-label-2" for="fpConfirm">Konfirmasi password baru</label>
                <div class="fp-input-text">
                    <input type="password" id="fpConfirm" name="password_confirmation" minlength="6" maxlength="64"
                           placeholder="Ulangi password baru" autocomplete="new-password" spellcheck="false">
                    <button type="button" class="fp-eye" data-target="fpConfirm" aria-label="Tampilkan konfirmasi password" title="Tampilkan konfirmasi password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                <div class="fp-match" id="fpMatch" aria-live="polite"></div>
                <div class="fp-error" id="fpError" role="alert"></div>
            </div>

            <button type="submit" class="fp-submit fp-rise" style="--i:5" id="fpSubmit" disabled>
                <span class="fp-spin" aria-hidden="true"></span>
                <span class="fp-submit-text">Simpan password baru</span>
            </button>

            <p class="fp-foot fp-rise" style="--i:6">
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                Password tidak boleh sama dengan {{ $fpLabel }}. Setelah disimpan, Anda diminta masuk lagi memakai password baru.
            </p>
        </form>

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
