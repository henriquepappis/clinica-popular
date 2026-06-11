<?php

namespace Tests\Feature\Api\Auth;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Enums\UserRole;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::truncate();
    }

    public function test_register_creates_user(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'role' => 'patient',
            'phone' => '11999999999',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
            ])
            ->assertJson([
                'message' => 'Usuário registrado com sucesso.',
                'user' => [
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                    'role' => 'Paciente',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'name' => 'João Silva',
        ]);
    }

    public function test_register_requires_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'João Silva',
            'password' => 'password123',
            'role' => 'patient',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_prevents_duplicate_email(): void
    {
        User::factory()->create(['email' => 'joao@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'role' => 'patient',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'joao@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ])
            ->assertJson([
                'message' => 'Login realizado com sucesso.',
                'user' => [
                    'name' => $user->name,
                    'email' => 'joao@example.com',
                ],
            ]);
    }

    public function test_login_requires_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'joao@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'joao@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas.',
            ]);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role', 'phone', 'is_active'],
            ])
            ->assertJson([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_deletes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso.',
            ]);
    }
}
