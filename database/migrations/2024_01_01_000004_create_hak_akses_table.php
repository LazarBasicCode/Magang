<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('menu'); // nama menu/submenu, mis: "lppm.dosen", "kerja_sama.mahasiswa"
            $table->boolean('boleh_akses')->default(false);
            $table->timestamps();

            // satu user tidak boleh punya baris ganda untuk menu yang sama
            $table->unique(['user_id', 'menu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hak_akses');
    }
};
