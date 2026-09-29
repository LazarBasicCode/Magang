<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use App\Models\User;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HakAksesController extends Controller
{
    use NotifiesOwner;

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

        // Superadmin selalu 'penuh' untuk semua menu (lihat User::menuLevel()),
        // jadi menyimpan baris hak_akses untuk user superadmin tidak akan
        // pernah terlihat berubah, siapa pun aktornya (termasuk sesama
        // superadmin) — bypass di menuLevel() membuat perubahan itu percuma.
        // Tolak di sini supaya jelas, bukan diam-diam no-op.
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

        // Jaring pengaman lain: seorang admin (non-superadmin) tidak boleh
        // mengubah akses menu admin-only (Log, Hak Akses, Data Master) untuk
        // AKUN ADMIN MANA PUN — dirinya sendiri maupun admin lain. Tanpa ini,
        // admin dengan akses "biasa" ke menu hak_akses bisa:
        //   a) membuka modal ini untuk akunnya sendiri dan memberi dirinya
        //      "penuh" di Data Master/Hak Akses/Log, atau
        //   b) menaikkan akses admin lain, lalu berkolusi supaya admin itu
        //      balas menaikkan akses dirinya.
        // Kedua jalur ini setara dengan menjadikan diri (atau sesama admin)
        // superadmin secara fungsional. Hanya superadmin yang boleh
        // mengubah baris admin-only untuk akun admin.
        if ($user->role === 'admin' && $actor->role !== 'superadmin') {
            foreach (HakAkses::ADMIN_ONLY_MENUS as $adminOnlyMenu) {
                if (array_key_exists($adminOnlyMenu, $levels)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya superadmin yang boleh mengubah hak akses menu admin-only (Data Master, Hak Akses, Log) untuk akun admin.',
                    ], 403);
                }
            }
        }

        $before = $user->allMenuLevels();

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

        if ($before !== $freshLevels) {
            $this->notifyUser(
                $request,
                $user->id,
                'Hak akses Anda diperbarui',
                "Hak akses menu Anda diubah oleh {$actor->name} ({$actor->role}).",
                ['target_user_id' => $user->id]
            );
        }

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