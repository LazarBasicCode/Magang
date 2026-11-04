<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Approval;
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

    /**
     * True = tampilkan versi sederhana "Saya" untuk akses "biasa" milik MAHASISWA atau DOSEN
     * (menu Rekognisi & Kerja Sama, yang dipakai kedua role). Kemahasiswaan tetap memakai isMahasiswaView().
     */
    protected function isSelfServiceView(Request $request, bool $ownScope): bool
    {
        return $ownScope && in_array($request->user()->role, ['mahasiswa', 'dosen'], true);
    }

    /**
     * Apakah data pengguna ini melewati antrean verifikasi Prodi? Mahasiswa & dosen (lihat Approval::SUBMITTER_ROLES);
     * peran lain langsung "disetujui", jadi tampilannya tanpa progres/kartu status.
     */
    protected function needsVerification(Request $request): bool
    {
        return in_array($request->user()->role, Approval::SUBMITTER_ROLES, true);
    }

    /** Filter ?status=disetujui|diproses|ditolak (kosong / "semua" = tanpa filter). */
    protected function applyStatusFilter(Builder $query, ?string $status): void
    {
        match ($status) {
            'disetujui' => $query->where('approval_status', 'disetujui'),
            'diproses'  => $query->whereIn('approval_status', ['diajukan', 'menunggu_prodi']),
            'ditolak'   => $query->where('approval_status', 'ditolak'),
            default     => null,
        };
    }

    /** @return array{total:int,disetujui:int,diproses:int,ditolak:int} */
    protected function statusSummary(Builder $query): array
    {
        $by = $query->selectRaw("COALESCE(approval_status, 'disetujui') as s, COUNT(*) as c")
            ->groupBy('s')->pluck('c', 's');

        $diproses = (int) ($by['diajukan'] ?? 0) + (int) ($by['menunggu_prodi'] ?? 0);

        return [
            'total'     => (int) $by->sum(),
            'disetujui' => (int) ($by['disetujui'] ?? 0),
            'diproses'  => $diproses,
            'ditolak'   => (int) ($by['ditolak'] ?? 0),
        ];
    }

    protected function requestedFocusId(Request $request): ?int
    {
        $id = filter_var($request->query('focus_id'), FILTER_VALIDATE_INT);

        return $id !== false && $id > 0 ? $id : null;
    }

    protected function focusPage(Builder $query, int $id, int $perPage, ?string $sortColumn = null): ?int
    {
        $model = $query->getModel();
        $key = $model->qualifyColumn($model->getKeyName());
        $sort = $sortColumn ? $model->qualifyColumn($sortColumn) : null;
        $target = (clone $query)->where($key, $id)->first(array_filter([$key, $sort]));
        if (!$target) {
            return null;
        }

        $beforeQuery = clone $query;
        if (!$sort) {
            $beforeQuery->where($key, '>', $id);
        } else {
            $sortValue = $target->{$sortColumn};
            $beforeQuery->where(function ($where) use ($key, $sort, $sortValue, $id) {
                if ($sortValue === null) {
                    $where->whereNotNull($sort)
                        ->orWhere(fn ($same) => $same->whereNull($sort)->where($key, '>', $id));
                } else {
                    $where->where($sort, '>', $sortValue)
                        ->orWhere(fn ($same) => $same->where($sort, $sortValue)->where($key, '>', $id));
                }
            });
        }
        $before = $beforeQuery->count();

        return intdiv($before, $perPage) + 1;
    }
}
