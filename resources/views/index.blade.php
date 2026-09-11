<!DOCTYPE html>
<html class="h-full" lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login &amp; Lupa Password - Institut Asia Malang</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&amp;display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #08132c;
            --navy-mid: #0e2a63;
            --ocean: #0a4892;
            --cyan: #22d3ee;
            --ink: #eef2ff;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Public Sans', sans-serif;
            color: var(--ink);
            background: #050b1c;
            overflow-x: hidden;
        }

        /* ================================================================
           SPLASH LOADER (scoped under .site-loader-* / siakad* keyframes
           so nothing here can collide with the rest of the page's CSS)
        ================================================================ */
        .site-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(1100px 700px at 12% 8%, rgba(34, 211, 238, .16), transparent 60%),
                radial-gradient(900px 650px at 88% 82%, rgba(79, 70, 229, .20), transparent 60%),
                linear-gradient(160deg, var(--navy-deep) 0%, var(--navy-mid) 48%, var(--ocean) 100%);
            opacity: 1;
            transition: opacity .5s ease;
        }

        .site-loader-overlay.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .site-loader {
            width: 80px;
            height: 50px;
            position: relative;
        }

        .site-loader-text {
            position: absolute;
            top: 0;
            padding: 0;
            margin: 0;
            color: #C8B6FF;
            animation: siakadText713 3.5s ease both infinite;
            font-size: .8rem;
            letter-spacing: 1px;
        }

        .site-load {
            background-color: #9A79FF;
            border-radius: 50px;
            display: block;
            height: 16px;
            width: 16px;
            bottom: 0;
            position: absolute;
            transform: translateX(64px);
            animation: siakadLoading713 3.5s ease both infinite;
        }

        .site-load::before {
            position: absolute;
            content: "";
            width: 100%;
            height: 100%;
            background-color: #D1C2FF;
            border-radius: inherit;
            animation: siakadLoading2713 3.5s ease both infinite;
        }

        @keyframes siakadText713 {
            0% {
                letter-spacing: 1px;
                transform: translateX(0px);
            }

            40% {
                letter-spacing: 2px;
                transform: translateX(26px);
            }

            80% {
                letter-spacing: 1px;
                transform: translateX(32px);
            }

            90% {
                letter-spacing: 2px;
                transform: translateX(0px);
            }

            100% {
                letter-spacing: 1px;
                transform: translateX(0px);
            }
        }

        @keyframes siakadLoading713 {
            0% {
                width: 16px;
                transform: translateX(0px);
            }

            40% {
                width: 100%;
                transform: translateX(0px);
            }

            80% {
                width: 16px;
                transform: translateX(64px);
            }

            90% {
                width: 100%;
                transform: translateX(0px);
            }

            100% {
                width: 16px;
                transform: translateX(0px);
            }
        }

        @keyframes siakadLoading2713 {
            0% {
                transform: translateX(0px);
                width: 16px;
            }

            40% {
                transform: translateX(0%);
                width: 80%;
            }

            80% {
                width: 100%;
                transform: translateX(0px);
            }

            90% {
                width: 80%;
                transform: translateX(15px);
            }

            100% {
                transform: translateX(0px);
                width: 16px;
            }
        }

        /* ---------- Unified backdrop shared by both panels ---------- */
        .scene {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @media(min-width:1024px) {
            .scene {
                flex-direction: row;
                height: 100vh;
            }

            /* Lock the page to exactly one screen on desktop: no leftover
               space at the bottom, and nothing to scroll down into. */
            html, body {
                height: 100%;
                overflow: hidden;
            }

            .left-panel,
            .right-panel {
                height: 100%;
                overflow: hidden;
            }
        }

        @media(min-width:1024px) and (max-height: 760px) {
            .left-panel {
                padding: 2rem 3rem;
            }

            .stat-grid {
                margin-top: 1.4rem;
                padding-top: 1rem;
            }

            .lede {
                margin-top: .6rem;
            }

            .card {
                padding: 1.5rem 1.9rem;
            }

            .card h2 {
                font-size: 1.35rem;
            }
        }

        .scene-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(1100px 700px at 12% 8%, rgba(34, 211, 238, .16), transparent 60%),
                radial-gradient(900px 650px at 88% 82%, rgba(79, 70, 229, .20), transparent 60%),
                linear-gradient(160deg, var(--navy-deep) 0%, var(--navy-mid) 48%, var(--ocean) 100%);
        }

        .scene-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            opacity: .07;
            pointer-events: none;
        }

        #bubbleCanvas {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }

        .panel {
            position: relative;
            z-index: 2;
        }

        /* ---------- Entrance choreography (single orchestrated sequence) ----------
           Paused by default so nothing plays behind the splash loader; the
           "loaded" class on <body> (added right when the loader fades out)
           is what starts the sequence, so the two effects never overlap. */
        .reveal {
            opacity: 0;
            transform: translateY(16px);
            animation: reveal .85s cubic-bezier(.16, 1, .3, 1) forwards;
            animation-play-state: paused;
        }

        body.loaded .reveal {
            animation-play-state: running;
        }

        @keyframes reveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .d1 {
            animation-delay: .05s;
        }

        .d2 {
            animation-delay: .16s;
        }

        .d3 {
            animation-delay: .27s;
        }

        .d4 {
            animation-delay: .38s;
        }

        .d5 {
            animation-delay: .49s;
        }

        .d6 {
            animation-delay: .60s;
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                animation: none;
                opacity: 1;
                transform: none;
            }

            #bubbleCanvas {
                display: none;
            }

            .site-loader-overlay {
                transition: none;
            }
        }

        /* ---------- Left informational panel ---------- */
        .left-panel {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
        }

        @media(min-width:1024px) {
            .left-panel {
                width: 58%;
                padding: 3.5rem 4rem;
            }
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .75rem;
            font-weight: 600;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #a5f3fc;
            backdrop-filter: blur(6px);
        }

        h1.headline {
            font-size: clamp(1.9rem, 3.2vw, 3rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -.01em;
            margin: 1.1rem 0 0;
        }

        .headline em {
            font-style: normal;
            background: linear-gradient(90deg, #bfe3ff, #67e8f9 55%, #e0f2fe);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .lede {
            margin-top: 1rem;
            font-size: .95rem;
            line-height: 1.7;
            color: rgba(224, 236, 255, .82);
            max-width: 34rem;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
            margin-top: 2.25rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, .12);
        }

        .stat-card {
            padding: .9rem 1rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
            transition: background .25s ease, transform .25s ease;
        }

        .stat-card:hover {
            background: rgba(255, 255, 255, .11);
            transform: translateY(-2px);
        }

        .stat-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #67e8f9;
            font-size: .72rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            margin: .25rem 0 0;
        }

        .stat-sub {
            font-size: .68rem;
            color: rgba(191, 219, 254, .8);
            margin-top: .1rem;
        }

        .foot-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            font-size: .75rem;
            color: rgba(191, 219, 254, .75);
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        /* ---------- Right auth panel ---------- */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem 1.25rem;
        }

        @media(min-width:1024px) {
            .right-panel {
                padding: 2.5rem 3.5rem;
            }
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 26rem;
            margin: 0 auto 1rem;
            width: 100%;
        }

        .live-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .72rem;
            font-weight: 500;
            padding: .3rem .7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #a5f3fc;
        }

        .live-dot {
            width: .45rem;
            height: .45rem;
            border-radius: 999px;
            background: #34d399;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .35;
            }
        }

        .theme-btn {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .75rem;
            border-radius: .8rem;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            font-size: .72rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s ease;
        }

        .theme-btn:hover {
            background: rgba(255, 255, 255, .16);
        }

        .card {
            max-width: 26rem;
            margin: 0 auto;
            width: 100%;
            background: rgba(255, 255, 255, .07);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 1.75rem;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, .55);
            padding: 2.1rem 1.9rem;
        }

        @media(min-width:480px) {
            .card {
                padding: 2.4rem 2.3rem;
            }
        }

        .card h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -.01em;
        }

        .card .sub {
            margin-top: .5rem;
            font-size: .87rem;
            color: rgba(224, 236, 255, .78);
            line-height: 1.6;
        }

        .field label {
            display: block;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .02em;
            color: rgba(224, 236, 255, .85);
            margin-bottom: .4rem;
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap i.icon-left {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(191, 219, 254, .6);
            font-size: .85rem;
            pointer-events: none;
        }

        .field-wrap input {
            width: 100%;
            padding: .78rem 1rem .78rem 2.5rem;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: .9rem;
            color: #fff;
            font-size: .88rem;
            outline: none;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .field-wrap input::placeholder {
            color: rgba(191, 219, 254, .45);
        }

        .field-wrap input:focus {
            border-color: var(--cyan);
            background: rgba(255, 255, 255, .11);
            box-shadow: 0 0 0 3px rgba(34, 211, 238, .18);
        }

        .field-wrap input.has-toggle {
            padding-right: 2.6rem;
        }

        .toggle-eye {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: 0;
            color: rgba(191, 219, 254, .65);
            cursor: pointer;
            font-size: .85rem;
        }

        .toggle-eye:hover {
            color: #fff;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-top: .15rem;
        }

        .remember-row input {
            accent-color: var(--cyan);
            width: 1rem;
            height: 1rem;
        }

        .remember-row span {
            font-size: .75rem;
            color: rgba(224, 236, 255, .85);
        }

        .btn-primary {
            width: 100%;
            padding: .95rem 1rem;
            border: 1px solid rgba(103, 232, 249, .35);
            border-radius: .9rem;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: #fff;
            font-weight: 600;
            font-size: .88rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            cursor: pointer;
            box-shadow: 0 14px 30px -10px rgba(6, 182, 212, .45);
            transition: filter .2s ease, transform .15s ease;
        }

        .btn-primary:hover {
            filter: brightness(1.08);
        }

        .btn-primary:active {
            transform: scale(.99);
        }

        .btn-primary i {
            font-size: .75rem;
            transition: transform .2s ease;
        }

        .btn-primary:hover i {
            transform: translateX(3px);
        }

        .link-line {
            text-align: center;
        }

        .link-line a {
            font-size: .78rem;
            font-weight: 600;
            color: #67e8f9;
            text-decoration: underline;
            text-underline-offset: 4px;
            text-decoration-color: rgba(103, 232, 249, .4);
        }

        .link-line a:hover {
            color: #a5f3fc;
        }

        .access-note {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, .14);
        }

        .access-note p {
            text-align: center;
            font-size: .72rem;
            color: rgba(191, 219, 254, .7);
            margin: 0 0 .6rem;
        }

        .chip-row {
            display: flex;
            justify-content: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .chip {
            font-size: .68rem;
            font-weight: 500;
            padding: .3rem .65rem;
            border-radius: .65rem;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: rgba(224, 236, 255, .9);
        }

        .info-box {
            display: flex;
            gap: .7rem;
            align-items: flex-start;
            padding: .85rem .9rem;
            border-radius: .9rem;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .14);
            font-size: .76rem;
            color: rgba(224, 236, 255, .85);
            line-height: 1.55;
        }

        .info-box i {
            color: #67e8f9;
            margin-top: .15rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .78rem;
            font-weight: 600;
            color: rgba(224, 236, 255, .85);
            background: none;
            border: 0;
            cursor: pointer;
        }

        .back-link:hover {
            color: #fff;
        }

        .helper-row {
            max-width: 26rem;
            margin: 1.1rem auto 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .75rem;
            color: rgba(191, 219, 254, .75);
        }

        .helper-row a {
            color: #67e8f9;
            font-weight: 500;
        }

        .helper-row a:hover {
            text-decoration: underline;
        }

        .fade-switch {
            transition: opacity .28s ease, transform .28s ease;
        }

        .fade-hidden {
            position: absolute;
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none;
        }

        .form-stack {
            position: relative;
        }
    </style>
</head>

<body>

    <!-- SPLASH LOADER: covers everything for ~1.8s, then fades out -->
    <div class="site-loader-overlay" id="siteLoaderOverlay">
        <div class="site-loader">
            <span class="site-loader-text">loading</span>
            <span class="site-load"></span>
        </div>
    </div>

    <div class="scene-bg"></div>
    <svg class="scene-grid" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse">
                <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#ffffff" stroke-dasharray="3 3" stroke-width="1"></path>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid)"></rect>
    </svg>
    <canvas id="bubbleCanvas"></canvas>

    <div class="scene">

        <!-- LEFT PANEL -->
        <div class="panel left-panel">
            <div class="brand-row reveal d1">
                <div>
                    <span class="badge"><i class="fa-solid fa-circle-nodes"></i> SIAKAD v2.4 &middot; 2026</span>
                    <p style="font-size:.72rem;color:rgba(191,219,254,.75);margin:.35rem 0 0;font-weight:500;">Sistem
                        Informasi Akademik &amp; Kemahasiswaan</p>
                </div>
            </div>

            <div class="reveal d2" style="max-width:38rem;">
                <span class="badge"><i class="fa-solid fa-graduation-cap"></i> Institut Teknologi dan Bisnis Asia
                    Malang</span>
                <h1 class="headline">Mewujudkan generasi <em>unggul &amp; berkarakter</em></h1>
                <p class="lede">Pusat integrasi layanan digital akademik, administrasi dosen, pemantauan prestasi
                    kemahasiswaan, dan kemitraan kampus dalam satu ekosistem terpadu.</p>

                <div class="stat-grid">
                    <div class="stat-card reveal d3">
                        <div class="stat-label"><i class="fa-solid fa-award"></i> Prestasi</div>
                        <p class="stat-value">46+</p>
                        <p class="stat-sub">Juara nasional &amp; global</p>
                    </div>
                    <div class="stat-card reveal d4">
                        <div class="stat-label"><i class="fa-solid fa-rocket"></i> Inkubasi</div>
                        <p class="stat-value">INBIS</p>
                        <p class="stat-sub">Start-up &amp; wirausaha</p>
                    </div>
                    <div class="stat-card reveal d5">
                        <div class="stat-label"><i class="fa-solid fa-shield-check"></i> Status</div>
                        <p class="stat-value">2026</p>
                        <p class="stat-sub">Tahun ajaran aktif</p>
                    </div>
                </div>
            </div>

            <div class="foot-row reveal d6">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <i class="fa-solid fa-location-dot" style="color:#67e8f9;"></i>
                    <span>Jl. Soekarno Hatta - Rembuksari No. 1A, Malang</span>
                </div>
                <span>&copy; 2026 Institut Asia Malang</span>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="panel right-panel">

            <div class="status-row reveal d2">
                <span class="live-pill"><span class="live-dot"></span> Server SIAKAD aktif</span>
            </div>

            <div class="card reveal d3">
                <div class="form-stack">

                    <div id="loginSection" class="fade-switch">
                        <h2>Selamat datang</h2>
                        <p class="sub">Masukkan Username, NIM, atau NIDN dan kata sandi untuk masuk ke sistem akademik.
                        </p>

                        <form id="loginForm" style="margin-top:1.5rem; display:flex; flex-direction:column; gap:1.1rem;"
                            onsubmit="event.preventDefault();">
                            <div class="field">
                                <label for="usernameInput">User / NIM / NIDN</label>
                                <div class="field-wrap">
                                    <i class="fa-regular fa-user icon-left"></i>
                                    <input id="usernameInput" name="username"
                                        placeholder="Contoh: 222011005 atau admin.kemahasiswaan" required type="text">
                                </div>
                            </div>

                            <div class="field">
                                <label for="passwordInput">Password</label>
                                <div class="field-wrap">
                                    <i class="fa-regular fa-lock icon-left"></i>
                                    <input class="has-toggle" id="passwordInput" name="password"
                                        placeholder="Masukkan kata sandi Anda" required type="password">
                                    <button class="toggle-eye" id="togglePasswordBtn" type="button">
                                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button class="btn-primary" type="submit">
                                <span>Masuk sekarang</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>

                            <div class="link-line">
                                <a href="#lupa-password" id="goToForgotBtn">Lupa password? Klik di sini untuk reset</a>
                            </div>
                        </form>
                    </div>

                    <div id="forgotSection" class="fade-switch fade-hidden">
                        <div
                            style="display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border-radius:1rem;background:rgba(34,211,238,.18);border:1px solid rgba(103,232,249,.35);margin-bottom:.9rem;">
                            <i class="fa-solid fa-key" style="color:#67e8f9;font-size:1.15rem;"></i>
                        </div>
                        <h2>Lupa password?</h2>
                        <p class="sub">Masukkan NIM, NIDN, atau email terdaftar untuk menerima tautan pemulihan kata
                            sandi.</p>

                        <form id="forgotForm"
                            style="margin-top:1.5rem; display:flex; flex-direction:column; gap:1.1rem;"
                            onsubmit="event.preventDefault();">
                            <div class="field">
                                <label for="recoveryIdentity">NIM / NIDN / Email kampus</label>
                                <div class="field-wrap">
                                    <i class="fa-regular fa-envelope icon-left"></i>
                                    <input id="recoveryIdentity"
                                        placeholder="Contoh: mahasiswa@asia.ac.id atau 222011005" required type="text">
                                </div>
                            </div>

                            <div class="info-box">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>Tautan verifikasi reset password akan dikirim otomatis ke email resmi kampus yang
                                    terhubung dengan akun Anda.</span>
                            </div>

                            <button class="btn-primary" type="submit">
                                <i class="fa-regular fa-paper-plane"></i>
                                <span>Kirim tautan reset password</span>
                            </button>

                            <div class="link-line">
                                <button class="back-link" id="backToLoginBtn" type="button">
                                    <i class="fa-solid fa-arrow-left"></i> Kembali ke halaman login
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- <div class="helper-row reveal d4">
                <span><i class="fa-regular fa-circle-question" style="color:#67e8f9;margin-right:.4rem;"></i>Butuh
                    bantuan akun?</span>
                <a href="#">Hubungi Helpdesk LPPM</a>
            </div> -->
        </div>

    </div>

    <script>
        // ---------- Splash loader: show ~1.8s, then fade out and start the reveal sequence ----------
        (function () {
            var overlay = document.getElementById('siteLoaderOverlay');
            var LOADER_DURATION = 1800; // ms — tune between 1500-2000 as needed

            function dismissLoader() {
                overlay.classList.add('is-hidden');
                document.body.classList.add('loaded'); // unpauses .reveal animations
                overlay.addEventListener('transitionend', function handler() {
                    overlay.remove();
                    overlay.removeEventListener('transitionend', handler);
                }, { once: true });
            }

            window.addEventListener('load', function () {
                setTimeout(dismissLoader, LOADER_DURATION);
            });

            // Fallback in case the 'load' event never fires for some reason
            setTimeout(function () {
                if (document.body.contains(overlay)) dismissLoader();
            }, LOADER_DURATION + 3000);
        })();

        // Toggle password visibility
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('fa-eye', !isPassword);
            eyeIcon.classList.toggle('fa-eye-slash', isPassword);
        });

        // Smooth cross-fade between login and forgot-password forms
        const loginSection = document.getElementById('loginSection');
        const forgotSection = document.getElementById('forgotSection');
        const goToForgotBtn = document.getElementById('goToForgotBtn');
        const backToLoginBtn = document.getElementById('backToLoginBtn');

        function swap(show, hide) {
            hide.classList.add('fade-hidden');
            show.classList.remove('fade-hidden');
        }
        goToForgotBtn.addEventListener('click', (e) => { e.preventDefault(); swap(forgotSection, loginSection); });
        backToLoginBtn.addEventListener('click', () => { swap(loginSection, forgotSection); });

        // Simple light/dark accent toggle (visual only, keeps single cohesive theme)
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        themeToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('light-mode');
        });

        // ---------- Particle / bubble background (canvas, delta-time based = no jank) ----------
        const canvas = document.getElementById('bubbleCanvas');
        const ctx = canvas.getContext('2d');
        let width, height, dpr;
        let bubbles = [];

        function resize() {
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function makeBubble(randomY) {
            const r = 3 + Math.random() * 10;
            return {
                x: Math.random() * width,
                y: randomY ? Math.random() * height : height + r + Math.random() * 200,
                r,
                speed: 10 + Math.random() * 22,      // px per second
                drift: (Math.random() - 0.5) * 14,   // horizontal sway amplitude
                driftSpeed: 0.4 + Math.random() * 0.6,
                phase: Math.random() * Math.PI * 2,
                alpha: 0.06 + Math.random() * 0.16,
            };
        }

        function initBubbles() {
            const count = Math.round((width * height) / 26000);
            bubbles = Array.from({ length: Math.max(24, Math.min(count, 70)) }, () => makeBubble(true));
        }

        let lastTime = null;
        function tick(now) {
            if (lastTime === null) lastTime = now;
            const dt = Math.min((now - lastTime) / 1000, 0.05); // clamp to avoid big jumps (no jank on tab refocus)
            lastTime = now;

            ctx.clearRect(0, 0, width, height);
            for (const b of bubbles) {
                b.y -= b.speed * dt;
                b.phase += b.driftSpeed * dt;
                const x = b.x + Math.sin(b.phase) * b.drift;

                const gradient = ctx.createRadialGradient(x, b.y, 0, x, b.y, b.r);
                gradient.addColorStop(0, `rgba(224,242,254,${b.alpha})`);
                gradient.addColorStop(1, `rgba(103,232,249,0)`);
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(x, b.y, b.r, 0, Math.PI * 2);
                ctx.fill();

                if (b.y < -20) {
                    Object.assign(b, makeBubble(false));
                }
            }
            requestAnimationFrame(tick);
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        resize();
        initBubbles();
        window.addEventListener('resize', () => { resize(); initBubbles(); });
        if (!prefersReducedMotion) {
            requestAnimationFrame(tick);
        }
    </script>
</body>

</html>