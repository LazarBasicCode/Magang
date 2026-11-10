<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use App\Models\Concerns\HasBuktiTambahan;
use Illuminate\Database\Eloquent\Model;

class KerjaSama extends Model
{
    use HasApproval, HasBuktiTambahan;

    protected $table = 'kerja_sama';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'jenis',
        'jenis_lainnya',
        'skala',
        'arah',
        'mitra',
        'judul_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'bukti_kegiatan',
        'bukti_tambahan',
        'approval_status',
        'approval_note',
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
