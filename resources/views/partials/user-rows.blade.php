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
                <div class="name-line">
                    <span class="name">{{ $item->name }}</span>
                    @unless($item->has_logged_in ?? true)
                        {{-- Akun baru yang belum pernah login. Animasi: public/css/never-login.css, dipicu public/js/never-login.js (hanya yang
                             terlihat di layar, munculnya berurutan mengikuti arah scroll lewat --nl-order). --}}
                        <span class="never-login-badge" role="img" tabindex="0" aria-label="Belum pernah login">
                            <span class="nl-face" aria-hidden="true">
                                <svg class="nl-icon" viewBox="0 0 24 24" focusable="false">
                                    <g class="nl-shackle">
                                        <path d="M7.6 11V8.2a4.4 4.4 0 0 1 8.8 0V11" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"/>
                                    </g>
                                    <g class="nl-body">
                                        <rect x="4.6" y="10.6" width="14.8" height="10.4" rx="3.1" fill="currentColor"/>
                                        <circle class="nl-hole" cx="12" cy="14.9" r="1.75"/>
                                        <rect class="nl-hole" x="11.15" y="15.4" width="1.7" height="3.1" rx=".85"/>
                                    </g>
                                </svg>
                            </span>
                            <span class="nl-tip" role="tooltip">
                                <strong>Belum pernah login</strong>
                                <span>Akun baru, menunggu login pertama</span>
                            </span>
                        </span>
                    @endunless
                </div>
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
    <td data-v="mahasiswa">
        <span class="plain-text">{{ $item->role === 'mahasiswa' ? (optional(optional(optional($item->mahasiswa)->dosenWali)->user)->name ?? '—') : '—' }}</span>
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
                data-dosen_wali_id="{{ $item->role === 'mahasiswa' ? (optional($item->mahasiswa)->dosen_wali_id ?? '') : '' }}"
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
