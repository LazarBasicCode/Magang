<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;

class Rekognisi extends Model
{
    use HasApproval;

    protected $table = 'rekognisi';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'mitra',
        'judul',
        'penulis',
        'nama_jurnal',
        'peringkat',
        'jenis_hki',
        'kategori_buku',
        'link_doi',
        'jenis',
        'skala',
        'jabatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun',
        'bukti_kegiatan',
        'bukti_tambahan',
        'approval_status',
        'approval_note',
    ];

    // Casting agar Laravel otomatis membacanya sebagai format tanggal (Date)
    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tahun'           => 'integer',
    ];

    /**
    * Jabatan yang berlaku hanya untuk tipe user Alumni, selain itu null.
     * Kolom `jabatan` di database sengaja tidak dihapus saat jenis diganti ke non-alumni,
     * supaya kalau dikembalikan ke alumni isinya muncul lagi. Tampilan, export, cetak & dashboard
     * memakai accessor ini ($item->jabatan_efektif), bukan $item->jabatan langsung.
     */
    public function getJabatanEfektifAttribute(): ?string
    {
        return $this->tipe_user === 'alumni' ? ($this->jabatan ?: null) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
