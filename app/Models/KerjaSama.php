<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KerjaSama extends Model
{
    protected $table = 'kerja_sama';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'jenis',
        'arah',
        'mitra',
        'judul_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'bukti_kegiatan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
