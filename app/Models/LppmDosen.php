<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LppmDosen extends Model
{
    protected $table = 'lppm_dosen';

    protected $fillable = [
        'dosen_id',
        'jenis',
        'judul',
        'penulis',
        'nama_jurnal',
        'peringkat',
        'jenis_hki',
        'kategori_buku',
        'link_doi',
        'bukti_kegiatan',
        'tahun',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}
