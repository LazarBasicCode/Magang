<?php

namespace App\Support;

use App\Models\ApprovalLog;
use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Rekognisi;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alur persetujuan pengajuan MAHASISWA:
 *
 *   mahasiswa mengisi  ->  menunggu_dosen  --(dosen wali: teruskan)-->  menunggu_superadmin  --(superadmin: setujui)-->  disetujui
 *                              |                                              |
 *                              +--(dosen wali / superadmin: tolak + catatan)--+--> ditolak --(mahasiswa edit)--> menunggu_dosen
 *
 * - Data yang diisi admin / superadmin / dosen langsung 'disetujui'.
 * - Mahasiswa tanpa dosen wali melewati langkah dosen (langsung menunggu_superadmin).
 * - Superadmin boleh memutuskan di kedua tahap (mis. dosen wali tidak aktif).
 * - Hanya 'disetujui' yang dihitung di dashboard, laporan & cetak (scope ->approved()).
 */
class Approval
{
    public const MENUNGGU_DOSEN = 'menunggu_dosen';
    public const MENUNGGU_SUPERADMIN = 'menunggu_superadmin';
    public const DISETUJUI = 'disetujui';
    public const DITOLAK = 'ditolak';

    public const STATUS = [
        self::MENUNGGU_DOSEN      => ['label' => 'Menunggu Dosen', 'badge' => 'badge-warning', 'icon' => 'hourglass_top'],
        self::MENUNGGU_SUPERADMIN => ['label' => 'Menunggu Superadmin', 'badge' => 'badge-info', 'icon' => 'pending_actions'],
        self::DISETUJUI           => ['label' => 'Disetujui', 'badge' => 'badge-success', 'icon' => 'task_alt'],
        self::DITOLAK             => ['label' => 'Ditolak', 'badge' => 'badge-danger', 'icon' => 'cancel'],
    ];

    /** @var array<int,int>|null cache jumlah antrean per user dalam satu request */
    private static array $pendingCache = [];

    // =====================================================================
    // Konfigurasi menu yang ikut alur persetujuan
    // =====================================================================

