<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Mahasiswa & dosen login HANYA dengan NIM/NIDN + password (bukan nama).
 * Admin & superadmin tetap boleh memakai nim_nidn atau nama akunnya.
 */
class LoginIdentityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $nim, string $name, string $role): User
    {
        return User::create([
            'name'     => $name,
            'nim_nidn' => $nim,
            'password' => Hash::make('secret123'),
            'role'     => $role,
        ]);
    }

    public function test_mahasiswa_login_dengan_nim(): void
    {
        $u = $this->makeUser('222011005', 'Ahmad Fauzi', 'mahasiswa');

        $this->post('/login-process', ['username' => '222011005', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($u);
    }

    public function test_dosen_login_dengan_nidn(): void
    {
        $u = $this->makeUser('0712048901', 'Dr. Budi Santoso', 'dosen');

        $this->post('/login-process', ['username' => '0712048901', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($u);
    }

    public function test_mahasiswa_tidak_bisa_login_dengan_nama(): void
    {
        $this->makeUser('222011005', 'Ahmad Fauzi', 'mahasiswa');

        $this->from('/')->post('/login-process', ['username' => 'Ahmad Fauzi', 'password' => 'secret123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_dosen_tidak_bisa_login_dengan_nama(): void
    {
        $this->makeUser('0712048901', 'Dr. Budi Santoso', 'dosen');

        $this->from('/')->post('/login-process', ['username' => 'dr. budi santoso', 'password' => 'secret123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_password_salah_tetap_ditolak_dengan_pesan_umum(): void
    {
        $this->makeUser('222011005', 'Ahmad Fauzi', 'mahasiswa');

        $this->from('/')->post('/login-process', ['username' => '222011005', 'password' => 'salah'])
            ->assertSessionHasErrors(['username' => 'Username/NIM/NIDN atau password salah.']);
        $this->assertGuest();
    }

    public function test_admin_tetap_bisa_login_dengan_nama_akun(): void
    {
        $admin = $this->makeUser('admin.kemahasiswaan', 'Admin Kemahasiswaan', 'admin');

        $this->post('/login-process', ['username' => 'admin kemahasiswaan', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_superadmin_bisa_login_dengan_nama_akun_saat_tidak_memiliki_nim_nidn(): void
    {
        $superadmin = User::create([
            'name'     => 'Super Admin',
            'nim_nidn' => null,
            'password' => Hash::make('secret123'),
            'role'     => 'superadmin',
        ]);

        $this->post('/login-process', ['username' => 'super admin', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($superadmin);
    }
}
