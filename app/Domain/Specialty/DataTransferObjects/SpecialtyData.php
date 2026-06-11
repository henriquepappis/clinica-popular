<?php

namespace App\Domain\Specialty\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\MaxLength;

class SpecialtyData extends Data
{
    public function __construct(
        #[Required, StringType, MaxLength(100)]
        public string $name,

        #[MaxLength(500)]
        public ?string $description = null,
    ) {}
}