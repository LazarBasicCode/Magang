<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekognisi extends Model
{
    protected $table = 'rekognisi';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'mitra',
        'jenis',
        'jabatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'bukti_kegiatan',
        'bukti_tambahan',
    ];

    // Casting agar Laravel otomatis membacanya sebagai format tanggal (Date)
    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Jabatan yang BERLAKU: hanya untuk jenis alumni, selain itu null.
     * Kolom `jabatan` di database sengaja tidak dihapus saat jenis diganti ke non-alumni,
     * supaya kalau dikembalikan ke alumni isinya muncul lagi. Tampilan, export, cetak & dashboard
     * memakai accessor ini ($item->jabatan_efektif), bukan $item->jabatan langsung.
     */
    public function getJabatanEfektifAttribute(): ?string
    {
        return $this->jenis === 'alumni' ? ($this->jabatan ?: null) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
