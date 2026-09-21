<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk · SIDA</title>

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
        /* ==========================================================
           TOKENS
        ========================================================== */
        :root {
            --font: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;

            /* background */
            --page-bg:
                linear-gradient(135deg, #4d7fff 0%, #1f45d3 42%, #2814ac 100%);
            --orb: rgba(255, 255, 255, .10);
            --solid-shadow: #7db4ff;   /* lapisan solid di belakang panel form */
            --ring: rgba(255, 255, 255, .22);
            --hero-ink: #ffffff;
            --hero-ink-soft: rgba(255, 255, 255, .80);
            --badge-bg: #ffffff;
            --badge-ink: #2447c9;

            /* jendela */ 
            --card-bg: #ffffff;
            --card-ink: #0f1b3d;
            --card-soft: #64739a;
            --card-line: #e6ecf8;
            --tile: #0f1a3c;
            --chip-bg: #eef3ff;
            --chip-ink: #2447c9;
            --row-active: #f5f8ff;
            --track: #dfe7ff;

            /* panel kanan */
            --panel: #ffffff;
            --ink: #12204a;
            --ink-soft: #5b6b90;
            --field: #f1f5fd;
            --field-line: #d9e3f6;
            --primary-a: #2563eb;
            --primary-b: #4338ca;
            --primary: #2f52d9;
            --focus: rgba(47, 82, 217, .28);
            --err-bg: #fef2f2;
            --err-line: #fecaca;
            --err-ink: #b42318;
            --shadow: #5b6b90;
        }

        /* 2 DARK MODE */
        :root[data-theme="dark"] {
            --page-bg:
                linear-gradient(135deg, #11266b 0%, #0e1b42 55%, #0d1327 100%);
            --orb: rgba(59, 100, 200, .16);
            --solid-shadow: #124114;
            --ring: rgba(255, 255, 255, .14);
            --badge-bg: #22273a;
            --badge-ink: #1d3fbf;
            --tile: #182238;
            --panel: #111827;
            --ink: #eaf0ff;
            --ink-soft: #93a3c6;
            --field: #182238;
            --field-line: #27345a;
            --primary-a: #3b82f6;
            --primary-b: #6366f1;
            --primary: #6f9dff;
            --focus: rgba(111, 157, 255, .35);
            --err-bg: rgba(239, 68, 68, .14);
            --err-line: rgba(239, 68, 68, .4);
            --err-ink: #fca5a5;
            --shadow: #6366f1;
            --card-bg: #1b2333; --card-ink: #eaf0ff; --card-soft: #93a3c6; --card-line: #27345a;
            --chip-bg: #243050; --chip-ink: #9dbcff; --row-active: #222c42; --track: #2b3856;
        }

        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --page-bg:
                    linear-gradient(135deg, #11266b 0%, #0e1b42 55%, #0d1327 100%);
                --orb: rgba(59, 100, 200, .16);
                --solid-shadow: #1a3c84;
                --ring: rgba(255, 255, 255, .14);
                --panel: #111827;
                --ink: #eaf0ff;
                --ink-soft: #93a3c6;
                --field: #182238;
                --field-line: #27345a;
                --primary-a: #3b82f6;
                --primary-b: #6366f1;
                --primary: #6f9dff;
                --focus: rgba(111, 157, 255, .35);
                --err-bg: rgba(239, 68, 68, .14);
                --err-line: rgba(239, 68, 68, .4);
                --err-ink: #fca5a5;
                --card-bg: #1b2333; --card-ink: #eaf0ff; --card-soft: #93a3c6; --card-line: #27345a;
                --chip-bg: #243050; --chip-ink: #9dbcff; --row-active: #222c42; --track: #2b3856;
            }
        }

        /* ==========================================================
           BASE
        ========================================================== */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: var(--font);
            color: var(--ink);
            background: var(--panel);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        button, input { font: inherit; }
        :focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }

        .svg-defs { position: absolute; width: 0; height: 0; overflow: hidden; }

        /* ==========================================================
           LAYOUT  60 / 40  — MENYATU
        ========================================================== */
        .page {
            display: grid;
            grid-template-columns: 3fr 2fr;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            background: var(--page-bg);
        }

        /* ==========================================================
           KIRI · PANGGUNG GRAFIS
        ========================================================== */
        .hero {
            position: relative;
            display: flex; flex-direction: column;
            min-height: 0; overflow: hidden;
            padding: clamp(1.25rem, 3.4vh, 2.5rem) clamp(1.5rem, 3.6vw, 3.5rem);
            padding-right: calc(clamp(1.5rem, 3.6vw, 3.5rem) + 16px); /* ruang untuk lapisan solid */
            color: var(--hero-ink);
        }

        .brand { display: flex; align-items: center; gap: .75rem; position: relative; z-index: 2; }
        .brand svg { width: 46px; height: 46px; filter: drop-shadow(0 8px 16px rgba(6, 14, 60, .35)); }
        .brand span { font-size: 1.6rem; font-weight: 800; letter-spacing: .02em; }

        .headline { position: relative; z-index: 2; margin-top: clamp(.75rem, 2.4vh, 1.75rem); max-width: 30rem; }
        .headline h1 {
            margin: 0; font-weight: 800; letter-spacing: -.025em; line-height: 1.12;
            font-size: clamp(1.6rem, 2.7vw, 2.55rem);
        }
        .headline p {
            margin: .6rem 0 0; color: var(--hero-ink-soft);
            font-size: clamp(.84rem, .95vw, .95rem); line-height: 1.6; max-width: 26rem;
        }

        .stage { position: relative; flex: 1; min-height: 0; display: grid; place-items: center; }

        /* teks polos: nama lengkap di samping logo + alamat di dasar */
        .brand-text { display: flex; flex-direction: column; gap: .2rem; }
        .brand-text span { line-height: 1.05; }
        .brand-text small { font-size: .74rem; font-weight: 500; color: var(--hero-ink-soft); }
        .hero-foot {
            position: relative; z-index: 2;
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: .2rem 1.5rem;
            font-size: .74rem; color: var(--hero-ink-soft);
        }
        .hero-foot i { margin-right: .4rem; }

        /* ornamen latar */
        .orb {
            position: absolute; border-radius: 50%; background: var(--orb);
            width: min(64%, 420px); aspect-ratio: 1; left: 6%; top: 50%; transform: translateY(-46%);
        }
        .ring {
            position: absolute; border-radius: 50%; border: 2px dashed var(--ring); pointer-events: none;
        }
        .ring.r1 { width: 320px; height: 320px; top: -170px; right: 6%; }
        .ring.r2 { width: 360px; height: 360px; bottom: -220px; left: -160px; }

        /* ---------- scene ---------- */
        .scene { position: relative; width: min(100%, 520px); }
        .float { animation: float 9s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        .window {
            position: relative;
            background: var(--card-bg); color: var(--card-ink);
            border-radius: 22px;
            padding: 1rem 1.1rem 1.1rem;
            transform: rotate(-4deg);
            transition: transform .6s cubic-bezier(.22, 1, .36, 1);
            box-shadow: 0 40px 70px -24px rgba(11, 31, 142, 0.65), 0 8px 20px -8px rgba(11, 31, 142, 0.35);
        }
        .scene:hover .window { transform: rotate(-1deg); }

        .dots { display: flex; gap: 6px; margin: 0 0 .7rem .1rem; }
        .dots i { width: 9px; height: 9px; border-radius: 50%; }
        .dots i:nth-child(1) { background: #ff9b9b; }
        .dots i:nth-child(2) { background: #ffd66b; }
        .dots i:nth-child(3) { background: #7ee6a5; }

        /* baris modul */
        .row { border-radius: 14px; transition: background .3s; }
        .row + .row { margin-top: .25rem; }
        .row.is-active { background: var(--row-active); }
        .row-head {
            width: 100%; display: flex; align-items: center; gap: .8rem;
            padding: .55rem .6rem; border: 0; background: none; color: inherit;
            text-align: left; cursor: pointer; border-radius: 14px;
        }
        .tile {
            flex: 0 0 auto; width: 2.5rem; height: 2.5rem; border-radius: 12px;
            display: grid; place-items: center;
            background: var(--tile); color: #fff; font-size: .95rem;
            transition: background .3s, transform .3s;
        }
        .row.is-active .tile {
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            transform: scale(1.06);
            box-shadow: 0 8px 16px -6px rgba(59, 90, 230, .7);
        }
        .row-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .row-txt strong { font-size: .95rem; font-weight: 700; }
        .row-txt small { font-size: .76rem; color: var(--card-soft); margin-top: 1px; }
        .caret { color: var(--card-soft); font-size: .72rem; transition: transform .35s; }
        .row.is-active .caret { transform: rotate(180deg); }

        .row-body {
            display: grid; grid-template-rows: 0fr;
            transition: grid-template-rows .45s cubic-bezier(.22, 1, .36, 1);
        }
        .row.is-active .row-body { grid-template-rows: 1fr; }
        .row-body-in { min-height: 0; overflow: hidden; padding: 0 .6rem 0 3.9rem; }
        .row.is-active .row-body-in { padding-bottom: .7rem; }

        .line { display: flex; align-items: baseline; gap: .6rem; margin-top: .45rem; }
        .line b { flex: 0 0 4.4rem; font-size: .7rem; font-weight: 600; color: var(--card-soft); }
        .chips { display: flex; flex-wrap: wrap; gap: .3rem; }
        .chip {
            font-style: normal; font-size: .72rem; font-weight: 600;
            padding: .16rem .55rem; border-radius: 999px;
            background: var(--chip-bg); color: var(--chip-ink); white-space: nowrap;
        }

        /* bilah progres hijau (juga penanda pergantian otomatis) */
        .progress { margin: .85rem .25rem 0; height: 6px; border-radius: 6px; background: var(--track); overflow: hidden; }
        .fill {
            height: 100%; width: 0; border-radius: 6px;
            background: linear-gradient(90deg, #22c55e, #34d399);
        }
        .fill.run { animation: fillbar var(--dur, 7s) linear forwards; }
        @keyframes fillbar { from { width: 0; } to { width: 100%; } }
        .scene:hover .fill { animation-play-state: paused; }

        /* lencana bulat mengambang */
        .badge {
            position: absolute; width: 3.1rem; height: 3.1rem; border-radius: 50%;
            display: grid; place-items: center;
            background: var(--badge-bg); color: var(--badge-ink); font-size: 1.15rem;
            border: 0; cursor: pointer;
            box-shadow: 0 14px 26px -8px rgba(4, 12, 60, .55);
            animation: bob 7s ease-in-out infinite;
            transition: background .3s, color .3s, box-shadow .3s;
        }
        .badge.is-active {
            background: linear-gradient(135deg, #3b82f6, #4f46e5); color: #fff;
            box-shadow: 0 0 0 5px rgba(255, 255, 255, .28), 0 14px 26px -8px rgba(4, 12, 60, .55);
        }
        .badge.b1 { top: -4%;  right: -7%; animation-delay: -1s; }
        .badge.b2 { bottom: 14%; right: -9%; animation-delay: -3s; width: 3.4rem; height: 3.4rem; }
        .badge.b3 { bottom: -9%; left: -6%; animation-delay: -5s; }
        .badge.ghost {
            top: 8%; left: -8%; width: 2.6rem; height: 2.6rem; font-size: .95rem; cursor: default;
            background: rgba(255, 255, 255, .16); color: rgba(255, 255, 255, .7); box-shadow: none;
            animation-delay: -2s;
        }
        @keyframes bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }

        /* ---------- animasi transisi: muncul dari bawah ke atas ---------- */
        @keyframes riseUp {
            from { opacity: 0; transform: translateY(36px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .rise { animation: riseUp .75s cubic-bezier(.22, 1, .36, 1) both; animation-delay: calc(var(--i, 0) * 90ms); }
        .row.is-active .line { animation: riseUp .55s cubic-bezier(.22, 1, .36, 1) both; animation-delay: calc(var(--i, 0) * 70ms + 120ms); }

        /* ==========================================================
           KANAN · FORM  (tidak scroll)
        ========================================================== */
        .auth {
            position: relative; z-index: 1;
            background: var(--panel);
            border-radius: 44px 0 0 44px;
            box-shadow: -.7rem 0 0 var(--solid-shadow);   /* lapisan di belakang panel (mengikuti lengkungan) */
            display: flex; flex-direction: column; justify-content: center;
            padding: clamp(1rem, 3vh, 2.5rem) clamp(1.5rem, 3.4vw, 3.25rem);
            overflow: hidden;
            min-height: 0;
        }
        .auth-inner { width: 100%; max-width: 380px; margin: 0 auto; }
        .auth h2 { margin: 0; font-size: clamp(1.6rem, 2.2vw, 1.75rem); font-weight: 800; letter-spacing: -.02em; }
        .auth .sub-title { margin: .35rem 0 1.5rem; color: var(--ink-soft); font-size: .88rem; line-height: 1.5; }

        #loginForm, #forgotForm { margin-top: clamp(1rem, 2.8vh, 1.6rem); display: flex; flex-direction: column; gap: clamp(.75rem, 2vh, 1.1rem); }

        .theme-toggle {
            position: absolute; top: clamp(.9rem, 2.4vh, 1.4rem); right: clamp(.9rem, 2vw, 1.4rem);
            width: 2.5rem; height: 2.5rem; border-radius: 50%;
            border: 0; background: var(--field); color: var(--ink-soft); cursor: pointer;
            transition: color .2s;
        }
        .theme-toggle:hover { color: var(--primary); }
        .theme-toggle .fa-sun { display: none; }
        :root[data-theme="dark"] .theme-toggle .fa-moon { display: none; }
        :root[data-theme="dark"] .theme-toggle .fa-sun  { display: inline; }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) .theme-toggle .fa-moon { display: none; }
            :root:not([data-theme="light"]) .theme-toggle .fa-sun  { display: inline; }
        }

        .alert {
            background: var(--err-bg); border: 1px solid var(--err-line); color: var(--err-ink);
            padding: 9px 13px; border-radius: 10px; font-size: .78rem;
            display: flex; align-items: center; gap: 8px;
        }

        .field { display: flex; flex-direction: column; gap: .4rem; }
        .field label { font-size: .8rem; font-weight: 600; }
        .field-wrap { position: relative; }
        .field-wrap input {
            width: 100%; padding: .8rem 1rem .8rem 2.7rem;
            background: var(--field); border: 1.5px solid var(--field-line); border-radius: 12px;
            color: var(--ink); font-size: .88rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .field-wrap input::placeholder { color: var(--ink-soft); opacity: .7; }
        .field-wrap input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px var(--focus); }
        .field-wrap input.has-toggle { padding-right: 3rem; }
        .icon-left { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--ink-soft); font-size: .88rem; pointer-events: none; }
        .toggle-eye {
            position: absolute; right: .45rem; top: 50%; transform: translateY(-50%);
            width: 2.2rem; height: 2.2rem; border: 0; border-radius: 8px;
            background: transparent; color: var(--ink-soft); cursor: pointer;
        }
        .toggle-eye:hover { color: var(--primary); }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
            width: 100%; padding: .88rem 1rem; border: 0; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-a), var(--primary-b));
            color: #fff; font-weight: 700; font-size: .93rem; cursor: pointer;
            box-shadow: 0 8px 24px -12px var(--shadow);
            margin-top: 1rem;
            transition: transform .2s, filter .2s;
        }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-primary:active { transform: translateY(1px); }
        .btn-primary i { transition: transform .2s; }
        .btn-primary:hover i { transform: translateX(4px); }

        .link-line { text-align: center; font-size: .8rem; }
        .link-line a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .link-line a:hover { text-decoration: underline; }

        /* ---------- Transisi silang antara login <-> lupa password ---------- */
        .auth-inner { display: grid; }
        .fade-switch {
            grid-area: 1 / 1; align-self: center; min-width: 0;
            transition: opacity .35s ease, transform .35s ease, visibility 0s linear 0s;
        }
        .fade-hidden {
            opacity: 0; transform: translateY(14px);
            visibility: hidden; pointer-events: none;
            transition: opacity .35s ease, transform .35s ease, visibility 0s linear .35s;
        }

        .forgot-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 3rem; height: 3rem; border-radius: 1rem; margin-bottom: .9rem;
            background: linear-gradient(135deg, var(--primary-a), var(--primary-b));
            color: #fff; font-size: 1.15rem;
            box-shadow: 0 12px 22px -10px var(--primary-b);
        }

        .info-box {
            display: flex; align-items: flex-start; gap: .65rem;
            background: var(--field); border: 1px solid var(--field-line);
            border-radius: 12px; padding: .75rem .9rem;
            color: var(--ink-soft); font-size: .78rem; line-height: 1.55;
        }
        .info-box i { color: var(--primary); margin-top: .18rem; }

        .back-link {
            display: inline-flex; align-items: center; gap: .45rem;
            border: 0; background: none; padding: 0;
            color: var(--primary); font-weight: 500; font-size: .8rem; cursor: pointer;
        }
        .back-link:hover { text-decoration: underline; }
        .back-link i { transition: transform .2s; }
        .back-link:hover i { transform: translateX(-3px); }

        /* ==========================================================
           RESPONSIVE
        ========================================================== */
        @media (max-height: 720px) {
            .headline p { display: none; }
            .badge.ghost { display: none; }
        }
        @media (max-height: 600px) {
            .headline { display: none; }
            .hero-foot { display: none; }
        }
        @media (max-width: 1100px) {
            .badge.b1 { right: -3%; } .badge.b2 { right: -4%; }
            .badge.b3 { left: -3%; }  .badge.ghost { left: -3%; }
        }
        @media (max-width: 900px) {
            html, body { height: auto; }
            .page { display: block; height: auto; min-height: 100vh; overflow: visible; }
            .hero { padding: 1.25rem 1.5rem 3.25rem; }
            .stage, .headline p, .hero-foot { display: none; }
            .headline { margin-top: 1rem; }
            .auth {
                margin: -28px 0 0; border-radius: 28px 28px 0 0; box-shadow: 0 -12px 0 var(--solid-shadow);
                padding: 2rem 1.5rem 2rem; overflow: visible;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .rise, .row.is-active .line, .float, .badge { animation: none !important; }
            .fill { width: 100%; animation: none !important; }
        }
    </style>
</head>
<body>

<!-- Definisi ikon SIDA (dipakai di panel kiri & form) -->
<svg class="svg-defs" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="sidaGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#38bdf8"/>
            <stop offset=".55" stop-color="#4f7bff"/>
            <stop offset="1" stop-color="#5b3df0"/>
        </linearGradient>
        <linearGradient id="sidaShine" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity=".38"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
        <symbol id="sida-mark" viewBox="0 0 48 48">
            <clipPath id="sidaClip"><circle cx="24" cy="24" r="22.5"/></clipPath>
            <image href="{{ asset('img/logo-prodi.png') }}" x="1.5" y="1.5" width="45" height="45" preserveAspectRatio="xMidYMid slice" clip-path="url(#sidaClip)"/>
            <circle cx="24" cy="24" r="22.5" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.5"/>
        </symbol>
    </defs>
</svg>

<main class="page">

    <!-- ================= KIRI (60%) ================= -->
    <section class="hero" aria-label="Ringkasan modul SIDA">
        <span class="ring r1"></span>
        <span class="ring r2"></span>

        <div class="brand rise" style="--i:0">
            <svg aria-hidden="true"><use href="#sida-mark"/></svg>
            <div class="brand-text">
                <span>SIDA </span>
                <small>Sistem Informasi Data Akademik &amp; Akreditasi</small>
            </div>
        </div>

        <div class="headline rise" style="--i:1">
            <h1>Capaian kampus, dalam satu pandangan.</h1>
            <p>Prestasi mahasiswa, riset dosen, dan kerja sama, tertata rapi dan mudah dicari.</p>
        </div>

        <div class="stage">
            <span class="orb"></span>

            <div class="scene rise" style="--i:3" id="scene">
                <div class="float">
                    <div class="window">
                        <div class="dots"><i></i><i></i><i></i></div>

                        <!-- 1 · Kemahasiswaan -->
                        <div class="row is-active" data-i="0">
                            <button class="row-head" type="button" aria-expanded="true" aria-controls="d0">
                                <span class="tile"><i class="fa-solid fa-graduation-cap"></i></span>
                                <span class="row-txt"><strong>Kemahasiswaan</strong><small>Prestasi mahasiswa</small></span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </button>
                            <div class="row-body" id="d0"><div class="row-body-in">
                                <div class="line" style="--i:0"><b>Sumber</b><div class="chips"><em class="chip">Inbis</em><em class="chip">Kemahasiswaan</em></div></div>
                                <div class="line" style="--i:1"><b>Kategori</b><div class="chips"><em class="chip">Akademik</em><em class="chip">Non-Akademik</em></div></div>
                                <div class="line" style="--i:2"><b>Tingkat</b><div class="chips"><em class="chip">Lokal</em><em class="chip">Nasional</em><em class="chip">Internasional</em></div></div>
                                <div class="line" style="--i:3"><b>Bukti</b><div class="chips"><em class="chip">SK</em><em class="chip">Foto</em><em class="chip">Sertifikat</em></div></div>
                            </div></div>
                        </div>

                        <!-- 2 · LPPM -->
                        <div class="row" data-i="1">
                            <button class="row-head" type="button" aria-expanded="false" aria-controls="d1">
                                <span class="tile"><i class="fa-solid fa-book-open"></i></span>
                                <span class="row-txt"><strong>LPPM</strong><small>Penelitian &amp; pengabdian</small></span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </button>
                            <div class="row-body" id="d1"><div class="row-body-in">
                                <div class="line" style="--i:0"><b>Mahasiswa</b><div class="chips"><em class="chip">SINTA 1–4</em><em class="chip">Conference</em><em class="chip">Jurnal Int'l</em></div></div>
                                <div class="line" style="--i:1"><b>Dosen</b><div class="chips"><em class="chip">Q1–Q4</em><em class="chip">HKI</em><em class="chip">SINTA 1–4</em><em class="chip">Book</em></div></div>
                                <div class="line" style="--i:2"><b>Rekognisi</b><div class="chips"><em class="chip">Nasional</em><em class="chip">Internasional</em><em class="chip">Alumni</em></div></div>
                            </div></div>
                        </div>

                        <!-- 3 · Kerja Sama -->
                        <div class="row" data-i="2">
                            <button class="row-head" type="button" aria-expanded="false" aria-controls="d2">
                                <span class="tile"><i class="fa-solid fa-handshake"></i></span>
                                <span class="row-txt"><strong>Kerja Sama</strong><small>Jejaring &amp; kolaborasi</small></span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </button>
                            <div class="row-body" id="d2"><div class="row-body-in">
                                <div class="line" style="--i:0"><b>Mahasiswa</b><div class="chips"><em class="chip">Conference</em><em class="chip">PKL</em><em class="chip">Sharing session</em></div></div>
                                <div class="line" style="--i:1"><b>Dosen</b><div class="chips"><em class="chip">Keynote speaker</em></div></div>
                                <div class="line" style="--i:2"><b>Guest Lecture</b><div class="chips"><em class="chip">Inbound</em><em class="chip">Outbound</em></div></div>
                                <div class="line" style="--i:3"><b>Internasional</b><div class="chips"><em class="chip">Pengabdian</em><em class="chip">Research</em></div></div>
                            </div></div>
                        </div>

                        <div class="progress" aria-hidden="true"><div class="fill" id="fill"></div></div>
                    </div>
                </div>

                <!-- lencana mengambang (klik untuk membuka modulnya) -->
                <button class="badge b1 is-active" type="button" data-i="0" aria-label="Kemahasiswaan"><i class="fa-solid fa-trophy"></i></button>
                <button class="badge b2" type="button" data-i="1" aria-label="LPPM"><i class="fa-solid fa-flask"></i></button>
                <button class="badge b3" type="button" data-i="2" aria-label="Kerja Sama"><i class="fa-solid fa-earth-asia"></i></button>
                <span class="badge ghost" aria-hidden="true"><i class="fa-solid fa-bullseye"></i></span>
            </div>
        </div>

        <div class="hero-foot rise" style="--i:4">
            <span><i class="fa-solid fa-location-dot"></i>Jl. Soekarno Hatta - Rembuksari No. 1A, Malang</span>
            <span>&copy; {{ date('Y') }} Institut Asia Malang</span>
        </div>
    </section>

    <!-- ================= KANAN (40%) ================= -->
    <section class="auth" aria-label="Form login">
        <button class="theme-toggle" id="themeToggle" type="button" aria-label="Ganti tema">
            <i class="fa-solid fa-moon"></i><i class="fa-solid fa-sun"></i>
        </button>

        <div class="auth-inner">
            <div id="loginSection" class="fade-switch">
            <div class="rise" style="--i:3">
                <h2>Selamat Datang</h2>
                <p class="sub-title">Masuk ke akun kampus Anda untuk melanjutkan.</p>
            </div>

            <form id="loginForm" method="POST" action="{{ url('/login-process') }}" class="rise" style="--i:4">
                @csrf
                @error('username')
                <div class="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $message }}</span>
                </div>
                @enderror
                <div class="field">
                    <label for="usernameInput">User / NIM / NIDN</label>
                    <div class="field-wrap">
                        <i class="fa-regular fa-user icon-left"></i>
                        <input id="usernameInput" name="username"
                            placeholder="Contoh: 222011005 atau admin.kemahasiswaan" required type="text"
                            autocomplete="username" value="{{ old('username') }}">
                    </div>
                </div>

                <div class="field">
                    <label for="passwordInput">Password</label>
                    <div class="field-wrap">
                        <i class="fa-solid fa-lock icon-left"></i>
                        <input class="has-toggle" id="passwordInput" name="password"
                            placeholder="Masukkan kata sandi Anda" required type="password"
                            autocomplete="current-password">
                        <button class="toggle-eye" id="togglePasswordBtn" type="button" aria-label="Tampilkan password">
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
                <span class="forgot-icon"><i class="fa-solid fa-key"></i></span>
                <h2>Lupa password?</h2>
                <p class="sub-title">Masukkan NIM, NIDN, atau email terdaftar untuk menerima tautan pemulihan kata sandi.</p>

                <form id="forgotForm" onsubmit="event.preventDefault();">
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
    </section>
</main>

<script>
    /* ---------- Panel grafis: pilih modul + putar otomatis ---------- */
    (function () {
        var rows   = Array.prototype.slice.call(document.querySelectorAll('.row'));
        var badges = Array.prototype.slice.call(document.querySelectorAll('.badge[data-i]'));
        var fill   = document.getElementById('fill');
        var current = 0;

        function restartBar() {
            fill.classList.remove('run');
            void fill.offsetWidth;              // reflow supaya animasi mulai dari nol
            fill.classList.add('run');
        }

        function select(i) {
            current = i;
            rows.forEach(function (r, idx) {
                var on = idx === i;
                r.classList.toggle('is-active', on);
                r.querySelector('.row-head').setAttribute('aria-expanded', on);
            });
            badges.forEach(function (b) {
                b.classList.toggle('is-active', +b.getAttribute('data-i') === i);
            });
            restartBar();
        }

        rows.forEach(function (r, idx) {
            r.querySelector('.row-head').addEventListener('click', function () { select(idx); });
        });
        badges.forEach(function (b) {
            b.addEventListener('click', function () { select(+b.getAttribute('data-i')); });
        });

        fill.addEventListener('animationend', function () {
            select((current + 1) % rows.length);
        });
        restartBar();
    })();

    /* ---------- Tampilkan / sembunyikan password ---------- */
    (function () {
        var btn = document.getElementById('togglePasswordBtn');
        var input = document.getElementById('passwordInput');
        var icon = document.getElementById('eyeIcon');
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();

    /* Ganti Mode Dark/Bright */
    (function () {
        var root = document.documentElement;
        document.getElementById('themeToggle').addEventListener('click', function () {
            var isDark = root.getAttribute('data-theme') === 'dark' ||
                (!root.getAttribute('data-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            var next = isDark ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('sida-theme', next); } catch (e) {}
        });
    })();

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
</script>
</body>
</html>