<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->string('source_lppm', 20)->nullable();
            $table->unsignedBigInteger('source_lppm_id')->nullable();
            $table->unique(['source_lppm', 'source_lppm_id'], 'rekognisi_source_lppm_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->dropUnique('rekognisi_source_lppm_unique');
            $table->dropColumn(['source_lppm', 'source_lppm_id']);
        });
    }
};