<?php

namespace Tests\Feature\Api\Appointment;

use App\Domain\Auth\Models\User;
use App\Domain\Appointment\Models\Appointment;
use App\Domain\Patient\Models\Patient;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Shift\Models\Shift;
use App\Domain\Specialty\Models\Specialty;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentControllerTest extends TestCase
{
    protected User $user;
    protected string $token;
    protected Patient $patient;
    protected Doctor $doctor;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        Appointment::truncate();
        Patient::truncate();
        Doctor::truncate();
        Shift::truncate();
        Specialty::truncate();
        User::truncate();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;

        $this->patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $this->doctor = Doctor::factory()->withSpecialties($specialty)->create();
        $this->shift = Shift::factory()->create();
    }

    public function test_index_returns_appointments(): void
    {
        Appointment::factory()->count(2)->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'patient', 'doctor', 'appointment_date', 'appointment_time', 'status'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_appointment(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/appointments', [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'shift_id' => $this->shift->id,
                'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
                'appointment_time' => '10:00',
                'notes' => 'Consulta de rotina',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'patient', 'doctor', 'appointment_date', 'appointment_time', 'status'],
            ])
            ->assertJson([
                'message' => 'Agendamento criado com sucesso.',
            ]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);
    }

    public function test_store_prevents_double_booking(): void
    {
        Appointment::factory()->create([
            'patient_id' => Patient::factory(),
            'doctor_id' => $this->doctor->id,
            'shift_id' => $this->shift->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'appointment_time' => '10:00',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/appointments', [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'shift_id' => $this->shift->id,
                'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
                'appointment_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Médico não está disponível neste horário.',
            ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/appointments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'patient_id', 'doctor_id', 'shift_id', 'appointment_date', 'appointment_time',
            ]);
    }

    public function test_store_rejects_past_date(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/appointments', [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'shift_id' => $this->shift->id,
                'appointment_date' => Carbon::yesterday()->format('Y-m-d'),
                'appointment_time' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_date']);
    }

    public function test_show_returns_404_for_nonexistent_appointment(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/appointments/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    public function test_show_returns_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/appointments/$appointment->id");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'patient', 'doctor', 'appointment_date', 'appointment_time', 'notes', 'status'],
            ]);
    }

    public function test_confirm_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/appointments/$appointment->id/confirm");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status'],
            ])
            ->assertJson([
                'message' => 'Agendamento confirmado com sucesso.',
                'data' => [
                    'status' => 'Confirmado',
                ],
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_cancel_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/appointments/$appointment->id/cancel", [
                'reason' => 'Paciente cancelou',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status'],
            ])
            ->assertJson([
                'message' => 'Agendamento cancelado com sucesso.',
                'data' => [
                    'status' => 'Cancelado',
                ],
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Paciente cancelou',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(401);
    }
}
