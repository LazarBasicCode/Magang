{{--
    Ganti Password dari menu profil (header + halaman Profil). Alur 3 modal + hasil:
      1. Pilih cara    : "Konfirmasi lewat email" atau "Kembalikan ke default (NIM/NIDN)", lalu tombol kirim.
      2. Menunggu      : menunggu pengguna menekan "Ya, ini saya" di email (dicek berkala).
      3. Password baru : terbuka LANGSUNG begitu email terkonfirmasi — 2 input (acak / ketik sendiri / salin).
      4. Berhasil      : semua perangkat keluar, diarahkan ke halaman masuk.
    Logika: public/js/password-change.js · Gaya: public/css/password-change.css
    Endpoint: PasswordChangeController (verify, verify/status, default) + AccountController::changePassword.
    Dipasang SEKALI lewat partials/header.blade.php. Pemicunya: tombol #btnGantiPassword.
--}}
@auth
@php
    $pcUser       = auth()->user();
    $pcLabel      = $pcUser->role === 'dosen' ? 'NIDN' : 'NIM';
    $pcHasEmail   = filled($pcUser->email);
    $pcEmail      = \App\Support\PasswordChangeVerification::maskEmail($pcUser->email);
    $pcCanDefault = \App\Support\DefaultPassword::appliesTo($pcUser->role) && filled($pcUser->nim_nidn);
@endphp
<link rel="stylesheet" href="{{ asset('css/password-change.css') }}?v={{ @filemtime(public_path('css/password-change.css')) }}">

