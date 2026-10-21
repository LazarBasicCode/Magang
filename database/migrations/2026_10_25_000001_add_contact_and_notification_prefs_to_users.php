<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kontak cadangan (email sekunder + telepon) & preferensi notifikasi per akun — dipakai halaman Profil. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('secondary_email');
            $table->json('notification_prefs')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['secondary_email', 'phone', 'notification_prefs']);
        });
    }
};
