{{--
    SEMUA KARTU dashboard (dipakai bersama oleh partials admin, dosen, mahasiswa).
    Jangan taruh teks/angka khusus role di sini; itu diatur di partial role masing-masing.
    Parameter yang diterima:
      $brand $subtitle $action? $hero[] $cards[] $cards2[] $donuts[] $widgets[]
      $trendSub $trendDatasets $barSub $barDatasets $recentSub $recent[] $showWho

    Urutan: sapaan+hero → kartu ringkasan → donut/tren/bar/aktivitas → widget khusus.
    Tipe widget: bars | attention | ranking | upcoming | laporan | log | donut
--}}
@php
    $hour = (int) now()->format('G');
    $sapaan = match (true) {
        $hour >= 4 && $hour < 11  => 'Selamat Pagi',
        $hour >= 11 && $hour < 15 => 'Selamat Siang',
        $hour >= 15 && $hour < 18 => 'Selamat Sore',
        default                   => 'Selamat Malam',
    };
    $periods = ['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'];
@endphp

{{-- ===== SAPAAN + HERO ===== --}}
<div class="dash-panel reveal">
    <div class="dash-stars" aria-hidden="true">
        <div class="dash-stars-rot">
            <div class="ds ds1"></div>
            <div class="ds ds2"></div>
            <div class="ds ds3"></div>
        </div>
    </div>

    <div class="dash-welcome">
        <div class="dash-welcome-main">
            <p class="dash-welcome-brand">{{ $brand }}</p>
            <p class="dash-welcome-title">{{ $sapaan }}, {{ auth()->user()->name }} 👋</p>
            <p class="dash-welcome-sub">{{ $subtitle }} &middot; {{ now()->year }}</p>
            @if(!empty($action['url']))
                <a href="{{ $action['url'] }}" class="btn-primary admin-quick-link">
                    <span class="material-symbols-outlined">arrow_forward</span>
                    <span>{{ $action['label'] }}</span>
                </a>
            @endif
        </div>
        <div class="dash-welcome-clock">
            <div class="dash-welcome-clock-time" id="dashWelcomeClock">{{ now()->format('H:i') }}</div>
            <div class="dash-welcome-clock-date">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
    </div>

    <div class="dash-panel-divider"></div>

    <div class="stat-hero-grid">
        <div class="stat-hero-card grad-1"></div>
        @foreach($hero as $i => $h)
            <div class="stat-hero-card grad-{{ $i + 2 }}">
                <span class="material-symbols-outlined stat-hero-icon">{{ $h['icon'] }}</span>
                <p class="stat-hero-label">{{ $h['label'] }}</p>
                <div class="stat-hero-value">{{ $h['value'] }}{{ $h['unit'] === '%' ? '' : ' ' }}<span class="stat-hero-unit">{{ $h['unit'] }}</span></div>
            </div>
        @endforeach
    </div>
</div>

{{-- ===== KARTU RINGKASAN (1 atau 2 baris) ===== --}}
@foreach(array_filter([$cards, $cards2]) as $row)
    <div class="stat-grid" @if(!$loop->first) style="margin-top:18px;" @endif>
        @foreach($row as $card)
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">{{ $card['label'] }}</span>
                    <span class="stat-value">{{ $card['value'] }}</span>
                </div>
                <div class="stat-icon {{ $card['color'] }}">
                    <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

