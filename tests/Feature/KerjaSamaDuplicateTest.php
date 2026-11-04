<?php

namespace Tests\Feature;

use App\Models\KerjaSama;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KerjaSamaDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $identifier): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . $identifier,
            'nim_nidn' => $identifier,
            'password' => Hash::make('secret123'),
            'role' => $role,
        ]);
    }

    private function payload(User $dosen, array $override = []): array
    {
        return array_merge([
            'user_id' => $dosen->id,
            'tipe_user' => 'dosen',
            'jenis' => 'penelitian',
            'skala' => 'nasional',
            'mitra' => 'Universitas Gadjah Mada',
            'judul_kegiatan' => 'Riset Padi Hibrida',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-02-01',
            'bukti_kegiatan' => 'https://example.com/bukti',
        ], $override);
    }

    public function test_kerja_sama_yang_sama_persis_meminta_konfirmasi(): void
    {
        $admin = $this->user('superadmin', '900001');
        $dosen = $this->user('dosen', '100001');

        $this->actingAs($admin)->postJson('/kerja-sama', $this->payload($dosen))->assertOk();

        $this->actingAs($admin)->postJson('/kerja-sama', $this->payload($dosen, ['mitra' => 'universitas gadjah mada']))
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, KerjaSama::count());

        $this->actingAs($admin)->postJson('/kerja-sama', $this->payload($dosen) + ['confirm_duplicates' => true])
            ->assertOk();
        $this->assertSame(2, KerjaSama::count());
    }

    public function test_mitra_atau_judul_berbeda_dan_edit_diri_sendiri_bukan_duplikat(): void
    {
        $admin = $this->user('superadmin', '900001');
        $dosen = $this->user('dosen', '100001');

        $id = $this->actingAs($admin)->postJson('/kerja-sama', $this->payload($dosen))->json('data.id');

        $this->actingAs($admin)->postJson('/kerja-sama', $this->payload($dosen, ['judul_kegiatan' => 'Workshop Dosen']))->assertOk();
        $this->actingAs($admin)->putJson("/kerja-sama/{$id}", $this->payload($dosen, ['skala' => 'internasional']))->assertOk();
        $this->assertSame(2, KerjaSama::count());
    }
}
