<?php

namespace App\Console\Commands;

use App\Support\MonthlyLogs;
use Illuminate\Console\Command;

class MergeMonthlyLogs extends Command
{
    protected $signature = 'logs:merge-monthly
                            {--keep= : Jumlah berkas bulanan yang disimpan (default: sesuai config logging masing-masing channel)}';

    protected $description = 'Gabungkan berkas log harian lama (activity-YYYY-MM-DD.log, laravel-YYYY-MM-DD.log) menjadi satu berkas per bulan, lalu hapus bulan yang melebihi batas simpan';

    public function handle(): int
    {
        $dir = storage_path('logs');
        $targets = [
            'activity' => (int) config('logging.channels.activity.days', 1),
            'laravel'  => (int) config('logging.channels.monthly.days', 3),
        ];

        foreach ($targets as $prefix => $defaultKeep) {
            $keep = $this->option('keep') !== null ? max(1, (int) $this->option('keep')) : max(1, $defaultKeep);
            $r = MonthlyLogs::merge($dir, $prefix, $keep);

            $this->line(sprintf(
                '<info>%s</info>: %d berkas harian digabung ke %d bulan%s; dihapus (di luar batas %d bulan): %s',
                $prefix,
                $r['merged'],
                count($r['months']),
                $r['months'] ? ' (' . implode(', ', $r['months']) . ')' : '',
                $keep,
                $r['deleted'] ? implode(', ', $r['deleted']) : '-'
            ));
        }

        return self::SUCCESS;
    }
}
