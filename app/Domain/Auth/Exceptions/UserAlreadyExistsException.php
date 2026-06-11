<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class UserAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'Usuário já existe.')
    {
        parent::__construct($message);
    }
}
