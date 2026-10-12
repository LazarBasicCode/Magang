<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NeverLoggedInBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $name, string $nim, string $role = 'mahasiswa'): User
    {
        return User::create([
            'name' => $name, 'nim_nidn' => $nim,
            'password' => Hash::make('secret123'), 'role' => $role,
        ]);
    }

    public function test_gembok_hanya_muncul_untuk_akun_yang_belum_pernah_login(): void
    {
        $admin  = $this->makeUser('Super Admin', '9000', 'superadmin');
        $pernah = $this->makeUser('Sudah Login', '1001');
        $this->makeUser('Belum Login', '1002');

        LoginAttempt::create([
            'user_id' => $pernah->id, 'username_input' => '1001',
            'ip_address' => '127.0.0.1', 'status' => 'success',
        ]);
        // percobaan gagal tidak dihitung sebagai "pernah login"
        LoginAttempt::create([
            'user_id' => null, 'username_input' => '1002',
            'ip_address' => '127.0.0.1', 'status' => 'failed',
        ]);

        $html = $this->actingAs($admin)
            ->get('/data-master/users?view=mahasiswa')
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'class="never-login-badge"'));
        $this->assertMatchesRegularExpression('/Belum Login.*?never-login-badge/s', $html);
    }
}
