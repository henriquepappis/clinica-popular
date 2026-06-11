<?php

namespace App\Domain\Auth\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;

class LoginData extends Data
{
    public function __construct(
        #[Required, Email]
        public string $email,

        #[Required]
        public string $password,
    ) {}
}
