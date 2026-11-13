<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Superadmin / Admin
        User::firstOrCreate(
            ['role' => 'superadmin', 'nim_nidn' => 'superadmin'],
            ['name' => 'superadmin', 'password' => Hash::make('12345678')]
        );

        // 2. Akun Mahasiswa
        $mahasiswa = User::firstOrCreate(
            ['role' => 'mahasiswa', 'nim_nidn' => '222011005'],
            ['name' => 'Fauzi', 'password' => Hash::make('222011005'), 'must_change_password' => true] // password awal = NIM
        );
        $this->ensureMahasiswaProfile($mahasiswa);

        // 3. Akun Dosen
        $dosen = User::firstOrCreate(
            ['role' => 'dosen', 'nim_nidn' => '0712048901'],
            ['name' => 'Budi', 'password' => Hash::make('0712048901'), 'must_change_password' => true] // password awal = NIDN
        );
        $this->ensureDosenProfile($dosen);
    }

    private function ensureMahasiswaProfile(User $user): void
    {
        $profile = Mahasiswa::where('user_id', $user->id)->first()
            ?? Mahasiswa::where('nim', $user->nim_nidn)->first();

        if ($profile) {
            $this->linkProfileToUser($profile, $user);
            return;
        }

        Mahasiswa::create(['user_id' => $user->id, 'nim' => $user->nim_nidn]);
    }

    private function ensureDosenProfile(User $user): void
    {
        $profile = Dosen::where('user_id', $user->id)->first()
            ?? Dosen::where('nidn', $user->nim_nidn)->first();

        if ($profile) {
            $this->linkProfileToUser($profile, $user);
            return;
        }

        Dosen::create(['user_id' => $user->id, 'nidn' => $user->nim_nidn]);
    }

    private function linkProfileToUser(Mahasiswa|Dosen $profile, User $user): void
    {
        if ($profile->user_id !== null && (int) $profile->user_id !== $user->id) {
            throw new RuntimeException(
                "Profil {$profile->getKey()} sudah terhubung ke akun pengguna lain."
            );
        }

        if ($profile->user_id === null) {
            $profile->user()->associate($user);
            $profile->save();
        }
    }
}