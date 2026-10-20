{{--
    Isi halaman tampilan MAHASISWA: judul + tombol tambah, kartu status (sekaligus filter),
    pencarian, dan daftar. Logika di public/js/mahasiswa-view.js.

    Parameter: $title, $subtitle, $addLabel, $searchPlaceholder, $summary, $canAdd (bool)
--}}
<main class="app-main">
    <div class="page-wrap">
        <div class="title-bar">
            <div>
                <h1 class="page-title">{{ $title }}</h1>
                <p class="page-subtitle">{{ $subtitle }}</p>
            </div>
            <div class="title-actions">
                <button type="button" class="btn-primary" id="mvAdd" {{ $canAdd ? '' : 'disabled' }}>
                    <span class="material-symbols-outlined">add</span>
                    <span>{{ $addLabel }}</span>
                </button>
            </div>
        </div>

        @unless($canAdd)
        <div class="modal-error">Profil mahasiswa belum terhubung ke akunmu. Hubungi admin prodi.</div>
        @endunless

        {{-- Kartu ringkasan = filter status --}}
        <div class="mv-tiles">
            <button type="button" class="mv-tile is-active" data-status="semua">
                <span class="mv-tile-icon"><span class="material-symbols-outlined">folder_open</span></span>
                <span><span class="mv-tile-num" data-count="total">{{ $summary['total'] }}</span><span class="mv-tile-label">Semua</span></span>
            </button>
            <button type="button" class="mv-tile is-ok" data-status="disetujui">
                <span class="mv-tile-icon"><span class="material-symbols-outlined">task_alt</span></span>
                <span><span class="mv-tile-num" data-count="disetujui">{{ $summary['disetujui'] }}</span><span class="mv-tile-label">Disetujui</span></span>
            </button>
            <button type="button" class="mv-tile is-wait" data-status="diproses">
                <span class="mv-tile-icon"><span class="material-symbols-outlined">hourglass_top</span></span>
                <span><span class="mv-tile-num" data-count="diproses">{{ $summary['diproses'] }}</span><span class="mv-tile-label">Sedang diproses</span></span>
            </button>
            <button type="button" class="mv-tile is-bad" data-status="ditolak">
                <span class="mv-tile-icon"><span class="material-symbols-outlined">error</span></span>
                <span><span class="mv-tile-num" data-count="ditolak">{{ $summary['ditolak'] }}</span><span class="mv-tile-label">Perlu diperbaiki</span></span>
            </button>
        </div>

        <div class="mv-search">
            <span class="material-symbols-outlined">search</span>
            <input id="mvSearch" type="search" placeholder="{{ $searchPlaceholder }}" autocomplete="off" />
        </div>

        <div class="table-card">
            <div class="table-scroll" id="mvTableWrap">
                <table class="data-table mv-table">
                    <thead>
                        <tr>
                            <th>{{ $listTitle }}</th>
                            <th>Status Verifikasi</th>
                            <th class="center" style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="mvList"></tbody>
                </table>
            </div>

            <div class="mv-empty" id="mvEmpty" hidden>
                <span class="material-symbols-outlined">inbox</span>
                <strong id="mvEmptyTitle"></strong>
                <p id="mvEmptyDesc"></p>
            </div>

            <div class="table-footer">
                <div class="footer-summary" id="mvFooter"></div>
                <nav class="pager" id="mvPager" aria-label="Navigasi halaman" hidden></nav>
            </div>
        </div>
    </div>
</main>