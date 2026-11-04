<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            // Prosiding
            $table->string('nama_konferensi', 255)->nullable()->after('link_doi');
            $table->string('nomor_issn_isbn', 100)->nullable()->after('nama_konferensi');
            // Paten: terdaftar (Filed) | granted (Bersertifikat)
            $table->string('status_paten', 20)->nullable()->after('nomor_issn_isbn');
            $table->string('nomor_paten', 100)->nullable()->after('status_paten');
        });
    }

    public function down(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->dropColumn(['nama_konferensi', 'nomor_issn_isbn', 'status_paten', 'nomor_paten']);
        });
    }
};
