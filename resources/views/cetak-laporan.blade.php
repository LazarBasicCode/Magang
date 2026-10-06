<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}?v={{ @filemtime(public_path('css/laporan.css')) }}">
    <title>Cetak {{ $cfg['title'] }} &middot; SIDA</title>
    <style>
        /* Halaman mandiri (tanpa sidebar/header). Gaya kartu, KPI, tabel, kop & tanda tangan dipakai ulang dari laporan.css */
        body { background: var(--canvas); }
        .kc-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px 48px; }
        .kc-table td.wrap { white-space: normal; min-width: 220px; }
        .kc-table td, .kc-table th { vertical-align: top; }
        .kc-table td.no, .kc-table th.no { width: 44px; }
        @media print {
            @page { size: A4 {{ $cfg['landscape'] ? 'landscape' : 'portrait' }}; margin: 14mm; }
            body { background: #fff !important; }
            .kc-wrap { max-width: none; padding: 0; }
            .kc-card { break-inside: auto; }
            .kc-table { font-size: 11.5px; }
            .kc-table td.wrap { min-width: 0; }
            .kc-table thead { display: table-header-group; }
        }
    </style>
</head>

<body>
    @php
        $user      = auth()->user();
        $periode   = $tahun ? 'Tahun ' . $tahun : ($cfg['year'] ? 'Semua Tahun' : 'Seluruh Data');
        $jabatan   = $user->role === 'superadmin' ? 'Super Admin' : 'Admin';
        $generated = now();
    @endphp

    <div class="kc-wrap rp-page">

        <!-- ===== TOOLBAR (tidak ikut tercetak) ===== -->
        <div class="rp-toolbar no-print">
            <div>
                <h1 class="rp-title">Cetak Laporan &middot; {{ $cfg['title'] }}</h1>
                <p class="rp-sub">Semua data menu ini siap cetak. Untuk PDF, pilih "Simpan sebagai PDF" di dialog cetak.</p>
            </div>
            <form method="GET" action="{{ route('cetak.show', $slug) }}" class="rp-filter">
                @if($cfg['year'])
                    <label for="kcTahun">Periode</label>
                    <select id="kcTahun" name="tahun" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected($tahun === (int) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="button" class="rp-btn rp-btn-primary" id="kcPrintBtn">
                    <span class="material-symbols-outlined">print</span> Cetak Laporan
                </button>
            </form>
        </div>

        <section class="rp-card no-print">
            <div class="rp-card-head" style="cursor:default"><div class="rp-card-title"><h2>Penandatangan</h2>
                <p>Nama dan jabatan yang tampil di bagian tanda tangan laporan</p></div></div>
            <div class="rp-print-sign" style="margin-bottom:0">
                <div><label for="kcName">Nama penandatangan</label><input type="text" id="kcName" value="{{ $user->name }}"></div>
                <div><label for="kcRole">Jabatan</label><input type="text" id="kcRole" value="{{ $jabatan }}"></div>
            </div>
        </section>

        <!-- ===== KOP LAPORAN (hanya tampil saat dicetak) ===== -->
        <div class="rp-print-header">
            <div class="rp-print-brand">SISTEM INFORMASI DATA AKADEMIK &middot; INSTITUT ASIA MALANG</div>
            <h1>Laporan {{ $cfg['title'] }}</h1>
            <p>Periode: <strong>{{ $periode }}</strong> &nbsp;&middot;&nbsp; Dicetak: {{ $generated->translatedFormat('d F Y, H:i') }} oleh {{ $user->name }}</p>
        </div>

        <!-- ===== RINGKASAN ===== -->
        <section class="rp-card">
            <div class="rp-card-head" style="cursor:default"><div class="rp-card-title"><h2>Ringkasan</h2>
                <p>Angka kunci untuk periode: {{ $periode }}</p></div></div>
            <div class="rp-kpi-grid rp-kpi-grid-4" style="margin-bottom:0">
                @foreach($stats as $s)
                    <div class="rp-kpi rp-c-{{ $s['color'] }}">
                        <span class="rp-kpi-ico"><span class="material-symbols-outlined">{{ $s['icon'] }}</span></span>
                        <span class="rp-kpi-label">{{ $s['label'] }}</span>
                        <span class="rp-kpi-value">{{ $s['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ===== DAFTAR DATA ===== -->
        <section class="rp-card kc-card">
            <div class="rp-card-head" style="cursor:default"><div class="rp-card-title"><h2>Daftar Data</h2>
                <p>{{ $items->count() }} data &middot; periode: {{ $periode }}</p></div></div>
            @if($items->count())
                <div class="rp-table-wrap">
                    <table class="rp-table kc-table">
                        <thead>
                            <tr>
                                <th class="no">No</th>
                                @foreach($cfg['columns'] as $c)
                                    <th @class(['num' => ($c[2] ?? '') === 'num'])>{{ $c[0] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i => $item)
                                <tr>
                                    <td class="no">{{ $i + 1 }}</td>
                                    @foreach($cfg['columns'] as $c)
                                        <td @class(['num' => ($c[2] ?? '') === 'num', 'wrap' => ($c[2] ?? '') === 'wrap'])>{{ $c[1]($item) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="{{ count($cfg['columns']) }}">Total data</td><td class="num">{{ $items->count() }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="rp-empty">Belum ada data pada periode ini.</p>
            @endif
        </section>

        <!-- ===== TANDA TANGAN (hanya tercetak) ===== -->
        <div class="rp-signature">
            <p>Malang, {{ $generated->translatedFormat('d F Y') }}</p>
            <p>Mengetahui,</p>
            <div class="rp-signature-space"></div>
            <p><strong id="kcSignName">{{ $user->name }}</strong></p>
            <p id="kcSignRole">{{ $jabatan }}</p>
        </div>
    </div>

    <script>
        (function () {
            var nameIn = document.getElementById('kcName');
            var roleIn = document.getElementById('kcRole');
            function sync() {
                document.getElementById('kcSignName').textContent = nameIn.value || '________';
                document.getElementById('kcSignRole').textContent = roleIn.value || '';
            }
            nameIn.addEventListener('input', sync);
            roleIn.addEventListener('input', sync);
            window.addEventListener('beforeprint', sync); // juga saat Ctrl+P
            document.getElementById('kcPrintBtn').addEventListener('click', function () { sync(); window.print(); });
        })();
    </script>
</body>

</html>
