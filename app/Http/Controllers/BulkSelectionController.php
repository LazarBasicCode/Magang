<?php

namespace App\Http\Controllers;

use App\Support\SelectedIds;
use Illuminate\Http\Request;

/**
 * Aksi untuk DATA TERPILIH (centang baris pada tabel): Export Excel, Cetak PDF & Hapus.
 * Satu controller untuk semua menu — logika export/cetak tiap menu TIDAK diduplikasi,
 * hanya didelegasikan ke controller menu itu / CetakLaporanController dengan filter id.
 *
 * Menambah menu baru cukup:
 *  1. satu baris di MENUS di bawah (slug => controller menu + slug laporan di App\Support\PrintReports),
 *  2. pasang di halamannya: atribut data-selectable pada <table>, @include('partials.row-select-bar'),
 *     dan <script src="js/row-select.js"> (lihat komentar di public/js/row-select.js).
 *
 * Hapus massal: controller menu harus punya destroyMany(Request) yang memanggil
 * HandlesBulkData::bulkDestroySelected() (lihat KemahasiswaanController).
 * Update massal: controller menu harus punya updateMany(Request) yang memanggil
 * HandlesBulkData::bulkUpdateSelected() (lihat KemahasiswaanController).
 *
 * Hak akses TIDAK diatur di sini: export(), show() & destroyMany() milik controller sasaran sudah memeriksa
 * peran admin/superadmin + akses menu (Download/Cetak: readonly ke atas; Hapus: akses penuh).
 */
class BulkSelectionController extends Controller
{
    /** @var array<string,array{controller:class-string,print:string}> */
    private const MENUS = [
        'kemahasiswaan'   => ['controller' => KemahasiswaanController::class, 'print' => 'kemahasiswaan'],
        'lppm-mahasiswa'  => ['controller' => LppmMahasiswaController::class, 'print' => 'lppm-mahasiswa'],
        'lppm-dosen'      => ['controller' => LppmDosenController::class, 'print' => 'lppm-dosen'],
        'rekognisi'       => ['controller' => LppmRekognisiController::class, 'print' => 'rekognisi'],
        'kerja-sama'      => ['controller' => KerjaSamaController::class, 'print' => 'kerja-sama'],
    ];

    /** Export Excel hanya baris terpilih (format sama dengan tombol Download, bisa diedit & di-upload lagi). */
    public function export(Request $request, string $menu)
    {
        $cfg = $this->menu($menu);
        $request->merge(['ids' => $this->ids($request)]);

        return app($cfg['controller'])->export($request);
    }

    /** Isi modal "Cetak Laporan" hanya untuk baris terpilih (dimuat lewat fetch oleh bulk-import.js). */
    public function cetak(Request $request, string $menu)
    {
        $cfg = $this->menu($menu);
        $request->merge(['ids' => $this->ids($request)]);

        return app(CetakLaporanController::class)->show($request, $cfg['print']);
    }

    /** Hapus semua baris terpilih (konfirmasi 2 langkah dilakukan di browser oleh DeleteConfirm). */
    public function hapus(Request $request, string $menu)
    {
        $cfg = $this->menu($menu);
        abort_unless(method_exists($cfg['controller'], 'destroyMany'), 404);
        $request->merge(['ids' => $this->ids($request)]);

        return app($cfg['controller'])->destroyMany($request);
    }

    /** Update massal semua baris terpilih (modal "Update Massal"; kolom kosong = tidak diubah). */
    public function ubah(Request $request, string $menu)
    {
        $cfg = $this->menu($menu);
        abort_unless(method_exists($cfg['controller'], 'updateMany'), 404);
        $request->merge(['ids' => $this->ids($request)]);

        return app($cfg['controller'])->updateMany($request);
    }

    private function menu(string $menu): array
    {
        return self::MENUS[$menu] ?? abort(404);
    }

    /** @return int[] */
    private function ids(Request $request): array
    {
        $ids = SelectedIds::from($request);
        abort_if(!$ids, 422, 'Belum ada data yang dipilih.');

        return $ids;
    }
}