    /** @return array<string,array<string,mixed>> slug => konfigurasi */
    public static function menus(): array
    {
        $pretty = fn ($v) => $v ? ucwords(str_replace('_', ' ', (string) $v)) : '—';

        return [
            'kemahasiswaan' => [
                'model'     => Kemahasiswaan::class,
                'label'     => 'Kemahasiswaan',
                'icon'      => 'school',
                'url'       => '/kemahasiswaan',
                'with'      => ['mahasiswa.user'],
                'title'     => fn ($i) => $i->nama_kegiatan,
                'mahasiswa' => fn ($i) => $i->mahasiswa,
                'bukti'     => fn ($i) => $i->bukti_kegiatan,
                'detail'    => fn ($i) => [
                    'Jenis'   => $pretty($i->jenis),
                    'Bidang'  => $pretty($i->tab),
                    'Tingkat' => $pretty($i->tingkat),
                    'Tahun'   => $i->tahun,
                ],
                'scope'     => fn (Builder $q, int $dosenId) => $q->whereHas('mahasiswa', fn ($m) => $m->where('dosen_wali_id', $dosenId)),
            ],
            'lppm-mahasiswa' => [
                'model'     => LppmMahasiswa::class,
                'label'     => 'LPPM Mahasiswa',
                'icon'      => 'person',
                'url'       => '/lppm/mahasiswa',
                'with'      => ['mahasiswa.user'],
                'title'     => fn ($i) => $i->judul,
                'mahasiswa' => fn ($i) => $i->mahasiswa,
                'bukti'     => fn ($i) => $i->bukti_kegiatan,
                'detail'    => fn ($i) => array_filter([
                    'Jenis'      => $pretty($i->jenis),
                    'Jurnal'     => $i->nama_jurnal,
                    'Peringkat'  => $i->peringkat,
                    'Tahun'      => $i->tahun,
                    'Link DOI'   => $i->link_doi,
                ], fn ($v) => $v !== null && $v !== ''),
                'scope'     => fn (Builder $q, int $dosenId) => $q->whereHas('mahasiswa', fn ($m) => $m->where('dosen_wali_id', $dosenId)),
            ],
            'rekognisi' => [
                'model'     => Rekognisi::class,
                'label'     => 'Rekognisi',
                'icon'      => 'workspace_premium',
                'url'       => '/lppm/rekognisi',
                'with'      => ['user.mahasiswa'],
                'title'     => fn ($i) => $i->jabatan ?: $i->mitra,
                'mahasiswa' => fn ($i) => $i->user?->mahasiswa,
                'bukti'     => fn ($i) => $i->bukti_kegiatan,
                'detail'    => fn ($i) => array_filter([
                    'Mitra'   => $i->mitra,
                    'Jenis'   => $pretty($i->jenis),
                    'Jabatan' => $i->jabatan,
                    'Periode' => optional($i->tanggal_mulai)->format('d/m/Y') . ' – ' . optional($i->tanggal_selesai)->format('d/m/Y'),
                ], fn ($v) => $v !== null && $v !== ''),
                'scope'     => fn (Builder $q, int $dosenId) => $q->where('tipe_user', 'mahasiswa')
                    ->whereHas('user.mahasiswa', fn ($m) => $m->where('dosen_wali_id', $dosenId)),
            ],
            'kerja-sama' => [
                'model'     => KerjaSama::class,
                'label'     => 'Kerja Sama',
                'icon'      => 'handshake',
                'url'       => '/kerja-sama',
                'with'      => ['user.mahasiswa'],
                'title'     => fn ($i) => $i->judul_kegiatan,
                'mahasiswa' => fn ($i) => $i->user?->mahasiswa,
                'bukti'     => fn ($i) => $i->bukti_kegiatan,
                'detail'    => fn ($i) => array_filter([
                    'Jenis'   => $i->jenis === 'lainnya' ? ($i->jenis_lainnya ?: 'Lainnya') : $pretty($i->jenis),
                    'Arah'    => $i->arah ? ucfirst($i->arah) : null,
                    'Mitra'   => $i->mitra,
                    'Periode' => optional($i->tanggal_mulai)->format('d/m/Y') . ' – ' . optional($i->tanggal_selesai)->format('d/m/Y'),
                ], fn ($v) => $v !== null && $v !== ''),
                'scope'     => fn (Builder $q, int $dosenId) => $q->where('tipe_user', 'mahasiswa')
                    ->whereHas('user.mahasiswa', fn ($m) => $m->where('dosen_wali_id', $dosenId)),
            ],
        ];
    }

    public static function config(string $menu): array
    {
        $all = self::menus();
        abort_unless(isset($all[$menu]), 404, 'Menu tidak dikenal.');

        return $all[$menu];
    }

    // =====================================================================
    // Saat data diisi / diedit
    // =====================================================================

    /** Status awal pengajuan: mahasiswa -> antre; selain mahasiswa -> langsung disetujui. */
    public static function initialStatus(User $actor, ?Mahasiswa $mahasiswa): string
    {
        if ($actor->role !== 'mahasiswa') {
            return self::DISETUJUI;
        }

        return $mahasiswa?->dosen_wali_id ? self::MENUNGGU_DOSEN : self::MENUNGGU_SUPERADMIN;
    }

    /** Atribut yang ditambahkan ke data create/update. Kosong bila pelaku bukan mahasiswa (status tidak disentuh saat edit). */
    public static function attributesFor(User $actor, ?Mahasiswa $mahasiswa, bool $isCreate): array
    {
        if (!$isCreate && $actor->role !== 'mahasiswa') {
            return [];
        }

        return [
            'approval_status' => self::initialStatus($actor, $mahasiswa),
            'approval_note'   => null,
        ];
    }

