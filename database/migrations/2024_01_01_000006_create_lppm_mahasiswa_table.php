<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->enum('jenis', ['sinta_nasional', 'conference_internasional', 'jurnal_internasional']);
            $table->string('judul');
            $table->string('penulis');
            $table->string('nama_jurnal')->nullable(); // diisi kalau jenis jurnal, kosong kalau conference
            $table->string('peringkat')->nullable();   // S1-S4 tergantung jenis
            $table->string('link_doi')->nullable();    // diisi kalau jenis jurnal
            $table->string('bukti_kegiatan');
            $table->smallInteger('tahun');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_mahasiswa');
    }
};
