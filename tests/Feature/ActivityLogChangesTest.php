<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/** Log aktivitas mencatat sebelum -> sesudah (input -> output) lewat ChangeTracker + middleware LogActivity. */
class ActivityLogChangesTest extends TestCase
{
    use RefreshDatabase;

    private ?array $logged = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Tangkap konteks yang ditulis ke channel "activity" (tanpa menulis file).
        $channel = \Mockery::mock();
        $channel->shouldReceive('info', 'warning')->andReturnUsing(function ($message, $context = []) {
            $this->logged = $context;
        });
        Log::partialMock()->shouldReceive('channel')->with('activity')->andReturn($channel);
    }

    private function superadmin(): User
    {
        return User::create(['name' => 'Super Admin', 'nim_nidn' => '9000', 'password' => Hash::make('secret123'), 'role' => 'superadmin']);
    }

    private function mahasiswa(): User
    {
        $u = User::create(['name' => 'Budi Santoso', 'nim_nidn' => '1001', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        Mahasiswa::create(['user_id' => $u->id, 'nim' => '1001', 'angkatan' => 2022, 'status' => 'aktif']);

        return $u;
    }

    /** Payload form edit akun mahasiswa (sama seperti yang dikirim modal Data Master). */
    private function form(User $u, array $override = []): array
    {
        return $override + [
            'name' => $u->name, 'role' => 'mahasiswa', 'identifier' => $u->nim_nidn,
            'angkatan' => 2022, 'status' => 'aktif',
        ];
    }

    public function test_update_mencatat_nilai_lama_sebagai_input_dan_nilai_baru_sebagai_output(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)
            ->putJson('/data-master/users/' . $mhs->id, $this->form($mhs, ['name' => 'Budi S.']))
            ->assertOk();

        $this->assertNotNull($this->logged, 'aktivitas tercatat');
        $this->assertSame('data-master.users.update', $this->logged['rute']);
        $this->assertSame('User', $this->logged['data']);
        $this->assertSame($mhs->id, $this->logged['data_id']);
        $this->assertSame(
            ['name' => ['input' => 'Budi Santoso', 'output' => 'Budi S.']],
            $this->logged['update'],
            'hanya kolom yang berubah yang dicatat',
        );
    }

    public function test_perubahan_password_tidak_pernah_mencatat_isinya(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)
            ->putJson('/data-master/users/' . $mhs->id, $this->form($mhs, ['password' => 'rahasiaBaru123']))
            ->assertOk();

        $this->assertSame(['input' => '••••••', 'output' => '••••••'], $this->logged['update']['password']);
        $json = json_encode($this->logged);
        $this->assertStringNotContainsString('rahasiaBaru123', $json);
        $this->assertStringNotContainsString('$2y$', $json, 'hash password juga tidak boleh masuk log');
    }

    public function test_hapus_mencatat_isi_data_yang_dihapus_dan_menyamarkan_password(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)->deleteJson('/data-master/users/' . $mhs->id)->assertOk();

        $this->assertSame('Budi Santoso', $this->logged['delete']['name']['input']);
        $this->assertSame('1001', $this->logged['delete']['nim_nidn']['input']);
        $this->assertSame('••••••', $this->logged['delete']['password']['input']);
        $this->assertStringNotContainsString('$2y$', json_encode($this->logged));
    }

    public function test_tambah_mencatat_nilai_baru_sebagai_output_dan_dikelompokkan_per_data(): void
    {
        $actor = $this->superadmin();

        $this->actingAs($actor)->postJson('/data-master/users', [
            'name' => 'Sari Dewi', 'role' => 'mahasiswa', 'identifier' => '2002',
            'angkatan' => 2023, 'status' => 'aktif', 'password' => 'secret123',
        ])->assertSuccessful();

        $user = User::where('nim_nidn', '2002')->firstOrFail();
        // dua data baru (akun + profil mahasiswa) -> dikelompokkan "Tipe#id"
        $create = $this->logged['create'];
        $this->assertSame('Sari Dewi', $create['User#' . $user->id]['name']['output']);
        $this->assertSame('••••••', $create['User#' . $user->id]['password']['output']);
        $this->assertSame('2002', $create['Mahasiswa#' . $user->mahasiswa->id]['nim']['output']);
        $this->assertArrayNotHasKey('input', $create['User#' . $user->id]['name'], 'data baru tidak punya nilai sebelumnya');
    }

    public function test_request_tanpa_perubahan_data_tidak_punya_kunci_perubahan(): void
    {
        $this->actingAs($this->superadmin())->get('/data-master/users/export')->assertOk();

        $this->assertNotNull($this->logged);
        foreach (['create', 'update', 'delete'] as $k) {
            $this->assertArrayNotHasKey($k, $this->logged);
        }
    }

    public function test_perubahan_request_sebelumnya_tidak_terbawa_ke_request_berikutnya(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)->putJson('/data-master/users/' . $mhs->id, $this->form($mhs, ['name' => 'Budi S.']))->assertOk();
        $this->assertArrayHasKey('update', $this->logged);

        $this->logged = null;
        $this->actingAs($actor)->get('/data-master/users/export')->assertOk();
        $this->assertArrayNotHasKey('update', $this->logged ?? []);
    }
}
