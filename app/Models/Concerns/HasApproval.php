<?php

namespace App\Models\Concerns;

use App\Support\Approval;
use Illuminate\Database\Eloquent\Builder;

/**
 * Dipakai model yang melewati alur persetujuan (Kemahasiswaan, LppmMahasiswa,
 * Rekognisi, KerjaSama). Wajib punya kolom approval_status & approval_note.
 */
trait HasApproval
{
    /** Hanya data yang sudah disetujui — dipakai dashboard, laporan & cetak. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where($this->getTable() . '.approval_status', Approval::DISETUJUI);
    }

    public function approvalLabel(): string
    {
        return Approval::STATUS[$this->approval_status]['label'] ?? ucfirst((string) $this->approval_status);
    }

    public function approvalBadge(): string
    {
        return Approval::STATUS[$this->approval_status]['badge'] ?? 'badge-neutral';
    }
}
