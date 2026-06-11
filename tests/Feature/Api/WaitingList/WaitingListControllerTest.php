<?php

namespace Tests\Feature\Api\WaitingList;

use App\Domain\Auth\Models\User;
use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\Patient\Models\Patient;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;

class WaitingListControllerTest extends TestCase
{
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        WaitingList::truncate();
        Patient::truncate();
        Specialty::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_index_returns_waiting_lists(): void
    {
        WaitingList::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/waiting-lists');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'patient', 'specialty', 'priority', 'status', 'added_at'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_specialty_and_status(): void
    {
        $specialty = Specialty::factory()->create();
        WaitingList::factory()->create(['specialty_id' => $specialty->id]);
        WaitingList::factory()->notified()->create(['specialty_id' => $specialty->id]);
        WaitingList::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/waiting-lists?specialty_id=$specialty->id&status=waiting");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_store_registers_interest(): void
    {
        $patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/waiting-lists', [
                'patient_id' => $patient->id,
                'specialty_id' => $specialty->id,
                'priority' => 2,
                'reason' => 'Paciente em espera por consulta',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Interesse registrado com sucesso.',
                'data' => [
                    'patient' => $patient->name,
                    'specialty' => $specialty->name,
                    'priority' => 2,
                    'status' => 'waiting',
                ],
            ]);

        $this->assertDatabaseHas('waiting_lists', [
            'patient_id' => $patient->id,
            'specialty_id' => $specialty->id,
        ]);
    }

    public function test_store_prevents_duplicate_interest(): void
    {
        $waiting_list = WaitingList::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/waiting-lists', [
                'patient_id' => $waiting_list->patient_id,
                'specialty_id' => $waiting_list->specialty_id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Paciente já está na fila de espera para esta especialidade.',
            ]);
    }

    public function test_store_requires_existing_patient(): void
    {
        $specialty = Specialty::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/waiting-lists', [
                'patient_id' => '00000000-0000-0000-0000-000000000000',
                'specialty_id' => $specialty->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['patient_id']);
    }

    public function test_show_returns_404_for_nonexistent_waiting_list(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/waiting-lists/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_waiting_list(): void
    {
        $waiting_list = WaitingList::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/waiting-lists/$waiting_list->id");

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['id' => $waiting_list->id],
            ]);
    }

    public function test_notify_marks_patient_as_notified(): void
    {
        $waiting_list = WaitingList::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/waiting-lists/$waiting_list->id/notify");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Paciente notificado com sucesso.',
                'data' => ['status' => 'notified'],
            ]);

        $this->assertDatabaseHas('waiting_lists', [
            'id' => $waiting_list->id,
            'status' => 'notified',
        ]);
    }

    public function test_destroy_cancels_waiting_list(): void
    {
        $waiting_list = WaitingList::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/waiting-lists/$waiting_list->id", [
                'reason' => 'Paciente desistiu',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Paciente removido da fila de espera.',
            ]);

        $this->assertDatabaseHas('waiting_lists', [
            'id' => $waiting_list->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/waiting-lists', []);

        $response->assertStatus(401);
    }
}
