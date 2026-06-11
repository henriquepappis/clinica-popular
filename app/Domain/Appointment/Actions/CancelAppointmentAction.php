<?php

namespace App\Domain\Appointment\Actions;

use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Appointment\Events\AppointmentCancelled;
use App\Domain\Appointment\Exceptions\AppointmentNotFoundException;

class CancelAppointmentAction
{
    public function execute(string $appointmentId, string $reason = null): Appointment
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            throw new AppointmentNotFoundException();
        }

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        event(new AppointmentCancelled($appointment));

        return $appointment;
    }
}