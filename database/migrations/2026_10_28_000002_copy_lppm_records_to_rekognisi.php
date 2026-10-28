<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->copyMahasiswaRecords();
        $this->copyDosenRecords();
        $this->moveApprovalHistoryToRekognisi();
    }

    public function down(): void
    {
        $copies = DB::table('rekognisi')
            ->where('source_lppm', 'mahasiswa')
            ->get(['id', 'source_lppm_id']);
        foreach ($copies as $copy) {
            foreach (['approval_logs', 'approval_comments'] as $table) {
                if (!Schema::hasTable($table)) continue;
                DB::table($table)
                    ->where('menu', 'rekognisi')
                    ->where('item_id', $copy->id)
                    ->update(['menu' => 'lppm-mahasiswa', 'item_id' => $copy->source_lppm_id]);
            }
        }
        DB::table('rekognisi')->whereNotNull('source_lppm')->delete();
    }

    private function moveApprovalHistoryToRekognisi(): void
    {
        $copies = DB::table('rekognisi')
            ->where('source_lppm', 'mahasiswa')
            ->get(['id', 'source_lppm_id']);
        foreach ($copies as $copy) {
            foreach (['approval_logs', 'approval_comments'] as $table) {
                if (!Schema::hasTable($table)) continue;
                DB::table($table)
                    ->where('menu', 'lppm-mahasiswa')
                    ->where('item_id', $copy->source_lppm_id)
                    ->update(['menu' => 'rekognisi', 'item_id' => $copy->id]);
            }
        }
    }

    private function copyMahasiswaRecords(): void
    {
        DB::table('lppm_mahasiswa as lp')
            ->join('mahasiswa as m', 'm.id', '=', 'lp.mahasiswa_id')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->select('lp.*', 'm.user_id as owner_user_id', 'm.status as mahasiswa_status')
            ->orderBy('lp.id')
            ->chunk(500, function ($records) {
                $rows = [];
                foreach ($records as $record) {
                    [$jenis, $skala] = match ($record->jenis) {
                        'sinta_nasional' => ['sinta', 'nasional'],
                        'conference_internasional' => ['conference', 'internasional'],
                        'jurnal_internasional' => ['jurnal', 'internasional'],
                        default => ['rekognisi', null],
                    };
                    $year = (int) $record->tahun;

                    $rows[] = [
                        'user_id' => $record->owner_user_id,
                        'tipe_user' => $record->mahasiswa_status === 'lulus' ? 'alumni' : 'mahasiswa',
                        'jenis' => $jenis,
                        'skala' => $skala,
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
                        'source_lppm' => 'mahasiswa',
                        'source_lppm_id' => $record->id,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ];
                }

                if ($rows) DB::table('rekognisi')->insertOrIgnore($rows);
            });
    }

    private function copyDosenRecords(): void
    {
        DB::table('lppm_dosen as lp')
            ->join('dosen as d', 'd.id', '=', 'lp.dosen_id')
            ->join('users as u', 'u.id', '=', 'd.user_id')
            ->select('lp.*', 'd.user_id as owner_user_id')
            ->orderBy('lp.id')
            ->chunk(500, function ($records) {
                $rows = [];
                foreach ($records as $record) {
                    [$jenis, $skala] = match ($record->jenis) {
                        'q_internasional' => ['jurnal', 'internasional'],
                        'sinta_nasional' => ['jurnal', 'nasional'],
                        'hki' => ['hki', null],
                        'book' => ['book', null],
                        default => ['rekognisi', null],
                    };
                    $year = (int) $record->tahun;

                    $rows[] = [
                        'user_id' => $record->owner_user_id,
                        'tipe_user' => 'dosen',
                        'jenis' => $jenis,
                        'skala' => $skala,
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
                        'source_lppm' => 'dosen',
                        'source_lppm_id' => $record->id,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ];
                }

                if ($rows) DB::table('rekognisi')->insertOrIgnore($rows);
            });
    }
};