<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah opsi "lainnya" ke kolom enum `jenis` + kolom `jenis_lainnya`
     * untuk menyimpan teks bebas yang diketik user saat memilih "Lainnya".
     */
    public function up(): void
    {
        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->string('jenis_lainnya')->nullable()->after('jenis');
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','sharing_session','keynote_session','guest_lecture','pengabdian_internasional','research_internasional','lainnya') NOT NULL");
        } else {
            // Driver lain (mis. sqlite/pgsql): enum dibuat sebagai string+check, jadi ubah ke string biasa.
            Schema::table('kerja_sama', function (Blueprint $table) {
                $table->string('jenis')->change();
            });
        }
    }

    public function down(): void
    {
        DB::table('kerja_sama')->where('jenis', 'lainnya')->delete();

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','sharing_session','keynote_session','guest_lecture','pengabdian_internasional','research_internasional') NOT NULL");
        }

        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->dropColumn('jenis_lainnya');
        });
    }
};
