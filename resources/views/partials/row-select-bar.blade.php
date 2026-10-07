{{--
    Bar aksi "data terpilih" untuk tabel yang bisa dicentang (muncul otomatis saat ada baris dicentang).
    Isinya: jumlah terpilih + Export Excel, Cetak PDF, Hapus, Batal pilih. Logikanya: public/js/row-select.js

    CARA PASANG di halaman baru (3 langkah):
      1. Pada <table>, tambahkan:
           data-selectable="slug-menu"
           data-select-export="{{ route('pilihan.export', 'slug-menu') }}"
           data-select-print="{{ route('pilihan.cetak', 'slug-menu') }}"
           data-select-delete="{{ route('pilihan.hapus', 'slug-menu') }}"   (opsional; hanya untuk akses penuh)
         (slug-menu harus terdaftar di BulkSelectionController::MENUS)
      2. Taruh @include('partials.row-select-bar') tepat di atas pembungkus tabel (di dalam kartu tabel).
      3. Muat script setelah js/script.js, js/bulk-import.js, js/delete-confirm.js dan js/toast.js:
           <script src="{{ asset('js/row-select.js') }}?v={{ @filemtime(public_path('js/row-select.js')) }}"></script>
    Kolom checkbox di header & tiap baris disisipkan otomatis oleh JS (termasuk baris yang ditambah lewat fetch),
    jadi markup <th>/<tr> yang sudah ada TIDAK perlu diubah. Tampilkan hanya untuk admin/superadmin ($showBulk).
--}}
<link rel="stylesheet" href="{{ asset('css/row-select.css') }}?v={{ @filemtime(public_path('css/row-select.css')) }}">

<div class="sel-bar" data-sel-bar role="region" aria-label="Aksi data terpilih" aria-hidden="true">
    <div class="sel-bar-info" aria-live="polite">
        <strong data-sel-count>0</strong>
        <span>data dipilih</span>
        <span class="sel-bar-hint" data-sel-hint hidden></span>
    </div>
    <div class="sel-bar-actions">
        <button type="button" class="sel-btn" data-sel-action="export" title="Unduh Excel hanya data yang dipilih">
            <span class="material-symbols-outlined">table_view</span>
            <span>Export Excel</span>
        </button>
        <button type="button" class="sel-btn" data-sel-action="print" title="Cetak laporan data yang dipilih / simpan sebagai PDF">
            <span class="material-symbols-outlined">picture_as_pdf</span>
            <span>Cetak PDF</span>
        </button>
        {{-- Hapus: hanya aktif bila <table> punya data-select-delete (akses penuh). Konfirmasi 2 langkah: DeleteConfirm. --}}
        <button type="button" class="sel-btn is-danger" data-sel-action="delete" title="Hapus permanen data yang dipilih">
            <span class="material-symbols-outlined">delete</span>
            <span>Hapus</span>
        </button>
        <button type="button" class="sel-btn is-ghost" data-sel-action="clear" title="Kosongkan pilihan">
            <span class="material-symbols-outlined">close</span>
            <span>Batal pilih</span>
        </button>
    </div>
</div>
