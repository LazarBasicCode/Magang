<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dosen', 'status')) {
            return;
        }

        Schema::table('dosen', function (Blueprint $table) {
            // Status kepegawaian/keaktifan dosen (istilah umum PDDikti).
            $table->enum('status', [
                'aktif',
                'tugas_belajar',
                'cuti',
                'pensiun',
                'non_aktif',
            ])->default('aktif')->after('nidn');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('dosen', 'status')) {
            Schema::table('dosen', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
