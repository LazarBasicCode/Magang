{{--
    Modal "Update Massal" untuk data terpilih (centang baris). Dipakai ulang di banyak halaman.
    Hanya kolom yang DIISI yang diubah; kolom yang dikosongkan tetap memakai nilai lama tiap baris.

    Parameter:
      $updateLabel  (string)  nama menu, mis. 'Kemahasiswaan'
      $updateFields (array)   daftar kolom yang boleh diubah massal. Tiap elemen:
          'name'        nama field di request & di payload baris (mis. 'jenis')
          'label'       label di form
          'type'        'text' | 'select'
          'options'     (select) [value => label]
          'placeholder' (text) opsional
          'col'         indeks kolom di tabel (mulai 0, TIDAK menghitung kolom checkbox)
          'target'      selector di dalam sel tsb yang diisi teks baru (mis. '.plain-text')
          'row'         (opsional) field dengan nilai 'row' sama ditaruh sejajar 1 baris
    Kolom khas per baris (NIM, nama, tahun, bukti, dsb.) cukup tidak dimasukkan ke $updateFields.

    CARA PASANG di halaman lain (4 langkah):
      1. Controller menu: tambah updateMany() yang memanggil HandlesBulkData::bulkUpdateSelected()
         (contoh: KemahasiswaanController::updateMany) dan daftarkan menunya di BulkSelectionController::MENUS.
      2. <table data-selectable=...> tambahkan  data-select-update="{{ route('pilihan.ubah', 'slug-menu') }}"
         (hanya untuk akses penuh, seperti data-select-delete).
      3. Letakkan @include('partials.bulk-update-modal', [...]) di luar .page-wrap (bareng modal lain).
      4. Muat <script src="js/bulk-update.js"> setelah js/script.js dan js/toast.js.
    Logika: public/js/bulk-update.js · gaya: public/css/bulk-update.css
--}}
<link rel="stylesheet" href="{{ asset('css/bulk-update.css') }}?v={{ @filemtime(public_path('css/bulk-update.css')) }}">

@php
    $buGroups = collect($updateFields)->values()->groupBy(fn ($f, $i) => $f['row'] ?? 'f' . $i);
    $buConfig = collect($updateFields)->map(fn ($f) => [
        'name'   => $f['name'],
        'col'    => $f['col'] ?? null,
        'target' => $f['target'] ?? null,
        'type'   => $f['type'] ?? 'text',
    ])->values();
@endphp

<div class="modal-backdrop" id="bulkUpdateBackdrop"></div>
<div class="modal-card bulk-update-card" id="bulkUpdateModal" role="dialog" aria-modal="true" aria-hidden="true"
    data-fields='@json($buConfig)'>
    <div class="modal-drag-handle" id="bulkUpdateDragHandle">
        <div>
            <h3 class="modal-title">Update Massal {{ $updateLabel }}</h3>
            <p class="modal-subtitle"><strong id="bulkUpdateCount">0</strong> data dipilih &middot; kolom kosong tidak diubah</p>
        </div>
        <button type="button" class="modal-close-btn" id="bulkUpdateCloseBtn" aria-label="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <form id="bulkUpdateForm" class="modal-body" novalidate>
        @foreach($buGroups as $group)
            @if($group->count() > 1)<div class="field-row">@endif
            @foreach($group as $f)
                @php $fid = 'bu-' . $f['name']; @endphp
                <div class="field">
                    <label class="field-label" for="{{ $fid }}">{{ $f['label'] }}</label>
                    @if(($f['type'] ?? 'text') === 'select')
                        <div class="dropdown" data-dropdown>
                            <input type="hidden" id="{{ $fid }}" name="{{ $f['name'] }}" value="">
                            <button type="button" class="dropdown-trigger">
                                <span class="dropdown-value">&mdash; Tidak diubah &mdash;</span>
                                <span class="material-symbols-outlined caret">expand_more</span>
                            </button>
                            <div class="dropdown-panel">
                                <button type="button" class="dropdown-option is-selected" data-value="">&mdash; Tidak diubah &mdash;</button>
                                @foreach($f['options'] as $value => $label)
                                    <button type="button" class="dropdown-option" data-value="{{ $value }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="field-control">
                            <input id="{{ $fid }}" name="{{ $f['name'] }}" type="text" autocomplete="off"
                                placeholder="{{ $f['placeholder'] ?? 'Kosongkan = tidak diubah' }}">
                        </div>
                    @endif
                </div>
            @endforeach
            @if($group->count() > 1)</div>@endif
        @endforeach

        <div class="modal-error" id="bulkUpdateError" hidden></div>

        <div class="modal-footer">
            <button type="button" class="btn-ghost" id="bulkUpdateCancelBtn">Batal</button>
            <button type="submit" class="btn-apply" id="bulkUpdateSubmitBtn">
                <span class="material-symbols-outlined">edit_note</span>
                <span>Update</span>
            </button>
        </div>
    </form>
</div>
