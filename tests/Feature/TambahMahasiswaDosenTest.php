<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TambahMahasiswaDosenTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Super Admin', 'nim_nidn' => '9000',
            'password' => Hash::make('secret123'), 'role' => 'superadmin',
        ]);
    }

    public function test_import_lppm_dengan_nim_belum_terdaftar_membuat_calon_tanpa_akun(): void
    {
        $csv = "nim,jenis,judul,penulis,bukti_kegiatan,tahun\n"
             . "999123,conference_internasional,Judul A,Penulis A,https://example.com/a,2025\n"
             . "999123,conference_internasional,Judul B,Penulis A,https://example.com/b,2025\n";

        $res = $this->actingAs($this->admin())->post('/lppm/mahasiswa/import', [
            'file' => UploadedFile::fake()->createWithContent('lppm.csv', $csv),
        ], ['Accept' => 'application/json']);

        $res->assertOk()->assertJson(['success' => true, 'created' => 2, 'new_owners' => 1]);

        $calon = Mahasiswa::where('nim', '999123')->first();
        $this->assertNotNull($calon);
        $this->assertNull($calon->user_id);
        $this->assertSame(2, LppmMahasiswa::where('mahasiswa_id', $calon->id)->count());
    }

    public function test_daftar_calon_menampilkan_nim_tanpa_akun_beserta_pemakaiannya(): void
    {
        Mahasiswa::create(['nim' => '999001']);
        $punyaAkun = User::create(['name' => 'Sudah Ada', 'nim_nidn' => '1001', 'password' => Hash::make('x12345'), 'role' => 'mahasiswa']);
        Mahasiswa::create(['user_id' => $punyaAkun->id, 'nim' => '1001']);

        $json = $this->actingAs($this->admin())
            ->getJson('/data-master/users/calon/mahasiswa')->assertOk()->json('data');

        $this->assertCount(1, $json);
        $this->assertSame('999001', $json[0]['identifier']);
    }

    public function test_nama_di_tabel_terisi_otomatis_dari_kolom_penulis_lppm(): void
    {
        $satu   = Mahasiswa::create(['nim' => '999010']);
        $banyak = Mahasiswa::create(['nim' => '999011']);
        $kosong = Mahasiswa::create(['nim' => '999012']);

        $data = fn ($m, $penulis) => [
            'mahasiswa_id' => $m->id, 'jenis' => 'conference_internasional', 'judul' => 'J',
            'penulis' => $penulis, 'bukti_kegiatan' => 'https://example.com/x', 'tahun' => 2025,
        ];
        LppmMahasiswa::create($data($satu, 'Maharani'));
        LppmMahasiswa::create($data($satu, 'Maharani'));
        LppmMahasiswa::create($data($banyak, 'Vito, Gopal'));

        $rows = collect($this->actingAs($this->admin())
            ->getJson('/data-master/users/calon/mahasiswa')->assertOk()->json('data'))
            ->keyBy('identifier');

        $this->assertSame('Maharani', $rows['999010']['name']);   // satu nama -> otomatis
        $this->assertNull($rows['999011']['name']);               // beberapa penulis -> tidak ditebak
        $this->assertSame('Vito, Gopal', $rows['999011']['hint']);
        $this->assertNull($rows['999012']['name']);               // tanpa data -> isi manual
        $this->assertNull($rows['999012']['hint']);
    }

    public function test_simpan_baris_terpilih_membuat_akun_dengan_password_nama_depan_plus_angkatan(): void
    {
        $a = Mahasiswa::create(['nim' => '999001']);
        $b = Mahasiswa::create(['nim' => '999002']);

        $this->actingAs($this->admin())->postJson('/data-master/users/people', [
            'role'     => 'mahasiswa',
            'selected' => [
                ['id' => $a->id, 'name' => 'Budi Santoso', 'angkatan' => 2022],
                ['id' => $b->id, 'name' => 'Sari Dewi'], // tanpa angkatan -> tahun terbaru
            ],
        ])->assertOk()->assertJson(['success' => true, 'created' => 2]);

        $ua = User::where('nim_nidn', '999001')->first();
        $this->assertSame('mahasiswa', $ua->role);
        $this->assertTrue(Hash::check('budi2022', $ua->password));
        $this->assertSame($ua->id, $a->fresh()->user_id);
        $this->assertSame(2022, (int) $a->fresh()->angkatan);

        $ub = User::where('nim_nidn', '999002')->first();
        $this->assertTrue(Hash::check('sari' . now()->year, $ub->password));
        $this->assertSame(now()->year, (int) $b->fresh()->angkatan);
    }

    public function test_tambah_dosen_memakai_tahun_terbaru_untuk_password(): void
    {
        $d = Dosen::create(['nidn' => '0712048901']);

        $this->actingAs($this->admin())->postJson('/data-master/users/people', [
            'role' => 'dosen',
            'selected' => [['id' => $d->id, 'name' => 'Dr. Ani Lestari']],
        ])->assertOk();

        $u = User::where('nim_nidn', '0712048901')->first();
        $this->assertSame('dosen', $u->role);
        $this->assertTrue(Hash::check('ani' . now()->year, $u->password));
        $this->assertSame($u->id, $d->fresh()->user_id);
    }

    public function test_isian_manual_memakai_nim_calon_menyambung_bukan_membuat_ganda(): void
    {
        $calon = Mahasiswa::create(['nim' => '999050']);

        $this->actingAs($this->admin())->postJson('/data-master/users/people', [
            'role'   => 'mahasiswa',
            'manual' => ['name' => 'Rina Putri', 'identifier' => '999050', 'angkatan' => 2023, 'status' => 'aktif'],
        ])->assertOk();

        $this->assertSame(1, Mahasiswa::where('nim', '999050')->count());
        $user = User::where('nim_nidn', '999050')->first();
        $this->assertSame($user->id, $calon->fresh()->user_id);
        $this->assertTrue(Hash::check('rina2023', $user->password));
    }

    public function test_tambah_pengguna_biasa_dengan_nim_calon_juga_menyambung(): void
    {
        $calon = Mahasiswa::create(['nim' => '999060']);

        $this->actingAs($this->admin())->postJson('/data-master/users', [
            'name' => 'Tono', 'role' => 'mahasiswa', 'identifier' => '999060',
            'angkatan' => 2021, 'status' => 'aktif', 'password' => 'secret123',
        ])->assertOk();

        $this->assertSame(1, Mahasiswa::where('nim', '999060')->count());
        $this->assertNotNull($calon->fresh()->user_id);
    }

    public function test_ditolak_jika_nama_baris_terpilih_kosong_atau_tidak_ada_yang_diisi(): void
    {
        $a = Mahasiswa::create(['nim' => '999001']);
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/data-master/users/people', [
            'role' => 'mahasiswa', 'selected' => [['id' => $a->id, 'name' => '']],
        ])->assertStatus(422);

        $this->actingAs($admin)->postJson('/data-master/users/people', ['role' => 'mahasiswa'])
            ->assertStatus(422);

        $this->assertNull($a->fresh()->user_id);
    }

    public function test_role_tidak_bisa_diubah_jadi_admin_lewat_endpoint_ini(): void
    {
        $this->actingAs($this->admin())->postJson('/data-master/users/people', [
            'role' => 'admin', 'manual' => ['name' => 'X', 'identifier' => '777'],
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', ['nim_nidn' => '777']);
    }

    public function test_baris_yang_sudah_punya_akun_tidak_bisa_dipakai_ulang(): void
    {
        $a = Mahasiswa::create(['nim' => '999001']);
        $admin = $this->admin();
        $payload = ['role' => 'mahasiswa', 'selected' => [['id' => $a->id, 'name' => 'Budi']]];

        $this->actingAs($admin)->postJson('/data-master/users/people', $payload)->assertOk();
        $this->actingAs($admin)->postJson('/data-master/users/people', $payload)->assertStatus(422);
        $this->assertSame(1, User::where('nim_nidn', '999001')->count());
    }
}
