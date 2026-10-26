<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->string('tipe_user')->change();
            $table->string('jenis')->change();
            $table->string('mitra')->nullable()->change();
            $table->string('judul')->nullable();
            $table->string('penulis')->nullable();
            $table->string('nama_jurnal')->nullable();
            $table->string('peringkat')->nullable();
            $table->string('jenis_hki')->nullable();
            $table->string('kategori_buku')->nullable();
            $table->string('link_doi')->nullable();
            $table->string('skala')->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
        });

        DB::table('rekognisi')->where('jenis', 'nasional')->update([
            'jenis' => 'rekognisi',
            'skala' => 'nasional',
        ]);
        DB::table('rekognisi')->where('jenis', 'internasional')->update([
            'jenis' => 'rekognisi',
            'skala' => 'internasional',
        ]);
        DB::table('rekognisi')->where('jenis', 'alumni')->update([
            'jenis' => 'rekognisi',
            'tipe_user' => 'alumni',
        ]);
    }

    public function down(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->dropColumn([
                'judul',
                'penulis',
                'nama_jurnal',
                'peringkat',
                'jenis_hki',
                'kategori_buku',
                'link_doi',
                'skala',
                'tahun',
            ]);
        });
    }
};