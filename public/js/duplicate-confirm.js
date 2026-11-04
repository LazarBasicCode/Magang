(function () {
    'use strict';

    const backdrop = document.getElementById('duplicateConfirmBackdrop');
    const card = document.getElementById('duplicateConfirmModal');
    if (!backdrop || !card || !window.SIDA?.modal) return;

    const title = document.getElementById('duplicateConfirmTitle');
    const subtitle = document.getElementById('duplicateConfirmSubtitle');
    const message = document.getElementById('duplicateConfirmMessage');
    const list = document.getElementById('duplicateConfirmList');
    const more = document.getElementById('duplicateConfirmMore');
    const cancel = document.getElementById('duplicateConfirmCancel');
    const continueButton = document.getElementById('duplicateConfirmContinue');
    let resolvePending = null;
    let hiddenModals = [];

    /**
     * Modal lain yang sedang terbuka (mis. form Tambah/Edit) disembunyikan selama konfirmasi tampil,
     * supaya tidak ada dua modal bertumpuk. Hanya class `is-active` yang dilepas — isi form dan posisi
     * geser modal tetap utuh — lalu dikembalikan setelah pengguna memilih.
     */
    function hideOtherModals() {
        hiddenModals = [...document.querySelectorAll('.modal-backdrop.is-active, .modal-card.is-active')]
            .filter((el) => el !== backdrop && el !== card);
        hiddenModals.forEach((el) => {
            el.classList.remove('is-active');
            if (el.classList.contains('modal-card')) el.setAttribute('aria-hidden', 'true');
        });
    }

    function restoreOtherModals() {
        hiddenModals.forEach((el) => {
            el.classList.add('is-active');
            if (el.classList.contains('modal-card')) el.setAttribute('aria-hidden', 'false');
        });
        hiddenModals = [];
    }

    const modal = SIDA.modal.attach({
        backdrop,
        card,
        closeBtn: document.getElementById('duplicateConfirmClose'),
        cancelBtn: cancel,
        dragHandle: document.getElementById('duplicateConfirmDrag'),
    });

    function finish(answer) {
        if (!resolvePending) return;
        const resolve = resolvePending;
        resolvePending = null;
        modal.close();
        restoreOtherModals();
        resolve(answer);
    }

    cancel.addEventListener('click', () => finish(false));
    document.getElementById('duplicateConfirmClose').addEventListener('click', () => finish(false));
    backdrop.addEventListener('click', (event) => {
        if (event.target === backdrop) finish(false);
    });
    continueButton.addEventListener('click', () => finish(true));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && resolvePending) {
            event.stopImmediatePropagation();
            finish(false);
        }
    }, true);

    window.DuplicateConfirm = {
        ask({ duplicates = [], moreCount = 0, context = 'data' } = {}) {
            if (resolvePending) finish(false);

            title.textContent = context === 'akun' ? 'Nama akun yang sama ditemukan' : 'Data yang sama ditemukan';
            subtitle.textContent = context === 'akun'
                ? 'Nama ini sudah digunakan oleh akun dengan role yang sama.'
                : 'Data dengan rincian yang sama sudah pernah disimpan.';
            message.textContent = context === 'akun'
                ? 'Tetap buat atau simpan perubahan akun ini?'
                : 'Tetap simpan data ini?';
            continueButton.textContent = context === 'akun' ? 'Tetap simpan akun' : 'Tetap simpan';

            list.replaceChildren();
            duplicates.slice(0, 10).forEach((duplicate) => {
                const item = document.createElement('li');
                const row = duplicate.row ? `Baris ${duplicate.row}: ` : '';
                const id = duplicate.id ? ` (ID #${duplicate.id})` : '';
                item.textContent = `${row}${duplicate.name || 'Data yang sama'}${id}`;
                list.appendChild(item);
            });
            const hiddenCount = Math.max(0, duplicates.length - 10) + moreCount;
            more.textContent = hiddenCount ? `Dan ${hiddenCount} data sama lainnya.` : '';
            more.hidden = !hiddenCount;

            hideOtherModals();
            modal.open();
            return new Promise((resolve) => {
                resolvePending = resolve;
            });
        },
    };
})();
