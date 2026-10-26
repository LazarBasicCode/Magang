<?php

namespace Tests\Feature;

use App\Models\Rekognisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RekognisiTypesTest extends TestCase
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

    private function admin(): User
    {
        return $this->user('superadmin', '900001');
    }

    public function test_dosen_can_save_journal_with_scale_and_required_mitra(): void
    {
        $dosen = $this->user('dosen', '100001');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/lppm/rekognisi', [
            'user_id' => $dosen->id,
            'tipe_user' => 'dosen',
            'jenis' => 'jurnal',
            'skala' => 'internasional',
            'judul' => 'Research output',
            'penulis' => 'Dosen 100001',
            'nama_jurnal' => 'Journal of Testing',
            'peringkat' => 'Q2',
            'link_doi' => 'https://doi.org/10.1234/example',
            'tahun' => 2026,
            'mitra' => 'Example Publisher',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-02',
            'bukti_kegiatan' => 'https://example.com/evidence',
        ]);

        $response->assertOk()->assertJsonPath('data.skala', 'internasional');
        $this->assertDatabaseHas('rekognisi', [
            'user_id' => $dosen->id,
            'tipe_user' => 'dosen',
            'jenis' => 'jurnal',
            'skala' => 'internasional',
            'mitra' => 'Example Publisher',
        ]);

        $this->actingAs($admin)->getJson('/lppm/rekognisi?skala=internasional')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.tipe_user', 'dosen');
    }

    public function test_mahasiswa_can_save_publication_without_mitra(): void
    {
        $mahasiswa = $this->user('mahasiswa', '200001');

        $response = $this->actingAs($this->admin())->postJson('/lppm/rekognisi', [
            'user_id' => $mahasiswa->id,
            'tipe_user' => 'mahasiswa',
            'jenis' => 'sinta',
            'skala' => 'nasional',
            'judul' => 'Student publication',
            'penulis' => 'Mahasiswa 200001',
            'nama_jurnal' => 'Jurnal Nasional',
            'peringkat' => 'S2',
            'link_doi' => 'https://doi.org/10.1234/student',
            'tahun' => 2026,
            'tanggal_mulai' => '2026-02-01',
            'tanggal_selesai' => '2026-02-02',
            'bukti_kegiatan' => 'https://example.com/student-evidence',
        ]);

        $response->assertOk()->assertJsonPath('data.mitra', null);
        $this->assertDatabaseHas('rekognisi', [
            'user_id' => $mahasiswa->id,
            'tipe_user' => 'mahasiswa',
            'jenis' => 'sinta',
            'skala' => 'nasional',
            'mitra' => null,
        ]);
    }

    public function test_alumni_type_requires_mitra_and_position(): void
    {
        $alumni = $this->user('mahasiswa', '300001');
        $admin = $this->admin();
        $this->actingAs($admin);

        $payload = [
            'user_id' => $alumni->id,
            'tipe_user' => 'alumni',
            'jenis' => 'rekognisi',
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-02',
            'bukti_kegiatan' => 'https://example.com/alumni-evidence',
        ];

        $this->postJson('/lppm/rekognisi', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mitra', 'jabatan']);

        $this->postJson('/lppm/rekognisi', $payload + [
            'mitra' => 'Example Company',
            'jabatan' => 'Product Manager',
        ])->assertOk();

        $this->assertDatabaseHas('rekognisi', [
            'user_id' => $alumni->id,
            'tipe_user' => 'alumni',
            'jenis' => 'rekognisi',
            'mitra' => 'Example Company',
            'jabatan' => 'Product Manager',
        ]);

        $this->actingAs($admin)->getJson('/lppm/rekognisi?tipe_user=alumni')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.tipe_user', 'alumni');
    }
}