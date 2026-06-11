<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Credenciais inválidas.')
    {
        parent::__construct($message);
    }
}
