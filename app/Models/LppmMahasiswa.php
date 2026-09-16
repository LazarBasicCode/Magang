<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LppmMahasiswa extends Model
{
    protected $table = 'lppm_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'judul',
        'penulis',
        'nama_jurnal',
        'peringkat',
        'link_doi',
        'bukti_kegiatan',
        'tahun',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
