<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->string('skala')->default('nasional')->after('jenis_lainnya');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','sharing_session','keynote_session','guest_lecture','pengabdian_internasional','research_internasional','lainnya','pertukaran_pelajar','fgd','pengabdian','penelitian') NOT NULL");
        }

        DB::table('kerja_sama')
            ->whereIn('jenis', ['conference_internasional', 'guest_lecture', 'pengabdian_internasional', 'research_internasional'])
            ->update(['skala' => 'internasional']);

        DB::table('kerja_sama')->where('jenis', 'sharing_session')->update(['jenis' => 'fgd']);
        DB::table('kerja_sama')->where('jenis', 'pengabdian_internasional')->update(['jenis' => 'pengabdian']);
        DB::table('kerja_sama')->where('jenis', 'research_internasional')->update(['jenis' => 'penelitian']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','pertukaran_pelajar','fgd','keynote_session','guest_lecture','pengabdian','penelitian','lainnya') NOT NULL");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','pertukaran_pelajar','fgd','keynote_session','guest_lecture','pengabdian','penelitian','lainnya','sharing_session','pengabdian_internasional','research_internasional') NOT NULL");
        }

        DB::table('kerja_sama')->where('jenis', 'pertukaran_pelajar')->update([
            'jenis' => 'lainnya',
            'jenis_lainnya' => 'Pertukaran Pelajar',
        ]);
        DB::table('kerja_sama')->where('jenis', 'fgd')->update(['jenis' => 'sharing_session']);
        DB::table('kerja_sama')->where('jenis', 'pengabdian')->update(['jenis' => 'pengabdian_internasional']);
        DB::table('kerja_sama')->where('jenis', 'penelitian')->update(['jenis' => 'research_internasional']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE kerja_sama MODIFY jenis ENUM('conference_internasional','pkl','sharing_session','keynote_session','guest_lecture','pengabdian_internasional','research_internasional','lainnya') NOT NULL");
        }

        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->dropColumn('skala');
        });
    }
};
