<?php

namespace App\Http\Controllers\Concerns;

use App\Models\UserNotification;
use Illuminate\Http\Request;

/**
 * Notifikasi "data Anda diubah oleh orang lain" yang dipakai bersama oleh
 * semua controller menu (Rekognisi, Kemahasiswaan, Kerja Sama, LPPM, dst).
 *
 * Aturan: notifikasi hanya dikirim kalau pelaku (user yang login) BUKAN
 * pemilik data — jadi kalau mahasiswa mengisi datanya sendiri, tidak ada
 * notifikasi; kalau admin/staf yang mengelola, pemilik diberi tahu.
 */
trait NotifiesOwner
{
    /**
     * @param  int|null  $ownerUserId  users.id pemilik data (BUKAN mahasiswa_id/dosen_id)
     * @param  string    $entity       nama data di judul, mis. "rekognisi", "kegiatan"
     * @param  string    $label        nama item yang tampil di deskripsi
     * @param  string    $aksi         "ditambahkan" | "diperbarui" | "dihapus"
     * @param  string    $detail       keterangan tambahan dalam kurung, opsional
     * @param  array     $extra        data tambahan (mis. ['rekognisi_id' => 5])
     */
    protected function notifyOwner(
        Request $request,
        ?int $ownerUserId,
        string $entity,
        string $label,
        string $aksi,
        string $detail = '',
        array $extra = []
    ): void {
        $actor = $request->user();

        if (!$actor || !$ownerUserId || $actor->id === $ownerUserId) {
            return;
        }

        $detailText = $detail !== '' ? " ({$detail})" : '';
        $menu = match (true) {
            isset($extra['kemahasiswaan_id']) => 'kemahasiswaan',
            isset($extra['rekognisi_id']), isset($extra['lppm_mahasiswa_id']), isset($extra['lppm_dosen_id']) => 'rekognisi',
            isset($extra['kerja_sama_id']) => 'kerja-sama',
            default => null,
        };
        $itemId = $menu ? ($extra[match ($menu) {
            'kemahasiswaan' => 'kemahasiswaan_id',
            'rekognisi' => isset($extra['rekognisi_id']) ? 'rekognisi_id' : (isset($extra['lppm_mahasiswa_id']) ? 'lppm_mahasiswa_id' : 'lppm_dosen_id'),
            'kerja-sama' => 'kerja_sama_id',
        }] ?? null) : null;

        UserNotification::send($ownerUserId, 'data_updated', [
            'title'       => "Data {$entity} Anda {$aksi}",
            'description' => "\"{$label}\"{$detailText} {$aksi} oleh {$actor->name} ({$actor->role}).",
            'data'        => array_merge(
                array_filter([
                    'actor_id' => $actor->id,
                    'actor_name' => $actor->name,
                    'menu' => $menu,
                    'item_id' => $itemId,
                    'record_action' => $aksi,
                ], fn ($value) => $value !== null),
                $extra
            ),
        ]);
    }

    /**
     * Notifikasi bebas untuk satu user (tanpa format "data ... Anda"),
     * tetap dilewati kalau pelakunya user itu sendiri.
     */
    protected function notifyUser(Request $request, int $targetUserId, string $title, string $description, array $extra = []): void
    {
        $actor = $request->user();

        if (!$actor || $actor->id === $targetUserId) {
            return;
        }

        UserNotification::send($targetUserId, 'data_updated', [
            'title'       => $title,
            'description' => $description,
            'data'        => array_merge(
                ['actor_id' => $actor->id, 'actor_name' => $actor->name],
                $extra
            ),
        ]);
    }
}