    /**
     * Catat & beri tahu penerima setelah mahasiswa mengajukan / mengajukan ulang.
     * Tidak melakukan apa-apa untuk data yang langsung disetujui, atau edit yang
     * tidak mengubah posisi antrean.
     */
    public static function afterSubmit(string $menu, Model $item, User $actor, ?string $previousStatus): void
    {
        $status = $item->approval_status;
        if ($status === self::DISETUJUI || $previousStatus === $status) {
            return;
        }

        $cfg = self::config($menu);
        $isResubmit = $previousStatus !== null;
        $title = (string) $cfg['title']($item);

        self::log($menu, $item, $isResubmit ? 'diajukan_ulang' : 'diajukan', $previousStatus, $status, $actor, null);

        $verb = $isResubmit ? 'mengajukan ulang' : 'mengajukan';
        $desc = "{$actor->name} {$verb} {$cfg['label']}: \"{$title}\". Menunggu keputusan Anda.";

        foreach (self::recipientsFor($item, $cfg, $status) as $userId) {
            UserNotification::send($userId, 'approval_request', [
                'title'       => 'Pengajuan baru perlu persetujuan',
                'description' => $desc,
                'data'        => ['menu' => $menu, 'item_id' => $item->getKey(), 'actor_id' => $actor->id],
            ]);
        }
    }

    // =====================================================================
    // Aksi peninjau
    // =====================================================================

    /** @return array{0:string,1:string} [status baru, pesan] */
    public static function act(User $actor, string $menu, int $id, string $aksi, ?string $note): array
    {
        $cfg = self::config($menu);
        $note = $note !== null ? trim($note) : null;

        return DB::transaction(function () use ($actor, $menu, $cfg, $id, $aksi, $note) {
            /** @var Model $item */
            $item = $cfg['model']::with($cfg['with'])->lockForUpdate()->findOrFail($id);
            $from = $item->approval_status;
            $allowed = self::allowedActions($actor, $item, $cfg);

            if (!in_array($aksi, $allowed, true)) {
                throw ValidationException::withMessages([
                    'aksi' => 'Pengajuan ini tidak bisa diproses dengan aksi tersebut (status sekarang: ' . (self::STATUS[$from]['label'] ?? $from) . ').',
                ]);
            }
            if ($aksi === 'tolak' && mb_strlen((string) $note) < 5) {
                throw ValidationException::withMessages(['note' => 'Alasan penolakan wajib diisi (minimal 5 karakter) supaya mahasiswa tahu apa yang perlu diperbaiki.']);
            }

            $to = match ($aksi) {
                'teruskan' => self::MENUNGGU_SUPERADMIN,
                'setujui'  => self::DISETUJUI,
                'tolak'    => self::DITOLAK,
            };

            $item->forceFill([
                'approval_status' => $to,
                'approval_note'   => $note ?: null,
            ])->save();

            $action = ['teruskan' => 'diteruskan', 'setujui' => 'disetujui', 'tolak' => 'ditolak'][$aksi];
            self::log($menu, $item, $action, $from, $to, $actor, $note);
            self::notifyAfterAction($menu, $item, $cfg, $actor, $aksi, $note);
            unset(self::$pendingCache[$actor->id]);

            return [$to, [
                'teruskan' => 'Pengajuan diteruskan ke Superadmin.',
                'setujui'  => 'Pengajuan disetujui.',
                'tolak'    => 'Pengajuan ditolak dan mahasiswa diberi tahu.',
            ][$aksi]];
        });
    }

    /** Aksi yang boleh dilakukan $actor pada $item saat ini. */
    public static function allowedActions(User $actor, Model $item, array $cfg): array
    {
        $status = $item->approval_status;

        if ($actor->role === 'superadmin') {
            return in_array($status, [self::MENUNGGU_DOSEN, self::MENUNGGU_SUPERADMIN], true) ? ['setujui', 'tolak'] : [];
        }

        if ($actor->role === 'dosen' && $status === self::MENUNGGU_DOSEN) {
            $mahasiswa = $cfg['mahasiswa']($item);
            $dosenId = $actor->dosen?->id;
            if ($dosenId && $mahasiswa && (int) $mahasiswa->dosen_wali_id === (int) $dosenId) {
                return ['teruskan', 'tolak'];
            }
        }

        return [];
    }

    // =====================================================================
    // Antrean
    // =====================================================================

