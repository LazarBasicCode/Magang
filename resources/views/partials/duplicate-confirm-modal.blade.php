<div class="modal-backdrop" id="duplicateConfirmBackdrop"></div>
<div class="modal-card" id="duplicateConfirmModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="duplicateConfirmTitle">
    <div class="modal-drag-handle" id="duplicateConfirmDrag">
        <div>
            <h3 class="modal-title" id="duplicateConfirmTitle">Data yang sama ditemukan</h3>
            <p class="modal-subtitle" id="duplicateConfirmSubtitle">Periksa data berikut sebelum melanjutkan.</p>
        </div>
        <button type="button" class="modal-close-btn" id="duplicateConfirmClose" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <div class="modal-body">
        <p id="duplicateConfirmMessage">Data berikut sudah ada. Apakah Anda tetap ingin menyimpan?</p>
        <ul id="duplicateConfirmList"></ul>
        <p id="duplicateConfirmMore" hidden></p>
        <div class="modal-footer">
            <button type="button" class="btn-ghost" id="duplicateConfirmCancel">Batal</button>
            <button type="button" class="btn-apply" id="duplicateConfirmContinue">Tetap simpan</button>
        </div>
    </div>
</div>
