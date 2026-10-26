<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

/** Log aktivitas mencatat sebelum -> sesudah (input -> output) lewat ChangeTracker + middleware LogActivity. */
class ActivityLogChangesTest extends TestCase
{
    use RefreshDatabase;

    private TestHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        // Arahkan channel "activity" ke handler di memori (tanpa menulis file). Channel lain tetap normal,
        // jadi kode aplikasi yang memanggil Log::info()/warning() di tempat lain tidak terganggu.
        $this->handler = new TestHandler();
        $handler = $this->handler;
        // PENTING: Log::extend() mengikat ulang $this ke LogManager, jadi handler dibawa lewat use(), bukan $this->handler.
        Log::extend('capture', function () use ($handler) {
            return new Logger('activity', [$handler]);
        });
        config(['logging.channels.activity' => ['driver' => 'capture']]);
    }

    /** Konteks entri log "activity" terakhir (null kalau belum ada). */
    private function lastLog(): ?array
    {
        $records = $this->handler->getRecords();
        if ($records === []) {
            return null;
        }
        $last = end($records);

        return is_array($last) ? $last['context'] : $last->context; // Monolog 2 = array, Monolog 3 = LogRecord
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

        $logged = $this->lastLog();
        $this->assertNotNull($logged, 'aktivitas tercatat');
        $this->assertSame('data-master.users.update', $logged['rute']);
        $this->assertSame('User', $logged['data']);
        $this->assertSame($mhs->id, $logged['data_id']);
        $this->assertSame(
            ['name' => ['input' => 'Budi Santoso', 'output' => 'Budi S.']],
            $logged['update'],
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

        $logged = $this->lastLog();
        $this->assertSame(['input' => '••••••', 'output' => '••••••'], $logged['update']['password']);
        $json = json_encode($logged);
        $this->assertStringNotContainsString('rahasiaBaru123', $json);
        $this->assertStringNotContainsString('$2y$', $json, 'hash password juga tidak boleh masuk log');
    }

    public function test_hapus_mencatat_isi_data_yang_dihapus_dan_menyamarkan_password(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)->deleteJson('/data-master/users/' . $mhs->id)->assertOk();

        $logged = $this->lastLog();
        $this->assertSame('Budi Santoso', $logged['delete']['name']['input']);
        $this->assertSame('1001', $logged['delete']['nim_nidn']['input']);
        $this->assertSame('••••••', $logged['delete']['password']['input']);
        $this->assertStringNotContainsString('$2y$', json_encode($logged));
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
        $create = $this->lastLog()['create'];
        $this->assertSame('Sari Dewi', $create['User#' . $user->id]['name']['output']);
        $this->assertSame('••••••', $create['User#' . $user->id]['password']['output']);
        $this->assertSame('2002', $create['Mahasiswa#' . $user->mahasiswa->id]['nim']['output']);
        $this->assertArrayNotHasKey('input', $create['User#' . $user->id]['name'], 'data baru tidak punya nilai sebelumnya');
    }

    public function test_request_tanpa_perubahan_data_tidak_punya_kunci_perubahan(): void
    {
        $this->actingAs($this->superadmin())->get('/data-master/users/export')->assertOk();

        $logged = $this->lastLog();
        $this->assertNotNull($logged);
        foreach (['create', 'update', 'delete'] as $k) {
            $this->assertArrayNotHasKey($k, $logged);
        }
    }

    public function test_perubahan_request_sebelumnya_tidak_terbawa_ke_request_berikutnya(): void
    {
        $actor = $this->superadmin();
        $mhs = $this->mahasiswa();

        $this->actingAs($actor)->putJson('/data-master/users/' . $mhs->id, $this->form($mhs, ['name' => 'Budi S.']))->assertOk();
        $this->assertArrayHasKey('update', $this->lastLog());

        $this->handler->clear();
        $this->actingAs($actor)->get('/data-master/users/export')->assertOk();
        $this->assertArrayNotHasKey('update', $this->lastLog() ?? []);
    }
}
