<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = ['user_id', 'nim', 'angkatan', 'status'];

    /** Status akademik: nilai di DB => label tampilan. Urutan = urutan di dropdown. */
    public const STATUS = [
        'aktif'             => 'Aktif',
        'cuti'              => 'Cuti',
        'lulus'             => 'Lulus',
        'non_aktif'         => 'Non-Aktif',
        'drop_out'          => 'Drop Out',
        'mengundurkan_diri' => 'Mengundurkan Diri',
    ];

    /** Kelas badge (lihat style.css) per status. */
    public const STATUS_BADGE = [
        'aktif'             => 'badge-success',
        'cuti'              => 'badge-warning',
        'lulus'             => 'badge-info',
        'non_aktif'         => 'badge-neutral',
        'drop_out'          => 'badge-danger',
        'mengundurkan_diri' => 'badge-neutral',
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

    public function kemahasiswaan()
    {
        return $this->hasMany(Kemahasiswaan::class);
    }

    public function lppmMahasiswa()
    {
        return $this->hasMany(LppmMahasiswa::class);
    }
}
