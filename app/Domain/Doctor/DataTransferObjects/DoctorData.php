<?php

namespace App\Domain\Doctor\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Regex;

class DoctorData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $name,

        #[Required, Regex('/^\d{4,6}$/')] // CRM tem 4-6 dígitos
        public string $crm,

        /** @var string[] $specialtyIds */
        #[Required]
        public array $specialtyIds,

        #[Email]
        public ?string $email = null,

        #[Regex('/^\d{10,11}$/')]
        public ?string $phone = null,

        public ?string $bio = null,
    ) {}
}