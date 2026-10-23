<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Catatan aktivitas pengguna: SIAPA melakukan APA, KAPAN, dan dari IP mana.
 *
 * Ditulis ke channel "activity" (storage/logs/activity-YYYY-MM.log, satu berkas per bulan, lihat
 * config/logging.php) — terpisah dari laravel.log supaya error aplikasi tidak
 * tertimbun catatan aktivitas. Berkas ini otomatis muncul di pemilih berkas
 * pada halaman Log Aplikasi (superadmin).
 *
 * Yang dicatat: login/logout, tambah/ubah/hapus data, unggah & unduh Excel,
 * cetak, persetujuan, hak akses, backup, ganti password. Yang TIDAK pernah
 * dicatat: password, token, atau isi formulir.
 *
 * Mencatat tidak boleh sampai merusak proses utama, jadi semua error di sini
 * ditelan (lihat try/catch di record()).
 */
final class ActivityLog
{
    public const CHANNEL = 'activity';

    /**
     * @param  string  $action   kode singkat (create|update|delete|upload|export|login|...) untuk filter/pencarian
     * @param  string  $text     kalimat setelah nama pelaku, mis. 'menghapus data Kemahasiswaan "X" (#3)'
     * @param  array   $context  detail tambahan (id, status, dsb.) — tampil sebagai "detail" di viewer
     * @param  string  $level    'info' (normal) atau 'warning' (gagal / ditolak / mencurigakan)
     * @param  User|null  $actor  default: user yang sedang login
     */
    public static function record(
        string $action,
        string $text,
        array $context = [],
        string $level = 'info',
        ?User $actor = null
    ): void {
        try {
            $actor ??= auth()->user();
            $level = $level === 'warning' ? 'warning' : 'info';
            $request = app()->bound('request') ? request() : null;

            $base = [
                'aksi'    => $action,
                'user_id' => $actor?->id,
                'role'    => $actor?->role,
                'ip'      => $request?->ip(),
            ];
            if ($request && ($route = $request->route())) {
                $base['rute'] = $route->getName();
            }

            // Isi detail kosong tidak perlu ditulis.
            $context = array_filter(
                array_merge($base, $context),
                fn ($v) => $v !== null && $v !== ''
            );

            Log::channel(self::CHANNEL)->{$level}(self::who($actor) . ' ' . $text, $context);
        } catch (\Throwable $e) {
            // Sengaja diam: gagal mencatat tidak boleh menggagalkan aksi pengguna.
        }
    }

    /** "Budi Santoso (mahasiswa)" — nama dibersihkan agar tidak bisa menyisipkan baris palsu ke log. */
    public static function who(?User $actor): string
    {
        if (!$actor) {
            return 'Pengunjung (belum login)';
        }

        return ActivityDescriber::clean((string) $actor->name, 60) . ' (' . ($actor->role ?: 'tanpa role') . ')';
    }

    /** Ambil ['type','id','label'] dari sebuah model untuk dipakai ActivityDescriber. */
    public static function subjectOf(Model $model): array
    {
        return [
            'type'  => class_basename($model),
            'id'    => $model->getKey(),
            'label' => ActivityDescriber::labelFrom($model->getAttributes()),
        ];
    }

    /**
     * Dipanggil dari Approval::log() untuk SETIAP perpindahan status pengajuan
     * (diajukan, diajukan_ulang, diteruskan, disetujui, ditolak).
     */
    public static function approval(
        string $menu,
        string $menuLabel,
        string $title,
        string|int $itemId,
        string $action,
        ?string $from,
        string $to,
        ?User $actor,
        ?string $note,
        ?string $ownerName = null
    ): void {
        $item = '"' . ActivityDescriber::clean($title, 90) . "\" (#{$itemId})";
        $owner = $ownerName ? ' milik ' . ActivityDescriber::clean($ownerName, 60) : '';

        $text = match ($action) {
            'diajukan'       => "meminta persetujuan {$menuLabel} {$item}{$owner}",
            'diajukan_ulang' => "mengajukan ulang {$menuLabel} {$item}{$owner} untuk persetujuan",
            'diteruskan'     => "meneruskan pengajuan {$menuLabel} {$item}{$owner} ke Superadmin",
            'disetujui'      => "menyetujui pengajuan {$menuLabel} {$item}{$owner}",
            'ditolak'        => "menolak pengajuan {$menuLabel} {$item}{$owner}",
            default          => "memproses pengajuan {$menuLabel} {$item}{$owner} ({$action})",
        };

        if ($action === 'ditolak' && $note) {
            $text .= ' — alasan: ' . ActivityDescriber::clean($note, 200);
        }

        self::record('approval.' . $action, $text, [
            'menu'    => $menu,
            'item_id' => $itemId,
            'dari'    => $from,
            'ke'      => $to,
        ], $action === 'ditolak' ? 'warning' : 'info', $actor);
    }
}
