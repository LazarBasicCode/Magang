<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ganti kolom boolean `boleh_akses` (hanya ya/tidak) menjadi kolom
     * `level` bertingkat: none, readonly, biasa, penuh — supaya bisa
     * mendukung 4 tingkat hak akses per menu sesuai kebutuhan Hak Akses.
     */
    public function up(): void
    {
        Schema::table('hak_akses', function (Blueprint $table) {
            $table->string('level', 20)->default('none')->after('menu');
        });

        // Migrasikan data lama (kalau ada): true -> penuh, false -> none
        DB::table('hak_akses')->where('boleh_akses', true)->update(['level' => 'penuh']);
        DB::table('hak_akses')->where('boleh_akses', false)->update(['level' => 'none']);

        Schema::table('hak_akses', function (Blueprint $table) {
            $table->dropColumn('boleh_akses');
        });
    }

    public function down(): void
    {
        Schema::table('hak_akses', function (Blueprint $table) {
            $table->boolean('boleh_akses')->default(false)->after('menu');
        });

        DB::table('hak_akses')->where('level', '!=', 'none')->update(['boleh_akses' => true]);

        Schema::table('hak_akses', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
