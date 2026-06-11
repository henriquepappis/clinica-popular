<?php

namespace Tests\Feature\Api\Doctor;

use App\Domain\Auth\Models\User;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
{
    protected User $user;
    protected string $token;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();
        Doctor::truncate();
        Specialty::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
        $this->specialty = Specialty::factory()->create();
    }

    public function test_index_returns_doctors(): void
    {
        Doctor::factory()->count(2)->withSpecialties($this->specialty)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/doctors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'crm', 'specialties', 'email', 'phone', 'status'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_doctor(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/doctors', [
                'name' => 'Dr. Carlos Silva',
                'crm' => '123456',
                'specialty_ids' => [$this->specialty->id],
                'email' => 'carlos@example.com',
                'phone' => '11999999999',
                'bio' => 'Médico experiente',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'crm', 'specialties', 'status'],
            ])
            ->assertJson([
                'message' => 'Médico criado com sucesso.',
                'data' => [
                    'name' => 'Dr. Carlos Silva',
                    'crm' => '123456',
                ],
            ]);

        $this->assertDatabaseHas('doctors', [
            'crm' => '123456',
            'name' => 'Dr. Carlos Silva',
        ]);
    }

    public function test_store_requires_valid_specialty(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/doctors', [
                'name' => 'Dr. Carlos Silva',
                'crm' => '123456',
                'specialty_ids' => ['00000000-0000-0000-0000-000000000000'],
                'email' => 'carlos@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Especialidade não encontrada.',
            ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/doctors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'crm', 'specialty_ids']);
    }

    public function test_show_returns_404_for_nonexistent_doctor(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/doctors/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_doctor(): void
    {
        $doctor = Doctor::factory()->withSpecialties($this->specialty)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/doctors/$doctor->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'crm', 'specialties', 'email', 'phone', 'bio', 'status'],
            ])
            ->assertJson([
                'data' => [
                    'name' => $doctor->name,
                    'crm' => $doctor->crm,
                ],
            ]);
    }

    public function test_update_doctor(): void
    {
        $doctor = Doctor::factory()->withSpecialties($this->specialty)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->patchJson("/api/doctors/$doctor->id", [
                'email' => 'newemail@example.com',
                'phone' => '11988888888',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Médico atualizado com sucesso.',
            ]);
    }

    public function test_destroy_deactivates_doctor(): void
    {
        $doctor = Doctor::factory()->withSpecialties($this->specialty)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/doctors/$doctor->id");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Médico desativado com sucesso.',
            ]);

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'status' => 'inactive',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/doctors', []);

        $response->assertStatus(401);
    }
}
