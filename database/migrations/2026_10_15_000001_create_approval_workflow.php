<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alur persetujuan prestasi mahasiswa: mahasiswa -> dosen wali -> superadmin.
 *
 *  - mahasiswa.dosen_wali_id            : dosen wali tetap (diatur di Data Master)
 *  - approval_status & approval_note    : di 4 tabel yang diisi mahasiswa
 *  - approval_logs                      : riwayat tiap perpindahan status
 *
 * Data yang SUDAH ADA otomatis berstatus 'disetujui' (default kolom), jadi
 * dashboard & laporan lama tidak berubah.
 */
return new class extends Migration
{
    private const TABLES = ['kemahasiswaan', 'lppm_mahasiswa', 'rekognisi', 'kerja_sama'];

    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('dosen_wali_id')->nullable()->after('status')
                ->constrained('dosen')->nullOnDelete();
        });

        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                // menunggu_dosen | menunggu_superadmin | disetujui | ditolak
                $table->string('approval_status', 20)->default('disetujui')->index();
                $table->text('approval_note')->nullable();
            });
        }

        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->string('menu', 30);                 // slug menu: kemahasiswaan | lppm-mahasiswa | rekognisi | kerja-sama
            $table->unsignedBigInteger('item_id');
            $table->string('action', 20);               // diajukan | diajukan_ulang | diteruskan | disetujui | ditolak
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['menu', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');

        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['approval_status', 'approval_note']);
            });
        }

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dosen_wali_id');
        });
    }
};
