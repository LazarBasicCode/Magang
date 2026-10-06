{{--
    Tombol titik tiga (⋮) dengan dropdown: Unduh Template, Upload, Download, Cetak Laporan.
    Dipasang di sebelah tombol Tambah pada .title-bar. Hanya admin & superadmin.

    Parameter (semua opsional; null/kosong = item disembunyikan):
      $bulkLabel     string  nama menu, mis. "Kerja Sama"
      $bulkTemplate  string  URL unduh template Excel
      $bulkImport    string  URL unggah Excel/CSV
      $bulkExport    string  URL unduh data Excel
      $bulkPrint     string  URL isi laporan (route cetak.show) — dimuat ke MODAL "Cetak Laporan", bukan tab baru
    JS-nya: public/js/bulk-import.js (modal upload: partials/bulk-import-modal.blade.php)
    Modal cetak ada di bawah ini (dipindah ke <body> oleh JS). Isinya: resources/views/cetak-laporan.blade.php
--}}
<div class="bulk-menu" id="bulkMenu">
    <button type="button" class="bulk-menu-btn" id="bulkMenuBtn" aria-haspopup="menu" aria-expanded="false"
        aria-label="Menu lainnya {{ $bulkLabel }}" title="Data massal & cetak laporan">
        <span class="material-symbols-outlined">more_vert</span>
    </button>

    <div class="bulk-menu-panel" role="menu" aria-labelledby="bulkMenuBtn">
        @if(!empty($bulkTemplate))
            <a role="menuitem" class="bulk-menu-item" href="{{ $bulkTemplate }}" download>
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">description</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Unduh Template</span>
                    <span class="bulk-menu-hint">File Excel dengan kotak, pilihan, & petunjuk</span>
                </span>
            </a>
        @endif

        @if(!empty($bulkImport))
            <button type="button" role="menuitem" class="bulk-menu-item" id="bulkOpenImport">
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">upload_file</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Upload</span>
                    <span class="bulk-menu-hint">Tambah data baru / edit data yang ada</span>
                </span>
            </button>
        @endif

        @if(!empty($bulkExport))
            <a role="menuitem" class="bulk-menu-item" href="{{ $bulkExport }}" download>
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">download</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Download</span>
                    <span class="bulk-menu-hint">Excel seluruh data, bisa diedit lalu di-upload lagi</span>
                </span>
            </a>
        @endif

        @if(!empty($bulkPrint))
            <div class="bulk-menu-sep" role="separator"></div>
            <button type="button" role="menuitem" class="bulk-menu-item" id="bulkOpenPrint" data-print-url="{{ $bulkPrint }}">
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">print</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Cetak Laporan</span>
                    <span class="bulk-menu-hint">Rekap siap cetak atau simpan sebagai PDF</span>
                </span>
            </button>
        @endif
    </div>
</div>

@if(!empty($bulkPrint))
    {{-- Modal Cetak Laporan (gaya .modal-backdrop / .modal-card yang sama dengan modal lain, tapi lebih lebar) --}}
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}?v={{ @filemtime(public_path('css/laporan.css')) }}">
    <div class="modal-backdrop" id="printBackdrop"></div>
    <div class="modal-card" id="printModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="printTitle">
        <div class="modal-drag-handle" id="printDragHandle">
            <div>
                <h3 class="modal-title" id="printTitle">Cetak Laporan &middot; {{ $bulkLabel }}</h3>
                <p class="modal-subtitle">Semua data menu ini siap cetak. Untuk PDF, pilih "Simpan sebagai PDF" di dialog cetak.</p>
            </div>
            <button type="button" class="modal-close-btn" id="printCloseBtn" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="modal-body" id="printBody"></div>

        <div class="pm-footer">
            <button type="button" class="btn-ghost" id="printCancelBtn">Tutup</button>
            <button type="button" class="btn-apply" id="printNowBtn" disabled>
                <span class="material-symbols-outlined">print</span>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>
@endif
