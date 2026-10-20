<?php

namespace App\Http\Controllers;

use App\Support\Approval;
use Illuminate\Http\Request;

/**
 * Halaman & aksi persetujuan pengajuan mahasiswa (lihat App\Support\Approval).
 * Prodi (superadmin): verifikasi (setujui) / tolak.
 */
class ApprovalController extends Controller
{
    private const TABS = ['perlu', 'riwayat'];

    /** GET /persetujuan — halaman; dengan Accept: JSON mengembalikan satu halaman data (filter, cari, paginasi). */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->ensureReviewer($request);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'perlu';
        $menus = collect(Approval::menus())->map(fn ($c) => $c['label'])->all();
        $menu = (string) $request->query('menu', 'semua');
        if ($menu !== 'semua' && !isset($menus[$menu])) {
            $menu = 'semua';
        }
        $tahun = preg_match('/^\d{4}$/', (string) $request->query('tahun')) ? (string) $request->query('tahun') : 'semua';
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $page = max(1, (int) $request->query('page', 1));

        $data = Approval::listing($user, $tab, $menu, $tahun, $q, $page);
        $counts = Approval::counts($user);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'items' => $data['items'], 'meta' => $data['meta'], 'counts' => $counts]);
        }

        $initial = ['items' => $data['items'], 'meta' => $data['meta'], 'counts' => $counts];

        return view('persetujuan', compact('initial', 'counts', 'tab', 'menu', 'menus'));
    }

    /** POST /persetujuan/{menu}/{id}/{aksi}  aksi = setujui | tolak | batal */
    public function act(Request $request, string $menu, int $id, string $aksi)
    {
        $this->ensureReviewer($request);
        abort_unless(in_array($aksi, ['setujui', 'tolak', 'batal'], true), 404);

        $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        [$status, $message] = Approval::act($request->user(), $menu, $id, $aksi, $request->input('note'));

        return response()->json([
            'success' => true,
            'status'  => $status,
            'message' => $message,
            'pending' => Approval::pendingCount($request->user()),
            'counts'  => Approval::counts($request->user()),
        ]);
    }

    /** POST /persetujuan/massal — verifikasi banyak pengajuan sekaligus. */
    public function bulk(Request $request)
    {
        $this->ensureReviewer($request);

        $data = $request->validate([
            'items'        => ['required', 'array', 'min:1', 'max:100'],
            'items.*.menu' => ['required', 'string', 'max:30'],
            'items.*.id'   => ['required', 'integer'],
            'note'         => ['nullable', 'string', 'max:1000'],
        ]);

        $r = Approval::bulkApprove($request->user(), $data['items'], $data['note'] ?? null);

        $message = "{$r['ok']} pengajuan diverifikasi" . ($r['failed'] ? ", {$r['failed']} dilewati (status sudah berubah)." : '.');

        return response()->json([
            'success' => $r['ok'] > 0,
            'ok'      => $r['ok'],
            'failed'  => $r['failed'],
            'message' => $r['ok'] > 0 ? $message : 'Tidak ada pengajuan yang bisa diverifikasi (status sudah berubah).',
            'pending' => Approval::pendingCount($request->user()),
            'counts'  => Approval::counts($request->user()),
        ], $r['ok'] > 0 ? 200 : 422);
    }

    /** GET /persetujuan/{menu}/{id}/komentar — Prodi atau mahasiswa pemilik. */
    public function comments(Request $request, string $menu, int $id)
    {
        abort_unless(Approval::canViewComments($request->user(), $menu, $id), 403);

        return response()->json(['success' => true, 'data' => Approval::comments($menu, $id, $request->user())]);
    }

    /** POST /persetujuan/{menu}/{id}/komentar — hanya Prodi, status apa pun. */
    public function addComment(Request $request, string $menu, int $id)
    {
        $this->ensureReviewer($request);
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:1000']], [
            'body.required' => 'Komentar tidak boleh kosong.',
            'body.min'      => 'Komentar terlalu pendek.',
        ]);

        return response()->json(['success' => true, 'data' => Approval::addComment($request->user(), $menu, $id, $data['body'])]);
    }

    /** POST /persetujuan/{menu}/{id}/mulai — Prodi membuka pengajuan: diajukan -> diproses (otomatis, sekali). */
    public function start(Request $request, string $menu, int $id)
    {
        $this->ensureReviewer($request);

        [$status, $changed] = Approval::startReview($request->user(), $menu, $id);

        return response()->json([
            'success'      => true,
            'changed'      => $changed,
            'status'       => $status,
            'status_label' => Approval::STATUS[$status]['label'] ?? $status,
            'pending'      => Approval::pendingCount($request->user()),
            'counts'       => Approval::counts($request->user()),
        ]);
    }

    /** GET /persetujuan/{menu}/{id}/riwayat — linimasa perpindahan status satu pengajuan. */
    public function history(Request $request, string $menu, int $id)
    {
        $this->ensureReviewer($request);
        Approval::config($menu); // 404 bila menu tidak dikenal

        $logs = Approval::history($menu, $id)->map(fn ($l) => [
            'action' => $l->action,
            'label'  => [
                'diajukan'       => 'Diajukan',
                'diajukan_ulang' => 'Diajukan ulang',
                'diproses'       => 'Mulai diperiksa Prodi',
                'disetujui'      => 'Terverifikasi',
                'ditolak'        => 'Ditolak',
                'dibatalkan'     => 'Verifikasi dibatalkan',
            ][$l->action] ?? $l->action,
            'actor'  => $l->actor?->name ?? 'Sistem',
            'role'   => $l->actor?->role,
            'note'   => $l->note,
            'at'     => $l->created_at?->translatedFormat('d M Y, H:i'),
        ])->values();

        return response()->json(['success' => true, 'data' => $logs]);
    }

    private function ensureReviewer(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, Approval::PRODI_ROLES, true),
            403,
            'Halaman ini hanya untuk Prodi (superadmin).'
        );
    }
}
