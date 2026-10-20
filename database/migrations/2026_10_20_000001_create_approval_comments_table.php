<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Komentar Prodi pada pengajuan (bisa ditambah di status apa pun, termasuk setelah terverifikasi). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_comments', function (Blueprint $table) {
            $table->id();
            $table->string('menu', 30);
            $table->unsignedBigInteger('item_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['menu', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_comments');
    }
};
