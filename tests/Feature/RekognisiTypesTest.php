<?php

namespace Tests\Feature;

use App\Models\Rekognisi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\HakAkses;
use App\Models\User;
use App\Support\AdminDashboardData;
use App\Support\ReportData;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RekognisiTypesTest extends TestCase
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

        if ($role === 'mahasiswa') {
            Mahasiswa::create(['user_id' => $user->id, 'nim' => $identifier]);
        } elseif ($role === 'dosen') {
            Dosen::create(['user_id' => $user->id, 'nidn' => $identifier]);
        }

        return $user;
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
            'dosen_id' => $dosen->dosen->id,
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

    public function test_seeded_dosen_can_create_rekognisi_and_seeder_is_repeatable(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $dosen = User::where('role', 'dosen')
            ->where('nim_nidn', '0712048901')
            ->firstOrFail();

        $this->assertNotNull($dosen->dosen);
        $this->assertSame(1, Dosen::where('user_id', $dosen->id)->count());

        $this->actingAs($dosen)->postJson('/lppm/rekognisi', [
            'user_id' => $dosen->id,
            'jenis' => 'jurnal',
            'skala' => 'internasional',
            'judul' => 'Publikasi Dosen Seed',
            'penulis' => 'Budi',
            'nama_jurnal' => 'Journal of Testing',
            'peringkat' => 'Q2',
            'tahun' => 2026,
            'mitra' => 'Penerbit Tes',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-01-02',
            'bukti_kegiatan' => 'https://example.com/evidence',
        ])->assertOk();

        $this->assertDatabaseHas('rekognisi', [
            'dosen_id' => $dosen->dosen->id,
            'judul' => 'Publikasi Dosen Seed',
        ]);
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
            'mahasiswa_id' => $mahasiswa->mahasiswa->id,
            'tipe_user' => 'mahasiswa',
            'jenis' => 'sinta',
            'skala' => 'nasional',
            'mitra' => null,
        ]);
    }

    public function test_alumni_publication_requires_mitra_and_position(): void
    {
        $alumni = $this->user('mahasiswa', '300001');
        $alumni->mahasiswa->update(['status' => 'lulus']);
        $admin = $this->admin();
        $this->actingAs($admin);

        $payload = [
            'user_id' => $alumni->id,
            'jenis' => 'sinta',
            'skala' => 'nasional',
            'judul' => 'Alumni publication',
            'penulis' => 'Alumni Author',
            'nama_jurnal' => 'Jurnal Alumni',
            'peringkat' => 'S2',
            'tahun' => 2026,
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
            'mahasiswa_id' => $alumni->mahasiswa->id,
            'tipe_user' => 'alumni',
            'jenis' => 'sinta',
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
        $graduate->mahasiswa->update(['status' => 'lulus']);
        $this->user('dosen', '700002');

        $response = $this->actingAs($admin)->get('/lppm/rekognisi')->assertOk();
        $response->assertDontSee('id="dd-tipe_user"', false)
            ->assertDontSee('id="form-tipe_user"', false)
            ->assertSee('data-status="lulus"', false)
            ->assertDontSee('data-value="rekognisi"', false)
            ->assertSee('Alumni', false);
    }

    public function test_rekognisi_kind_and_related_records_are_deleted(): void
    {
        $dosen = $this->user('dosen', '800001');
        $item = Rekognisi::create([
            'dosen_id' => $dosen->dosen->id,
            'tipe_user' => 'dosen',
            'jenis' => 'rekognisi',
            'mitra' => 'Legacy partner',
            'tanggal_mulai' => '2025-03-01',
            'tanggal_selesai' => '2025-03-02',
            'bukti_kegiatan' => 'https://example.com/legacy',
        ]);
        $previouslyArchivedItem = Rekognisi::create([
            'dosen_id' => $dosen->dosen->id,
            'tipe_user' => 'dosen',
            'jenis' => 'legacy',
            'mitra' => 'Previously archived partner',
            'tanggal_mulai' => '2025-03-03',
            'tanggal_selesai' => '2025-03-04',
            'bukti_kegiatan' => 'https://example.com/previously-archived',
        ]);

        DB::table('approval_logs')->insert([
            'menu' => 'rekognisi',
            'item_id' => $item->id,
            'action' => 'diajukan',
            'to_status' => 'diajukan',
            'created_at' => now(),
        ]);
        DB::table('approval_comments')->insert([
            'menu' => 'rekognisi',
            'item_id' => $item->id,
            'user_id' => $dosen->id,
            'body' => 'Test comment',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_notifications')->insert([
            'user_id' => $dosen->id,
            'type' => 'data_updated',
            'color' => 'warning',
            'icon' => 'edit_note',
            'title' => 'Data rekognisi diperbarui',
            'description' => 'Test notification',
            'data' => json_encode(['rekognisi_id' => $item->id]),
            'created_at' => now(),
        ]);

        $migration = require base_path('database/migrations/2026_10_29_000001_remove_rekognisi_kind.php');
        $migration->up();

        $this->assertDatabaseMissing('rekognisi', ['id' => $item->id]);
        $this->assertDatabaseMissing('rekognisi', ['id' => $previouslyArchivedItem->id]);
        $this->assertDatabaseMissing('approval_logs', ['menu' => 'rekognisi', 'item_id' => $item->id]);
        $this->assertDatabaseMissing('approval_comments', ['menu' => 'rekognisi', 'item_id' => $item->id]);
        $this->assertDatabaseMissing('user_notifications', ['data->rekognisi_id' => $item->id]);

        $migration->down();
        $this->assertDatabaseMissing('rekognisi', ['id' => $item->id]);
    }

    public function test_rekognisi_is_not_accepted_as_a_new_kind(): void
    {
        $dosen = $this->user('dosen', '810001');

        $this->actingAs($this->admin())->postJson('/lppm/rekognisi', [
            'user_id' => $dosen->id,
            'jenis' => 'rekognisi',
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-02',
            'bukti_kegiatan' => 'https://example.com/evidence',
        ])->assertUnprocessable()->assertJsonValidationErrors(['jenis']);
    }

    public function test_lppm_publication_tables_are_removed_by_consolidation(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('lppm_mahasiswa'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('lppm_dosen'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('rekognisi', 'user_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('rekognisi', 'mahasiswa_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('rekognisi', 'dosen_id'));
    }

    public function test_legacy_lppm_permissions_are_not_part_of_the_current_menu_set(): void
    {
        $this->assertArrayNotHasKey('lppm_mahasiswa', HakAkses::MENUS);
        $this->assertArrayNotHasKey('lppm_dosen', HakAkses::MENUS);
    }

    public function test_profile_without_login_account_can_own_rekognisi(): void
    {
        $profile = Mahasiswa::create(['nim' => '2200001', 'status' => 'aktif']);

        $this->actingAs($this->admin())->postJson('/lppm/rekognisi', [
            'owner_ref' => 'mahasiswa:' . $profile->id,
            'jenis' => 'conference',
            'skala' => 'internasional',
            'judul' => 'Publication without account',
            'penulis' => 'A. Student',
            'tahun' => 2026,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-02',
            'bukti_kegiatan' => 'https://example.com/evidence',
        ])->assertOk();

        $this->assertDatabaseHas('rekognisi', [
            'mahasiswa_id' => $profile->id,
            'dosen_id' => null,
            'tipe_user' => 'mahasiswa',
        ]);
    }

    public function test_dashboards_and_reports_read_rekognisi_by_profile(): void
    {
        $mahasiswa = $this->user('mahasiswa', '2300001');
        $dosen = $this->user('dosen', '2300002');
        $base = [
            'jenis' => 'jurnal',
            'skala' => 'internasional',
            'judul' => 'Research output',
            'penulis' => 'Author',
            'nama_jurnal' => 'Journal',
            'peringkat' => 'Q1',
            'tahun' => 2026,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-02',
            'bukti_kegiatan' => 'https://example.com/evidence',
            'approval_status' => 'disetujui',
        ];
        Rekognisi::create($base + [
            'mahasiswa_id' => $mahasiswa->mahasiswa->id,
            'tipe_user' => 'mahasiswa',
        ]);
        Rekognisi::create($base + [
            'dosen_id' => $dosen->dosen->id,
            'tipe_user' => 'dosen',
            'mitra' => 'Example Publisher',
        ]);

        $this->actingAs($mahasiswa)->get('/dashboard')->assertOk();
        $this->actingAs($dosen)->get('/dashboard')->assertOk();

        $report = ReportData::build(null);
        $this->assertSame(1, $report['kpi']['mhs_active']);
        $this->assertSame(1, $report['kpi']['dsn_active']);
        $this->assertSame('sida', AdminDashboardData::build($this->admin())['mode']);
    }
}