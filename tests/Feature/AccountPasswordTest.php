<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_returns_six_character_password_not_used_by_another_account(): void
    {
        $user = $this->makeUser('222011005', 'Mahasiswa', 'mahasiswa');
        $other = $this->makeUser('0712048901', 'Dosen', 'dosen');
        $other->forceFill(['password' => Hash::make('Ab4!xy')])->save();

        $response = $this->actingAs($user)->postJson('/account/password/generate');

        $response->assertOk();
        $password = $response->json('password');
        $this->assertSame(6, strlen($password));
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
        $this->assertMatchesRegularExpression('/[2-9]/', $password);
        $this->assertMatchesRegularExpression('/[!@#$%&*?+=]/', $password);
        $this->assertFalse(Hash::check($password, $other->fresh()->password));
    }

    public function test_profile_password_update_ends_session_and_prefills_login(): void
    {
        $user = $this->makeUser('222011005', 'Mahasiswa', 'mahasiswa');

        $this->actingAs($user)
            ->postJson('/account/password', ['password' => 'Ab4!xy'])
            ->assertOk()
            ->assertJsonPath('logout', true);

        $this->assertGuest();
        $this->assertTrue(Hash::check('Ab4!xy', $user->fresh()->password));

        $this->get('/')
            ->assertOk()
            ->assertViewHas('loginPrefill', [
                'username' => '222011005',
                'password' => 'Ab4!xy',
            ]);
    }

    public function test_superadmin_password_update_prefills_account_username(): void
    {
            $superadmin = User::create([
                'name' => 'Root Login',
                'nim_nidn' => null,
                'password' => Hash::make('Oldpass1'),
                'role' => 'superadmin',
            ]);

            $this->actingAs($superadmin)
                ->postJson('/account/password', ['password' => 'Ab4!xy'])
                ->assertOk();

            $this->get('/')
                ->assertOk()
                ->assertViewHas('loginPrefill', [
                    'username' => 'Root Login',
                    'password' => 'Ab4!xy',
                ]);
    }

    public function test_profile_password_update_rejects_password_used_by_another_account(): void
    {
        $user = $this->makeUser('222011005', 'Mahasiswa', 'mahasiswa');
        $other = $this->makeUser('0712048901', 'Dosen', 'dosen');
        $other->forceFill(['password' => Hash::make('Ab4!xy')])->save();

        $this->actingAs($user)
            ->postJson('/account/password', ['password' => 'Ab4!xy'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertAuthenticatedAs($user);
    }

    public function test_new_data_master_account_receives_a_unique_random_password(): void
    {
        $superadmin = $this->makeUser('root-account', 'Root Account', 'superadmin');

        $response = $this->actingAs($superadmin)->postJson('/data-master/users', [
            'name' => 'Mahasiswa Baru',
            'role' => 'mahasiswa',
            'identifier' => '233011009',
            'angkatan' => 2023,
            'status' => 'aktif',
        ]);

        $response->assertOk();
        $password = $response->json('credentials.password');
        $this->assertSame(6, strlen($password));
        $this->assertTrue(Hash::check($password, User::where('nim_nidn', '233011009')->firstOrFail()->password));
    }

    private function makeUser(string $identifier, string $name, string $role): User
    {
        return User::create([
            'name' => $name,
            'nim_nidn' => $identifier,
            'password' => Hash::make('Oldpass1'),
            'role' => $role,
        ]);
    }
}
