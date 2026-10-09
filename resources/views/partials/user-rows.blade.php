{{-- Baris tabel Data Master Pengguna. Dipakai halaman utama & respons AJAX (UserController::index). --}}
@foreach($users as $item)
@php
$initials = collect(explode(' ', $item->name))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
$colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
$avatarColor = $colors[$item->id % count($colors)];
$roleBadge = [
'superadmin' => 'badge-danger',
'admin' => 'badge-warning',
'dosen' => 'badge-info',
'mahasiswa' => 'badge-success',
][$item->role] ?? 'badge-neutral';
$roleLabel = ucfirst($item->role);
$identifier = $item->role === 'mahasiswa'
? optional($item->mahasiswa)->nim
: ($item->role === 'dosen' ? optional($item->dosen)->nidn : $item->nim_nidn);
// Profil sesuai role: sumber angkatan & status (admin/superadmin tidak punya).
$profile = $item->role === 'mahasiswa' ? $item->mahasiswa : ($item->role === 'dosen' ? $item->dosen : null);
$angkatan = $item->role === 'mahasiswa' ? optional($item->mahasiswa)->angkatan : null;
@endphp
<tr data-id="{{ $item->id }}" data-role="{{ $item->role }}">
    <td><span class="nim-code">USR-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</span></td>
    <td>
        <div class="student-cell">
            <div class="avatar {{ $avatarColor }}">{{ $initials }}</div>
            <div class="student-name">
                <span class="name">{{ $item->name }}</span>
            </div>
        </div>
    </td>
    <td class="center col-role" data-v="admin"><span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
    <td class="center col-identifier">
        <span class="plain-text">{{ $identifier ?? '—' }}</span>
    </td>
    <td class="center col-angkatan" data-v="mahasiswa">
        <span class="plain-text">{{ $angkatan ?? '—' }}</span>
    </td>
    <td class="center col-status" data-v="mahasiswa dosen">
        @if($profile && $profile->status)
            <span class="badge {{ $profile->statusBadge() }}">{{ $profile->statusLabel() }}</span>
        @else
            <span class="plain-text">—</span>
        @endif
    </td>
    <td class="center col-email">
        <span class="plain-text">{{ $item->email ?? '—' }}</span>
    </td>
    <td class="center">
        <div class="row-actions">
            <button type="button" title="Edit" class="row-action-btn btn-edit-row"
                data-id="{{ $item->id }}"
                data-name="{{ $item->name }}"
                data-role="{{ $item->role }}"
                data-identifier="{{ $identifier ?? '' }}"
                data-email="{{ $item->email ?? '' }}"
                data-angkatan="{{ $angkatan ?? '' }}"
                data-status="{{ optional($profile)->status ?? '' }}">
                <span class="material-symbols-outlined">edit</span>
            </button>
            <button type="button" title="Hapus" class="row-action-btn is-secondary btn-delete-row"
                data-id="{{ $item->id }}">
                <span class="material-symbols-outlined">delete</span>
            </button>
        </div>
    </td>
</tr>
@endforeach
