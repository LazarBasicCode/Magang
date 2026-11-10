<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use App\Models\Concerns\HasBuktiTambahan;
use Illuminate\Database\Eloquent\Model;

class Kemahasiswaan extends Model
{
    use HasApproval, HasBuktiTambahan;

    protected $table = 'kemahasiswaan';

    protected $fillable = [
        'mahasiswa_id',
        'jenis',
        'tab',
        'tingkat',
        'tahun',
        'nama_kegiatan',
        'bukti_kegiatan',
        'bukti_tambahan',
        'approval_status',
        'approval_note',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
