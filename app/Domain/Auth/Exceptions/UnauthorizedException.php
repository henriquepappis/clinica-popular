<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct(string $message = 'Não autorizado.')
    {
        parent::__construct($message);
    }
}
