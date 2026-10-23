<?php

namespace App\Support;

use App\Models\ApprovalComment;
use App\Models\ApprovalLog;
use App\Models\LoginAttempt;
use App\Models\UserNotification;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Mengumpulkan PERUBAHAN DATA selama satu request supaya log aktivitas bisa menampilkan
 * "sebelum -> sesudah" (input -> output), mirip response di Swagger:
 *
 *   "update": { "name": { "input": "Budi", "output": "Budi Santoso" } }
 *   "create": { "name": { "output": "Budi" }, ... }
 *   "delete": { "name": { "input": "Budi" }, ... }
 *
 * Kalau satu request menyentuh lebih dari satu data (hapus massal, impor Excel, update akun + profilnya),
 * isinya dikelompokkan per data: { "Mahasiswa#12": { ...kolom... }, "User#457": { ... } }.
 *
 * Dipasok oleh event Eloquent (created/updated/deleted, didaftarkan di AppServiceProvider) dan dibaca
 * oleh middleware LogActivity. Aturan keamanan: nilai kolom sensitif (password, token, dst.) TIDAK pernah
 * dicatat — hanya ditandai "••••••" bahwa kolom itu berubah. Update massal lewat query builder
 * (Model::where()->update()) tidak memicu event Eloquent sehingga tidak ikut terinci.
 */
final class ChangeTracker
{
    public const MASK = '••••••';

    /** Jumlah data yang dirinci per jenis aksi (sisanya cukup dihitung). */
    private const MAX_RECORDS = 25;
    /** Panjang maksimum satu nilai teks. */
    private const MAX_VALUE = 160;
    /** Batas total JSON perubahan; viewer log memotong detail di 12.000 karakter (JSON terpotong = tidak terbaca). */
    private const MAX_BYTES = 7000;

