<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sengaja dinamai "user_notifications" (bukan "notifications") supaya
     * tidak bentrok dengan tabel notifikasi bawaan Laravel (yang skemanya
     * polymorphic notifiable_type/notifiable_id) — User model di proyek ini
     * sudah pakai trait Notifiable untuk keperluan lain (email reset
     * password), jadi kita hindari nama yang sama persis.
     */
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();

            // Penerima notifikasi.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Kategori notifikasi, dipakai untuk logika di frontend (ikon,
            // link "lihat semua", dsb): data_updated, concurrent_login, system.
            $table->string('type', 40);

            // Warna & ikon material-symbols, match langsung ke class CSS
            // .notif-item-icon.c-{color} yang sudah ada di style.css.
            $table->string('color', 20)->default('primary');
            $table->string('icon', 60)->default('notifications');

            $table->string('title');
            $table->string('description');

            // Konteks tambahan (siapa pelakunya, IP, dsb) — tidak wajib
            // ditampilkan, tapi berguna untuk detail/debug ke depannya.
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
