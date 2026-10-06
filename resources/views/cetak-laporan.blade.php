{{--
    Isi modal "Cetak Laporan" (potongan HTML, BUKAN halaman penuh).
    Dimuat lewat fetch oleh public/js/bulk-import.js ke #printBody (markup modal: partials/bulk-menu.blade.php).
    Variabel dari CetakLaporanController: $cfg, $slug, $items, $years, $tahun, $stats
    Gaya kartu/KPI/tabel/kop/tanda tangan: laporan.css. Gaya khusus modal & cetak: style.css (bagian "CETAK LAPORAN").
--}}
@php
    $user      = auth()->user();
    $periode   = $tahun ? 'Tahun ' . $tahun : ($cfg['year'] ? 'Semua Tahun' : 'Seluruh Data');
    $jabatan   = $user->role === 'superadmin' ? 'Super Admin' : 'Admin';
    $generated = now();
@endphp

<div class="rp-page pm-report" data-orient="{{ $cfg['landscape'] ? 'landscape' : 'portrait' }}">

    <!-- ===== FILTER PERIODE (tidak ikut tercetak) — dropdown custom .dropdown, ditangani bulk-import.js ===== -->
    @if($cfg['year'])
        <div class="rp-filter pm-filter no-print">
            <label>Periode</label>
            <div class="dropdown" data-dropdown id="dd-pm-tahun">
                <input type="hidden" id="pmTahun" value="{{ $tahun }}" />
                <button type="button" class="dropdown-trigger">
                    <span class="dropdown-value">{{ $tahun ?: 'Semua Tahun' }}</span>
                    <span class="material-symbols-outlined caret">expand_more</span>
                </button>
                <div class="dropdown-panel">
                    <button type="button" @class(['dropdown-option', 'is-selected' => !$tahun]) data-value="">Semua Tahun</button>
                    @foreach($years as $y)
                        <button type="button" @class(['dropdown-option', 'is-selected' => $tahun === (int) $y]) data-value="{{ $y }}">{{ $y }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <section class="rp-card no-print">
        <div class="rp-card-head" style="cursor:default"><div class="rp-card-title"><h2>Penandatangan</h2>
            <p>Nama dan jabatan yang tampil di bagian tanda tangan laporan</p></div></div>
        <div class="rp-print-sign" style="margin-bottom:0">
            <div><label for="pmName">Nama penandatangan</label><input type="text" id="pmName" value="{{ $user->name }}"></div>
            <div><label for="pmRole">Jabatan</label><input type="text" id="pmRole" value="{{ $jabatan }}"></div>
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
    <section class="rp-card pm-card">
        <div class="rp-card-head" style="cursor:default"><div class="rp-card-title"><h2>Daftar Data</h2>
            <p>{{ $items->count() }} data &middot; periode: {{ $periode }}</p></div></div>
        @if($items->count())
            <div class="rp-table-wrap">
                <table class="rp-table pm-table">
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
        <p><strong id="pmSignName">{{ $user->name }}</strong></p>
        <p id="pmSignRole">{{ $jabatan }}</p>
    </div>
</div>
