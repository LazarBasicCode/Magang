<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;

/**
 * "Tap" untuk channel log bertipe daily: ganti pola nama berkas dari per-TANGGAL
 * (activity-2026-10-23.log) menjadi per-BULAN (activity-2026-10.log).
 *
 * Perilaku bawaan RotatingFileHandler yang dimanfaatkan:
 *  - semua catatan dalam satu bulan masuk ke SATU berkas;
 *  - saat catatan pertama bulan baru ditulis, berkas bulan baru dibuat dan berkas
 *    bulan-bulan lama yang melebihi batas (config "days" = jumlah berkas yang disimpan) otomatis dihapus.
 *
 * Dipasang lewat config/logging.php:  'tap' => [\App\Logging\MonthlyRotation::class]
 */
class MonthlyRotation
{
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof RotatingFileHandler) {
                $handler->setFilenameFormat('{filename}-{date}', RotatingFileHandler::FILE_PER_MONTH);
            }
        }
    }
}
