{{--
    Halaman tujuan tombol "Ya, ini saya" di email reset password.
    Token sudah diperiksa di server (AuthController::showResetForm), jadi pop-up langsung terbuka:
    password baru (terisi acak, boleh diganti) + konfirmasi -> tersimpan -> diarahkan ke login.
    Tampilan memakai gaya pop-up force password. Logika: public/js/reset-password.js
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Password Baru · SIDA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/force-password.css') }}?v={{ @filemtime(public_path('css/force-password.css')) }}">

    <script>
        (function () {
            var dark = false;
            try { dark = localStorage.getItem('theme') === 'dark'; } catch (e) {}
            document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            document.addEventListener('DOMContentLoaded', function () { document.body.classList.toggle('dark-mode', dark); });
        })();
    </script>
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #4d7fff 0%, #1f45d3 42%, #2814ac 100%);
            --card: #ffffff; --ink: #12204a; --ink-muted: #5b6b90; --ink-faint: #9aa7c4;
            --canvas: #f1f5fd; --border: #d9e3f6; --border-strong: #c3d0ea;
            --primary: #2f52d9; --primary-soft: #e6ecff; --primary-border: #b7c6f5;
        }
        body.dark-mode {
            background: linear-gradient(135deg, #213e9e 0%, #1f2e5e 55%, #1b1847 100%);
            --card: #111827; --ink: #eaf0ff; --ink-muted: #93a3c6; --ink-faint: #6b7ba0;
            --canvas: #182238; --border: #27345a; --border-strong: #34446f;
            --primary: #6f9dff; --primary-soft: #243050; --primary-border: #3d5290;
        }
        .fp-back { display: inline-block; margin-top: 14px; font-size: .8rem; font-weight: 600; color: var(--primary); text-decoration: none; }
        .fp-back:hover { text-decoration: underline; }
        .fp-account { margin: -8px 0 16px; font-size: .8rem; color: var(--ink-muted); }
    </style>
</head>
<body>
@php
    $at = strpos($email, '@');
    $maskedEmail = $at !== false
        ? mb_substr($email, 0, 2) . str_repeat('•', max(mb_strlen(mb_substr($email, 0, $at)) - 2, 1)) . mb_substr($email, $at)
        : $email;
@endphp
<div class="fp-overlay" id="fpOverlay" role="dialog" aria-modal="true" aria-labelledby="fpTitle" aria-describedby="fpDesc"
     data-token="{{ $token }}" data-email="{{ $email }}" data-redirect="{{ route('login') }}">
    <div class="fp-card" id="fpCard">
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
                Terima kasih sudah mengonfirmasi, <strong>{{ $userName }}</strong>. Kami sudah menyiapkan password acak,
                Anda boleh memakainya atau menggantinya sendiri. Ketik ulang di kolom konfirmasi untuk menyimpan.
            </p>
            <p class="fp-account fp-rise" style="--i:2">Akun: {{ $maskedEmail }}</p>

            <ol class="fp-steps fp-rise" style="--i:3" aria-label="Langkah">
                <li class="is-active" data-step="1"><b>1</b><span>Password</span></li>
                <li data-step="2"><b>2</b><span>Konfirmasi</span></li>
                <li data-step="3"><b>3</b><span>Simpan</span></li>
            </ol>

            <div class="fp-field fp-rise" style="--i:4" id="fpField">
                <label class="fp-label" for="fpPassword">Password baru</label>
                <div class="fp-input-text">
                    <input type="text" id="fpPassword" name="password" class="fp-pad3" minlength="6" maxlength="64"
                           placeholder="Password acak dibuat otomatis" autocomplete="new-password" spellcheck="false">
                    <button type="button" class="fp-eye fp-r3" id="fpGen" aria-label="Buat password acak baru" title="Buat password acak baru">
                        <span class="material-symbols-outlined">autorenew</span>
                    </button>
                    <button type="button" class="fp-eye fp-r2" id="fpCopy" aria-label="Salin password" title="Salin password">
                        <span class="material-symbols-outlined">content_copy</span>
                    </button>
                    <button type="button" class="fp-eye" data-target="fpPassword" aria-label="Sembunyikan password" title="Sembunyikan password">
                        <span class="material-symbols-outlined">visibility_off</span>
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
                @if($isDefault)Password tidak boleh sama dengan NIM/NIDN. @endif
                Setelah disimpan, Anda diminta masuk lagi memakai password baru.
            </p>
            <a class="fp-back fp-rise" style="--i:6" href="{{ route('login') }}">← Batal, kembali ke halaman login</a>
        </form>

        <div class="fp-view fp-success" id="fpSuccessView" hidden>
            <div class="fp-check-circle" aria-hidden="true">
                <svg viewBox="0 0 52 52" width="76" height="76" fill="none">
                    <circle class="fp-check-ring" cx="26" cy="26" r="23"/>
                    <path class="fp-check-mark" d="M15 27.5l8 8 15-17"/>
                </svg>
            </div>
            <h2 class="fp-title">Password berhasil disimpan</h2>
            <p class="fp-desc">Silakan masuk lagi dengan password baru. Mengalihkan ke halaman masuk…</p>
            <div class="fp-countdown" aria-hidden="true"><i id="fpCountdown"></i></div>
        </div>
    </div>
</div>

<script src="{{ asset('js/toast.js') }}?v={{ @filemtime(public_path('js/toast.js')) }}"></script>
<script src="{{ asset('js/reset-password.js') }}?v={{ @filemtime(public_path('js/reset-password.js')) }}"></script>
</body>
</html>
