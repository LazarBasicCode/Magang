<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Kemahasiswaan;
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

    public function test_tambah_akun_dari_calon_meminta_konfirmasi_nama_persis_sama(): void
    {
        $admin = $this->admin();
        $existing = User::create(['name' => 'Nama Sama', 'nim_nidn' => '999065', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        Mahasiswa::create(['user_id' => $existing->id, 'nim' => '999065']);
        $calon = Mahasiswa::create(['nim' => '999066']);
        $payload = ['role' => 'mahasiswa', 'selected' => [['id' => $calon->id, 'name' => 'Nama Sama', 'angkatan' => 2022]]];

        $this->actingAs($admin)->postJson('/data-master/users/people', $payload)
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, User::where('role', 'mahasiswa')->count());

        $this->actingAs($admin)->postJson('/data-master/users/people', $payload + ['confirm_duplicates' => true])
            ->assertOk()
            ->assertJsonPath('created', 1);
        $this->assertSame(2, User::where('role', 'mahasiswa')->count());
    }

    public function test_kemahasiswaan_meminta_konfirmasi_sebelum_menyimpan_duplikat_persis(): void
    {
        $admin = $this->admin();
        $user = User::create(['name' => 'Budi', 'nim_nidn' => '999070', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        $mahasiswa = Mahasiswa::create(['user_id' => $user->id, 'nim' => '999070']);
        $data = [
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => 'kemahasiswaan',
            'tab' => 'akademik',
            'tingkat' => 'nasional',
            'tahun' => 2025,
            'nama_kegiatan' => 'Juara Lomba Karya Tulis',
            'bukti_kegiatan' => 'https://example.com/bukti',
        ];
        Kemahasiswaan::create($data);

        $this->actingAs($admin)->postJson('/kemahasiswaan', $data)
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, Kemahasiswaan::count());

        $this->actingAs($admin)->postJson('/kemahasiswaan', $data + ['confirm_duplicates' => true])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame(2, Kemahasiswaan::count());
    }

    public function test_kemahasiswaan_mengembalikan_peringatan_jika_namanya_mirip(): void
    {
        $admin = $this->admin();
        $user = User::create(['name' => 'Budi', 'nim_nidn' => '999071', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        $mahasiswa = Mahasiswa::create(['user_id' => $user->id, 'nim' => '999071']);
        Kemahasiswaan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => 'kemahasiswaan',
            'tab' => 'akademik',
            'tingkat' => 'nasional',
            'tahun' => 2025,
            'nama_kegiatan' => 'Juara Lomba Karya Tulis',
            'bukti_kegiatan' => 'https://example.com/bukti-lama',
        ]);

        $this->actingAs($admin)->postJson('/kemahasiswaan', [
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => 'kemahasiswaan',
            'tab' => 'akademik',
            'tingkat' => 'nasional',
            'tahun' => 2025,
            'nama_kegiatan' => 'Juara Lomba Karya Tuli',
            'bukti_kegiatan' => 'https://example.com/bukti-baru',
        ])->assertOk()->assertJsonCount(1, 'warnings');
    }

    public function test_impor_kemahasiswaan_meminta_konfirmasi_untuk_duplikat_persis(): void
    {
        $admin = $this->admin();
        $user = User::create(['name' => 'Budi', 'nim_nidn' => '999072', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        $mahasiswa = Mahasiswa::create(['user_id' => $user->id, 'nim' => '999072']);
        Kemahasiswaan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis' => 'kemahasiswaan',
            'tab' => 'akademik',
            'tingkat' => 'nasional',
            'tahun' => 2025,
            'nama_kegiatan' => 'Juara Lomba Karya Tulis',
            'bukti_kegiatan' => 'https://example.com/bukti-lama',
        ]);
        $csv = "nim,jenis_divisi,jenis_prestasi,skala,tahun,nama_kegiatan,bukti_kegiatan\n"
             . "999072,kemahasiswaan,akademik,nasional,2025,Juara Lomba Karya Tulis,https://example.com/bukti-baru\n";

        $this->actingAs($admin)->post('/kemahasiswaan/import', [
            'file' => UploadedFile::fake()->createWithContent('kemahasiswaan.csv', $csv),
        ], ['Accept' => 'application/json'])
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame(1, Kemahasiswaan::count());

        $this->actingAs($admin)->post('/kemahasiswaan/import', [
            'file' => UploadedFile::fake()->createWithContent('kemahasiswaan.csv', $csv),
            'confirm_duplicates' => '1',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame(2, Kemahasiswaan::count());
    }

    public function test_impor_data_master_mengembalikan_peringatan_nama_mirip(): void
    {
        $admin = $this->admin();
        User::create(['name' => 'Siti Maharani', 'nim_nidn' => '999092', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        $csv = "nim_nidn,nama,role,angkatan,status,password\n"
             . "999093,Siti Maharan,mahasiswa,2022,aktif,secret123\n";

        $this->actingAs($admin)->post('/data-master/users/import', [
            'file' => UploadedFile::fake()->createWithContent('users.csv', $csv),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'warnings');
    }

    public function test_edit_akun_meminta_konfirmasi_nama_persis_sama(): void
    {
        $admin = $this->admin();
        $target = User::create(['name' => 'Nama Lama', 'nim_nidn' => '999080', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        User::create(['name' => 'Nama Sama', 'nim_nidn' => '999081', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        $payload = [
            'name' => 'Nama Sama',
            'role' => 'mahasiswa',
            'identifier' => $target->nim_nidn,
            'angkatan' => 2022,
            'status' => 'aktif',
            'password' => '',
        ];

        $this->actingAs($admin)->putJson('/data-master/users/' . $target->id, $payload)
            ->assertStatus(409)
            ->assertJsonPath('duplicate_confirmation_required', true);
        $this->assertSame('Nama Lama', $target->fresh()->name);

        $this->actingAs($admin)->putJson('/data-master/users/' . $target->id, $payload + ['confirm_duplicates' => true])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame('Nama Sama', $target->fresh()->name);
    }

    public function test_edit_akun_mengembalikan_peringatan_nama_mirip(): void
    {
        $admin = $this->admin();
        $target = User::create(['name' => 'Nama Lama', 'nim_nidn' => '999090', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);
        User::create(['name' => 'Siti Maharani', 'nim_nidn' => '999091', 'password' => Hash::make('secret123'), 'role' => 'mahasiswa']);

        $this->actingAs($admin)->putJson('/data-master/users/' . $target->id, [
            'name' => 'Siti Maharani',
            'role' => 'mahasiswa',
            'identifier' => $target->nim_nidn,
            'angkatan' => 2022,
            'status' => 'aktif',
            'password' => '',
        ])->assertStatus(409);

        $this->actingAs($admin)->putJson('/data-master/users/' . $target->id, [
            'name' => 'Siti Maharan',
            'role' => 'mahasiswa',
            'identifier' => $target->nim_nidn,
            'angkatan' => 2022,
            'status' => 'aktif',
            'password' => '',
        ])->assertOk()->assertJsonCount(1, 'warnings');
    }
}
