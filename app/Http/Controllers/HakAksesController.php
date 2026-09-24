<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HakAksesController extends Controller
{
    /**
     * Halaman utama Hak Akses (server-rendered untuk load pertama).
     * Simpan perubahan selanjutnya lewat fetch() tanpa reload (lihat update()).
     */
    public function index(Request $request)
    {
        $users = User::with('hakAkses')->orderBy('name')->get();

        $rows = $users->map(function (User $user) {
            $levels = $user->allMenuLevels();
            $status = HakAkses::summarizeStatus($levels);

            return [
                'user'   => $user,
                'levels' => $levels,
                'status' => $status,
                'ringkasan' => [
                    'penuh'    => collect($levels)->filter(fn ($l) => $l === 'penuh')->count(),
                    'biasa'    => collect($levels)->filter(fn ($l) => $l === 'biasa')->count(),
                    'readonly' => collect($levels)->filter(fn ($l) => $l === 'readonly')->count(),
                    'none'     => collect($levels)->filter(fn ($l) => $l === 'none')->count(),
                ],
            ];
        });

        $stats = [
            'total'    => $users->count(),
            'penuh'    => $rows->filter(fn ($r) => $r['status'] === 'aktif' && in_array('penuh', $r['levels']))->count(),
            'biasa'    => $rows->filter(fn ($r) => $r['status'] === 'aktif' && !in_array('penuh', $r['levels']))->count(),
            'readonly' => $rows->filter(fn ($r) => $r['status'] === 'read')->count(),
        ];

        // Menu yang diatur (untuk membangun daftar dropdown di modal)
        $menus = collect(HakAkses::MENUS)->map(function ($m, $key) {
            return ['key' => $key, 'label' => $m['label'], 'icon' => $m['icon']];
        })->values();

        // Boleh mengedit hak akses (bukan cuma lihat) kalau level user untuk
        // menu hak_akses sendiri minimal "biasa" (superadmin otomatis lolos).
        $canManage = $request->user()->canAccessMenu('hak_akses', 'biasa');

        return view('hak-akses', compact('rows', 'stats', 'menus', 'canManage'));
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();

        // Safety: hanya superadmin yang boleh mengubah hak akses milik superadmin lain.
        if ($user->role === 'superadmin' && $actor->role !== 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya superadmin yang boleh mengubah hak akses superadmin lain.',
            ], 403);
        }

        // Superadmin selalu 'penuh' untuk semua menu (lihat User::menuLevel()),
        // jadi menyimpan baris hak_akses untuk user superadmin tidak akan pernah
        // terlihat berubah. Tolak di sini supaya jelas, bukan diam-diam no-op.
        if ($user->role === 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Superadmin selalu memiliki akses penuh ke semua menu dan tidak bisa dibatasi.',
            ], 422);
        }

        $menuKeys = array_keys(HakAkses::MENUS);

        $data = $request->validate([
            'levels'   => ['required', 'array'],
            'levels.*' => ['required', 'string', 'in:' . implode(',', HakAkses::LEVELS)],
        ]);

        // Cuma terima key menu yang valid & dikenal sistem
        $levels = array_intersect_key($data['levels'], array_flip($menuKeys));

        // Jaring pengaman: menu khusus admin/superadmin (Log, Hak Akses,
        // Data Master) dipaksa "none" untuk target mahasiswa/dosen, apa pun
        // yang dikirim dari klien — role tsb memang tidak pernah relevan
        // untuk menu-menu ini (lihat juga filter di sisi UI, hak-akses.blade.php).
        if (in_array($user->role, ['mahasiswa', 'dosen'], true)) {
            foreach (HakAkses::ADMIN_ONLY_MENUS as $adminOnlyMenu) {
                if (array_key_exists($adminOnlyMenu, $levels)) {
                    $levels[$adminOnlyMenu] = 'none';
                }
            }
        }

        DB::transaction(function () use ($user, $levels) {
            foreach ($levels as $menu => $level) {
                HakAkses::updateOrCreate(
                    ['user_id' => $user->id, 'menu' => $menu],
                    ['level' => $level]
                );
            }
        });

        $user->load('hakAkses');
        $freshLevels = $user->allMenuLevels();
        $status = HakAkses::summarizeStatus($freshLevels);

        return response()->json([
            'success' => true,
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'nim_nidn' => $user->nim_nidn,
                'role'   => $user->role,
                'status' => $status,
                'levels' => $freshLevels,
                'ringkasan' => [
                    'penuh'    => collect($freshLevels)->filter(fn ($l) => $l === 'penuh')->count(),
                    'biasa'    => collect($freshLevels)->filter(fn ($l) => $l === 'biasa')->count(),
                    'readonly' => collect($freshLevels)->filter(fn ($l) => $l === 'readonly')->count(),
                    'none'     => collect($freshLevels)->filter(fn ($l) => $l === 'none')->count(),
                ],
            ],
        ]);
    }
}
