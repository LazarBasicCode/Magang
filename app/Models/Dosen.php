<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = ['user_id', 'nidn', 'status'];

    /** Status dosen: nilai di DB => label tampilan. Urutan = urutan di dropdown. */
    public const STATUS = [
        'aktif'         => 'Aktif',
        'tugas_belajar' => 'Tugas Belajar',
        'cuti'          => 'Cuti',
        'pensiun'       => 'Pensiun',
        'non_aktif'     => 'Non-Aktif',
    ];

    /** Kelas badge (lihat style.css) per status. */
    public const STATUS_BADGE = [
        'aktif'         => 'badge-success',
        'tugas_belajar' => 'badge-info',
        'cuti'          => 'badge-warning',
        'pensiun'       => 'badge-neutral',
        'non_aktif'     => 'badge-neutral',
    ];

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGE[$this->status] ?? 'badge-neutral';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lppm()
    {
        return $this->hasMany(LppmDosen::class, 'dosen_id');
    }
}
