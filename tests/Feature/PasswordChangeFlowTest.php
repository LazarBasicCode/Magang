<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangeConfirmationNotification;
use App\Support\PasswordChangeVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Ganti password dari menu profil: kirim email konfirmasi -> menunggu -> (email dikonfirmasi) -> buat password baru.
 * Alternatifnya: kembalikan ke default (NIM/NIDN) dengan mengetik ulang NIM/NIDN.
 */
class PasswordChangeFlowTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role = 'mahasiswa', string $id = '222011005', ?string $email = 'budi@example.com'): User
    {
        return User::create([
            'name' => 'Budi Santoso',
            'nim_nidn' => $id,
            'email' => $email,
            'password' => Hash::make('Lama!1'),
            'role' => $role,
            'must_change_password' => false,
        ]);
    }

    /** Ambil tautan konfirmasi dari email yang "terkirim". */
    private function sentUrl(): string
    {
        $url = null;
        Notification::assertSentOnDemand(PasswordChangeConfirmationNotification::class, function ($n) use (&$url) {
            $url = $n->url;

            return true;
        });

        return $url;
    }

    public function test_tanpa_konfirmasi_email_password_tidak_bisa_diganti(): void
    {
        $user = $this->person();

        $this->actingAs($user)->postJson('/account/password', [
            'password' => 'BaruKu123', 'password_confirmation' => 'BaruKu123',
        ])->assertStatus(403)->assertJson(['verification_required' => true]);

        $this->assertTrue(Hash::check('Lama!1', $user->fresh()->password));
    }

    public function test_selama_menunggu_konfirmasi_status_pending_dan_password_belum_bisa_disimpan(): void
    {
        Notification::fake();
        $user = $this->person();

        // 1) kirim email konfirmasi
        $this->actingAs($user)->postJson('/account/password/verify')
            ->assertOk()
            ->assertJson(['success' => true, 'email' => 'bu***@example.com']);
        Notification::assertSentOnDemandTimes(PasswordChangeConfirmationNotification::class, 1);

        // 2) menunggu: belum dikonfirmasi -> status pending, penyimpanan password masih ditolak
        $this->getJson('/account/password/verify/status')->assertOk()->assertJson(['status' => 'pending']);
        $this->postJson('/account/password', [
            'password' => 'BaruKu123', 'password_confirmation' => 'BaruKu123',
        ])->assertStatus(403);

        $this->assertTrue(Hash::check('Lama!1', $user->fresh()->password));
    }

    public function test_status_menjadi_verified_begitu_email_dikonfirmasi_dan_password_bisa_disimpan(): void
    {
        Notification::fake();
        $user = $this->person();

        $this->actingAs($user)->postJson('/account/password/verify')->assertOk();
        $url = $this->sentUrl();

        // Email dibuka di perangkat lain (tanpa sesi login): halaman konfirmasi tampil.
        $this->app['auth']->forgetGuards();
        $this->get($url)->assertOk()->assertSee('Konfirmasi berhasil');

        // Sesi pemohon memeriksa status -> langsung verified (pop-up ketiga terbuka tanpa jeda buatan).
        $this->actingAs($user)->getJson('/account/password/verify/status')
            ->assertOk()->assertJson(['status' => 'verified']);

        $this->postJson('/account/password', [
            'password' => 'BaruKu123', 'password_confirmation' => 'BaruKu123',
        ])->assertOk()->assertJson(['logout' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('BaruKu123', $user->password));
        $this->assertFalse($user->must_change_password);
    }

    public function test_izin_hanya_menempel_di_sesi_pemohon(): void
    {
        Notification::fake();
        $user = $this->person();

        $this->actingAs($user)->postJson('/account/password/verify')->assertOk();
        $url = $this->sentUrl();
        $this->get($url)->assertOk();

        // Sesi LAIN milik akun yang sama (tanpa SESSION_REQUEST) tidak boleh mengklaim konfirmasi itu.
        $this->flushSession();
        $this->actingAs($user)->getJson('/account/password/verify/status')->assertJson(['status' => 'none']);
        $this->postJson('/account/password', [
            'password' => 'BaruKu123', 'password_confirmation' => 'BaruKu123',
        ])->assertStatus(403);
    }

    public function test_tautan_email_yang_diubah_atau_kedaluwarsa_ditolak(): void
    {
        Notification::fake();
        $user = $this->person();
        $this->actingAs($user)->postJson('/account/password/verify')->assertOk();
        $url = $this->sentUrl();

        // tanda tangan rusak
        $this->get($url . 'x')->assertForbidden();

        // token salah (tanda tangan valid, tetapi bukan token yang dikirim)
        $bad = URL::temporarySignedRoute('account.password.confirm', now()->addMinutes(5), ['user' => $user->id, 'token' => 'salah']);
        $this->get($bad)->assertStatus(410)->assertSee('Tautan tidak berlaku');

        $this->getJson('/account/password/verify/status')->assertJson(['status' => 'pending']);
    }

    public function test_kirim_ulang_dibatasi_jeda_dan_email_lama_tidak_berlaku(): void
    {
        Notification::fake();
        $user = $this->person();

        $this->actingAs($user)->postJson('/account/password/verify')->assertOk();
        $this->postJson('/account/password/verify')->assertStatus(429)->assertJsonStructure(['retry_after']);

        $this->assertGreaterThan(0, PasswordChangeVerification::secondsUntilResend($user));
    }

    public function test_tanpa_email_pemulihan_tidak_bisa_kirim_konfirmasi(): void
    {
        Notification::fake();
        $user = $this->person(email: null);

        $this->actingAs($user)->postJson('/account/password/verify')
            ->assertStatus(422)->assertJson(['needs_email' => true]);
        Notification::assertNothingSent();
    }

    public function test_email_gagal_terkirim_dibatalkan_dan_bisa_diulang(): void
    {
        $user = $this->person();
        Notification::shouldReceive('route')->andThrow(new \RuntimeException('smtp down'));

        $this->actingAs($user)->postJson('/account/password/verify')->assertStatus(502);

        $this->assertSame(0, PasswordChangeVerification::secondsUntilResend($user));
    }

    // ---------- Opsi 2: kembalikan ke default ----------

    public function test_kembalikan_ke_default_dengan_konfirmasi_nim(): void
    {
        $user = $this->person('mahasiswa', '222011005');

        $this->actingAs($user)->postJson('/account/password/default', ['confirm_identity' => '222011005'])
            ->assertOk()->assertJson(['logout' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('222011005', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_kembalikan_ke_default_menolak_nim_salah(): void
    {
        $user = $this->person('mahasiswa', '222011005');

        $this->actingAs($user)->postJson('/account/password/default', ['confirm_identity' => '999'])
            ->assertStatus(422)->assertJsonValidationErrors(['confirm_identity']);

        $this->assertTrue(Hash::check('Lama!1', $user->fresh()->password));
    }

    public function test_kembalikan_ke_default_hanya_untuk_mahasiswa_dan_dosen(): void
    {
        $admin = $this->person('admin', 'admin-1');

        $this->actingAs($admin)->postJson('/account/password/default', ['confirm_identity' => 'admin-1'])
            ->assertStatus(422);

        $this->assertTrue(Hash::check('Lama!1', $admin->fresh()->password));
    }

    // ---------- Pengecualian: pop-up wajib ganti password saat pertama masuk ----------

    public function test_pengguna_yang_wajib_ganti_password_tidak_butuh_email(): void
    {
        $user = $this->person();
        $user->forceFill(['must_change_password' => true])->save();

        $this->actingAs($user)->postJson('/account/password', [
            'password' => 'BaruKu123', 'password_confirmation' => 'BaruKu123',
        ])->assertOk();
    }
}
