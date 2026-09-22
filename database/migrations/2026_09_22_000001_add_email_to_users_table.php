<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom email (opsional, unik) supaya user bisa menerima
     * tautan reset password. Nullable karena akun lama (dibuat sebelum
     * fitur ini ada) belum tentu punya email terdaftar — untuk akun
     * seperti itu, admin/superadmin yang mengatur ulang passwordnya
     * lewat menu Data Master Pengguna.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nim_nidn');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
