<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan permanen setiap percobaan login (berhasil, gagal, maupun yang
     * ditolak karena kena rate limit). Beda dengan RateLimiter (yang cuma
     * nyimpen counter sementara di cache dan hilang setelah reset), tabel
     * ini nyimpen histori lengkap untuk keperluan audit/forensik kalau ada
     * kecurigaan serangan brute-force di kemudian hari.
     */
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();

            // Nullable: kalau usernamenya bahkan tidak ditemukan di database,
            // kita tetap catat percobaannya tapi tidak ada user_id yang valid.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Input mentah yang diketik di form login, apa adanya (NIM/NIDN/nama).
            // Disimpan terpisah dari user_id supaya tetap ada jejak walau usernya
            // tidak ditemukan atau akunnya kemudian dihapus.
            $table->string('username_input');

            $table->string('ip_address', 45);
            $table->string('user_agent', 255)->nullable();

            // success  = login berhasil
            // failed   = username/password salah
            // locked   = ditolak duluan karena sudah kena rate limit
            $table->enum('status', ['success', 'failed', 'locked']);

            $table->timestamp('created_at')->useCurrent();

            $table->index(['username_input', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