<div class="pc-overlay" id="pcOverlay" hidden
     data-has-email="{{ $pcHasEmail ? 1 : 0 }}" data-can-default="{{ $pcCanDefault ? 1 : 0 }}"
     data-label="{{ $pcLabel }}" data-email="{{ $pcEmail }}">
    <div class="pc-card" id="pcCard" role="dialog" aria-modal="true" aria-labelledby="pcTitle" tabindex="-1">
        <button type="button" class="pc-close" data-pc-close aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>

        {{-- ============ 1. Pilih cara ============ --}}
        <form class="pc-view" data-pc-view="choose" novalidate>
            <div class="pc-icon" aria-hidden="true"><span class="material-symbols-outlined">lock</span></div>
            <h2 class="pc-title" id="pcTitle">Ganti password</h2>
            <p class="pc-desc">Pilih cara untuk mengganti password akun Anda.</p>

            <div class="pc-options" role="radiogroup" aria-label="Cara ganti password">
                <label class="pc-option {{ $pcHasEmail ? '' : 'is-disabled' }}">
                    <input type="radio" name="pc_method" value="email" {{ $pcHasEmail ? '' : 'disabled' }}>
                    <span class="pc-option-icon" aria-hidden="true"><span class="material-symbols-outlined">mark_email_read</span></span>
                    <span class="pc-option-text">
                        <strong>Konfirmasi lewat email</strong>
                        @if($pcHasEmail)
                            <small>Tautan konfirmasi dikirim ke {{ $pcEmail }}. Setelah dikonfirmasi, Anda membuat password baru.</small>
                        @else
                            <small>Belum ada email pemulihan. Tambahkan lewat menu &ldquo;Email Pemulihan&rdquo; terlebih dahulu.</small>
                        @endif
                    </span>
                    <span class="pc-radio" aria-hidden="true"></span>
                </label>

                <label class="pc-option {{ $pcCanDefault ? '' : 'is-disabled' }}">
                    <input type="radio" name="pc_method" value="default" {{ $pcCanDefault ? '' : 'disabled' }}>
                    <span class="pc-option-icon" aria-hidden="true"><span class="material-symbols-outlined">restart_alt</span></span>
                    <span class="pc-option-text">
                        <strong>Kembalikan ke default</strong>
                        @if($pcCanDefault)
                            <small>Password menjadi {{ $pcLabel }} Anda, lalu Anda membuat password baru saat masuk.</small>
                        @else
                            <small>Khusus akun mahasiswa dan dosen.</small>
                        @endif
                    </span>
                    <span class="pc-radio" aria-hidden="true"></span>
                </label>
            </div>

            <div class="pc-confirm" data-pc-confirm hidden>
                <label class="pc-label" for="pcConfirmIdentity">Ketik ulang {{ $pcLabel }} untuk konfirmasi</label>
                <input class="pc-input" id="pcConfirmIdentity" type="text" autocomplete="off" spellcheck="false" placeholder="{{ $pcLabel }} Anda">
            </div>

            <div class="pc-error" data-pc-error="choose" role="alert"></div>

            <button type="submit" class="pc-btn pc-btn-primary pc-btn-block" data-pc-submit="choose" disabled>Kirim konfirmasi ke email</button>
        </form>

        {{-- ============ 2. Menunggu konfirmasi email ============ --}}
        <div class="pc-view" data-pc-view="wait" hidden>
            <div class="pc-icon" aria-hidden="true"><span class="material-symbols-outlined">mark_email_unread</span></div>
            <h2 class="pc-title">Menunggu konfirmasi email</h2>
            <p class="pc-desc">
                Kami mengirim email ke <strong data-pc-email>{{ $pcEmail }}</strong>. Buka email tersebut dan tekan
                &ldquo;Ya, ini saya&rdquo;. Halaman ini otomatis lanjut setelah Anda mengonfirmasi.
            </p>

            <div class="pc-wait" aria-live="polite">
                <span data-pc-wait-text>Menunggu konfirmasi&hellip;</span>
                <small data-pc-wait-timer></small>
            </div>

            <div class="pc-actions">
                <button type="button" class="pc-btn" data-pc-back>Ubah pilihan</button>
                <button type="button" class="pc-btn" data-pc-resend disabled>Kirim ulang email</button>
            </div>
        </div>

        {{-- ============ 3. Buat password baru (email sudah terkonfirmasi) ============ --}}
        <form class="pc-view" data-pc-view="create" hidden novalidate autocomplete="off">
            <div class="pc-icon" aria-hidden="true"><span class="material-symbols-outlined">lock_reset</span></div>
            <h2 class="pc-title">Buat password baru</h2>
            <p class="pc-desc">
                Email sudah terkonfirmasi. Kami sudah menyiapkan password acak; Anda boleh memakainya atau mengetik sendiri.
                Ketik ulang di kolom konfirmasi untuk melanjutkan.
            </p>

            <ol class="pc-steps" aria-label="Langkah">
                <li class="is-active" data-pc-step="1"><b>1</b><span>Password</span></li>
                <li data-pc-step="2"><b>2</b><span>Konfirmasi</span></li>
                <li data-pc-step="3"><b>3</b><span>Simpan</span></li>
            </ol>

            <div class="pc-field">
                <label class="pc-label" for="pcPassword">Password baru</label>
                <div class="pc-input-wrap">
                    <input class="pc-input pc-pad3" type="text" id="pcPassword" name="password" minlength="6" maxlength="64"
                           placeholder="Ketik password atau pakai acak" autocomplete="new-password" spellcheck="false">
                    <button type="button" class="pc-eye pc-r3" data-pc-gen aria-label="Buat password acak baru" title="Buat password acak baru">
                        <span class="material-symbols-outlined">autorenew</span>
                    </button>
                    <button type="button" class="pc-eye pc-r2" data-pc-copy aria-label="Salin password" title="Salin password">
                        <span class="material-symbols-outlined">content_copy</span>
                    </button>
                    <button type="button" class="pc-eye" data-pc-eye="pcPassword" aria-label="Sembunyikan password" title="Sembunyikan password">
                        <span class="material-symbols-outlined">visibility_off</span>
                    </button>
                </div>

                <label class="pc-label pc-label-2" for="pcConfirm">Konfirmasi password baru</label>
                <div class="pc-input-wrap">
                    <input class="pc-input" type="password" id="pcConfirm" name="password_confirmation" minlength="6" maxlength="64"
                           placeholder="Ulangi password baru" autocomplete="new-password" spellcheck="false">
                    <button type="button" class="pc-eye" data-pc-eye="pcConfirm" aria-label="Tampilkan konfirmasi password" title="Tampilkan konfirmasi password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                <div class="pc-match" data-pc-match aria-live="polite"></div>
            </div>

            <div class="pc-error" data-pc-error="create" role="alert"></div>

            <button type="submit" class="pc-btn pc-btn-primary pc-btn-block" data-pc-submit="create" disabled>Simpan password baru</button>

            <p class="pc-foot">
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                @if($pcCanDefault)Password tidak boleh sama dengan {{ $pcLabel }}. @endif
                Setelah disimpan, semua perangkat dikeluarkan dan Anda masuk lagi dengan password baru.
            </p>
        </form>

        {{-- ============ 4. Berhasil ============ --}}
        <div class="pc-view" data-pc-view="done" hidden>
            <div class="pc-icon" aria-hidden="true"><span class="material-symbols-outlined">check</span></div>
            <h2 class="pc-title" data-pc-done-title>Password berhasil diganti</h2>
            <p class="pc-desc" data-pc-done-text>Mengalihkan ke halaman masuk&hellip;</p>
        </div>
    </div>
</div>

<script src="{{ asset('js/password-change.js') }}?v={{ @filemtime(public_path('js/password-change.js')) }}"></script>
@endauth
