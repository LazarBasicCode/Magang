<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Dashboard tidak lagi diatur lewat Hak Akses: bersihkan baris lamanya.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hak_akses')->where('menu', 'dashboard')->delete();
    }

    public function down(): void
    {
        // Data lama tidak perlu dikembalikan.
    }
};
