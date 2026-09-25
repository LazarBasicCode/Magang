<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'color',
        'icon',
        'title',
        'description',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'created_at' => 'datetime',
        'read_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Helper singkat supaya controller lain tidak perlu tahu detail kolom
     * (color/icon default, dsb) tiap kali mau kirim notifikasi.
     *
     * Dipakai misalnya:
     *   UserNotification::send($mahasiswa->user_id, 'data_updated', [
     *       'title' => 'Data Anda diperbarui',
     *       'description' => "Diubah oleh {$actor->name}",
     *   ]);
     */
    public static function send(int $userId, string $type, array $attrs): self
    {
        $defaults = match ($type) {
            'data_updated'      => ['color' => 'warning', 'icon' => 'edit_note'],
            'concurrent_login'  => ['color' => 'danger', 'icon' => 'gpp_maybe'],
            'account_deleted'   => ['color' => 'danger', 'icon' => 'person_remove'],
            default              => ['color' => 'primary', 'icon' => 'notifications'],
        };

        return static::create(array_merge([
            'user_id' => $userId,
            'type'    => $type,
        ], $defaults, $attrs));
    }
}
