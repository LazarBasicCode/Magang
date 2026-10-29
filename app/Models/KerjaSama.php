<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;

class KerjaSama extends Model
{
    use HasApproval;

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
