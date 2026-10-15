/**
 * Chip status persetujuan untuk baris tabel yang dibangun lewat JS (setelah tambah / edit).
 * Pasangan server-side-nya: resources/views/partials/approval-chip.blade.php
 * Pemakaian: ${window.apprChip ? window.apprChip(item) : ''}  — item = hasil format() controller.
 */
window.apprChip = function (item) {
    const status = item && item.approval_status;
    if (!status || status === 'disetujui') return '';

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const icons = { menunggu_dosen: 'hourglass_top', menunggu_superadmin: 'pending_actions', ditolak: 'cancel' };

    let html = '<span class="appr-chip is-' + esc(status) + '" title="' + esc(item.approval_note) + '">'
        + '<span class="material-symbols-outlined">' + (icons[status] || 'info') + '</span>' + esc(item.approval_label) + '</span>';
    if (status === 'ditolak' && item.approval_note) {
        html += '<span class="appr-note">Alasan: ' + esc(item.approval_note) + '</span>';
    }

    return html;
};
