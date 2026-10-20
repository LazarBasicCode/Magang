{{--
    Chip status persetujuan di bawah judul pada tabel menu (Kemahasiswaan, LPPM Mahasiswa, Rekognisi, Kerja Sama).
    Hanya tampil untuk yang BELUM disetujui — baris tanpa chip berarti sudah disetujui.
    Parameter: $item (model dengan trait HasApproval)
--}}
@php
    $apprStatus = $item->approval_status ?? 'disetujui';
    $apprIcons = ['diajukan' => 'send', 'menunggu_prodi' => 'hourglass_top', 'ditolak' => 'cancel'];
@endphp
@if($apprStatus !== 'disetujui')
    <span class="appr-chip is-{{ $apprStatus }}" title="{{ $item->approval_note }}"><span class="material-symbols-outlined">{{ $apprIcons[$apprStatus] ?? 'info' }}</span>{{ $item->approvalLabel() }}</span>
    @if($apprStatus === 'ditolak' && $item->approval_note)
        <span class="appr-note">Alasan: {{ $item->approval_note }}</span>
    @endif
@endif
