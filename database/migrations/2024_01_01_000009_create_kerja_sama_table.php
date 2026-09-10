<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kerja_sama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // bisa dosen/mahasiswa
            $table->enum('tipe_user', ['mahasiswa', 'dosen']);
            $table->enum('jenis', [
                'conference_internasional',
                'pkl',
                'sharing_session',
                'keynote_session',
                'guest_lecture',
                'pengabdian_internasional',
                'research_internasional',
            ]);
            $table->enum('arah', ['inbound', 'outbound'])->nullable(); // hanya diisi kalau jenis='guest_lecture'
            $table->string('mitra'); // nama institusi/perusahaan/negara mitra
            $table->string('judul_kegiatan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('bukti_kegiatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerja_sama');
    }
};
