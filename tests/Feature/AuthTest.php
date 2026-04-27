<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_session_mengambil_role_dari_token_bukan_dari_payload_client(): void
    {
        $user = User::create([
            'username'      => 'kasir1',
            'email'         => 'kasir1@test.com',
            'password_hash' => Hash::make('password123'),
            'role'          => 'kasir',
            'nama_lengkap'  => 'Test Kasir',
            'is_active'     => true,
        ]);

        $token = $user->createToken('web-login')->plainTextToken;

        $response = $this->post('/auth/session', [
            'token' => $token,
            'user' => json_encode([
                'role' => 'pemilik',
                'nama_lengkap' => 'Role Palsu',
            ]),
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('user.role', 'kasir');
        $response->assertSessionHas('user.nama_lengkap', 'Test Kasir');
    }

    public function test_auth_session_menolak_token_tidak_valid(): void
    {
        $response = $this->post('/auth/session', [
            'token' => 'token-tidak-valid',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('pos_token');
    }

    public function test_pemilik_dapat_login(): void
    {
        User::create([
            'username'      => 'pemilik',
            'email'         => 'pemilik@test.com',
            'password_hash' => Hash::make('password123'),
            'role'          => 'pemilik',
            'nama_lengkap'  => 'Test Pemilik',
            'is_active'     => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'pemilik',
            'password' => 'password123',
            'role'     => 'pemilik',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['token', 'user']]);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        User::create([
            'username'      => 'kasir1',
            'email'         => 'kasir1@test.com',
            'password_hash' => Hash::make('password123'),
            'role'          => 'kasir',
            'nama_lengkap'  => 'Test Kasir',
            'is_active'     => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'kasir1',
            'password' => 'wrongpassword',
            'role'     => 'kasir',
        ]);

        $response->assertStatus(422);
    }

    public function test_kasir_tidak_bisa_login_sebagai_pemilik(): void
    {
        User::create([
            'username'      => 'kasir1',
            'email'         => 'kasir1@test.com',
            'password_hash' => Hash::make('password123'),
            'role'          => 'kasir',
            'nama_lengkap'  => 'Test Kasir',
            'is_active'     => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'username' => 'kasir1',
            'password' => 'password123',
            'role'     => 'pemilik', // role salah
        ]);

        $response->assertStatus(422);
    }
}
