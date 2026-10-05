{{--
    Tombol "+" dengan dropdown Data Massal: Unduh Template, Upload, Download.
    Dipasang di sebelah tombol Tambah pada .title-bar. Hanya admin & superadmin.

    Parameter:
      $bulkLabel     string  nama menu, mis. "Kerja Sama"
      $bulkTemplate  string  URL unduh template   (null = sembunyikan item)
      $bulkImport    string  URL unggah CSV        (null = sembunyikan item)
      $bulkExport    string  URL unduh data        (null = sembunyikan item)
    JS-nya: public/js/bulk-import.js (modal: partials/bulk-import-modal.blade.php)
--}}
<div class="bulk-menu" id="bulkMenu">
    <button type="button" class="bulk-menu-btn" id="bulkMenuBtn" aria-haspopup="menu" aria-expanded="false"
        aria-label="Menu data massal {{ $bulkLabel }}" title="Tambah / edit banyak data sekaligus">
        <span class="material-symbols-outlined">add</span>
    </button>

    <div class="bulk-menu-panel" role="menu" aria-labelledby="bulkMenuBtn">
        <div class="bulk-menu-head">
            <span class="bulk-menu-title">Data Massal</span>
            <span class="bulk-menu-sub">Tambah &amp; edit banyak data {{ $bulkLabel }} sekaligus lewat file CSV</span>
        </div>

        @if($bulkTemplate)
            <a role="menuitem" class="bulk-menu-item" href="{{ $bulkTemplate }}" download>
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">description</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Unduh Template</span>
                    <span class="bulk-menu-hint">File CSV kosong + petunjuk pengisian</span>
                </span>
            </a>
        @endif

        @if($bulkImport)
            <button type="button" role="menuitem" class="bulk-menu-item" id="bulkOpenImport">
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">upload_file</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Upload</span>
                    <span class="bulk-menu-hint">Tambah data baru / edit data yang ada</span>
                </span>
            </button>
        @endif

        @if($bulkExport)
            <a role="menuitem" class="bulk-menu-item" href="{{ $bulkExport }}" download>
                <span class="bulk-menu-icon"><span class="material-symbols-outlined">download</span></span>
                <span class="bulk-menu-text">
                    <span class="bulk-menu-label">Download</span>
                    <span class="bulk-menu-hint">Seluruh data, bisa diedit lalu di-upload lagi</span>
                </span>
            </a>
        @endif
    </div>
</div>
