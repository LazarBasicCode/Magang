<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('rekognisi')
            ->whereIn('jenis', ['rekognisi', 'legacy'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        foreach (['approval_logs', 'approval_comments'] as $table) {
            DB::table($table)
                ->where('menu', 'rekognisi')
                ->whereIn('item_id', $ids)
                ->delete();
        }

        foreach ($ids as $id) {
            DB::table('user_notifications')
                ->whereJsonContains('data->rekognisi_id', $id)
                ->delete();
        }

        DB::table('rekognisi')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // Deleted test records cannot be restored.
    }
};
