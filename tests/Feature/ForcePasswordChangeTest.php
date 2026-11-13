<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DefaultPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alur password default mahasiswa/dosen: password awal = NIM/NIDN, direset admin ke default,
 * dan wajib diganti lewat pop-up di dashboard sebelum fitur lain bisa dipakai.
 */
class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $id, bool $mustChange = true): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . $id,
            'nim_nidn' => $id,
            'password' => Hash::make($id),
            'role' => $role,
            'must_change_password' => $mustChange,
        ]);
    }

    private function superadmin(): User
    {
        return User::create([
            'name' => 'Super Admin', 'nim_nidn' => '9000',
            'password' => Hash::make('secret123'), 'role' => 'superadmin',
        ]);
    }

    public function test_default_password_helper_memakai_nim_nidn_apa_adanya(): void
    {
        $this->assertSame('222011005', DefaultPassword::forIdentifier(' 222011005 '));
        $this->assertTrue(DefaultPassword::appliesTo('mahasiswa'));
        $this->assertTrue(DefaultPassword::appliesTo('dosen'));
        $this->assertFalse(DefaultPassword::appliesTo('admin'));
        $this->assertFalse(DefaultPassword::appliesTo('superadmin'));
    }

    public function test_login_pertama_dengan_nim_berhasil_dan_dashboard_menampilkan_popup(): void
    {
        $this->person('mahasiswa', '222011005');

        $this->post('/login-process', ['username' => '222011005', 'password' => '222011005'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('id="fpOverlay"', false)
            ->assertSee('force-password.js', false);
    }

    public function test_dashboard_tanpa_kewajiban_ganti_password_tidak_memuat_popup(): void
    {
        $user = $this->person('dosen', '0712048901', mustChange: false);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('id="fpOverlay"', false);
    }

    public function test_halaman_lain_diblokir_sampai_password_diganti(): void
    {
        $user = $this->person('mahasiswa', '222011005');

        $this->actingAs($user)->get('/profil')->assertRedirect(route('dashboard.index'));

        $this->actingAs($user)->getJson('/kemahasiswaan')
            ->assertStatus(423)
            ->assertJson(['force_password_change' => true]);
    }

    public function test_ganti_password_menghapus_kewajiban_dan_membuka_kunci(): void
    {
        $user = $this->person('mahasiswa', '222011005');

        $this->actingAs($user)->postJson('/account/password', ['password' => 'Ab4!xy'])
            ->assertOk()
            ->assertJson(['logout' => true]);

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('Ab4!xy', $user->password));

        $this->actingAs($user)->get('/profil')->assertOk();
    }

    public function test_superadmin_mereset_mahasiswa_ke_password_default(): void
    {
        $mhs = $this->person('mahasiswa', '222011005', mustChange: false);
        $mhs->forceFill(['password' => Hash::make('Ab4!xy')])->save();

        $this->actingAs($this->superadmin())
            ->postJson("/data-master/users/{$mhs->id}/reset-password")
            ->assertOk()
            ->assertJson(['success' => true]);

        $mhs->refresh();
        $this->assertTrue(Hash::check('222011005', $mhs->password));
        $this->assertTrue($mhs->must_change_password);
    }

    public function test_reset_default_hanya_untuk_mahasiswa_dan_dosen(): void
    {
        $admin = User::create([
            'name' => 'Admin Lain', 'nim_nidn' => 'admin-lain',
            'password' => Hash::make('Ab4!xy'), 'role' => 'admin',
        ]);

        $this->actingAs($this->superadmin())
            ->postJson("/data-master/users/{$admin->id}/reset-password")
            ->assertStatus(422);

        $this->assertTrue(Hash::check('Ab4!xy', $admin->fresh()->password));
    }

    public function test_mahasiswa_tidak_boleh_mereset_password_orang_lain(): void
    {
        $a = $this->person('mahasiswa', '111111', mustChange: false);
        $b = $this->person('mahasiswa', '222222', mustChange: false);

        $this->actingAs($a)->postJson("/data-master/users/{$b->id}/reset-password")
            ->assertStatus(403);
    }
}
