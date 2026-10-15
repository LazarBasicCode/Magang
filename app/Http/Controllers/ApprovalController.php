<?php

namespace App\Http\Controllers;

use App\Support\Approval;
use Illuminate\Http\Request;

/**
 * Halaman & aksi persetujuan pengajuan mahasiswa (lihat App\Support\Approval).
 * Dosen wali: teruskan / tolak. Superadmin: setujui / tolak.
 */
class ApprovalController extends Controller
{
    private const TABS = ['perlu', 'proses', 'riwayat'];

    public function index(Request $request)
    {
        $user = $request->user();
        $this->ensureReviewer($request);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'perlu';
        $menu = (string) $request->query('menu', 'semua');
        $menus = collect(Approval::menus())->map(fn ($c) => $c['label'])->all();
        if ($menu !== 'semua' && !isset($menus[$menu])) {
            $menu = 'semua';
        }

        $rows = Approval::queue($user, $tab, $menu);
        $counts = Approval::counts($user);

        return view('persetujuan', compact('rows', 'counts', 'tab', 'menu', 'menus'));
    }

    /** POST /persetujuan/{menu}/{id}/{aksi}  aksi = teruskan | setujui | tolak */
    public function act(Request $request, string $menu, int $id, string $aksi)
    {
        $this->ensureReviewer($request);
        abort_unless(in_array($aksi, ['teruskan', 'setujui', 'tolak'], true), 404);

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
                'diteruskan'     => 'Diteruskan ke Superadmin',
                'disetujui'      => 'Disetujui',
                'ditolak'        => 'Ditolak',
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
            in_array($request->user()->role, ['dosen', 'superadmin'], true),
            403,
            'Halaman ini hanya untuk dosen wali dan superadmin.'
        );
    }
}