{{-- ===== DONUT + TREN + BAR/AKTIVITAS ===== --}}
<div class="dash-grid">
    @foreach($donuts as $d)
        <div class="dash-card donut-card reveal" @if(!$loop->first) style="transition-delay:80ms" @endif>
            <h2 class="dash-card-title">{{ $d['title'] }}</h2>
            <p class="dash-card-sub">{{ $d['sub'] }}</p>
            <div class="donut-widget" data-donut data-caption="{{ $d['caption'] }}" data-chart='@json($d['data'])'>
                <div class="donut-svg-holder">
                    <svg viewBox="0 0 200 200"></svg>
                    <div class="donut-center-label">
                        <span class="donut-center-value">0</span>
                        <span class="donut-center-caption">{{ $d['caption'] }}</span>
                    </div>
                </div>
                <div class="donut-legend"></div>
            </div>
        </div>
    @endforeach

    {{-- Kurva tren --}}
    <div class="dash-card trend-card reveal">
        <div class="trend-card-top">
            <div>
                <h2 class="dash-card-title">Kurva Tren Kegiatan</h2>
                <p class="dash-card-sub">{{ $trendSub }}</p>
            </div>
            <span class="trend-hint">
                <span class="material-symbols-outlined">info</span>
                Arahkan kursor ke titik untuk detail
            </span>
        </div>
        <div class="period-switcher" id="trendPeriodSwitcher" role="tablist">
            @foreach($periods as $key => $label)
                <button type="button" class="period-btn {{ $key === 'bulanan' ? 'is-active' : '' }}" data-period="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="trend-legend-row" id="trendLegend"></div>
        <div class="trend-chart-holder" data-trendchart data-active-period="bulanan" data-chart-sets='@json($trendDatasets)'></div>
    </div>

    {{-- Bar per periode + aktivitas terbaru --}}
    <div class="dash-card trend-card reveal">
        <div class="periode-recent-split">
            <div class="periode-recent-col periode-recent-col-chart">
                <div class="trend-card-top">
                    <div>
                        <h2 class="dash-card-title">Kegiatan per Periode</h2>
                        <p class="dash-card-sub">{{ $barSub }}</p>
                    </div>
                    <span class="trend-hint">
                        <span class="material-symbols-outlined">info</span>
                        Arahkan kursor ke batang untuk detail
                    </span>
                </div>
                <div class="period-switcher" id="periodSwitcher" role="tablist">
                    @foreach($periods as $key => $label)
                        <button type="button" class="period-btn {{ $key === 'bulanan' ? 'is-active' : '' }}" data-period="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </div>
                <div class="bar-chart-holder" data-barchart data-active-period="bulanan" data-chart-sets='@json($barDatasets)'></div>
            </div>

            <div class="periode-recent-divider"></div>

            <div class="periode-recent-col periode-recent-col-recent">
                <h2 class="dash-card-title">Aktivitas Terbaru</h2>
                <p class="dash-card-sub">{{ $recentSub }}</p>
                <div class="recent-list">
                    @forelse($recent as $item)
                        <div class="recent-item">
                            <div class="recent-item-icon">
                                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                            </div>
                            <div class="recent-item-text">
                                <span class="recent-item-title">{{ $item['title'] ?? '(tanpa judul)' }}</span>
                                <span class="recent-item-meta">{{ $item['menu'] }} &middot; @if($showWho){{ $item['who'] ?? '-' }} &middot; @endif{{ $item['date']?->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="recent-empty">Belum ada aktivitas yang tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== WIDGET KHUSUS (kalau jumlahnya ganjil, yang terakhir melebar) ===== --}}
