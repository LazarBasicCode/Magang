<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kemahasiswaan extends Model
{
    protected $table = 'kemahasiswaan';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'tab',
        'tingkat',
        'tahun',
        'nama_kegiatan',
        'bukti_kegiatan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
