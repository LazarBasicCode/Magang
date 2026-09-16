<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = ['user_id', 'nim'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kemahasiswaan()
    {
        return $this->hasMany(Kemahasiswaan::class);
    }
}
