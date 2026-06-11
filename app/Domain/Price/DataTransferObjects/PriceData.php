<?php

namespace App\Domain\Price\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Min;

class PriceData extends Data
{
    public function __construct(
        #[Required, Numeric, Min(0.01)]
        public float $value,

        public ?string $doctorId = null,

        public ?string $specialtyId = null,

        public ?int $durationMinutes = null,
    ) {}
}
