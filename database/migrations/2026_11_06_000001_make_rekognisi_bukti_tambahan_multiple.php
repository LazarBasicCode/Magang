<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bukti tambahan Rekognisi kini bisa lebih dari satu. Kolom `bukti_tambahan` tetap dipakai,
 * tetapi isinya menjadi JSON array berisi link (kolom diperlebar ke TEXT). Data lama yang
 * berupa satu link biasa tetap terbaca (lihat accessor Rekognisi::bukti_tambahan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->text('bukti_tambahan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->string('bukti_tambahan')->nullable()->change();
        });
    }
};
