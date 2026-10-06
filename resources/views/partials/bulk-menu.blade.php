{{--
    Tombol titik tiga (⋮) dengan dropdown: Unduh Template, Upload, Download, Cetak Laporan.
    Dipasang di sebelah tombol Tambah pada .title-bar. Hanya admin & superadmin.

    Parameter (semua opsional; null/kosong = item disembunyikan):
      $bulkLabel     string  nama menu, mis. "Kerja Sama"
      $bulkTemplate  string  URL unduh template Excel
      $bulkImport    string  URL unggah Excel/CSV
      $bulkExport    string  URL unduh data Excel
      $bulkPrint     string  URL halaman cetak laporan (dibuka di tab baru)
    JS-nya: public/js/bulk-import.js (modal: partials/bulk-import-modal.blade.php)
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
            <a role="menuitem" class="bulk-menu-item" href="{{ $bulkPrint }}" target="_blank" rel="noopener">
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">print</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Cetak Laporan</span>
                    <span class="bulk-menu-hint">Rekap siap cetak atau simpan sebagai PDF</span>
                </span>
            </a>
        @endif
    </div>
</div>
