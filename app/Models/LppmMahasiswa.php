<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;

class LppmMahasiswa extends Model
{
    use HasApproval;

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
        'approval_status',
        'approval_note',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
