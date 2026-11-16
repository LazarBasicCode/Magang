<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * - Pop-up wajib ganti password: password buatan sendiri + konfirmasi password.
 * - Lupa password di halaman login: kembalikan ke default (NIM/NIDN) dengan konfirmasi NIM/NIDN.
 */
class ForgotPasswordFlowTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $id, string $password = null, bool $mustChange = false): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . $id,
            'nim_nidn' => $id,
            'password' => Hash::make($password ?? $id),
            'role' => $role,
            'must_change_password' => $mustChange,
        ]);
    }

    // ---------- Pop-up ganti password: password + konfirmasi ----------

    public function test_ganti_password_manual_dengan_konfirmasi_berhasil(): void
    {
        $user = $this->person('mahasiswa', '222011005', mustChange: true);

        $this->actingAs($user)->postJson('/account/password', [
            'password' => 'katasandi1',
            'password_confirmation' => 'katasandi1',
        ])->assertOk()->assertJson(['logout' => true]);

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('katasandi1', $user->password));
    }

    public function test_ganti_password_manual_gagal_bila_konfirmasi_tidak_sama(): void
    {
        $user = $this->person('mahasiswa', '222011005', mustChange: true);

        $this->actingAs($user)->postJson('/account/password', [
            'password' => 'katasandi1',
            'password_confirmation' => 'katasandi2',
        ])->assertStatus(422)->assertJsonValidationErrors('password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_ganti_password_manual_gagal_bila_kurang_dari_6_karakter(): void
    {
        $user = $this->person('dosen', '0712048901', mustChange: true);

        $this->actingAs($user)->postJson('/account/password', [
            'password' => 'abc12',
            'password_confirmation' => 'abc12',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_ganti_password_manual_tidak_boleh_sama_dengan_nim(): void
    {
        $user = $this->person('mahasiswa', '222011005', mustChange: true);

        $this->actingAs($user)->postJson('/account/password', [
            'password' => '222011005',
            'password_confirmation' => '222011005',
        ])->assertStatus(422);

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_popup_memuat_field_konfirmasi_password(): void
    {
        $user = $this->person('mahasiswa', '222011005', mustChange: true);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('id="fpPassword"', false)
            ->assertSee('id="fpConfirm"', false);
    }

    // ---------- Lupa password: kembalikan ke default ----------

    public function test_halaman_login_menampilkan_pilihan_reset_ke_default(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('forgot-password/default', false)
            ->assertSee('confirmIdentity', false);
    }

    public function test_reset_ke_default_mengembalikan_password_ke_nim(): void
    {
        $mhs = $this->person('mahasiswa', '222011005', password: 'Rahasia99');

        $this->postJson('/forgot-password/default', [
            'identity' => '222011005',
            'confirm_identity' => '222011005',
        ])->assertOk()->assertJson(['success' => true]);

        $mhs->refresh();
        $this->assertTrue(Hash::check('222011005', $mhs->password));
        $this->assertTrue($mhs->must_change_password);
    }

    public function test_reset_ke_default_ditolak_bila_konfirmasi_nim_tidak_sama(): void
    {
        $mhs = $this->person('mahasiswa', '222011005', password: 'Rahasia99');

        $this->postJson('/forgot-password/default', [
            'identity' => '222011005',
            'confirm_identity' => '222011006',
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertTrue(Hash::check('Rahasia99', $mhs->fresh()->password));
    }

    public function test_reset_ke_default_wajib_mengisi_konfirmasi(): void
    {
        $this->person('dosen', '0712048901', password: 'Rahasia99');

        $this->postJson('/forgot-password/default', ['identity' => '0712048901'])
            ->assertStatus(422)->assertJsonValidationErrors('confirm_identity');
    }

    public function test_reset_ke_default_tidak_berlaku_untuk_admin_dan_responsnya_generik(): void
    {
        $admin = $this->person('admin', '9001', password: 'Rahasia99');

        $this->postJson('/forgot-password/default', [
            'identity' => '9001',
            'confirm_identity' => '9001',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('Rahasia99', $admin->fresh()->password));
    }

    public function test_reset_ke_default_nim_tidak_terdaftar_memberi_respons_generik(): void
    {
        $this->postJson('/forgot-password/default', [
            'identity' => '000000',
            'confirm_identity' => '000000',
        ])->assertOk()->assertJson(['success' => true]);
    }

    public function test_user_yang_sudah_login_tidak_bisa_memakai_reset_ke_default(): void
    {
        $user = $this->person('mahasiswa', '222011005', password: 'Rahasia99');

        $this->actingAs($user)->postJson('/forgot-password/default', [
            'identity' => '222011005',
            'confirm_identity' => '222011005',
        ]);

        $this->assertTrue(Hash::check('Rahasia99', $user->fresh()->password));
    }
}
