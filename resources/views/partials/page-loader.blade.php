{{--
    Loading screen roket. Hanya untuk transisi login -> dashboard.
    Parameter: $autoHide (true di dashboard: tampil HANYA bila datang dari login, lalu hilang setelah selesai dimuat).
    Logika: public/js/page-loader.js
--}}
<div class="page-loader" id="pageLoader" @if(!empty($autoHide)) data-auto-hide @endif role="status" aria-live="polite" aria-label="Memuat">
    <div class="pl-loader">
        <span><span></span><span></span><span></span><span></span></span>
        <div class="pl-base">
            <span></span>
            <div class="pl-face"></div>
        </div>
    </div>
    <div class="pl-longfazers">
        <span></span><span></span><span></span><span></span>
    </div>
</div>
<script src="{{ asset('js/page-loader.js') }}?v={{ @filemtime(public_path('js/page-loader.js')) }}"></script>
