<?php

namespace Tests\Feature\Api\Price;

use App\Domain\Auth\Models\User;
use App\Domain\Price\Models\Price;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class PriceControllerTest extends TestCase
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Price::truncate();
        Doctor::truncate();
        Specialty::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_index_returns_prices(): void
    {
        Price::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/prices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'doctor', 'specialty', 'duration_minutes', 'value'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_specialty(): void
    {
        $specialty = Specialty::factory()->create();
        Price::factory()->create(['specialty_id' => $specialty->id]);
        Price::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/prices?specialty_id=$specialty->id");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_store_creates_price_for_specialty(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/prices', [
                'value' => 150.00,
                'specialty_id' => $specialty->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'type', 'specialty', 'value'],
            ])
            ->assertJson([
                'message' => 'Preço criado com sucesso.',
                'data' => [
                    'specialty' => $specialty->name,
                ],
            ]);

        $this->assertDatabaseHas('prices', [
            'specialty_id' => $specialty->id,
            'value' => 150.00,
        ]);
    }

    public function test_store_creates_price_for_doctor(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/prices', [
                'value' => 200.00,
                'doctor_id' => $doctor->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('prices', [
            'doctor_id' => $doctor->id,
            'value' => 200.00,
        ]);
    }

    public function test_store_requires_valid_value(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/prices', [
                'value' => -10,
                'specialty_id' => $specialty->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    public function test_store_requires_price_target(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/prices', [
                'value' => 100.00,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Informe médico, especialidade ou duração para configurar o preço.',
            ]);
    }

    public function test_store_prevents_duplicate_config(): void
    {
        $specialty = Specialty::factory()->create();
        Price::factory()->create(['specialty_id' => $specialty->id]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/prices', [
                'value' => 150.00,
                'specialty_id' => $specialty->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Já existe um preço configurado para esta combinação.',
            ]);
    }

    public function test_show_returns_404_for_nonexistent_price(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/prices/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_price(): void
    {
        $price = Price::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/prices/$price->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'type', 'doctor', 'specialty', 'duration_minutes', 'value'],
            ])
            ->assertJson([
                'data' => ['id' => $price->id],
            ]);
    }

    public function test_update_price(): void
    {
        $price = Price::factory()->create(['value' => 100.00]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->patchJson("/api/prices/$price->id", [
                'value' => 180.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preço atualizado com sucesso.',
            ]);

        $this->assertDatabaseHas('prices', [
            'id' => $price->id,
            'value' => 180.00,
        ]);
    }

    public function test_destroy_deletes_price(): void
    {
        $price = Price::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/prices/$price->id");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preço removido com sucesso.',
            ]);

        $this->assertDatabaseMissing('prices', [
            'id' => $price->id,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/prices', []);

        $response->assertStatus(401);
    }
}
