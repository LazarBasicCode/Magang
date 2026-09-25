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
        $data = $this->validated($request, isUpdate: true, user: $user);

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

    public function destroy(User $user)
    {
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
            'identifier' => [
                Rule::requiredIf(fn () => in_array($request->role, ['mahasiswa', 'dosen'])),
                'nullable', 'string', 'max:50',
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