<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lppm_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosen')->cascadeOnDelete();
            $table->enum('jenis', ['q_internasional', 'hki', 'sinta_nasional', 'book']);
            $table->string('judul');
            $table->string('penulis');
            $table->string('nama_jurnal')->nullable();      // diisi kalau jenis q_internasional/sinta_nasional
            $table->string('peringkat')->nullable();        // Q1-Q4 / S1-S4 tergantung jenis
            $table->enum('jenis_hki', ['hak_cipta', 'paten', 'merek'])->nullable(); // diisi kalau jenis=hki
            $table->enum('kategori_buku', ['ajar', 'referensi', 'chapter'])->nullable(); // diisi kalau jenis=book
            $table->string('link_doi')->nullable();         // diisi kalau jenis jurnal
            $table->string('bukti_kegiatan');
            $table->smallInteger('tahun');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lppm_dosen');
    }
};
