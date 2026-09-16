<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'nim_nidn',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke tabel Mahasiswa (1 User memiliki 1 profil Mahasiswa)
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    // Relasi ke tabel Dosen (1 User memiliki 1 profil Dosen)
    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    // Relasi ke tabel Rekognisi (1 User bisa punya banyak Rekognisi)
    public function rekognisi()
    {
        return $this->hasMany(Rekognisi::class);
    }

    // Relasi ke tabel Kerja_Sama (1 User bisa menginput banyak Kerja Sama)
    // public function kerjaSama()
    // {
    //     return $this->hasMany(KerjaSama::class); // Asumsi kamu punya model KerjaSama
    // }
}