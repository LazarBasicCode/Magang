<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda "wajib ganti password" untuk akun mahasiswa & dosen.
 * Akun baru (dan akun yang direset ke default) memakai NIM/NIDN sebagai password awal,
 * lalu dipaksa menggantinya lewat pop-up di dashboard saat pertama kali masuk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });

        // Akun lama yang password-nya MASIH sama dengan NIM/NIDN ikut ditandai wajib ganti.
        DB::table('users')
            ->whereIn('role', ['mahasiswa', 'dosen'])
            ->whereNotNull('nim_nidn')
            ->orderBy('id')
            ->each(function ($user) {
                if (Hash::check((string) $user->nim_nidn, $user->password)) {
                    DB::table('users')->where('id', $user->id)->update(['must_change_password' => true]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
