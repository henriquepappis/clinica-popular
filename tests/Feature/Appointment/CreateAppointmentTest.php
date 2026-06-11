<?php

namespace Tests\Feature\Appointment;

use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\Actions\CreateAppointmentAction;
use App\Domain\Appointment\Actions\ConfirmAppointmentAction;
use App\Domain\Appointment\Actions\CancelAppointmentAction;
use App\Domain\Appointment\DataTransferObjects\AppointmentData;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Appointment\Exceptions\ShiftFullException;
use App\Domain\Appointment\Exceptions\DoctorUnavailableException;
use App\Domain\Patient\Models\Patient;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Shift\Models\Shift;
use App\Domain\Specialty\Models\Specialty;
use Carbon\Carbon;
use Tests\TestCase;

class CreateAppointmentTest extends TestCase
{
    private CreateAppointmentAction $createAction;
    private ConfirmAppointmentAction $confirmAction;
    private CancelAppointmentAction $cancelAction;

    protected function setUp(): void
    {
        parent::setUp();
        Appointment::truncate();
        Patient::truncate();
        Doctor::truncate();
        Shift::truncate();
        Specialty::truncate();

        $this->createAction = app(CreateAppointmentAction::class);
        $this->confirmAction = app(ConfirmAppointmentAction::class);
        $this->cancelAction = app(CancelAppointmentAction::class);
    }

    public function test_can_create_appointment(): void
    {
        $patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();
        $shift = Shift::factory()->create();

        $data = new AppointmentData(
            patientId: $patient->id,
            doctorId: $doctor->id,
            shiftId: $shift->id,
            appointmentDate: Carbon::tomorrow()->format('Y-m-d'),
            appointmentTime: '10:00',
            notes: 'Primeira consulta'
        );

        $appointment = $this->createAction->execute($data);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertEquals(AppointmentStatus::SCHEDULED, $appointment->status);
        $this->assertEquals($patient->id, $appointment->patient_id);
        $this->assertEquals($doctor->id, $appointment->doctor_id);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
    }

    public function test_cannot_create_appointment_for_same_doctor_same_time(): void
    {
        $patient = Patient::factory()->create();
        $patient2 = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();
        $shift = Shift::factory()->create();
        $date = Carbon::tomorrow()->format('Y-m-d');
        $time = '10:00';

        // Primeiro agendamento
        $data1 = new AppointmentData(
            patientId: $patient->id,
            doctorId: $doctor->id,
            shiftId: $shift->id,
            appointmentDate: $date,
            appointmentTime: $time,
        );
        $this->createAction->execute($data1);

        // Segundo agendamento no mesmo horário
        $data2 = new AppointmentData(
            patientId: $patient2->id,
            doctorId: $doctor->id,
            shiftId: $shift->id,
            appointmentDate: $date,
            appointmentTime: $time,
        );

        $this->expectException(DoctorUnavailableException::class);
        $this->createAction->execute($data2);
    }

    public function test_cannot_create_appointment_when_shift_is_full(): void
    {
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();
        $shift = Shift::factory()->create(['max_appointments' => 1]);
        $date = Carbon::tomorrow()->format('Y-m-d');

        // Preencher o turno
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'shift_id' => $shift->id,
            'appointment_date' => $date,
        ]);

        // Tentar criar outro
        $patient = Patient::factory()->create();
        $data = new AppointmentData(
            patientId: $patient->id,
            doctorId: $doctor->id,
            shiftId: $shift->id,
            appointmentDate: $date,
            appointmentTime: '11:00',
        );

        $this->expectException(ShiftFullException::class);
        $this->createAction->execute($data);
    }

    public function test_appointment_status_is_scheduled_by_default(): void
    {
        $patient = Patient::factory()->create();
        $specialty = Specialty::factory()->create();
        $doctor = Doctor::factory()->withSpecialties($specialty)->create();
        $shift = Shift::factory()->create();

        $data = new AppointmentData(
            patientId: $patient->id,
            doctorId: $doctor->id,
            shiftId: $shift->id,
            appointmentDate: Carbon::tomorrow()->format('Y-m-d'),
            appointmentTime: '10:00',
        );

        $appointment = $this->createAction->execute($data);

        $this->assertEquals(AppointmentStatus::SCHEDULED, $appointment->status);
    }

    public function test_can_confirm_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $confirmed = $this->confirmAction->execute($appointment->id);

        $this->assertEquals(AppointmentStatus::CONFIRMED, $confirmed->status);
    }

    public function test_can_cancel_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $cancelled = $this->cancelAction->execute($appointment->id, 'Paciente cancelou');

        $this->assertEquals(AppointmentStatus::CANCELLED, $cancelled->status);
        $this->assertEquals('Paciente cancelou', $cancelled->cancellation_reason);
    }

    public function test_can_list_active_appointments(): void
    {
        Appointment::factory()->create(['status' => AppointmentStatus::SCHEDULED]);
        Appointment::factory()->create(['status' => AppointmentStatus::CANCELLED]);

        $active = Appointment::active()->get();

        $this->assertEquals(1, $active->count());
    }
}
