<?php

namespace Tests\Feature;

use App\Models\Rekognisi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\HakAkses;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        Mahasiswa::create(['user_id' => $alumni->id, 'nim' => '300001', 'status' => 'lulus']);
        $admin = $this->admin();
        $this->actingAs($admin);

        $payload = [
            'user_id' => $alumni->id,
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

    public function test_old_lppm_pages_redirect_to_rekognisi(): void
    {
        $admin = $this->admin();
        $destination = route('lppm.rekognisi.index');

        $this->actingAs($admin)->get('/lppm/dosen')->assertRedirect($destination);
        $this->actingAs($admin)->get('/lppm/mahasiswa')->assertRedirect($destination);
    }

    public function test_rekognisi_modal_derives_type_from_selected_user(): void
    {
        $admin = $this->admin();
        $graduate = $this->user('mahasiswa', '700001');
        Mahasiswa::create(['user_id' => $graduate->id, 'nim' => '700001', 'status' => 'lulus']);
        $this->user('dosen', '700002');

        $response = $this->actingAs($admin)->get('/lppm/rekognisi')->assertOk();
        $response->assertDontSee('id="dd-tipe_user"', false)
            ->assertDontSee('id="form-tipe_user"', false)
            ->assertSee('data-status="lulus"', false)
            ->assertSee('Alumni', false);
    }

    public function test_linked_lppm_publications_copy_into_rekognisi_only_once(): void
    {
        $student = $this->user('mahasiswa', '400001');
        $mahasiswa = Mahasiswa::create(['user_id' => $student->id, 'nim' => '400001', 'status' => 'lulus']);
        $lecturer = $this->user('dosen', '500001');
        $dosen = Dosen::create(['user_id' => $lecturer->id, 'nidn' => '500001']);

        $studentPublication = LppmMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => 'sinta_nasional',
            'judul' => 'Student paper',
            'penulis' => 'Student Author',
            'nama_jurnal' => 'Jurnal Mahasiswa',
            'peringkat' => 'S2',
            'link_doi' => 'https://doi.org/10.1234/student-paper',
            'bukti_kegiatan' => 'https://example.com/student-paper',
            'tahun' => 2025,
        ]);
        LppmDosen::create([
            'dosen_id' => $dosen->id,
            'jenis' => 'q_internasional',
            'judul' => 'Lecturer paper',
            'penulis' => 'Lecturer Author',
            'nama_jurnal' => 'International Journal',
            'peringkat' => 'Q1',
            'link_doi' => 'https://doi.org/10.1234/lecturer-paper',
            'bukti_kegiatan' => 'https://example.com/lecturer-paper',
            'tahun' => 2024,
        ]);
        DB::table('approval_logs')->insert([
            'menu' => 'lppm-mahasiswa',
            'item_id' => $studentPublication->id,
            'action' => 'diajukan',
            'to_status' => 'diajukan',
        ]);
        DB::table('approval_comments')->insert([
            'menu' => 'lppm-mahasiswa',
            'item_id' => $studentPublication->id,
            'user_id' => null,
            'body' => 'Mohon lengkapi bukti.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require base_path('database/migrations/2026_10_28_000002_copy_lppm_records_to_rekognisi.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseCount('rekognisi', 2);
        $this->assertDatabaseHas('rekognisi', [
            'user_id' => $student->id,
            'tipe_user' => 'alumni',
            'jenis' => 'sinta',
            'skala' => 'nasional',
            'source_lppm' => 'mahasiswa',
            'source_lppm_id' => 1,
        ]);
        $this->assertDatabaseHas('rekognisi', [
            'user_id' => $lecturer->id,
            'tipe_user' => 'dosen',
            'jenis' => 'jurnal',
            'skala' => 'internasional',
            'source_lppm' => 'dosen',
            'source_lppm_id' => 1,
        ]);
        $this->assertDatabaseHas('lppm_mahasiswa', ['mahasiswa_id' => $mahasiswa->id]);
        $this->assertDatabaseHas('lppm_dosen', ['dosen_id' => $dosen->id]);
        $studentRekognisiId = DB::table('rekognisi')
            ->where('source_lppm', 'mahasiswa')
            ->where('source_lppm_id', $studentPublication->id)
            ->value('id');
        $this->assertDatabaseHas('approval_logs', ['menu' => 'rekognisi', 'item_id' => $studentRekognisiId]);
        $this->assertDatabaseHas('approval_comments', ['menu' => 'rekognisi', 'item_id' => $studentRekognisiId, 'body' => 'Mohon lengkapi bukti.']);
    }

    public function test_legacy_lppm_access_levels_merge_into_rekognisi(): void
    {
        $admin = $this->user('admin', '600001');
        HakAkses::create(['user_id' => $admin->id, 'menu' => 'lppm_mahasiswa', 'level' => 'readonly']);
        HakAkses::create(['user_id' => $admin->id, 'menu' => 'lppm_dosen', 'level' => 'penuh']);

        $migration = require base_path('database/migrations/2026_10_28_000003_merge_lppm_permissions_into_rekognisi.php');
        $migration->up();

        $this->assertDatabaseHas('hak_akses', [
            'user_id' => $admin->id,
            'menu' => 'rekognisi',
            'level' => 'penuh',
        ]);
        $this->assertDatabaseHas('hak_akses', ['user_id' => $admin->id, 'menu' => 'lppm_mahasiswa']);
        $this->assertDatabaseHas('hak_akses', ['user_id' => $admin->id, 'menu' => 'lppm_dosen']);
    }
}