<?php

namespace App\Models;

use App\Models\Concerns\HasApproval;
use App\Models\Concerns\HasBuktiTambahan;
use Illuminate\Database\Eloquent\Model;

class Rekognisi extends Model
{
    use HasApproval, HasBuktiTambahan;

    protected $table = 'rekognisi';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'tipe_user',
        'mitra',
        'judul',
        'penulis',
        'nama_jurnal',
        'peringkat',
        'jenis_hki',
        'kategori_buku',
        'link_doi',
        'nama_konferensi',
        'nomor_issn_isbn',
        'status_paten',
        'nomor_paten',
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
        return $this->tipe_user_efektif === 'alumni' ? ($this->jabatan ?: null) : null;
    }

    public function getTipeUserEfektifAttribute(): string
    {
        if ($this->dosen_id !== null) return 'dosen';
        if ($this->mahasiswa_id !== null) {
            return $this->mahasiswa?->status === 'lulus' ? 'alumni' : 'mahasiswa';
        }

        return $this->tipe_user;
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function getOwnerUserAttribute(): ?User
    {
        return $this->tipe_user_efektif === 'dosen'
            ? $this->dosen?->user
            : $this->mahasiswa?->user;
    }

    public function getOwnerProfileAttribute(): Mahasiswa|Dosen|null
    {
        return $this->dosen_id !== null ? $this->dosen : $this->mahasiswa;
    }

    public function getUserAttribute(): ?User
    {
        return $this->owner_user;
    }

    public function getOwnerIdentifierAttribute(): ?string
    {
        return $this->tipe_user_efektif === 'dosen'
            ? $this->dosen?->nidn
            : $this->mahasiswa?->nim;
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->owner_user?->name
            ?? ($this->owner_identifier ? 'Profil ' . $this->owner_identifier : 'Belum ada akun');
    }

    public function getOwnerRefAttribute(): ?string
    {
        if ($this->dosen_id !== null) return 'dosen:' . $this->dosen_id;
        if ($this->mahasiswa_id !== null) return 'mahasiswa:' . $this->mahasiswa_id;

        return null;
    }

    public function getUserIdAttribute(): ?int
    {
        return $this->owner_user?->id;
    }
}
