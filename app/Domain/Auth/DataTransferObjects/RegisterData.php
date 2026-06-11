<?php

namespace App\Domain\Auth\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\MinLength;

class RegisterData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, MinLength(6)]
        public string $password,

        #[Required]
        public string $role, // admin, doctor, receptionist, patient

        public ?string $phone = null,
    ) {}
}
