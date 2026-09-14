<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Superadmin / Admin
        User::create([
            'name' => 'Admin Kemahasiswaan',
            'nim_nidn' => 'admin.kemahasiswaan',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        // 2. Akun Mahasiswa
        User::create([
            'name' => 'Ahmad Rizal Fauzi',
            'nim_nidn' => '222011005',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa'
        ]);

        // 3. Akun Dosen
        User::create([
            'name' => 'Dr. Budi Santoso',
            'nim_nidn' => '0712048901',
            'password' => Hash::make('password123'),
            'role' => 'dosen'
        ]);
    }
}