<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekognisi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('rekognisi', function (Blueprint $table) {
            $table->foreignId('mahasiswa_id')->nullable()->after('id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->after('mahasiswa_id')->constrained('dosen')->cascadeOnDelete();
        });

        DB::transaction(function () {
            $this->linkExistingRekognisiToProfiles();
            $this->consolidateMahasiswaPublications();
            $this->consolidateDosenPublications();
        });

        Schema::table('rekognisi', function (Blueprint $table) {
            $table->dropUnique('rekognisi_source_lppm_unique');
            $table->dropColumn(['user_id', 'source_lppm', 'source_lppm_id']);
        });

        Schema::dropIfExists('lppm_mahasiswa');
        Schema::dropIfExists('lppm_dosen');
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Konsolidasi Rekognisi tidak dapat dibatalkan tanpa kehilangan data publikasi baru. Pulihkan backup yang dibuat sebelum migrasi.'
        );
    }

    private function linkExistingRekognisiToProfiles(): void
    {
        DB::table('rekognisi')->orderBy('id')->chunkById(500, function ($records) {
            foreach ($records as $record) {
                if ($record->source_lppm === 'mahasiswa' && $record->source_lppm_id !== null) {
                    $profile = DB::table('lppm_mahasiswa')
                        ->where('id', $record->source_lppm_id)
                        ->value('mahasiswa_id');
                    if ($profile !== null) {
                        DB::table('rekognisi')->where('id', $record->id)->update([
                            'mahasiswa_id' => $profile,
                            'dosen_id' => null,
                            'tipe_user' => $this->mahasiswaType((int) $profile),
                        ]);
                        continue;
                    }
                }

                if ($record->source_lppm === 'dosen' && $record->source_lppm_id !== null) {
                    $profile = DB::table('lppm_dosen')
                        ->where('id', $record->source_lppm_id)
                        ->value('dosen_id');
                    if ($profile !== null) {
                        DB::table('rekognisi')->where('id', $record->id)->update([
                            'mahasiswa_id' => null,
                            'dosen_id' => $profile,
                            'tipe_user' => 'dosen',
                        ]);
                        continue;
                    }
                }

                $user = DB::table('users')->where('id', $record->user_id)->first();
                if (!$user || !in_array($user->role, ['mahasiswa', 'dosen'], true)) {
                    throw new RuntimeException("Rekognisi #{$record->id} tidak dapat dipasangkan ke profil mahasiswa atau dosen.");
                }

                if ($user->role === 'mahasiswa') {
                    $profileId = $this->ensureMahasiswaProfile($user);
                    DB::table('rekognisi')->where('id', $record->id)->update([
                        'mahasiswa_id' => $profileId,
                        'dosen_id' => null,
                        'tipe_user' => $this->mahasiswaType($profileId),
                    ]);
                } else {
                    $profileId = $this->ensureDosenProfile($user);
                    DB::table('rekognisi')->where('id', $record->id)->update([
                        'mahasiswa_id' => null,
                        'dosen_id' => $profileId,
                        'tipe_user' => 'dosen',
                    ]);
                }
            }
        });
    }

    private function consolidateMahasiswaPublications(): void
    {
        DB::table('lppm_mahasiswa')->orderBy('id')->chunkById(500, function ($records) {
            foreach ($records as $record) {
                $existing = DB::table('rekognisi')
                    ->where('source_lppm', 'mahasiswa')
                    ->where('source_lppm_id', $record->id)
                    ->first();

                $profile = DB::table('mahasiswa')->where('id', $record->mahasiswa_id)->first();
                if (!$profile) {
                    throw new RuntimeException("Publikasi mahasiswa #{$record->id} tidak memiliki profil pemilik yang valid.");
                }

                $type = $this->mahasiswaType((int) $profile->id);
                $kindAndScale = match ($record->jenis) {
                    'sinta_nasional' => ['sinta', 'nasional'],
                    'conference_internasional' => ['conference', 'internasional'],
                    'jurnal_internasional' => ['jurnal', 'internasional'],
                    default => throw new RuntimeException("Jenis publikasi mahasiswa #{$record->id} tidak dikenal."),
                };
                $year = (int) $record->tahun;
                $data = [
                    'mahasiswa_id' => $profile->id,
                    'dosen_id' => null,
                    'tipe_user' => $type,
                    'jenis' => $kindAndScale[0],
                    'skala' => $kindAndScale[1],
                    'judul' => $record->judul,
                    'penulis' => $record->penulis,
                    'nama_jurnal' => $record->nama_jurnal,
                    'peringkat' => $record->peringkat,
                    'link_doi' => $record->link_doi,
                    'mitra' => null,
                    'jabatan' => null,
                    'tanggal_mulai' => Carbon::create($year, 1, 1)->toDateString(),
                    'tanggal_selesai' => Carbon::create($year, 12, 31)->toDateString(),
                    'tahun' => $year,
                    'bukti_kegiatan' => $record->bukti_kegiatan,
                    'bukti_tambahan' => null,
                    'approval_status' => $record->approval_status ?: 'disetujui',
                    'approval_note' => $record->approval_note,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ];

                if ($existing) {
                    DB::table('rekognisi')->where('id', $existing->id)->update($data);
                    $rekognisiId = $existing->id;
                } else {
                    $rekognisiId = DB::table('rekognisi')->insertGetId($data);
                }

                foreach (['approval_logs', 'approval_comments'] as $table) {
                    if (!Schema::hasTable($table)) continue;
                    DB::table($table)
                        ->where('menu', 'lppm-mahasiswa')
                        ->where('item_id', $record->id)
                        ->update(['menu' => 'rekognisi', 'item_id' => $rekognisiId]);
                }
            }
        });
    }

    private function consolidateDosenPublications(): void
    {
        DB::table('lppm_dosen')->orderBy('id')->chunkById(500, function ($records) {
            foreach ($records as $record) {
                $existing = DB::table('rekognisi')
                    ->where('source_lppm', 'dosen')
                    ->where('source_lppm_id', $record->id)
                    ->first();
                $profile = DB::table('dosen')->where('id', $record->dosen_id)->first();
                if (!$profile) {
                    throw new RuntimeException("Publikasi dosen #{$record->id} tidak memiliki profil pemilik yang valid.");
                }

                [$kind, $scale] = match ($record->jenis) {
                    'q_internasional' => ['jurnal', 'internasional'],
                    'sinta_nasional' => ['jurnal', 'nasional'],
                    'hki' => ['hki', null],
                    'book' => ['book', null],
                    default => throw new RuntimeException("Jenis publikasi dosen #{$record->id} tidak dikenal."),
                };
                $year = (int) $record->tahun;
                $data = [
                    'mahasiswa_id' => null,
                    'dosen_id' => $profile->id,
                    'tipe_user' => 'dosen',
                    'jenis' => $kind,
                    'skala' => $scale,
                    'judul' => $record->judul,
                    'penulis' => $record->penulis,
                    'nama_jurnal' => $record->nama_jurnal,
                    'peringkat' => $record->peringkat,
                    'jenis_hki' => $record->jenis_hki,
                    'kategori_buku' => $record->kategori_buku,
                    'link_doi' => $record->link_doi,
                    'mitra' => null,
                    'jabatan' => null,
                    'tanggal_mulai' => Carbon::create($year, 1, 1)->toDateString(),
                    'tanggal_selesai' => Carbon::create($year, 12, 31)->toDateString(),
                    'tahun' => $year,
                    'bukti_kegiatan' => $record->bukti_kegiatan,
                    'bukti_tambahan' => null,
                    'approval_status' => 'disetujui',
                    'approval_note' => null,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ];

                if ($existing) {
                    DB::table('rekognisi')->where('id', $existing->id)->update($data);
                } else {
                    DB::table('rekognisi')->insert($data);
                }
            }
        });
    }

    private function ensureMahasiswaProfile(object $user): int
    {
        $profile = DB::table('mahasiswa')->where('user_id', $user->id)->first();
        if ($profile) return (int) $profile->id;

        if (blank($user->nim_nidn)) {
            throw new RuntimeException("Akun mahasiswa #{$user->id} tidak memiliki NIM untuk membuat profil.");
        }

        $profile = DB::table('mahasiswa')->where('nim', $user->nim_nidn)->first();
        if ($profile) {
            if ($profile->user_id !== null && (int) $profile->user_id !== (int) $user->id) {
                throw new RuntimeException("NIM {$user->nim_nidn} pada Rekognisi sudah terhubung ke akun lain.");
            }
            DB::table('mahasiswa')->where('id', $profile->id)->update(['user_id' => $user->id]);

            return (int) $profile->id;
        }

        return (int) DB::table('mahasiswa')->insertGetId([
            'user_id' => $user->id,
            'nim' => $user->nim_nidn,
            'angkatan' => null,
            'status' => 'aktif',
            'dosen_wali_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureDosenProfile(object $user): int
    {
        $profile = DB::table('dosen')->where('user_id', $user->id)->first();
        if ($profile) return (int) $profile->id;

        if (blank($user->nim_nidn)) {
            throw new RuntimeException("Akun dosen #{$user->id} tidak memiliki NIDN untuk membuat profil.");
        }

        $profile = DB::table('dosen')->where('nidn', $user->nim_nidn)->first();
        if ($profile) {
            if ($profile->user_id !== null && (int) $profile->user_id !== (int) $user->id) {
                throw new RuntimeException("NIDN {$user->nim_nidn} pada Rekognisi sudah terhubung ke akun lain.");
            }
            DB::table('dosen')->where('id', $profile->id)->update(['user_id' => $user->id]);

            return (int) $profile->id;
        }

        return (int) DB::table('dosen')->insertGetId([
            'user_id' => $user->id,
            'nidn' => $user->nim_nidn,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mahasiswaType(int $profileId): string
    {
        return DB::table('mahasiswa')->where('id', $profileId)->value('status') === 'lulus'
            ? 'alumni'
            : 'mahasiswa';
    }
};
