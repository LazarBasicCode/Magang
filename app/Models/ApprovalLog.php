<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Riwayat perpindahan status persetujuan (lihat App\Support\Approval). */
class ApprovalLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
