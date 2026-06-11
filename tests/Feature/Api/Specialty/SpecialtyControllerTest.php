<?php

namespace Tests\Feature\Api\Specialty;

use App\Domain\Auth\Models\User;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class SpecialtyControllerTest extends TestCase
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Specialty::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_index_returns_active_specialties(): void
    {
        Specialty::factory()->create(['status' => 'active', 'name' => 'Cardiologia ' . uniqid()]);
        Specialty::factory()->create(['status' => 'inactive', 'name' => 'Dermatologia ' . uniqid()]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/specialties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'status'],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_store_creates_specialty(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/specialties', [
                'name' => 'Oftalmologia ' . uniqid(),
                'description' => 'Especialidade dos olhos',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'description', 'status'],
            ])
            ->assertJson([
                'message' => 'Especialidade criada com sucesso.',
            ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/specialties', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_404_for_nonexistent_specialty(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/specialties/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_specialty(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/specialties/$specialty->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description', 'status'],
            ])
            ->assertJson([
                'data' => [
                    'name' => $specialty->name,
                ],
            ]);
    }

    public function test_update_specialty(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->patchJson("/api/specialties/$specialty->id", [
                'description' => 'Descrição atualizada',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Especialidade atualizada com sucesso.',
            ]);
    }

    public function test_destroy_deactivates_specialty(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/specialties/$specialty->id");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Especialidade desativada com sucesso.',
            ]);

        $this->assertDatabaseHas('specialties', [
            'id' => $specialty->id,
            'status' => 'inactive',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/specialties', []);

        $response->assertStatus(401);
    }
}
