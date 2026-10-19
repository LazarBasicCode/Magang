{{-- Modal detail (progres verifikasi + data + Edit/Hapus). Diisi oleh public/js/mahasiswa-view.js --}}
<div class="modal-backdrop" id="mvDetailBackdrop"></div>
<div class="modal-card mv-detail" id="mvDetailModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-drag-handle">
        <div>
            <h3 class="modal-title">Detail</h3>
            <p class="modal-subtitle">Pantau status verifikasi pengajuanmu</p>
        </div>
        <button type="button" class="modal-close-btn" id="mvDetailClose" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <div class="modal-body">
        <div id="mvDetailBody" style="display:flex;flex-direction:column;gap:14px"></div>
        <div class="modal-footer">
            <button type="button" class="mv-btn-danger" id="mvDetailDelete">
                <span class="material-symbols-outlined">delete</span><span>Hapus</span>
            </button>
            <button type="button" class="btn-ghost" id="mvDetailCancel">Tutup</button>
            <button type="button" class="btn-apply" id="mvDetailEdit">
                <span class="material-symbols-outlined">edit</span><span id="mvDetailEditLabel">Edit</span>
            </button>
        </div>
    </div>
</div>
