<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $nim, string $role = 'mahasiswa'): User
    {
        return User::create([
            'name'     => 'User '.$nim,
            'nim_nidn' => $nim,
            'password' => Hash::make('secret123'),
            'role'     => $role,
        ]);
    }

    public function test_tamu_bisa_membuka_halaman_login(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_user_login_tidak_bisa_membuka_halaman_login(): void
    {
        $this->actingAs($this->makeUser('1001'))
            ->get('/')
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_user_login_tidak_bisa_login_sebagai_akun_lain_tanpa_logout(): void
    {
        $a = $this->makeUser('1001');
        $b = $this->makeUser('1002', 'dosen');

        $this->actingAs($a)
            ->post('/login-process', ['username' => '1002', 'password' => 'secret123'])
            ->assertRedirect(route('dashboard.index'));

        // Sesi tetap milik akun A, bukan berganti ke B.
        $this->assertAuthenticatedAs($a);
        $this->assertNotEquals($b->id, auth()->id());
    }

    public function test_user_login_tidak_bisa_memakai_lupa_dan_reset_password(): void
    {
        $user = $this->makeUser('1001');

        $this->actingAs($user)->post('/forgot-password', ['identity' => '1001'])
            ->assertRedirect(route('dashboard.index'));
        $this->actingAs($user)->get('/reset-password/abc')
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_logout_hanya_untuk_user_login_dan_membersihkan_sesi(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('1001'))
            ->post('/logout')
            ->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_setelah_logout_dashboard_tidak_bisa_dibuka(): void
    {
        $this->actingAs($this->makeUser('1001'))->post('/logout');
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_halaman_dilarang_di_cache_browser(): void
    {
        $res = $this->get('/');
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }
}
