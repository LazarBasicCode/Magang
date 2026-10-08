<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Defensif: lewati kolom yang sudah ada supaya migrate tidak error
        // "duplicate column".
        $needAngkatan = !Schema::hasColumn('mahasiswa', 'angkatan');
        $needStatus   = !Schema::hasColumn('mahasiswa', 'status');

        Schema::table('mahasiswa', function (Blueprint $table) use ($needAngkatan, $needStatus) {
            if ($needAngkatan) {
                // Tahun masuk (4 digit, mis. 2022). Nullable agar data lama tidak error.
                $table->unsignedSmallInteger('angkatan')->nullable()->after('nim');
            }

            if ($needStatus) {
                // Status akademik mengacu pada istilah umum PDDikti.
                $table->enum('status', [
                    'aktif',
                    'cuti',
                    'lulus',
                    'non_aktif',
                    'drop_out',
                    'mengundurkan_diri',
                ])->default('aktif')->after('nim');
            }
        });
    }

    public function down(): void
    {
        foreach (['status', 'angkatan'] as $column) {
            if (Schema::hasColumn('mahasiswa', $column)) {
                Schema::table('mahasiswa', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
