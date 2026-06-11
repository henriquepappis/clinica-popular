<?php

namespace App\Domain\Appointment\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\UUID;

class AppointmentData extends Data
{
    public function __construct(
        #[Required, UUID]
        public string $patientId,

        #[Required, UUID]
        public string $doctorId,

        #[Required, UUID]
        public string $shiftId,

        #[Required, DateFormat('Y-m-d')]
        public string $appointmentDate,

        #[Required, DateFormat('H:i')]
        public string $appointmentTime,

        public ?string $notes = null,
    ) {}
}