@if(!empty($widgets))
<div class="dash-grid">
    @foreach($widgets as $w)
        @php
            $wide  = ($loop->last && $loop->count % 2 === 1) ? 'trend-card' : '';
            $delay = in_array($w['type'], ['attention', 'upcoming'], true) ? 80 : 0;
        @endphp

        @if($w['type'] === 'donut')
            <div class="dash-card donut-card reveal">
                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                <p class="dash-card-sub">{{ $w['sub'] }}</p>
                <div class="donut-widget" data-donut data-caption="{{ $w['caption'] }}" data-chart='@json($w['data'])'>
                    <div class="donut-svg-holder">
                        <svg viewBox="0 0 200 200"></svg>
                        <div class="donut-center-label">
                            <span class="donut-center-value">0</span>
                            <span class="donut-center-caption">{{ $w['caption'] }}</span>
                        </div>
                    </div>
                    <div class="donut-legend"></div>
                </div>
            </div>
        @else
            <div class="dash-card reveal {{ $wide }}" @if($delay) style="transition-delay:{{ $delay }}ms" @endif>
                <h2 class="dash-card-title">{{ $w['title'] }}</h2>
                <p class="dash-card-sub">{{ $w['sub'] }}</p>

                @switch($w['type'])
                    @case('bars')
                        <div class="quality-list">
                            @forelse($w['rows'] as $r)
                                <div class="quality-row">
                                    <span class="quality-label {{ ($w['wide'] ?? true) ? 'is-wide' : '' }}" title="{{ $r['label'] }}">{{ $r['label'] }}</span>
                                    <div class="quality-track">
                                        <div class="quality-fill {{ $r['fill'] ?? $w['fill'] }}" style="width: {{ $r['width'] }}%"></div>
                                    </div>
                                    <span class="quality-value">{{ $r['value'] }}</span>
                                </div>
                            @empty
                                <div class="recent-empty">{{ $w['empty'] ?? 'Belum ada data untuk ditampilkan.' }}</div>
                            @endforelse
                        </div>
                        @break

                    @case('attention')
                        <div class="complete-head">
                            <span class="complete-pct">{{ $w['pct'] }}%</span>
                            <span class="complete-note">{{ $w['note'] }}</span>
                        </div>
                        <div class="quality-track complete-track">
                            <div class="quality-fill is-ok" style="width: {{ $w['pct'] }}%"></div>
                        </div>
                        @if(!empty($w['rows']))
                            <p class="complete-sub">Perlu dilengkapi</p>
                            <div class="recent-list">
                                @foreach($w['rows'] as $p)
                                    <div class="recent-item">
                                        <div class="recent-item-icon"><span class="material-symbols-outlined">{{ $w['icon'] ?? 'error' }}</span></div>
                                        <div class="recent-item-text">
                                            <span class="recent-item-title">{{ $p['title'] }}</span>
                                            <span class="recent-item-meta">{{ $p['meta'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(isset($w['ok']))
                            <p class="complete-sub">{{ $w['ok'] }}</p>
                        @endif
                        @if(!empty($w['extra']))
                            <p class="complete-sub admin-extra-note">{{ $w['extra'] }}</p>
                        @endif
                        @break

                    @case('ranking')
                        <div class="recent-list">
                            @forelse($w['rows'] as $n => $r)
                                <div class="recent-item">
                                    <div class="recent-item-icon">
                                        <span class="material-symbols-outlined">{{ $n === 0 ? 'emoji_events' : 'military_tech' }}</span>
                                    </div>
                                    <div class="recent-item-text">
                                        <span class="recent-item-title">{{ $n + 1 }}. {{ $r['name'] }}</span>
                                        <span class="recent-item-meta">{{ $r['value'] }} {{ $w['unit'] }}</span>
                                    </div>
                                    <span class="status-chip is-live">{{ $r['value'] }}</span>
                                </div>
                            @empty
                                <div class="recent-empty">Belum ada data untuk diperingkat.</div>
                            @endforelse
                        </div>
                        @break

                    @case('upcoming')
                        <div class="recent-list">
                            @forelse($w['rows'] as $b)
                                <div class="recent-item">
                                    <div class="recent-item-icon"><span class="material-symbols-outlined">{{ $b['icon'] }}</span></div>
                                    <div class="recent-item-text">
                                        <span class="recent-item-title">{{ $b['title'] }}</span>
                                        <span class="recent-item-meta">{{ $b['who'] ?? '-' }} &middot; {{ $b['start']->translatedFormat('d M Y') }} &ndash; {{ $b['end']->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <span class="status-chip {{ $b['status'] === 'Berlangsung' ? 'is-live' : 'is-soon' }}">{{ $b['status'] }}</span>
                                </div>
                            @empty
                                <div class="recent-empty">Tidak ada kegiatan yang sedang berjalan.</div>
                            @endforelse
                        </div>
                        @break

                    @case('laporan')
                        <ul class="laporan-mini-list">
                            @foreach($w['items'] as $item)
                                <li><span class="material-symbols-outlined">check_circle</span>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ $w['url'] }}" class="admin-log-link">
                            <span class="material-symbols-outlined">assignment</span>
                            <span>Buka Laporan</span>
                        </a>
                        @break

                    @case('log')
                        <div class="log-mini-grid">
                            <div class="log-mini is-ok"><span class="log-mini-value">{{ $w['success'] }}</span><span class="log-mini-label">Berhasil</span></div>
                            <div class="log-mini is-warn"><span class="log-mini-value">{{ $w['failed'] }}</span><span class="log-mini-label">Gagal</span></div>
                            <div class="log-mini is-bad"><span class="log-mini-value">{{ $w['locked'] }}</span><span class="log-mini-label">Terkunci</span></div>
                        </div>
                        <a href="{{ $w['url'] }}" class="admin-log-link">
                            <span class="material-symbols-outlined">history</span>
                            <span>Buka Log Aktivitas</span>
                        </a>
                        @break
                @endswitch
            </div>
        @endif
    @endforeach
</div>
@endif
