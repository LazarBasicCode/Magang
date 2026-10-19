<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Bantuan untuk tampilan khusus MAHASISWA (resources/views/mahasiswa/*):
 * filter status persetujuan & ringkasan jumlah per status.
 */
trait MahasiswaView
{
    /** True = tampilkan versi sederhana untuk mahasiswa (akses "biasa" milik role mahasiswa). */
    protected function isMahasiswaView(Request $request, bool $ownScope): bool
    {
        return $ownScope && $request->user()->role === 'mahasiswa';
    }

    /** Filter ?status=disetujui|diproses|ditolak (kosong / "semua" = tanpa filter). */
    protected function applyStatusFilter(Builder $query, ?string $status): void
    {
        match ($status) {
            'disetujui' => $query->where('approval_status', 'disetujui'),
            'diproses'  => $query->whereIn('approval_status', ['menunggu_dosen', 'menunggu_superadmin']),
            'ditolak'   => $query->where('approval_status', 'ditolak'),
            default     => null,
        };
    }

    /** @return array{total:int,disetujui:int,diproses:int,ditolak:int} */
    protected function statusSummary(Builder $query): array
    {
        $by = $query->selectRaw("COALESCE(approval_status, 'disetujui') as s, COUNT(*) as c")
            ->groupBy('s')->pluck('c', 's');

        $diproses = (int) ($by['menunggu_dosen'] ?? 0) + (int) ($by['menunggu_superadmin'] ?? 0);

        return [
            'total'     => (int) $by->sum(),
            'disetujui' => (int) ($by['disetujui'] ?? 0),
            'diproses'  => $diproses,
            'ditolak'   => (int) ($by['ditolak'] ?? 0),
        ];
    }
}
