<?php

namespace App\Support;

use App\Models\ApprovalComment;
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
 * Alur verifikasi pengajuan MAHASISWA (tanpa dosen):
 *
 *   mahasiswa mengirim -> Diajukan (diajukan)
 *   Prodi membuka pengajuan (modal Periksa) -> Diproses Prodi (menunggu_prodi)  [otomatis, sekali]
 *   Prodi memutuskan  -> Terverifikasi (disetujui)  |  Ditolak (ditolak, wajib catatan)
 *   Ditolak --(mahasiswa edit & simpan)--> Diajukan lagi
 *
 * - "Prodi" = superadmin (admin prodi). Ganti PRODI_ROLES bila peran lain ikut memverifikasi.
 * - Data yang diisi admin / superadmin / dosen langsung 'disetujui'.
 * - Nilai DB 'disetujui' tetap dipakai (label tampilan: "Terverifikasi") supaya scope ->approved(),
 *   dashboard, laporan & cetak tidak perlu diubah.
 */
class Approval
{
    public const DIAJUKAN = 'diajukan';
    public const MENUNGGU_PRODI = 'menunggu_prodi';
    public const DISETUJUI = 'disetujui';
    public const DITOLAK = 'ditolak';

    /** Peran yang bertindak sebagai "Prodi" (memverifikasi pengajuan). */
    public const PRODI_ROLES = ['superadmin'];

    public const PER_PAGE = 15;

