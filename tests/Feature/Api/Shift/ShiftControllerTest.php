<?php

namespace Tests\Feature\Api\Shift;

use App\Domain\Auth\Models\User;
use App\Domain\Shift\Models\Shift;
use Tests\TestCase;

class ShiftControllerTest extends TestCase
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Shift::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_index_returns_active_shifts(): void
    {
        Shift::factory()->create(['status' => 'active']);
        Shift::factory()->create(['status' => 'inactive']);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/shifts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'period', 'start_time', 'end_time', 'max_appointments', 'status'],
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_store_creates_shift(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/shifts', [
                'name' => 'Turno da Madrugada',
                'period' => 'evening',
                'start_time' => '22:00',
                'end_time' => '23:00',
                'max_appointments' => 15,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'period', 'status'],
            ])
            ->assertJson([
                'message' => 'Turno criado com sucesso.',
                'data' => [
                    'name' => 'Turno da Madrugada',
                ],
            ]);
    }

    public function test_store_validates_period(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/shifts', [
                'name' => 'Turno Inválido',
                'period' => 'madrugada',
                'start_time' => '00:00',
                'end_time' => '06:00',
                'max_appointments' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['period']);
    }

    public function test_show_returns_404_for_nonexistent_shift(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/shifts/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_shift(): void
    {
        $shift = Shift::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/shifts/$shift->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'period', 'start_time', 'end_time', 'max_appointments', 'status'],
            ])
            ->assertJson([
                'data' => [
                    'name' => $shift->name,
                ],
            ]);
    }

    public function test_update_shift(): void
    {
        $shift = Shift::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->patchJson("/api/shifts/$shift->id", [
                'max_appointments' => 25,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Turno atualizado com sucesso.',
            ]);
    }

    public function test_destroy_deactivates_shift(): void
    {
        $shift = Shift::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/shifts/$shift->id");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Turno desativado com sucesso.',
            ]);

        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'status' => 'inactive',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/shifts', []);

        $response->assertStatus(401);
    }
}