    /**
     * Baris antrean yang relevan untuk $user.
     *
     * @param  string  $tab   'perlu' (butuh tindakan saya) | 'proses' (sedang di tahap lain) | 'riwayat' (sudah diputuskan)
     * @return Collection<int,array<string,mixed>>
     */
    public static function queue(User $user, string $tab, ?string $menuFilter = null, int $limit = 200): Collection
    {
        $dosenId = $user->role === 'dosen' ? $user->dosen?->id : null;
        if ($user->role === 'dosen' && !$dosenId) {
            return collect();
        }

        $statuses = match (true) {
            $tab === 'perlu' && $user->role === 'superadmin'  => [self::MENUNGGU_SUPERADMIN],
            $tab === 'perlu'                                   => [self::MENUNGGU_DOSEN],
            $tab === 'proses' && $user->role === 'superadmin' => [self::MENUNGGU_DOSEN],
            $tab === 'proses'                                  => [self::MENUNGGU_SUPERADMIN],
            default                                            => [self::DISETUJUI, self::DITOLAK],
        };

        $rows = collect();
        foreach (self::menus() as $slug => $cfg) {
            if ($menuFilter && $menuFilter !== 'semua' && $menuFilter !== $slug) {
                continue;
            }

            /** @var Builder $query */
            $query = $cfg['model']::with($cfg['with'])->whereIn('approval_status', $statuses);
            if ($dosenId) {
                $cfg['scope']($query, $dosenId);
            }
            if ($tab === 'riwayat') {
                // Hanya data yang benar-benar melewati alur persetujuan (punya catatan keputusan).
                // Data lama / input admin yang otomatis 'disetujui' tidak ikut.
                $query->whereIn(
                    (new $cfg['model'])->getQualifiedKeyName(),
                    ApprovalLog::where('menu', $slug)->whereIn('action', ['disetujui', 'ditolak'])->select('item_id')
                );
            }

            foreach ($query->latest('updated_at')->limit($limit)->get() as $item) {
                $rows->push(self::row($slug, $cfg, $item, $user));
            }
        }

        return $rows->sortByDesc('updated_ts')->take($limit)->values();
    }

    /** Jumlah pengajuan yang menunggu tindakan $user (untuk lencana di sidebar). */
    public static function pendingCount(User $user): int
    {
        if (!in_array($user->role, ['dosen', 'superadmin'], true)) {
            return 0;
        }

        return self::$pendingCache[$user->id] ??= (function () use ($user) {
            $dosenId = $user->role === 'dosen' ? $user->dosen?->id : null;
            if ($user->role === 'dosen' && !$dosenId) {
                return 0;
            }

            $total = 0;
            foreach (self::menus() as $cfg) {
                $query = $cfg['model']::query()->where(
                    'approval_status',
                    $user->role === 'superadmin' ? self::MENUNGGU_SUPERADMIN : self::MENUNGGU_DOSEN
                );
                if ($dosenId) {
                    $cfg['scope']($query, $dosenId);
                }
                $total += $query->count();
            }

            return $total;
        })();
    }

    /** Jumlah per status (untuk kartu ringkasan). */
    public static function counts(User $user): array
    {
        return [
            'perlu'   => self::queue($user, 'perlu')->count(),
            'proses'  => self::queue($user, 'proses')->count(),
            'riwayat' => self::queue($user, 'riwayat')->count(),
        ];
    }

    /** Riwayat perpindahan status satu data. */
    public static function history(string $menu, int $id): Collection
    {
        return ApprovalLog::with('actor:id,name,role')->where('menu', $menu)->where('item_id', $id)->orderBy('id')->get();
    }

    // =====================================================================
    // Internal
    // =====================================================================

