{{--
    Modal "Upload Data Massal" (pakai gaya .modal-backdrop / .modal-card yang sama dengan modal lain).
    Parameter: $bulkLabel (string), $bulkImport (URL POST unggah), $bulkTemplate (URL template, boleh null)
    Letakkan di luar .page-wrap (bareng modal lain). JS: public/js/bulk-import.js
--}}
<div class="modal-backdrop" id="bulkBackdrop"></div>
<div class="modal-card" id="bulkModal" role="dialog" aria-modal="true" aria-hidden="true"
    data-import-url="{{ $bulkImport }}">
    <div class="modal-drag-handle" id="bulkDragHandle">
        <div>
            <h3 class="modal-title">Upload Data {{ $bulkLabel }}</h3>
            <p class="modal-subtitle">Tambah data baru atau edit data yang sudah ada lewat file CSV</p>
        </div>
        <button type="button" class="modal-close-btn" id="bulkCloseBtn" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <form id="bulkForm" class="modal-body" novalidate>
        <ul class="bulk-rules">
            <li><strong>Baris tanpa <code>id</code></strong> akan ditambahkan sebagai data baru.</li>
            <li><strong>Baris dengan <code>id</code></strong> (dari hasil Download) akan mengedit data itu.</li>
            <li>Semua baris dicek dulu. Kalau ada yang salah, <strong>tidak ada data yang disimpan</strong>.</li>
            @if(!empty($bulkTemplate))
                <li>Belum punya file? <a href="{{ $bulkTemplate }}" download>Unduh template</a>.</li>
            @endif
        </ul>

        <label class="bulk-drop" id="bulkDrop" for="bulkFileInput">
            <span class="material-symbols-outlined bulk-drop-icon">upload_file</span>
            <span class="bulk-drop-title" id="bulkFileName">Pilih file CSV atau seret ke sini</span>
            <span class="bulk-drop-sub" id="bulkFileMeta">Maksimal 2 MB &middot; 1000 baris</span>
            <input type="file" id="bulkFileInput" accept=".csv,.txt,text/csv" hidden>
        </label>

        <div class="bulk-result" id="bulkResult" hidden></div>

        <div class="modal-footer">
            <button type="button" class="btn-ghost" id="bulkCancelBtn">Batal</button>
            <button type="submit" class="btn-apply" id="bulkSubmitBtn" disabled>
                <span class="material-symbols-outlined">upload</span>
                <span>Unggah</span>
            </button>
        </div>
    </form>
</div>
