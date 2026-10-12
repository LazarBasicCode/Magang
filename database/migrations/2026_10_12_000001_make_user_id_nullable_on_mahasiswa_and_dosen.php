<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mahasiswa/dosen boleh ada TANPA akun (user_id kosong).
     *
     * Dipakai saat data LPPM diimpor memakai NIM/NIDN yang belum punya akun:
     * NIM-nya disimpan dulu sebagai "calon", lalu akunnya dilengkapi lewat
     * Data Master -> Tambah Mahasiswa / Tambah Dosen (baris itu otomatis
     * dihubungkan ke akun baru, jadi seluruh data LPPM-nya ikut tersambung).
     */
    public function up(): void
    {
        foreach (['mahasiswa', 'dosen'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table($tabel, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });

            Schema::table($tabel, function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Baris tanpa akun tidak bisa dipertahankan kalau kolom kembali wajib diisi.
        foreach (['mahasiswa', 'dosen'] as $tabel) {
            DB::table($tabel)->whereNull('user_id')->delete();

            Schema::table($tabel, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table($tabel, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });

            Schema::table($tabel, function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
