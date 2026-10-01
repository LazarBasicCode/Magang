<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Halaman utama Data Master Pengguna (server-rendered untuk load pertama).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     */
    public function index(Request $request)
    {
        $users = User::with(['mahasiswa', 'dosen'])->latest()->paginate(10);

        $stats = [
            'total'     => User::count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen'     => User::where('role', 'dosen')->count(),
            'admin'     => User::whereIn('role', ['admin', 'superadmin'])->count(),
        ];

        return view('data-master-users', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, isUpdate: false);

        // Admin cuma boleh membuat akun dosen/mahasiswa. Tanpa cek ini, admin
        // bisa membuat akun admin/superadmin baru lewat form yang sama
        // (privilege escalation via akun baru, bukan cuma edit akun lama).
        $actor = $request->user();
        if ($actor->role !== 'superadmin' && !in_array($data['role'], ['mahasiswa', 'dosen'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya boleh membuat akun dengan role Dosen atau Mahasiswa.',
            ], 403);
        }

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'nim_nidn' => $data['identifier'] ?? null,
                'email'    => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            $this->syncIdentifier($user, $data['role'], $data['identifier'] ?? null);

            return $user->load('mahasiswa', 'dosen');
        });

        return response()->json([
            'success' => true,
            'data'    => $this->format($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();

        // --- Otorisasi berbasis hierarki role, tidak cukup hanya level menu ---

        // (6) Admin tidak boleh menaikkan role dirinya sendiri (atau mengubah
        // role dirinya sama sekali) lewat form ini. Dicek sebelum aturan
        // umum di bawah karena kasus "edit diri sendiri" butuh pesan
        // spesifik dan tidak boleh disamakan dengan "kelola user lain".
        if ($actor->id === $user->id) {
            // Pakai filled(), bukan input(), sebagai jaring pengaman: kalau
            // request tidak mengirim 'role' sama sekali (misal klien API lain
            // di luar form saat ini, atau form berubah nanti), kita tidak mau
            // membandingkan null !== $actor->role (selalu true) yang akan
            // memblokir dengan pesan "ubah role" padahal masalahnya cuma
            // field hilang. validated() di bawah tetap akan menolak request
            // semacam itu dengan pesan yang benar (role wajib diisi).
            if ($actor->role !== 'superadmin' && $request->filled('role') && $request->input('role') !== $actor->role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak boleh mengubah role akun Anda sendiri.',
                ], 403);
            }
        } elseif (!$actor->canManageTargetUser($user)) {
            // (1) (2) (3) Admin tidak boleh mengelola superadmin, sesama
            // admin, atau siapa pun di luar dosen/mahasiswa. Dosen/mahasiswa
            // tidak boleh mengelola user lain sama sekali.
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk mengubah data pengguna ini.',
            ], 403);
        }

        $data = $this->validated($request, isUpdate: true, user: $user);

        // (3) Admin hanya boleh menempatkan/mempertahankan target sebagai
        // dosen/mahasiswa — mencegah admin "menaikkan" user lain menjadi
        // admin/superadmin lewat field role pada form edit ini.
        if ($actor->role !== 'superadmin' && $actor->id !== $user->id
            && !in_array($data['role'], ['mahasiswa', 'dosen'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya boleh menetapkan role Dosen atau Mahasiswa.',
            ], 403);
        }

        // (5) Superadmin terakhir tidak boleh "dilucuti" jadi role lain
        // lewat edit role (setara dengan menghapusnya secara fungsional).
        if ($user->role === 'superadmin' && $data['role'] !== 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mengubah role superadmin terakhir yang tersisa di sistem.',
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $user, $data) {
            $user->name = $data['name'];
            $user->role = $data['role'];
            $user->nim_nidn = $data['identifier'] ?? null;
            $user->email = $data['email'] ?? null;
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            $this->syncIdentifier($user, $data['role'], $data['identifier'] ?? null);

            // Beri tahu pemilik akun kalau datanya diubah oleh orang lain
            // (admin/superadmin) — bukan oleh dirinya sendiri.
            $actor = $request->user();
            if ($actor && $actor->id !== $user->id) {
                UserNotification::send($user->id, 'data_updated', [
                    'title'       => 'Data akun Anda diperbarui',
                    'description' => "Diubah oleh {$actor->name} ({$actor->role}).",
                    'data'        => ['actor_id' => $actor->id, 'actor_name' => $actor->name],
                ]);
            }
        });

        $user->load('mahasiswa', 'dosen');

        return response()->json([
            'success' => true,
            'data'    => $this->format($user),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();

        // (4) User tidak boleh menghapus dirinya sendiri lewat Data Master.
        if ($actor->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri.',
            ], 403);
        }

        // (1) (2) (3) Bug utama: middleware menu.access:data_master,biasa
        // hanya memastikan admin PUNYA akses ke menu Data Master — bukan
        // memeriksa SIAPA yang boleh mereka hapus. Tanpa cek ini, admin
        // dengan level "biasa" bisa menghapus superadmin atau admin lain.
        if (!$actor->canManageTargetUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menghapus pengguna ini.',
            ], 403);
        }

        // (5) Superadmin terakhir tidak boleh dihapus, siapa pun pelakunya
        // (termasuk sesama superadmin), supaya sistem tidak pernah
        // kehilangan seluruh akses superadmin-nya.
        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus superadmin terakhir yang tersisa di sistem.',
                ], 422);
            }
        }

        $id = $user->id;
        $user->mahasiswa()->delete();
        $user->dosen()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    private function validated(Request $request, bool $isUpdate, ?User $user = null): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'role'       => ['required', 'in:superadmin,admin,dosen,mahasiswa'],
            'password'   => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6'],
            // Wajib untuk SEMUA role: kolom ini (users.nim_nidn) dipakai sebagai
            // kredensial login. Admin/superadmin tanpa nilai ini tidak akan
            // pernah bisa login. Harus unik supaya login tidak ambigu.
            'identifier' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'nim_nidn')->ignore($user?->id),
            ],
            // Email opsional — dipakai untuk fitur lupa password. Kalau diisi,
            // harus unik supaya tautan reset tidak salah sasaran ke akun lain.
            'email' => [
                'nullable', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
        ]);
    }

    /**
     * Sinkronkan baris mahasiswa/dosen sesuai role terbaru.
     * Kalau role berubah, baris relasi lama yang tidak relevan dihapus.
     * Catatan: users.nim_nidn ditulis terpisah di store()/update() karena
     * kolom itu ada langsung di tabel users, di luar tabel mahasiswa/dosen.
     */
    private function syncIdentifier(User $user, string $role, ?string $identifier): void
    {
        if ($role !== 'mahasiswa') {
            Mahasiswa::where('user_id', $user->id)->delete();
        }
        if ($role !== 'dosen') {
            Dosen::where('user_id', $user->id)->delete();
        }

        if ($role === 'mahasiswa') {
            Mahasiswa::updateOrCreate(
                ['user_id' => $user->id],
                ['nim' => $identifier]
            );
        } elseif ($role === 'dosen') {
            Dosen::updateOrCreate(
                ['user_id' => $user->id],
                ['nidn' => $identifier]
            );
        }
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'role'       => $user->role,
            'identifier' => $user->nim_nidn,
            'email'      => $user->email,
        ];
    }
}