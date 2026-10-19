<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
    <script>
        (function() {
            try {
                var w = parseInt(localStorage.getItem('sida.sidebarW'), 10);
                if (w) document.documentElement.style.setProperty('--sidebar-w', (w < 140 ? 72 : Math.min(340, Math.max(200, w))) + 'px');
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ @filemtime(public_path('css/toast.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ @filemtime(public_path('css/dashboard.css')) }}">
    <title>Dashboard &middot; SIDA</title>
</head>

<body>
    <!-- Definisi dark mode -->
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
        } catch (e) {}
    </script>
    <!-- Definisi ikon SIDA (dipakai di logo sidebar) -->
    <svg class="svg-defs" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="sida-mark" viewBox="0 0 48 48">
                <clipPath id="sidaClip">
                    <circle cx="24" cy="24" r="22.5" />
                </clipPath>
                <image href="{{ asset('img/logo-prodi.png') }}" x="1.5" y="1.5" width="45" height="45" preserveAspectRatio="xMidYMid slice" clip-path="url(#sidaClip)" />
                <circle cx="24" cy="24" r="22.5" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5" />
            </symbol>
        </defs>
    </svg>

    @include('partials.sidebar', ['active' => 'dashboard'])

    <div class="app-content">
        @include('partials.header', ['active' => 'dashboard'])

        <main class="app-main">
            <div class="page-wrap">
                <div class="dash-welcome">
                    <p class="dash-welcome-title">Halo, {{ $user->name }} 👋</p>
                    <p class="dash-welcome-sub">Dashboard ringkasan untuk role "{{ $user->role }}" belum tersedia &mdash; silakan pilih menu di sidebar.</p>
                </div>
            </div>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/profile-account.js') }}?v={{ @filemtime(public_path('js/profile-account.js')) }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>