<?php

namespace Tests\Feature;

use App\Models\Rekognisi;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RekognisiDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $identifier): User
    {
        $user = User::create([
            'name' => ucfirst($role) . ' ' . $identifier,
            'nim_nidn' => $identifier,
            'password' => Hash::make('secret123'),
            'role' => $role,
        ]);

        if ($role === 'dosen') {
            Dosen::create(['user_id' => $user->id, 'nidn' => $identifier]);
        }

        return $user;
    }

    private function admin(): User
    {
        return $this->user('superadmin', '900001');
    }

    private function prosiding(User $dosen, array $override = []): array
    {
        return array_merge([
            'user_id' => $dosen->id,
            'jenis' => 'prosiding',
            'skala' => 'internasional',
            'judul' => 'Deep Learning untuk Padi',
            'penulis' => 'Dosen 100001',
            'nama_konferensi' => 'ICSE 2026',
            'nomor_issn_isbn' => '2345-6789',
            'link_doi' => 'https://doi.org/10.1234/prosiding',
            'tahun' => 2026,
            'mitra' => 'Example Publisher',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-02',
            'bukti_kegiatan' => 'https://example.com/bukti',
        ], $override);
    }

    private function paten(User $dosen, array $override = []): array
    {
        return array_merge([
            'user_id' => $dosen->id,
            'jenis' => 'paten',
            'skala' => 'nasional',
            'judul' => 'Deep Learning untuk Padi',
            'penulis' => 'Dosen 100001',
            'status_paten' => 'terdaftar',
            'nomor_paten' => 'P00202312345',
            'tahun' => 2026,
            'mitra' => 'Example Publisher',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-02',
            'bukti_kegiatan' => 'https://example.com/bukti',
        ], $override);
    }

    public function test_judul_yang_sama_persis_meminta_konfirmasi_sebelum_disimpan(): void
    {
        $admin = $this->admin();
        $dosen = $this->user('dosen', '100001');

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen))->assertOk();

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen, ['judul' => 'deep learning untuk padi!']))
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, Rekognisi::count());

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen) + ['confirm_duplicates' => true])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame(2, Rekognisi::count());
    }

    public function test_judul_yang_mirip_hanya_mengembalikan_peringatan(): void
    {
        $admin = $this->admin();
        $dosen = $this->user('dosen', '100001');

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen))->assertOk();

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen, ['judul' => 'Deep Learning untuk Padii']))
            ->assertOk()
            ->assertJsonCount(1, 'warnings');
        $this->assertSame(2, Rekognisi::count());
    }

    public function test_mengubah_data_sendiri_tidak_dianggap_duplikat(): void
    {
        $admin = $this->admin();
        $dosen = $this->user('dosen', '100001');

        $id = $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen))->json('data.id');

        $this->actingAs($admin)->putJson("/lppm/rekognisi/{$id}", $this->prosiding($dosen, ['nama_konferensi' => 'ICSE 2027']))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_jenis_atau_pemilik_yang_berbeda_bukan_duplikat(): void
    {
        $admin = $this->admin();
        $dosen = $this->user('dosen', '100001');
        $lain = $this->user('dosen', '100002');

        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen))->assertOk();

        // Judul sama tetapi Paten (bukan Prosiding): boleh.
        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->paten($dosen))->assertOk()->assertJsonMissingPath('duplicate_confirmation_required');
        // Judul sama tetapi dosen lain: boleh.
        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($lain))->assertOk();

        $this->assertSame(3, Rekognisi::count());
    }

    public function test_impor_meminta_konfirmasi_untuk_duplikat_persis(): void
    {
        $admin = $this->admin();
        $dosen = $this->user('dosen', '100001');
        $this->actingAs($admin)->postJson('/lppm/rekognisi', $this->prosiding($dosen))->assertOk();

        $csv = "nim_nidn,jenis,skala,judul,penulis,nama_konferensi,nomor_issn_isbn,link_doi,tahun,mitra,tanggal_mulai,tanggal_selesai,bukti_kegiatan\n"
             . "100001,prosiding,internasional,Deep Learning untuk Padi,Dosen 100001,ICSE 2026,2345-6789,https://doi.org/10.1234/prosiding,2026,Example Publisher,2026-01-01,2026-01-02,https://example.com/bukti\n";

        $this->actingAs($admin)->post('/lppm/rekognisi/import', [
            'file' => UploadedFile::fake()->createWithContent('rekognisi.csv', $csv),
        ], ['Accept' => 'application/json'])
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, Rekognisi::count());

        $this->actingAs($admin)->post('/lppm/rekognisi/import', [
            'file' => UploadedFile::fake()->createWithContent('rekognisi.csv', $csv),
            'confirm_duplicates' => '1',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame(2, Rekognisi::count());
    }
}
