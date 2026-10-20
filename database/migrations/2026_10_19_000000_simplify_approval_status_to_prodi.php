<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alur baru: diajukan -> diproses prodi -> terverifikasi (tanpa dosen).
 * Nilai 'disetujui' & 'ditolak' tidak berubah.
 */
return new class extends Migration
{
    // Tambahkan tabel menu lain di sini saat diterapkan ke semua menu.
    private array $tables = ['kemahasiswaan', 'lppm_mahasiswa', 'rekognisi', 'kerja_sama'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            // belum diperiksa dosen -> diajukan; sudah diteruskan dosen -> sedang diproses Prodi
            DB::table($table)->where('approval_status', 'menunggu_dosen')->update(['approval_status' => 'diajukan']);
            DB::table($table)->where('approval_status', 'menunggu_superadmin')->update(['approval_status' => 'menunggu_prodi']);
        }

        DB::table('approval_logs')->where('action', 'diteruskan')->update(['action' => 'diproses']);
        foreach (['from_status', 'to_status'] as $col) {
            DB::table('approval_logs')->where($col, 'menunggu_dosen')->update([$col => 'diajukan']);
            DB::table('approval_logs')->where($col, 'menunggu_superadmin')->update([$col => 'menunggu_prodi']);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::table($table)->whereIn('approval_status', ['diajukan', 'menunggu_prodi'])->update(['approval_status' => 'menunggu_superadmin']);
        }
    }
};
