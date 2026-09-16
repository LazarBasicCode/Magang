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
        'tgl_mulai',
        'tgl_selesai',
        'bukti_kegiatan',
        'bukti_tambahan'
    ];

    // Casting agar Laravel otomatis membacanya sebagai format tanggal (Date)
    protected $casts = [
        'tgl_mulai'   => 'date',
        'tgl_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}