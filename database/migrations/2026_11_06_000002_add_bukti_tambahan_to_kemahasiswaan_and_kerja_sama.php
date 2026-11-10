<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bukti tambahan (JSON array link, lihat trait HasBuktiTambahan) juga untuk Kemahasiswaan & Kerja Sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['kemahasiswaan', 'kerja_sama'] as $table) {
            if (Schema::hasColumn($table, 'bukti_tambahan')) continue;
            Schema::table($table, function (Blueprint $t) {
                $t->text('bukti_tambahan')->nullable()->after('bukti_kegiatan');
            });
        }
    }

    public function down(): void
    {
        foreach (['kemahasiswaan', 'kerja_sama'] as $table) {
            if (!Schema::hasColumn($table, 'bukti_tambahan')) continue;
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('bukti_tambahan');
            });
        }
    }
};