    private static function row(string $slug, array $cfg, Model $item, User $viewer): array
    {
        $mahasiswa = $cfg['mahasiswa']($item);
        $status = $item->approval_status;

        return [
            'menu'       => $slug,
            'menu_label' => $cfg['label'],
            'icon'       => $cfg['icon'],
            'url'        => $cfg['url'],
            'id'         => $item->getKey(),
            'title'      => (string) $cfg['title']($item),
            'name'       => $mahasiswa?->user?->name ?? $mahasiswa?->nim ?? '—',
            'nim'        => $mahasiswa?->nim,
            'detail'     => $cfg['detail']($item),
            'bukti'      => $cfg['bukti']($item),
            'status'     => $status,
            'status_label' => self::STATUS[$status]['label'] ?? $status,
            'badge'      => self::STATUS[$status]['badge'] ?? 'badge-neutral',
            'note'       => $item->approval_note,
            'submitted'  => optional($item->created_at)->translatedFormat('d M Y, H:i'),
            'updated'    => optional($item->updated_at)->translatedFormat('d M Y, H:i'),
            'updated_ts' => optional($item->updated_at)->timestamp ?? 0,
            'actions'    => self::allowedActions($viewer, $item, $cfg),
            'wali'       => $mahasiswa?->dosenWali?->user?->name,
        ];
    }

    private static function log(string $menu, Model $item, string $action, ?string $from, string $to, ?User $actor, ?string $note): void
    {
        ApprovalLog::create([
            'menu'        => $menu,
            'item_id'     => $item->getKey(),
            'action'      => $action,
            'from_status' => $from,
            'to_status'   => $to,
            'actor_id'    => $actor?->id,
            'note'        => $note ?: null,
        ]);
    }

    /** user_id penerima notifikasi pengajuan baru, sesuai tahap saat ini. */
    private static function recipientsFor(Model $item, array $cfg, string $status): array
    {
        if ($status === self::MENUNGGU_DOSEN) {
            $wali = $cfg['mahasiswa']($item)?->dosenWali?->user_id;

            return $wali ? [(int) $wali] : self::superadminIds();
        }

        return self::superadminIds();
    }

    private static function superadminIds(): array
    {
        return User::where('role', 'superadmin')->pluck('id')->map(fn ($v) => (int) $v)->all();
    }

    private static function notifyAfterAction(string $menu, Model $item, array $cfg, User $actor, string $aksi, ?string $note): void
    {
        $title = (string) $cfg['title']($item);
        $ownerId = $cfg['mahasiswa']($item)?->user_id;
        $base = ['menu' => $menu, 'item_id' => $item->getKey(), 'actor_id' => $actor->id];
        $by = "{$actor->name} ({$actor->role})";

        if ($aksi === 'teruskan') {
            foreach (self::superadminIds() as $id) {
                UserNotification::send($id, 'approval_request', [
                    'title'       => 'Pengajuan diteruskan dosen wali',
                    'description' => "{$by} meneruskan {$cfg['label']} \"{$title}\" untuk persetujuan Anda."
                        . ($note ? " Catatan: {$note}" : ''),
                    'data'        => $base,
                ]);
            }
            if ($ownerId) {
                UserNotification::send($ownerId, 'approval_request', [
                    'title'       => 'Pengajuan diteruskan ke Superadmin',
                    'description' => "\"{$title}\" sudah diperiksa dosen wali dan kini menunggu persetujuan Superadmin.",
                    'data'        => $base,
                ]);
            }

            return;
        }

        $approved = $aksi === 'setujui';
        $type = $approved ? 'approval_approved' : 'approval_rejected';
        $text = $approved
            ? "\"{$title}\" ({$cfg['label']}) disetujui oleh {$by} dan kini tampil di laporan."
            : "\"{$title}\" ({$cfg['label']}) ditolak oleh {$by}. Alasan: {$note}. Perbaiki lalu simpan ulang untuk mengajukan lagi.";

        $targets = array_filter(array_unique([
            $ownerId,
            // Dosen wali ikut diberi tahu kalau keputusan diambil superadmin.
            $actor->role === 'superadmin' ? $cfg['mahasiswa']($item)?->dosenWali?->user_id : null,
        ]));

        foreach ($targets as $id) {
            if ((int) $id === $actor->id) {
                continue;
            }
            UserNotification::send((int) $id, $type, [
                'title'       => $approved ? 'Pengajuan disetujui' : 'Pengajuan ditolak',
                'description' => $text,
                'data'        => $base,
            ]);
        }
    }
}
