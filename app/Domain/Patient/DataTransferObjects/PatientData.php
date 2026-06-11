<?php

namespace App\Domain\Patient\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Carbon\Carbon;

class PatientData extends Data
{
    public function __construct(
        #[Required, Regex('/^[a-zA-Záéíóúàâêôãõçñ\s]+$/i')]
        public string $name,

        #[Required, Regex('/^\d{11}$/')]
        public string $cpf,

        #[Required]
        public Carbon $birthDate,

        #[Required, Regex('/^\d{10,11}$/')]
        public string $phone,
    ) {}
}