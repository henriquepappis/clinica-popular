<?php

namespace App\Domain\Appointment\Actions;

use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\DataTransferObjects\AppointmentData;
use App\Domain\Appointment\Events\AppointmentCreated;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Appointment\Exceptions\ShiftFullException;
use App\Domain\Appointment\Exceptions\DoctorUnavailableException;
use App\Domain\Patient\Models\Patient;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Shift\Models\Shift;
use Illuminate\Support\Str;

class CreateAppointmentAction
{
    public function execute(AppointmentData $data): Appointment
    {
        // Validar se paciente existe
        $patient = Patient::find($data->patientId);
        if (!$patient) {
            throw new \Exception('Paciente não encontrado.');
        }

        // Validar se médico existe
        $doctor = Doctor::find($data->doctorId);
        if (!$doctor) {
            throw new \Exception('Médico não encontrado.');
        }

        // Validar se turno existe
        $shift = Shift::find($data->shiftId);
        if (!$shift) {
            throw new \Exception('Turno não encontrado.');
        }

        // Verificar se médico já tem agendamento neste horário
        $existing = Appointment::where('doctor_id', $data->doctorId)
            ->where('appointment_date', $data->appointmentDate)
            ->where('appointment_time', $data->appointmentTime)
            ->active()
            ->exists();

        if ($existing) {
            throw new DoctorUnavailableException(
                'Médico não está disponível neste horário.'
            );
        }

        // Verificar se turno está cheio
        $appointmentsInShift = Appointment::where('shift_id', $data->shiftId)
            ->where('appointment_date', $data->appointmentDate)
            ->active()
            ->count();

        if ($appointmentsInShift >= $shift->max_appointments) {
            throw new ShiftFullException(
                'Este turno está com capacidade máxima.'
            );
        }

        // Criar agendamento
        $appointment = Appointment::create([
            'patient_id' => $data->patientId,
            'doctor_id' => $data->doctorId,
            'shift_id' => $data->shiftId,
            'appointment_date' => $data->appointmentDate,
            'appointment_time' => $data->appointmentTime,
            'notes' => $data->notes,
            'status' => AppointmentStatus::SCHEDULED,
        ]);

        event(new AppointmentCreated($appointment));

        return $appointment;
    }
}