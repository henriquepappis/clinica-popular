<?php

namespace App\Domain\Shift\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\DateFormat;

class ShiftData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $name,

        #[Required]
        public string $period, // MORNING, AFTERNOON, EVENING

        #[Required, DateFormat('H:i')]
        public string $startTime,

        #[Required, DateFormat('H:i')]
        public string $endTime,

        #[Required]
        public int $maxAppointments = 20,
    ) {}
}