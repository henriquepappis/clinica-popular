<?php

namespace Tests\Feature\Api\Patient;

use App\Domain\Auth\Models\User;
use App\Domain\Patient\Models\Patient;
use Tests\TestCase;
use Carbon\Carbon;

class PatientControllerTest extends TestCase
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Patient::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    private function generateValidCpf(): string
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = rand(0, 9);

        $d1 = ($n1 * 10 + $n2 * 9 + $n3 * 8 + $n4 * 7 + $n5 * 6 + $n6 * 5 + $n7 * 4 + $n8 * 3 + $n9 * 2) % 11;
        $d1 = $d1 < 2 ? 0 : 11 - $d1;

        $d2 = ($n1 * 11 + $n2 * 10 + $n3 * 9 + $n4 * 8 + $n5 * 7 + $n6 * 6 + $n7 * 5 + $n8 * 4 + $n9 * 3 + $d1 * 2) % 11;
        $d2 = $d2 < 2 ? 0 : 11 - $d2;

        return "$n1$n2$n3$n4$n5$n6$n7$n8$n9$d1$d2";
    }

    public function test_index_returns_patients(): void
    {
        Patient::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/patients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'cpf', 'phone', 'status'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_patient(): void
    {
        $cpf = $this->generateValidCpf();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/patients', [
                'name' => 'Maria Silva',
                'cpf' => $cpf,
                'birth_date' => '1990-05-15',
                'phone' => '11999999999',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'cpf', 'phone', 'status'],
            ])
            ->assertJson([
                'message' => 'Paciente criado com sucesso.',
                'data' => [
                    'name' => 'Maria Silva',
                    'cpf' => $cpf,
                ],
            ]);

        $this->assertDatabaseHas('patients', [
            'cpf' => $cpf,
            'name' => 'Maria Silva',
        ]);
    }

    public function test_store_requires_authentication(): void
    {
        $cpf = $this->generateValidCpf();

        $response = $this->postJson('/api/patients', [
            'name' => 'Maria Silva',
            'cpf' => $cpf,
            'birth_date' => '1990-05-15',
            'phone' => '11999999999',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/patients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'cpf', 'birth_date']);
    }

    public function test_store_prevents_duplicate_cpf(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/patients', [
                'name' => 'Maria Silva',
                'cpf' => $patient->cpf,
                'birth_date' => '1990-05-15',
                'phone' => '11999999999',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_show_returns_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/patients/$patient->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'cpf', 'birth_date', 'phone', 'status'],
            ])
            ->assertJson([
                'data' => [
                    'name' => $patient->name,
                    'cpf' => $patient->cpf,
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_patient(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/patients/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_update_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->patchJson("/api/patients/$patient->id", [
                'name' => 'Maria Silva Atualizado',
                'phone' => '11988888888',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Paciente atualizado com sucesso.',
            ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Maria Silva Atualizado',
        ]);
    }

    public function test_destroy_deactivates_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/patients/$patient->id");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Paciente desativado com sucesso.',
            ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'status' => 'inactive',
        ]);
    }
}
