<?php

namespace App\Domain\Appointment\Actions;

use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Appointment\Events\AppointmentConfirmed;
use App\Domain\Appointment\Exceptions\AppointmentNotFoundException;

class ConfirmAppointmentAction
{
    public function execute(string $appointmentId): Appointment
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            throw new AppointmentNotFoundException();
        }

        $appointment->update(['status' => AppointmentStatus::CONFIRMED]);

        event(new AppointmentConfirmed($appointment));

        return $appointment;
    }
}