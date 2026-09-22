<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Atur Ulang Password · SIDA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        (function () {
            try {
                var t = localStorage.getItem('sida-theme');
                if (t) document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <style>
        :root {
            --font: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            --page-bg: linear-gradient(135deg, #7554fb 0%, #4f1fd3 42%, #2814ac 100%);
            --panel: #ffffff;
            --ink: #12204a;
            --ink-soft: #5b6b90;
            --field: #f1f5fd;
            --field-line: #d9e3f6;
            --primary-a: #2563eb;
            --primary-b: #4338ca;
            --primary: #2f52d9;
            --focus: rgba(47, 82, 217, .28);
            --err-bg: #fef2f2; --err-line: #fecaca; --err-ink: #b42318;
            --ok-bg: #f0fdf4; --ok-line: #bbf7d0; --ok-ink: #15803d;
            --shadow: #5b6b90;
        }
        :root[data-theme="dark"] {
            --page-bg: linear-gradient(135deg, #11266b 0%, #0e1b42 55%, #0d1327 100%);
            --panel: #111827; --ink: #eaf0ff; --ink-soft: #93a3c6;
            --field: #182238; --field-line: #27345a;
            --primary-a: #3b82f6; --primary-b: #6366f1; --primary: #6f9dff;
            --focus: rgba(111, 157, 255, .35);
            --err-bg: rgba(239,68,68,.14); --err-line: rgba(239,68,68,.4); --err-ink: #fca5a5;
            --ok-bg: rgba(34,197,94,.14); --ok-line: rgba(34,197,94,.4); --ok-ink: #86efac;
            --shadow: #6366f1;
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --page-bg: linear-gradient(135deg, #11266b 0%, #0e1b42 55%, #0d1327 100%);
                --panel: #111827; --ink: #eaf0ff; --ink-soft: #93a3c6;
                --field: #182238; --field-line: #27345a;
                --primary-a: #3b82f6; --primary-b: #6366f1; --primary: #6f9dff;
                --focus: rgba(111, 157, 255, .35);
                --err-bg: rgba(239,68,68,.14); --err-line: rgba(239,68,68,.4); --err-ink: #fca5a5;
                --ok-bg: rgba(34,197,94,.14); --ok-line: rgba(34,197,94,.4); --ok-ink: #86efac;
                --shadow: #6366f1;
            }
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: var(--font); color: var(--ink); background: var(--page-bg);
            display: grid; place-items: center; min-height: 100dvh; padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        button, input { font: inherit; }
        :focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }

        .card {
            width: 100%; max-width: 400px; background: var(--panel);
            border-radius: 28px; padding: clamp(1.75rem, 4vw, 2.5rem);
            box-shadow: 0 30px 60px -20px rgba(11, 31, 142, .45);
        }
        .brand { display: flex; align-items: center; gap: .6rem; margin-bottom: 1.5rem; }
        .brand i { width: 2.4rem; height: 2.4rem; border-radius: 10px; display: grid; place-items: center;
            background: linear-gradient(135deg, var(--primary-a), var(--primary-b)); color: #fff; font-size: 1.05rem; }
        .brand span { font-weight: 800; font-size: 1.15rem; letter-spacing: .01em; }

        h1 { margin: 0; font-size: 1.4rem; font-weight: 800; letter-spacing: -.02em; }
        .sub-title { margin: .4rem 0 1.5rem; color: var(--ink-soft); font-size: .86rem; line-height: 1.55; }

        form { display: flex; flex-direction: column; gap: 1rem; }
        .field { display: flex; flex-direction: column; gap: .4rem; }
        .field label { font-size: .8rem; font-weight: 600; }
        .field-wrap { position: relative; }
        .field-wrap input {
            width: 100%; padding: .8rem 1rem .8rem 2.7rem;
            background: var(--field); border: 1.5px solid var(--field-line); border-radius: 12px;
            color: var(--ink); font-size: .88rem;
        }
        .field-wrap input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px var(--focus); }
        .icon-left { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--ink-soft); font-size: .88rem; }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
            width: 100%; padding: .88rem 1rem; border: 0; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-a), var(--primary-b));
            color: #fff; font-weight: 700; font-size: .93rem; cursor: pointer;
            box-shadow: 0 8px 24px -12px var(--shadow); margin-top: .25rem;
        }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-primary[disabled] { opacity: .7; cursor: default; }

        .alert {
            padding: 9px 13px; border-radius: 10px; font-size: .78rem;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-err { background: var(--err-bg); border: 1px solid var(--err-line); color: var(--err-ink); }
        .alert-ok  { background: var(--ok-bg); border: 1px solid var(--ok-line); color: var(--ok-ink); }

        .link-line { text-align: center; font-size: .8rem; margin-top: 1.25rem; }
        .link-line a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .link-line a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <i class="fa-solid fa-shield-halved"></i>
            <span>SIDA</span>
        </div>

        <h1>Atur ulang password</h1>
        <p class="sub-title">Buat password baru untuk akun Anda. Pastikan mudah diingat tapi tetap aman.</p>

        @if ($errors->any())
            <div class="alert alert-err" style="margin-bottom: 1rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ url('/reset-password') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">Email</label>
                <div class="field-wrap">
                    <i class="fa-regular fa-envelope icon-left"></i>
                    <input id="email" name="email" type="email" required
                        placeholder="email@kampus.ac.id" value="{{ old('email', $email) }}">
                </div>
            </div>

            <div class="field">
                <label for="password">Password baru</label>
                <div class="field-wrap">
                    <i class="fa-solid fa-key icon-left"></i>
                    <input id="password" name="password" type="password" required minlength="6"
                        placeholder="Minimal 6 karakter" autocomplete="new-password">
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi password baru</label>
                <div class="field-wrap">
                    <i class="fa-solid fa-key icon-left"></i>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6"
                        placeholder="Ulangi password baru" autocomplete="new-password">
                </div>
            </div>

            <button class="btn-primary" type="submit">
                <span>Simpan password baru</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="link-line">
            <a href="{{ url('/') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>
