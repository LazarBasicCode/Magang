<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kemahasiswaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->enum('jenis', ['inbis', 'kemahasiswaan']);
            $table->enum('tab', ['akademik', 'non_akademik']);
            $table->enum('tingkat', ['lokal', 'nasional', 'internasional']);
            $table->smallInteger('tahun'); // TS-2, TS-1, TS
            $table->string('nama_kegiatan');
            $table->string('bukti_kegiatan'); // bukti_sk / bukti_foto / bukti_sertifikat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kemahasiswaan');
    }
};