    public const STATUS = [
        self::DIAJUKAN       => ['label' => 'Diajukan', 'badge' => 'badge-info', 'icon' => 'send'],
        self::MENUNGGU_PRODI => ['label' => 'Diproses Prodi', 'badge' => 'badge-warning', 'icon' => 'hourglass_top'],
        self::DISETUJUI      => ['label' => 'Terverifikasi', 'badge' => 'badge-success', 'icon' => 'check_circle'],
        self::DITOLAK        => ['label' => 'Ditolak', 'badge' => 'badge-danger', 'icon' => 'cancel'],
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
            ],
            'rekognisi' => [
                'model'     => Rekognisi::class,
                'label'     => 'Rekognisi',
                'icon'      => 'workspace_premium',
                'url'       => '/lppm/rekognisi',
                'with'      => ['user.mahasiswa'],
                'title'     => fn ($i) => $i->judul ?: ($i->jabatan ?: ($i->mitra ?: 'Rekognisi')),
                'mahasiswa' => fn ($i) => $i->user?->mahasiswa,
                'bukti'     => fn ($i) => $i->bukti_kegiatan,
                'detail'    => fn ($i) => array_filter([
                    'Tipe'     => $pretty($i->tipe_user),
                    'Jenis'    => $pretty($i->jenis),
                    'Skala'    => $pretty($i->skala),
                    'Penulis'  => $i->penulis,
                    'Jurnal'   => $i->nama_jurnal,
                    'Peringkat'=> $i->peringkat,
                    'Mitra'    => $i->mitra,
                    'Jabatan'  => $i->jabatan_efektif,
                    'Periode'  => optional($i->tanggal_mulai)->format('d/m/Y') . ' – ' . optional($i->tanggal_selesai)->format('d/m/Y'),
                ], fn ($v) => $v !== null && $v !== ''),
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
    public static function initialStatus(User $actor, ?Mahasiswa $mahasiswa = null): string
    {
        if ($actor->role !== 'mahasiswa') {
            return self::DISETUJUI;
        }

        return self::DIAJUKAN;
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
        $desc = "{$actor->name} {$verb} {$cfg['label']}: \"{$title}\". Menunggu verifikasi Prodi.";

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

            if ($aksi === 'batal' && !ApprovalLog::where('menu', $menu)->where('item_id', $item->getKey())->whereIn('action', ['disetujui', 'ditolak'])->exists()) {
                throw ValidationException::withMessages(['aksi' => 'Data ini tidak melewati alur persetujuan, jadi verifikasinya tidak bisa dibatalkan di sini.']);
            }

            $to = match ($aksi) {
                'setujui' => self::DISETUJUI,
                'tolak'   => self::DITOLAK,
                'batal'   => self::MENUNGGU_PRODI, // kembali ke antrean Prodi
            };

            $item->forceFill([
                'approval_status' => $to,
                'approval_note'   => $aksi === 'batal' ? null : ($note ?: null),
            ])->save();

            $action = ['setujui' => 'disetujui', 'tolak' => 'ditolak', 'batal' => 'dibatalkan'][$aksi];
            self::log($menu, $item, $action, $from, $to, $actor, $note);
            self::notifyAfterAction($menu, $item, $cfg, $actor, $aksi, $note);
            unset(self::$pendingCache[$actor->id]);

            return [$to, [
                'setujui'  => 'Pengajuan diverifikasi.',
                'tolak'    => 'Pengajuan ditolak dan mahasiswa diberi tahu.',
                'batal'    => 'Verifikasi dibatalkan. Pengajuan kembali ke antrean Prodi.',
            ][$aksi]];
        });
    }

    /**
     * Verifikasi banyak pengajuan sekaligus. Tiap item diproses sendiri (satu gagal tidak menggagalkan yang lain).
     *
     * @param  array<int,array{menu:string,id:int}>  $items
     * @return array{ok:int,failed:int}
     */
    public static function bulkApprove(User $actor, array $items, ?string $note): array
    {
        $ok = 0;
        $failed = 0;
        foreach (array_slice($items, 0, 100) as $it) {
            try {
                self::act($actor, (string) ($it['menu'] ?? ''), (int) ($it['id'] ?? 0), 'setujui', $note);
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return ['ok' => $ok, 'failed' => $failed];
    }

    // =====================================================================
    // Komentar
    // =====================================================================

    /** Boleh melihat komentar: Prodi, atau mahasiswa pemilik pengajuan. */
    public static function canViewComments(User $user, string $menu, int $id): bool
    {
        if (in_array($user->role, self::PRODI_ROLES, true)) {
            return true;
        }
        $cfg = self::config($menu);
        $item = $cfg['model']::with($cfg['with'])->find($id);

        return $item && (int) ($cfg['mahasiswa']($item)?->user_id) === $user->id;
    }

    /** @return Collection<int,array<string,mixed>> */
    public static function comments(string $menu, int $id, User $viewer): Collection
    {
        return ApprovalComment::with('author:id,name,role')->where('menu', $menu)->where('item_id', $id)
            ->orderBy('id')->get()->map(fn ($c) => self::commentRow($c, $viewer))->values();
    }

    /** Prodi menambah komentar (status apa pun). Pemilik diberi notifikasi. */
    public static function addComment(User $actor, string $menu, int $id, string $body): array
    {
        abort_unless(in_array($actor->role, self::PRODI_ROLES, true), 403);
        $cfg = self::config($menu);
        $item = $cfg['model']::with($cfg['with'])->findOrFail($id);

        $comment = ApprovalComment::create(['menu' => $menu, 'item_id' => $id, 'user_id' => $actor->id, 'body' => trim($body)]);
        $comment->setRelation('author', $actor);

        $ownerId = $cfg['mahasiswa']($item)?->user_id;
        if ($ownerId && (int) $ownerId !== $actor->id) {
            UserNotification::send((int) $ownerId, 'approval_comment', [
                'title'       => 'Komentar baru dari Prodi',
                'description' => '"' . $cfg['title']($item) . '" (' . $cfg['label'] . '): ' . mb_strimwidth(trim($body), 0, 120, '…'),
                'data'        => ['menu' => $menu, 'item_id' => $id, 'actor_id' => $actor->id],
            ]);
        }

        return self::commentRow($comment, $actor);
    }

    private static function commentRow(ApprovalComment $c, User $viewer): array
    {
        return [
            'id'     => $c->id,
            'body'   => $c->body,
            'author' => $c->author?->name ?? 'Pengguna dihapus',
            'role'   => $c->author?->role,
            'at'     => $c->created_at?->translatedFormat('d M Y, H:i'),
            'mine'   => (int) $c->user_id === $viewer->id,
        ];
    }

    /**
     * Prodi membuka pengajuan: 'diajukan' -> 'menunggu_prodi' (otomatis, hanya sekali).
     * Status lain dibiarkan. Dipanggil saat modal Periksa dibuka.
     *
     * @return array{0:string,1:bool} [status sekarang, apakah baru berpindah]
     */
    public static function startReview(User $actor, string $menu, int $id): array
    {
        $cfg = self::config($menu);
        abort_unless(in_array($actor->role, self::PRODI_ROLES, true), 403);

        return DB::transaction(function () use ($actor, $menu, $cfg, $id) {
            /** @var Model $item */
            $item = $cfg['model']::with($cfg['with'])->lockForUpdate()->findOrFail($id);
            $from = $item->approval_status;

            if ($from !== self::DIAJUKAN) {
                return [$from, false];
            }

            $item->forceFill(['approval_status' => self::MENUNGGU_PRODI])->save();
            self::log($menu, $item, 'diproses', $from, self::MENUNGGU_PRODI, $actor, null);

            if ($ownerId = $cfg['mahasiswa']($item)?->user_id) {
                UserNotification::send((int) $ownerId, 'approval_request', [
                    'title'       => 'Pengajuan sedang diperiksa Prodi',
                    'description' => "\"" . $cfg['title']($item) . "\" ({$cfg['label']}) sedang diperiksa Prodi.",
                    'data'        => ['menu' => $menu, 'item_id' => $item->getKey(), 'actor_id' => $actor->id],
                ]);
            }
            unset(self::$pendingCache[$actor->id]);

            return [self::MENUNGGU_PRODI, true];
        });
    }

    /** Aksi yang boleh dilakukan $actor pada $item saat ini. */
    public static function allowedActions(User $actor, Model $item, array $cfg): array
    {
        $isProdi = in_array($actor->role, self::PRODI_ROLES, true);

        if (!$isProdi) {
            return [];
        }

        return match ($item->approval_status) {
            self::DIAJUKAN, self::MENUNGGU_PRODI => ['setujui', 'tolak'],
            self::DISETUJUI                      => ['batal'], // batalkan verifikasi (hanya data yang lewat alur, dicek di act())
            default                              => [],
        };
    }

    // =====================================================================
    // Antrean
    // =====================================================================

    /**
     * Satu halaman daftar pengajuan untuk $user (filter + cari + urut + paginasi).
     *
     * @param  string  $tab  'perlu' (menunggu verifikasi Prodi) | 'riwayat' (sudah diputuskan)
     * @return array{items:array<int,array<string,mixed>>,meta:array<string,int>}
     */
    public static function listing(User $user, string $tab, string $menuFilter = 'semua', ?string $tahun = null, ?string $q = null, int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $empty = ['items' => [], 'meta' => ['page' => 1, 'last' => 1, 'total' => 0, 'from' => 0, 'to' => 0, 'per_page' => $perPage]];
        if (!in_array($user->role, self::PRODI_ROLES, true)) {
            return $empty;
        }

        $statuses = $tab === 'perlu' ? [self::DIAJUKAN, self::MENUNGGU_PRODI] : [self::DISETUJUI, self::DITOLAK];

        $rows = collect();
        foreach (self::menus() as $slug => $cfg) {
            if ($menuFilter !== 'semua' && $menuFilter !== $slug) {
                continue;
            }
            foreach (self::baseQuery($slug, $cfg, $statuses)->get() as $item) {
                $rows->push(self::row($slug, $cfg, $item, $user));
            }
        }

        if ($tahun && $tahun !== 'semua') {
            $rows = $rows->filter(fn ($r) => (string) $r['tahun'] === $tahun);
        }
        if ($q !== null && trim($q) !== '') {
            $needle = mb_strtolower(trim($q));
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name'] . ' ' . $r['nim'] . ' ' . $r['title']), $needle));
        }

        // Antrean: yang paling lama menunggu di atas. Riwayat: keputusan terbaru di atas.
        $rows = $tab === 'perlu' ? $rows->sortBy('submitted_ts') : $rows->sortByDesc('updated_ts');

        $total = $rows->count();
        $last = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $last);
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        // Jumlah komentar per pengajuan (satu query per menu)
        foreach ($items->groupBy('menu') as $slug => $group) {
            $counts = ApprovalComment::where('menu', $slug)->whereIn('item_id', $group->pluck('id'))
                ->selectRaw('item_id, COUNT(*) as c')->groupBy('item_id')->pluck('c', 'item_id');
            $items = $items->map(fn ($r) => $r['menu'] === $slug ? $r + ['comments' => (int) ($counts[$r['id']] ?? 0)] : $r);
        }
        $items = $items->map(fn ($r) => $r + ['comments' => 0])->values();

        return [
            'items' => $items->all(),
            'meta'  => [
                'page' => $page, 'last' => $last, 'total' => $total, 'per_page' => $perPage,
                'from' => $total ? ($page - 1) * $perPage + 1 : 0,
                'to'   => min($page * $perPage, $total),
            ],
        ];
    }

    /** Query dasar satu menu. Status keputusan hanya untuk data yang benar-benar melewati alur (punya log keputusan). */
    private static function baseQuery(string $slug, array $cfg, array $statuses): Builder
    {
        /** @var Builder $query */
        $query = $cfg['model']::with($cfg['with'])->whereIn('approval_status', $statuses);

        if (array_intersect($statuses, [self::DISETUJUI, self::DITOLAK])) {
            // Data lama / input admin yang otomatis 'disetujui' tidak ikut.
            $query->where(function (Builder $w) use ($slug, $cfg, $statuses) {
                $w->whereIn('approval_status', array_diff($statuses, [self::DISETUJUI, self::DITOLAK]))
                    ->orWhereIn(
                        (new $cfg['model'])->getQualifiedKeyName(),
                        ApprovalLog::where('menu', $slug)->whereIn('action', ['disetujui', 'ditolak'])->select('item_id')
                    );
            });
        }

        return $query;
    }

    /** Jumlah pengajuan yang menunggu tindakan $user (untuk lencana di sidebar). */
    public static function pendingCount(User $user): int
    {
        if (!in_array($user->role, self::PRODI_ROLES, true)) {
            return 0;
        }

        return self::$pendingCache[$user->id] ??= (function () {
            $total = 0;
            foreach (self::menus() as $cfg) {
                $total += $cfg['model']::query()->whereIn('approval_status', [self::DIAJUKAN, self::MENUNGGU_PRODI])->count();
            }

            return $total;
        })();
    }

    /** Jumlah per status (untuk kartu ringkasan). */
    public static function counts(User $user): array
    {
        $c = [self::DIAJUKAN => 0, self::MENUNGGU_PRODI => 0, self::DISETUJUI => 0, self::DITOLAK => 0];
        if (in_array($user->role, self::PRODI_ROLES, true)) {
            foreach (self::menus() as $slug => $cfg) {
                foreach (array_keys($c) as $st) {
                    $c[$st] += self::baseQuery($slug, $cfg, [$st])->count();
                }
            }
        }

        return $c + [
            'perlu'   => $c[self::DIAJUKAN] + $c[self::MENUNGGU_PRODI],
            'riwayat' => $c[self::DISETUJUI] + $c[self::DITOLAK],
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
            'tahun'      => $item->tahun ?? optional($item->created_at)->year,
            'submitted'  => optional($item->created_at)->translatedFormat('d M Y, H:i'),
            'submitted_ts' => optional($item->created_at)->timestamp ?? 0,
            'waiting'    => $item->created_at ? (int) $item->created_at->diffInDays(now(), true) : 0,
            'updated'    => optional($item->updated_at)->translatedFormat('d M Y, H:i'),
            'updated_ts' => optional($item->updated_at)->timestamp ?? 0,
            'actions'    => self::allowedActions($viewer, $item, $cfg),
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

        // Catat juga ke log aktivitas (siapa meminta/meneruskan/menyetujui/menolak apa).
        // Dibungkus try/catch: log aktivitas tidak boleh menggagalkan proses persetujuan.
        try {
            $cfg = self::config($menu);
            $owner = ($cfg['mahasiswa']($item))?->user?->name;

            ActivityLog::approval(
                $menu,
                $cfg['label'],
                (string) ($cfg['title']($item) ?? ''),
                $item->getKey(),
                $action,
                $from,
                $to,
                $actor,
                $note,
                $owner
            );
        } catch (\Throwable $e) {
            // abaikan
        }
    }

    /** user_id penerima notifikasi pengajuan baru, sesuai tahap saat ini. */
    private static function recipientsFor(Model $item, array $cfg, string $status): array
    {
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

        if ($aksi === 'batal') {
            foreach (array_filter([$ownerId]) as $id) {
                if ((int) $id !== $actor->id) {
                    UserNotification::send((int) $id, 'approval_request', [
                        'title'       => 'Verifikasi dibatalkan',
                        'description' => "Verifikasi \"{$title}\" ({$cfg['label']}) dibatalkan oleh {$by} dan diperiksa ulang." . ($note ? " Alasan: {$note}" : ''),
                        'data'        => $base,
                    ]);
                }
            }

            return;
        }

        $approved = $aksi === 'setujui';
        $type = $approved ? 'approval_approved' : 'approval_rejected';
        $text = $approved
            ? "\"{$title}\" ({$cfg['label']}) diverifikasi oleh {$by} dan kini tampil di laporan."
            : "\"{$title}\" ({$cfg['label']}) ditolak oleh {$by}. Alasan: {$note}. Perbaiki lalu simpan ulang untuk mengajukan lagi.";

        $targets = array_filter(array_unique([
            $ownerId,
        ]));

        foreach ($targets as $id) {
            if ((int) $id === $actor->id) {
                continue;
            }
            UserNotification::send((int) $id, $type, [
                'title'       => $approved ? 'Pengajuan terverifikasi' : 'Pengajuan ditolak',
                'description' => $text,
                'data'        => $base,
            ]);
        }
    }
}
