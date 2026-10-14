<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\HakAkses;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Pilih banyak akun di Data Master: export / hapus / update massal (BulkSelectionController, menu 'data-master'). */
class DataMasterBulkSelectionTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function makeUser(string $role, ?string $name = null): User
    {
        $n = ++$this->seq;
        $user = User::create([
            'name'     => $name ?? ucfirst($role) . " {$n}",
            'nim_nidn' => (string) (5000 + $n),
            'password' => Hash::make('secret123'),
            'role'     => $role,
        ]);

        if ($role === 'mahasiswa') {
            Mahasiswa::create(['user_id' => $user->id, 'nim' => $user->nim_nidn, 'angkatan' => 2022, 'status' => 'aktif']);
        } elseif ($role === 'dosen') {
            Dosen::create(['user_id' => $user->id, 'nidn' => $user->nim_nidn, 'status' => 'aktif']);
        }

        return $user;
    }

    private function adminWithLevel(string $level): User
    {
        $admin = $this->makeUser('admin');
        HakAkses::create(['user_id' => $admin->id, 'menu' => 'data_master', 'level' => $level]);

        return $admin;
    }

    // ---------------- HAPUS ----------------

    public function test_hapus_massal_menghapus_akun_beserta_profilnya(): void
    {
        $actor = $this->makeUser('superadmin');
        $a = $this->makeUser('mahasiswa');
        $b = $this->makeUser('dosen');

        $this->actingAs($actor)->postJson('/pilihan/data-master/hapus', ['ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'missing' => 0])
            ->assertJsonCount(2, 'deleted');

        $this->assertDatabaseMissing('users', ['id' => $a->id]);
        $this->assertDatabaseMissing('users', ['id' => $b->id]);
        $this->assertDatabaseMissing('mahasiswa', ['user_id' => $a->id]);
        $this->assertDatabaseMissing('dosen', ['user_id' => $b->id]);
    }

    public function test_akun_sendiri_dilewati_yang_lain_tetap_terhapus(): void
    {
        $actor = $this->makeUser('superadmin');
        $other = $this->makeUser('superadmin');
        $mhs   = $this->makeUser('mahasiswa');

        $res = $this->actingAs($actor)->postJson('/pilihan/data-master/hapus', ['ids' => [$actor->id, $mhs->id]])
            ->assertOk();

        $this->assertSame([$mhs->id], $res->json('deleted'));
        $this->assertSame(1, $res->json('skipped'));
        $this->assertStringContainsString('akun Anda sendiri', $res->json('notice'));
        $this->assertDatabaseHas('users', ['id' => $actor->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_hanya_akun_sendiri_yang_dipilih_ditolak_dengan_pesan(): void
    {
        $actor = $this->makeUser('superadmin');
        $this->makeUser('superadmin');

        $this->actingAs($actor)->postJson('/pilihan/data-master/hapus', ['ids' => [$actor->id]])
            ->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseHas('users', ['id' => $actor->id]);
    }

    public function test_superadmin_terakhir_tidak_bisa_hilang_walau_semua_dipilih(): void
    {
        $actor = $this->makeUser('superadmin');
        $b = $this->makeUser('superadmin');
        $c = $this->makeUser('superadmin');

        // Pelaku bukan salah satu yang dipilih: dua dari tiga superadmin boleh dihapus, tetapi tidak semuanya.
        $res = $this->actingAs($actor)->postJson('/pilihan/data-master/hapus', ['ids' => [$b->id, $c->id]])->assertOk();
        $this->assertCount(2, $res->json('deleted'));

        $this->assertSame(1, User::where('role', 'superadmin')->count());
    }

    public function test_admin_tidak_bisa_menghapus_admin_lain_atau_superadmin_lewat_hapus_massal(): void
    {
        $actor = $this->adminWithLevel('penuh');
        $otherAdmin = $this->makeUser('admin');
        $super = $this->makeUser('superadmin');
        $mhs = $this->makeUser('mahasiswa');

        $res = $this->actingAs($actor)
            ->postJson('/pilihan/data-master/hapus', ['ids' => [$otherAdmin->id, $super->id, $mhs->id]])
            ->assertOk();

        $this->assertSame([$mhs->id], $res->json('deleted'));
        $this->assertSame(2, $res->json('skipped'));
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
        $this->assertDatabaseHas('users', ['id' => $super->id]);
    }

    public function test_admin_level_readonly_tidak_boleh_hapus_atau_ubah_massal(): void
    {
        $actor = $this->adminWithLevel('readonly');
        $mhs = $this->makeUser('mahasiswa');

        $this->actingAs($actor)->postJson('/pilihan/data-master/hapus', ['ids' => [$mhs->id]])->assertForbidden();
        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$mhs->id], 'status' => 'cuti'])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $mhs->id]);
    }

    public function test_tanpa_pilihan_atau_tanpa_login_ditolak(): void
    {
        $this->postJson('/pilihan/data-master/hapus', ['ids' => [1]])->assertUnauthorized();

        $this->actingAs($this->makeUser('superadmin'))
            ->postJson('/pilihan/data-master/hapus', ['ids' => []])->assertStatus(422);
    }

    // ---------------- UBAH ----------------

    public function test_update_massal_status_dan_angkatan_mahasiswa(): void
    {
        $actor = $this->makeUser('superadmin');
        $a = $this->makeUser('mahasiswa');
        $b = $this->makeUser('mahasiswa');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', [
            'ids' => [$a->id, $b->id], 'status' => 'cuti', 'angkatan' => '2023',
        ])->assertOk()->assertJson(['success' => true])->assertJsonCount(2, 'updated');

        foreach ([$a, $b] as $u) {
            $m = Mahasiswa::where('user_id', $u->id)->first();
            $this->assertSame('cuti', $m->status);
            $this->assertSame(2023, (int) $m->angkatan);
        }
    }

    public function test_update_massal_kolom_kosong_tidak_mengubah_apa_apa(): void
    {
        $actor = $this->makeUser('superadmin');
        $a = $this->makeUser('mahasiswa');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$a->id], 'status' => 'lulus'])->assertOk();

        $m = Mahasiswa::where('user_id', $a->id)->first();
        $this->assertSame('lulus', $m->status);
        $this->assertSame(2022, (int) $m->angkatan); // angkatan dikosongkan -> tetap

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$a->id]])->assertStatus(422);
    }

    public function test_angkatan_untuk_dosen_ditolak_dan_tidak_ada_yang_berubah(): void
    {
        $actor = $this->makeUser('superadmin');
        $mhs = $this->makeUser('mahasiswa');
        $dsn = $this->makeUser('dosen');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', [
            'ids' => [$mhs->id, $dsn->id], 'status' => 'cuti', 'angkatan' => '2024',
        ])->assertStatus(422)->assertJson(['success' => false]);

        // semua-atau-tidak-sama-sekali: mahasiswa pun tidak berubah
        $this->assertSame('aktif', Mahasiswa::where('user_id', $mhs->id)->value('status'));
        $this->assertSame('aktif', Dosen::where('user_id', $dsn->id)->value('status'));
    }

    public function test_status_milik_mahasiswa_ditolak_untuk_dosen(): void
    {
        $actor = $this->makeUser('superadmin');
        $dsn = $this->makeUser('dosen');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$dsn->id], 'status' => 'lulus'])
            ->assertStatus(422);
        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$dsn->id], 'status' => 'pensiun'])
            ->assertOk();

        $this->assertSame('pensiun', Dosen::where('user_id', $dsn->id)->value('status'));
    }

    public function test_update_massal_admin_ditolak_karena_tidak_punya_status(): void
    {
        $actor = $this->makeUser('superadmin');
        $adm = $this->makeUser('admin');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$adm->id], 'status' => 'aktif'])
            ->assertStatus(422);
    }

    public function test_angkatan_harus_tahun_valid(): void
    {
        $actor = $this->makeUser('superadmin');
        $a = $this->makeUser('mahasiswa');

        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$a->id], 'angkatan' => 'abc'])->assertStatus(422);
        $this->actingAs($actor)->postJson('/pilihan/data-master/ubah', ['ids' => [$a->id], 'angkatan' => '1800'])->assertStatus(422);
        $this->assertSame(2022, (int) Mahasiswa::where('user_id', $a->id)->value('angkatan'));
    }

    // ---------------- EXPORT ----------------

    public function test_export_data_terpilih_mengunduh_file_terpilih(): void
    {
        $actor = $this->makeUser('superadmin');
        $a = $this->makeUser('mahasiswa');

        $res = $this->actingAs($actor)->post('/pilihan/data-master/export', ['ids' => (string) $a->id]);

        $res->assertOk();
        $this->assertStringContainsString('-terpilih.xlsx', (string) $res->headers->get('content-disposition'));
    }
}
