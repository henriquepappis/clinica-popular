<?php

namespace App\Domain\Appointment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Appointment\Models\Appointment;

class AppointmentCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}
}