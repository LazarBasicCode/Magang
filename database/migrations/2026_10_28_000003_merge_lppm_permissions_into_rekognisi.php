<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const RANK = ['none' => 0, 'readonly' => 1, 'biasa' => 2, 'penuh' => 3];

    public function up(): void
    {
        $legacy = DB::table('hak_akses')
            ->whereIn('menu', ['lppm_mahasiswa', 'lppm_dosen'])
            ->get()
            ->groupBy('user_id');

        foreach ($legacy as $userId => $rows) {
            $current = DB::table('hak_akses')
                ->where('user_id', $userId)
                ->where('menu', 'rekognisi')
                ->first();
            $levels = $rows->pluck('level')->all();
            if ($current) $levels[] = $current->level;

            $level = collect($levels)
                ->sortByDesc(fn ($value) => self::RANK[$value] ?? 0)
                ->first() ?? 'none';

            DB::table('hak_akses')->updateOrInsert(
                ['user_id' => $userId, 'menu' => 'rekognisi'],
                ['level' => $level, 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Legacy permission rows remain available for restoring the old menu configuration.
    }
};