    /** Kolom yang tidak informatif (otomatis diisi sistem). */
    private const SKIP = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];

    /** Kolom yang isinya tidak boleh masuk log. */
    private const SENSITIVE = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'remember_token', 'token', 'api_token', 'access_token', 'secret', 'api_key',
    ];

    /** Model pencatat yang sudah merupakan log/notifikasi itu sendiri. */
    private const IGNORED_MODELS = [UserNotification::class, LoginAttempt::class, ApprovalLog::class, ApprovalComment::class];

    /** @var array<string, array<int, array{type:string, id:mixed, fields:array}>> */
    private array $records = ['create' => [], 'update' => [], 'delete' => []];

    /** @var array<string, int> */
    private array $totals = ['create' => 0, 'update' => 0, 'delete' => 0];

    public function flush(): void
    {
        $this->records = ['create' => [], 'update' => [], 'delete' => []];
        $this->totals  = ['create' => 0, 'update' => 0, 'delete' => 0];
    }

    /** @param 'create'|'update'|'delete' $event */
    public function track(string $event, Model $model): void
    {
        if (!isset($this->records[$event]) || $this->ignored($model)) {
            return;
        }

        $fields = match ($event) {
            'update' => $this->diff($model),
            'create' => $this->snapshot($model->getAttributes(), 'output'),
            'delete' => $this->snapshot($model->getAttributes(), 'input'),
        };

        if ($fields === []) {
            return; // mis. touch() yang hanya mengubah updated_at
        }

        $this->totals[$event]++;
        if (count($this->records[$event]) < self::MAX_RECORDS) {
            $this->records[$event][] = ['type' => class_basename($model), 'id' => $model->getKey(), 'fields' => $fields];
        }
    }

    public function hasChanges(): bool
    {
        return array_sum($this->totals) > 0;
    }

    /**
     * Bentuk siap-log: ['update' => [...]] / ['create' => [...]] / ['delete' => [...]] (hanya yang ada isinya).
     *
     * @return array<string, array>
     */
    public function summary(): array
    {
        $keep = array_map('count', $this->records);
        $valueLimit = self::MAX_VALUE;

        for ($guard = 0; $guard < 200; $guard++) {
            $out = $this->build($keep, $valueLimit);
            if ($out === [] || strlen((string) json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) <= self::MAX_BYTES) {
                return $out;
            }

            // Terlalu besar: kurangi jumlah data yang dirinci (dari jenis yang paling banyak), terakhir pendekkan nilai.
            arsort($keep);
            $event = array_key_first($keep);
            if ($keep[$event] > 1) {
                $keep[$event]--;
            } elseif ($valueLimit > 30) {
                $valueLimit = max(30, intdiv($valueLimit, 2));
            } else {
                return $out;
            }
        }

        return $this->build($keep, 30);
    }

    // ------------------------------------------------------------------

    /** @param array<string,int> $keep jumlah data dirinci per jenis */
    private function build(array $keep, int $valueLimit): array
    {
        $out = [];
        foreach (['create', 'update', 'delete'] as $event) {
            if ($this->totals[$event] === 0) {
                continue;
            }

            $records = array_slice($this->records[$event], 0, $keep[$event]);
            $omitted = $this->totals[$event] - count($records);

            if ($this->totals[$event] === 1) {
                $out[$event] = $this->limit($records[0]['fields'], $valueLimit);
                continue;
            }

            $grouped = [];
            foreach ($records as $r) {
                $grouped[$r['type'] . '#' . $r['id']] = $this->limit($r['fields'], $valueLimit);
            }
            if ($omitted > 0) {
                $grouped['…'] = "dan {$omitted} data lainnya tidak dirinci";
            }
            $out[$event] = $grouped;
        }

        return $out;
    }

    /** Pendekkan nilai teks bila perlu (dipakai saat total JSON melewati batas). */
    private function limit(array $fields, int $valueLimit): array
    {
        if ($valueLimit >= self::MAX_VALUE) {
            return $fields;
        }

        foreach ($fields as $col => $pair) {
            foreach ($pair as $side => $value) {
                if (is_string($value) && mb_strlen($value) > $valueLimit) {
                    $fields[$col][$side] = rtrim(mb_substr($value, 0, $valueLimit - 1)) . '…';
                }
            }
        }

        return $fields;
    }

    /** update: hanya kolom yang BENAR-BENAR berubah, dengan nilai lama (input) dan baru (output). */
    private function diff(Model $model): array
    {
        $fields = [];
        foreach ($model->getChanges() as $col => $new) {
            if (in_array($col, self::SKIP, true)) {
                continue;
            }
            $fields[$col] = $this->sensitive($col)
                ? ['input' => self::MASK, 'output' => self::MASK]
                : ['input' => $this->normalize($model->getOriginal($col)), 'output' => $this->normalize($new)];
        }

        return $fields;
    }

    /** create (hanya output) / delete (hanya input). */
    private function snapshot(array $attributes, string $side): array
    {
        $fields = [];
        foreach ($attributes as $col => $value) {
            if (in_array($col, self::SKIP, true) || $value === null || $value === '') {
                continue;
            }
            $fields[$col] = [$side => $this->sensitive($col) ? self::MASK : $this->normalize($value)];
        }

        return $fields;
    }

    private function sensitive(string $column): bool
    {
        return in_array(strtolower($column), self::SENSITIVE, true);
    }

    private function ignored(Model $model): bool
    {
        foreach (self::IGNORED_MODELS as $class) {
            if ($model instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /** Nilai apa pun -> skalar yang aman ditulis ke log (tanpa baris baru, panjang terbatas). */
    private function normalize(mixed $value): string|int|float|bool|null
    {
        return match (true) {
            $value === null                       => null,
            is_bool($value), is_int($value), is_float($value) => $value,
            $value instanceof DateTimeInterface   => $value->format('Y-m-d H:i:s'),
            is_array($value), is_object($value) && !($value instanceof \Stringable)
                                                  => ActivityDescriber::clean((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), self::MAX_VALUE),
            default                               => ActivityDescriber::clean((string) $value, self::MAX_VALUE),
        };
    }
}
