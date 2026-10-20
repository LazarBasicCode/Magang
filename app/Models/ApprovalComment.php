<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Komentar pada satu pengajuan (lihat App\Support\Approval::addComment). */
class ApprovalComment extends Model
{
    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
