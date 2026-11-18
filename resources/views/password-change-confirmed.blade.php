<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $ok ? 'Konfirmasi berhasil' : 'Tautan tidak berlaku' }} — SIDA</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <style>
        :root { --bg:#eceef8; --card:#fff; --ink:#2f3349; --muted:#6b7280; --border:#dde1f1; --primary:#5b5fef; --primary-dark:#4547d1; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#21273c; --card:#252c43; --ink:#f8fafc; --muted:#a8b3cf; --border:#35425f; }
        }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:20px; background:var(--bg); color:var(--ink);
               font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .card { width:min(420px,100%); padding:32px 28px; text-align:center; background:var(--card); border:1px solid var(--border); border-radius:16px; }
        .icon { width:64px; height:64px; margin:0 auto 16px; display:grid; place-items:center; border:1.5px solid var(--border); border-radius:16px; }
        .icon .material-symbols-outlined { font-size:32px; }
        h1 { margin:0 0 8px; font-size:1.25rem; }
        p { margin:0 0 20px; font-size:.92rem; line-height:1.55; color:var(--muted); }
        a.btn { display:inline-block; padding:10px 18px; border-radius:10px; background:var(--primary); color:#fff; font-weight:600; font-size:.9rem; text-decoration:none; }
        a.btn:hover { background:var(--primary-dark); }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon"><span class="material-symbols-outlined">{{ $ok ? 'check' : 'link_off' }}</span></div>
        @if($ok)
            <h1>Konfirmasi berhasil</h1>
            <p>Terima kasih, {{ $name }}. Kembali ke halaman SIDA yang sedang terbuka — halamannya otomatis lanjut ke pembuatan password baru.</p>
        @else
            <h1>Tautan tidak berlaku</h1>
            <p>Tautan ini sudah kedaluwarsa atau sudah diganti dengan yang lebih baru. Buka menu Ganti Password di SIDA untuk meminta email konfirmasi baru.</p>
        @endif
        <a class="btn" href="{{ route('dashboard.index') }}">Buka SIDA</a>
    </main>
    @if($ok)
    <script>
        // Beri tahu tab SIDA di browser yang sama agar langsung lanjut tanpa menunggu giliran pengecekan berkala.
        try { const c = new BroadcastChannel('sida-password-change'); c.postMessage('confirmed'); c.close(); } catch (e) {}
    </script>
    @endif
</body>
</html>
