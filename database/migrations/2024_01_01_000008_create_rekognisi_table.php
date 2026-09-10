<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekognisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // nengok mahasiswa/dosen via tipe_user
            $table->enum('tipe_user', ['mahasiswa', 'dosen']);
            $table->string('mitra'); // nama perusahaan/instansi/mitra terkait
            $table->enum('jenis', ['nasional', 'internasional', 'alumni']);
            $table->string('jabatan')->nullable(); // diisi kalau jenis='alumni'
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('bukti_kegiatan');
            $table->string('bukti_tambahan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekognisi');
    }
};
