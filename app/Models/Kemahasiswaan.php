<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;

class Kemahasiswaan extends Model
{
    use HasApproval;

    protected $table = 'kemahasiswaan';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'tab',
        'tingkat',
        'tahun',
        'nama_kegiatan',
        'bukti_kegiatan',
        'approval_status',
        'approval_note',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
